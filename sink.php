<?php
/**
 * Plugin Name: Sink
 * Description: Sync media to S3 seamlessly
 * Version:     1.0.0
 * Author:      Caffeina
 * Author URI:  https://caffeina.com/
 * Plugin URI:  https://github.com/caffeinalab/sink
 */

namespace Sink;

require 'updater.php';
require 'ui.php';
require 'vendor/autoload.php';

use Aws\S3\S3Client;
use Sink\UI;
use Sink\Updater;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

define( 'SINK_URL', plugin_dir_url( __FILE__ ) );
define( 'SINK_PATH', plugin_dir_path( __FILE__ ) );
define( 'SINK_VERSION', '1.0.0' );

/**
 * Main class of the plugin. Everything starts here
 */
class Sink
{
  /**
   * Defines plugin options and some properties for each in order to create
   * dynamically the options page
   */
  public $configMap = [
    'aws_region' => ['type' => 'string', 'placeholder' => 'eu-west-1', 'title' => 'AWS Region'],
    'aws_bucket' => ['type' => 'string', 'placeholder' => 'caffeina', 'title' => 'AWS Bucket'],
    'aws_access_id' => ['type' => 'string', 'placeholder' => '', 'title' => 'AWS Access Key ID'],
    'aws_secret' => ['type' => 'string', 'placeholder' => '', 'title' => 'AWS Secret', 'password' => true],
    'aws_uploads_path' => ['type' => 'string', 'placeholder' => '', 'title' => 'AWS uploads path'],
    'keep_site_domain' => ['type' => 'boolean', 'placeholder' => '', 'title' => 'Do not override website domain.'],
    'cdn_endpoint' => ['type' => 'string', 'placeholder' => '', 'title' => 'Imgix or Cloudfront URL (Leave empty to use S3 default domain)'],
    'http_proxy_url' => ['type' => 'string', 'placeholder' => 'http://127.0.0.1', 'title' => 'HTTP Proxy URL'],
    'http_proxy_port' => ['type' => 'number', 'placeholder' => '8080', 'title' => 'HTTP Proxy port'],
  ];

  public $plugin_name = "sink";
  public $plugin_slug = 'sink/sink.php';
  public $github_user = 'caffeinalab';

  /**
   * Reference to the plugin's UI class
   */
  protected $ui;

  /**
   * Default property. Required by S3.
   */
  protected $default_uploads_folder = 'uploads';

  /**
   * Construct
   * @return Sink instance of Sink
   */
  public function __construct()
  {
    // Boot of Sink
    if (is_admin()) {
        add_action('admin_init', [$this, 'registerSettings']);
        add_action('admin_init', [$this, 'checkWPConfig']);

        $this->ui = new UI($this->plugin_name);
        $updater = new Updater(
          $this->plugin_slug,
          $this->plugin_name,
          $this->github_user,
          $this->plugin_name
        );

        $updater->bootUpdateService();
        $this->initLogic();
    }

    $this->fixUploadDir();
  }

  /**
   * Loops through config options to save them on the database
   * @uses `register_setting` wp global function
   * @return void
   */
  public function registerSettings()
  {
    foreach($this->configMap as $config => $type) {
      register_setting(
        $this->plugin_name.'_options',
        $config, ['type' => $type['type']]
      );
    }
  }

  /**
   * Checks whether the config options are set through wp-config.php
   * Loops through them all
   * @uses `get_option`
   * @uses `update_option`
   * @uses UI
   * @return void
   */
  public function checkWPConfig()
  {
      $loaded = false;

      foreach ($this->configMap as $config => $type) {
          if (get_option($config) == false && defined(strtoupper($this->plugin_name.$config))) {
              update_option($config, constant(strtoupper($this->plugin_name.$config)));
              $loaded = true;
          }
      }

      if ($loaded) {
          $this->ui->renderNotice(
            'notice-info',
            'I have loaded the config from wp-config. It will not overwrite the settings if set from the options page'
          );
      }
  }

  /**
   * Builds an S3 client
   * @uses Aws\S3\S3Client
   * @uses `get_option`
   * @return S3Client object that represents a connection to AWS S3
   */
  private function getS3Client()
  {
    $config = [
      'version' => 'latest',
      'region'  => get_option('aws_region'),
      'credentials' => [
        'key'    => get_option('aws_access_id'),
        'secret' => get_option('aws_secret'),
      ]
    ];

    if(null != get_option('http_proxy_url')) {
      $config['http'] = [
        'proxy' => get_option('http_proxy_url').':'.get_option('http_proxy_port'),
      ];
    }

    return new S3Client($config);
  }

