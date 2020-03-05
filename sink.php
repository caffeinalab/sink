<?php
/**
 * Plugin Name: Sink
 * Description: Sync media to S3 seamlessly
 * Version:     1.0.2
 * Author:      Caffeina
 * Author URI:  https://caffeina.com/
 * Plugin URI:  https://github.com/caffeinalab/sink.
 */
require_once 'classes/sink.php';

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

define('SINK_URL', plugin_dir_url(__FILE__));
define('SINK_PATH', plugin_dir_path(__FILE__));
define('SINK_VERSION', '1.0.2');

add_action('plugins_loaded', array('Sink\Sink', 'init'));
