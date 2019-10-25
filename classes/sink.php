<?php

namespace Sink;

require_once __DIR__.'/options.php';
require_once __DIR__.'/updater.php';
require_once __DIR__.'/ui.php';
require_once __DIR__.'/../vendor/autoload.php';

use Aws\S3\S3Client;
use Sink\UI;
use Sink\Updater;
use Sink\Options;

/**
 * Main class of the plugin. Everything starts here
 */
class Sink
{
    /**
     * Defines plugin options and some properties for each in order to create
     * dynamically the options page
     */
    public $config_map = [];

    public $plugin_name = "sink";
    public $plugin_slug = 'sink/sink.php';
    public $github_user = 'caffeinalab';

    /**
     * Reference to the plugin's UI class
     */
    protected $ui;

    /**
     * Reference to the plugin's Options class
     */
    protected $options;

    /**
     * self instance
     */
    protected static $instance;

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
        if (null != self::$instance) {
            return self::$instance;
        }

        $this->options = new Options($this->plugin_name);
        $this->ui = new UI($this->plugin_name, $this->plugin_slug);

        if (is_admin()) {
            $updater = new Updater($this->plugin_slug, $this->plugin_name, $this->github_user, $this->plugin_name);
            $updater->bootUpdateService();
        }

        $options = $this->options->loadOptions();

        if (!$options) {
            $this->ui->renderNotice('notice-error', 'Plugin not loaded, missing configuration');
            return;
        }

        $this->config_map = $this->options->config_map;
        $this->fixUploadDir();

        return self::$instance = $this;
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
          'region'  => $this->config_map[0]['value'],
          'credentials' => [
            'key'    => $this->config_map[2]['value'],
            'secret' => $this->config_map[3]['value'],
          ]
        ];

        if (! empty($this->config_map[7]['value'])) {
            $config['http'] = [
              'proxy' => $this->config_map[7]['value'].':'.$this->config_map[8]['value'],
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
                die;
            }

            // Moved because it should always create the directory first.
            if (! empty($this->config_map[6]['value'])) {
                return $this->config_map[6]['value'];
            }

            if (true == $this->config_map[5]['value']) {
                return WP_SITEURL;
            }

            $result = $client->getObjectUrl($this->config_map[1]['value'], $key);
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
        $dir = "s3://".$this->config_map[1]['value']."/";
        $key = $this->config_map['4']['value'] ?: $this->default_uploads_folder;
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
}