  /**
   * Enables using `s3://` protocol to be able to use S3 paths
   * as filesystem dirs.
   * @uses self::getS3Client
   * @return Aws\S3\S3Client object that represents a connection to AWS S3
   */
  public function registerS3StreamWrapper()
  {
    // Instantiate an Amazon S3 client.
    $client = $this->getS3Client();
    // Register the stream wrapper from an S3Client object
    $client->registerStreamWrapper();
    return $client;
  }

  /**
   * Handles the creation of a default directory in the Bucket
   * @uses `get_option`
   * @uses Aws\S3\S3Client
   * @uses UI
   * @return String domain name to the Bucket or CDN
   */
  public function createDefaultDir($dir, $key)
  {
    // Instantiate an Amazon S3 client.
    $client = $this->registerS3StreamWrapper();

    try {
      if (!file_exists($dir.$key)) {
        mkdir($dir.$key);
      }

      // Moved because it should always create the directory first.
      if (null != get_option('cdn_endpoint')) {
          return get_option('cdn_endpoint');
      }

      if (true == get_option('keep_site_domain')) {
          return WP_SITEURL;
      }

      $result = $client->getObjectUrl(get_option('aws_bucket'), $key);
    } catch (\Exception $e) {
      if (is_admin()) {
        $this->ui->renderNotice('notice-error', 'There was an error while configuring S3');
      }
    }
    return $result;
  }

  /**
   * Configures WP upload_dir to use our own settings instead of those calculated at runtime
   * @uses Aws\S3\S3Client
   * @uses `get_option`
   * @uses self::createDefaultDir
   * @return Array $uploads, contains an associative array as WP expects it
   */
  public function setUploadDir($uploads)
  {
    // Instantiate an Amazon S3 client.
    $client = $this->registerS3StreamWrapper();

    $dir = "s3://".get_option('aws_bucket')."/";
    $key = get_option('aws_uploads_path') ? get_option('aws_uploads_path') : $this->default_uploads_folder;
    $result = $this->createDefaultDir($dir, $key);

    $uploads = array_merge(
      $uploads,
      [
        'path' => $dir.$key.$uploads['subdir'],
        'url' => $result.$uploads['subdir'],
        'subdir' => $key.$uploads['subdir'],
        'baseurl' => $result,
        'basedir' => $dir.$key
      ]
    );

    return $uploads;
  }

  /**
   * Adds a filter to update upload_dir
   * @uses `add_filter`
   * @uses self::setUploadDir
   * @return void
   */
  public function fixUploadDir()
  {
    add_filter('upload_dir', [$this, 'setUploadDir']);
  }


  // Unused methods

  function deleteMedia($id)
  {
      $this->registerS3StreamWrapper();

      $meta = get_post_meta($id);
      $metadata = maybe_unserialize($meta['_wp_attachment_metadata'][0]);
      $basename = basename($metadata['file']);
      $path = str_replace($basename, '', $metadata['file']);

      try {
          unlink($metadata['file']);
      } catch (\Exception $e) {
          $this->ui->renderNotice('notice-error', $e->getMessage);
      }

      foreach ($metadata['sizes'] as $thumbnail) {
          unlink($path.$thumbnail['file']);
      }
  }

  public function handleDelete()
  {
    add_action('delete_attachment', [$this, 'deleteMedia']);
  }

  public function processMetadata($metadata, $post)
  {
    // Upload of single resized files can happen here.
    // If using AWS S3 stream, no need to implement here.
    return array($metadata, $post);
  }

  public function processResizing($payload, $orig_w, $orig_h, $dest_w, $dest_h, $crop)
  {
    // Implement here any photo resizing. If using a service such as imgix,
    // this is not necessary and can be disabled
    return array($payload, $orig_w, $orig_h, $dest_w, $dest_h, $crop);
  }

  public function readMetadata($meta, $file, $sourceimagetype, $iptc)
  {
    // Do something with the image file exif information
    return array($meta, $file, $sourceimagetype, $iptc);
  }

  public function initLogic()
  {
    // two ways, set `upload_dir` correctly
    // or make changes on post_save (post-type: attachment)

    // if resize is true // removed this option
    if (get_option('resize_wordpress')) {

      // resize images with your own logic
      add_filter('image_resize_dimensions', [$this, 'processResizing'], 10, 6);

      // use this to upload resized files
      add_filter('wp_generate_attachment_metadata', [$this, 'processMetadata'], 10, 2);
      add_filter('wp_read_image_metadata', [$this, 'readMetadata'], 10,5);

      // upload files after resize
      // add_filter('pre_move_uploaded_file', [$this, 'uploadToS3'], 10, 4);
    } else {
      // upload original
      // add_filter('pre_move_uploaded_file', [$this, 'uploadToS3'], 10, 4);
    }
  }
}

(new Sink());
