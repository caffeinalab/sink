<?php

namespace Sink;

require_once __DIR__.'/options.php';
require_once __DIR__.'/updater.php';
require_once __DIR__.'/ui.php';
require_once __DIR__.'/../vendor/autoload.php';

use Aws\S3\S3Client;

/**
 * Main class of the plugin. Everything starts here.
 */
class Sink
{
    /**
     * Defines plugin options and some properties for each in order to create
     * dynamically the options page.
     */
    public $config_map = [];

    public $plugin_name = 'sink';
    public $plugin_slug = 'sink/sink.php';
    public $github_user = 'caffeinalab';

    /**
     * Reference to the plugin's UI class.
     */
    protected $ui;

    /**
     * Reference to the plugin's Options class.
     */
    protected $options;

    /**
     * self instance.
     */
    protected static $instance;

    /**
     * Default property. Required by S3.
     */
    protected $default_uploads_folder = 'uploads';

    public $uploads_original;
    public static $uploads_dir_local;

    public static function init()
    {
        return self::getInstance();
    }

    public static function getInstance()
    {
        $class = __CLASS__;
        if (self::$instance == null) {
            self::$instance = new $class();
        }

        return self::$instance;
    }

    /**
     * Construct.
     *
     * @return Sink instance of Sink
     */
    public function __construct()
    {
        self::$uploads_dir_local = self::$uploads_dir_local ? self::$uploads_dir_local : wp_get_upload_dir();
        $this->uploads_original = self::$uploads_dir_local;
        $this->options = Options::init($this->plugin_name);
        $this->ui = UI::init($this->plugin_name, $this->plugin_slug);

        if (is_admin()) {
            $updater = new Updater($this->plugin_slug, $this->plugin_name, $this->github_user, $this->plugin_name);
            $updater->bootUpdateService();
            add_action('wp_ajax_sink_transfer', [$this, 'handleTransferAjax']);
        }

        $options = $this->options->loadOptions();
        if (!$options) {
            $this->ui->renderNotice('notice-error', 'Plugin not loaded, missing configuration');

            return $this;
        }
        $this->config_map = $this->options->config_map;

        if ($this->containsLocalFiles()) {
            return $this;
        }
        $this->fixUploadDir();

        return $this;
    }

    /**
     * Builds an S3 client.
     *
     * @uses Aws\S3\S3Client
     * @uses `get_option`
     *
     * @return S3Client object that represents a connection to AWS S3
     */
    private function getS3Client()
    {
        $config = [
          'version' => 'latest',
          'region' => $this->config_map[0]['value'],
          'credentials' => [
            'key' => $this->config_map[2]['value'],
            'secret' => $this->config_map[3]['value'],
          ],
        ];

        if (!empty($this->config_map[7]['value'])) {
            $config['http'] = [
              'proxy' => $this->config_map[7]['value'].':'.$this->config_map[8]['value'],
            ];
        }

        return new S3Client($config);
    }

    /**
     * Enables using `s3://` protocol to be able to use S3 paths
     * as filesystem dirs.
     *
     * @uses self::getS3Client
     *
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
     * Handles the creation of a default directory in the Bucket.
     *
     * @uses `get_option`
     * @uses Aws\S3\S3Client
     * @uses UI
     *
     * @return string domain name to the Bucket or CDN
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
            if (!empty($this->config_map[6]['value'])) {
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
     * Configures WP upload_dir to use our own settings instead of those calculated at runtime.
     *
     * @uses Aws\S3\S3Client
     * @uses `get_option`
     * @uses self::createDefaultDir
     *
     * @return array $uploads, contains an associative array as WP expects it
     */
    public function setUploadDir($uploads)
    {
        if (!$this->isActive()) {
            $this->uploads_original = wp_get_upload_dir();

            return $this->uploads_original;
        }

        // Instantiate an Amazon S3 client.
        $client = $this->registerS3StreamWrapper();
        $dir = 's3://'.$this->config_map[1]['value'].'/';
        $key = $this->config_map['4']['value'] ?: $this->default_uploads_folder;
        $result = $this->createDefaultDir($dir, $key);

        $uploads = array_merge(
            $uploads,
            [
              'path' => $dir.$key.$uploads['subdir'],
              'url' => $result.$uploads['subdir'],
              'subdir' => $key.$uploads['subdir'],
              'baseurl' => $result,
              'basedir' => $dir.$key,
            ]
        );

        return $uploads;
    }

    public function isActive()
    {
        if (function_exists('is_plugin_active') && !\is_plugin_active($this->plugin_slug)) {
            return false;
        }

        if ($this->containsLocalFiles()) {
            return false;
        }

        return true;
    }

    public function containsLocalFiles()
    {
        $ignore_media = \get_option($this->plugin_name.'_ignore_existing_media');
        $files = $this->listLocalFiles();

        if (count($files) > 0 && $ignore_media != 1) {
            if (is_admin()) {
                $this->ui->renderNotice('notice-error',
                    'You have media files in the current uploads directory. '
                    .'Do you want to upload them to S3? '
                    .'<br>The plugin will not work until you make a decision. '
                    .'<a href="'.admin_url('options-general.php?page='.$this->plugin_name).'">Settings</a> '
                    .'<a style="color:red" href="'.admin_url('options-general.php?page='.$this->plugin_name).'&ignore_existing_media=1">'
                    .'Ignore current media files</a>');
            }

            return true;
        }

        return false;
    }

    public function listLocalFiles()
    {
        $dir = $this->uploads_original['basedir'];
        $files = array_filter(glob($dir.'/*/*/*'), 'is_file');

        return $files;
    }

    /**
     * Adds a filter to update upload_dir.
     *
     * @uses `add_filter`
     * @uses self::setUploadDir
     */
    public function fixUploadDir()
    {
        add_filter('upload_dir', [$this, 'setUploadDir']);
    }

    public function handleTransferAjax()
    {
        // Instantiate an Amazon S3 client.
        $file = $_POST['file_path'];
        if (!$file) {
            wp_send_json(json_encode([
                'error' => 'No file provided',
                'code' => 404,
            ]));
        }

        $client = $this->registerS3StreamWrapper();
        $dir = 's3://'.$this->config_map[1]['value'].'/';
        $key = $this->config_map['4']['value'] ?: $this->default_uploads_folder;

        if (strpos($file, $this->uploads_original['basedir']) !== 0) {
            wp_send_json(json_encode([
                'error' => 'Trying to move file from a directory that doesn\'t match the current uploads directory',
                'code' => 403,
            ]));
        }

        $to = $dir.$key.str_replace($this->uploads_original['basedir'], '', $file);

        if (!copy($file, $to)) {
            wp_send_json(json_encode([
                'error' => 'There was an error while transferring the file '.basename($file),
                'code' => 500,
            ]));
        }

        if (!unlink($file)) {
            wp_send_json(json_encode([
                'error' => 'There was an error while removing the original file '.basename($file),
                'code' => 500,
            ]));
        }

        wp_send_json(json_encode([
            'file' => basename($file),
            'status' => 'ok',
            'code' => 200,
        ]));
    }
}
