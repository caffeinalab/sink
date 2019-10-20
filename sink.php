<?php
/**
 * Plugin Name: Sink
 * Description: Sync media to S3
 * Version:     0.0.1
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
define( 'SINK_VERSION', '0.0.1' );

class Sink
{
  public $configMap = [
    'aws_region' => ['type' => 'string', 'placeholder' => '', 'title' => 'AWS Region'],
    'aws_bucket' => ['type' => 'string', 'placeholder' => '', 'title' => 'AWS Bucket'],
    'aws_access_id' => ['type' => 'string', 'placeholder' => '', 'title' => 'AWS Access Key ID'],
    'aws_secret' => ['type' => 'string', 'placeholder' => '', 'title' => 'AWS Secret', 'password' => true],
    'aws_uploads_path' => ['type' => 'string', 'placeholder' => '', 'title' => 'AWS uploads path'],
    'delete_original' => ['type' => 'boolean', 'placeholder' => '', 'title' => 'Delete original file after upload'],
    'retain_on_delete' => ['type' => 'boolean', 'placeholder' => '', 'title' => 'Keep after delete'],
    'resize_wordpress' => ['type' => 'boolean', 'placeholder' => '', 'title' => 'Upload WP resized files'],
    'cdn_endpoint' => ['type' => 'string', 'placeholder' => '', 'title' => 'Imgix or Cloudfront URL'],
    'http_proxy_url' => ['type' => 'string', 'placeholder' => '', 'title' => 'HTTP Proxy URL'],
    'http_proxy_port' => ['type' => 'number', 'placeholder' => '', 'title' => 'HTTP Proxy port'],
  ];

  public $plugin_name = "sink";
  public $plugin_slug = 'sink/sink.php';
  public $github_user = 'caffeinalab';
  protected $ui;
  protected $default_uploads_folder = 'uploads';

  public function __construct()
  {
    // Boot of Sink
    if (is_admin()) {
        add_action('admin_init', [$this, 'registerSettings']);
        add_action('admin_init', [$this, 'checkWPConfig']);

        $this->ui = new UI();
        $updater = new Updater(
          $this->plugin_slug,
          $this->plugin_name,
          $this->github_user,
          $this->plugin_name
        );

        $updater->bootUpdateService();
        $this->initLogic();
    }
  }

  public function registerSettings()
  {
    foreach($this->configMap as $config => $type) {
      register_setting(
        $this->plugin_name.'_options',
        $config, ['type' => $type['type']]
      );
    }
  }

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

  private function getS3Client()
  {
    return new S3Client([
        'version' => 'latest',
        'region'  => get_option('aws_region'),
        'credentials' => [
          'key'    => get_option('aws_access_id'),
          'secret' => get_option('aws_secret'),
        ]
    ]);
  }

  public function registerS3StreamWrapper()
  {
    // Instantiate an Amazon S3 client.
    $client = $this->getS3Client();
    // Register the stream wrapper from an S3Client object
    $client->registerStreamWrapper();
    return $client;
  }

  public function setUploadDir($uploads)
  {
    // Instantiate an Amazon S3 client.
    $client = $this->registerS3StreamWrapper();

    $dir = "s3://".get_option('aws_bucket')."/";
    $key = get_option('aws_uploads_path') ? get_option('aws_uploads_path') : $this->default_uploads_folder;

    try {

      if(!file_exists($dir.$key)) {
        mkdir($dir.$key);
      }

      $result = $client->getObjectUrl(get_option('aws_bucket'), $key);

    } catch(\Exception $e) {
      $this->ui->renderNotice(
        'notice-error',
        'There was an error while configuring S3'
      );
    }

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

  function deleteMedia($id)
  {
    $this->registerS3StreamWrapper();

    $meta = get_post_meta($id);
    $metadata = maybe_unserialize($meta['_wp_attachment_metadata'][0]);
    $basename = basename($metadata['file']);
    $path = str_replace($basename, '', $metadata['file']);

    try {
      unlink($metadata['file']);
    } catch(Exception $e) {
      $this->ui->renderNotice(
        'notice-error',
        $e->getMessage
      );
    }

    foreach($metadata['sizes'] as $thumbnail) {
      unlink($path.$thumbnail['file']);
    }
  }

  public function initLogic()
  {
    // two ways, update `upload_dir`
    // or make changes on post_save (post-type: attachment)

    // if resize is true
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

    add_action('delete_attachment', [$this, 'deleteMedia']);

    // set upload_dir path to update save path with s3 stream and
    add_filter('wp_handle_upload_prefilter', function($file) {
      add_filter('upload_dir', [$this, 'setUploadDir']);
      return $file;
    });

    add_filter('wp_handle_upload', function($fileinfo) {
      remove_filter('upload_dir', [$this, 'setUploadDir']);
      return $fileinfo;
    });
  }
}

(new Sink());
