<?php
/**
 * Plugin Name: Sink
 * Description: Sync media to S3
 * Version:     0.0.1
 * Author:      Caffeina
 * Author URI:  https://caffeina.com/
 * Plugin URI:  https://github.com/caffeinalab/sink
 */

require 'updater.php';

use Sink\Updater;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

define( 'SINK_URL', plugin_dir_url( __FILE__ ) );
define( 'SINK_PATH', plugin_dir_path( __FILE__ ) );
define( 'SINK_VERSION', '0.0.1' );

function sink_register_settings()
{
    register_setting('sink_options', 'aws_region', ['type' => 'string']);
    register_setting('sink_options', 'aws_endpoint', ['type' => 'string']);
    register_setting('sink_options', 'aws_bucket', ['type' => 'string']);
    register_setting('sink_options', 'aws_access_id', ['type' => 'string']);
    register_setting('sink_options', 'aws_secret', ['type' => 'string']);
    register_setting('sink_options', 'delete_original', ['type' => 'boolean']);
    register_setting('sink_options', 'resize_wordpress', ['type' => 'boolean']);
    register_setting('sink_options', 'cdn_endpoint', ['type' => 'boolean']);
    register_setting('sink_options', 'http_proxy_url', ['type' => 'text']);
    register_setting('sink_options', 'http_proxy_port', ['type' => 'number']);
}

function sink_register_menu_entry()
{
    add_options_page(
        'Sink settings',
        'Sink',
        'manage_options',
        'sink',
        'sink_render_option_page'
    );
}

function sink_render_option_page()
{
    include 'Templates/OptionPage.php';
}

function sink_check_if_options_exist()
{
    $loaded = false;

    $options = [
      'aws_region',
      'aws_endpoint',
      'aws_bucket',
      'aws_access_id',
      'aws_secret',
      'delete_original',
      'resize_wordpress',
      'cdn_endpoint',
      'http_proxy_url',
      'http_proxy_port',
    ];
    foreach ($options as $option) {
        if (get_option($option) == false && defined(strtoupper('SINK_'.$option))) {
            update_option($option, constant('SINK_'.strtoupper($option)));
            $loaded = true;
        }
    }

    if ($loaded) {
        render_admin_notice('notice-info', 'I have loaded the config from wp-config. It will not overwrite the settings if set from the options page');
    }
}

function sink_render_admin_notice($type, $message)
{
    add_action(
        'admin_notices',
        function () use ($type, $message) {
            include 'Templates/AdminNotice.php';
        }
    );
}

function sink_setting_button_adder($links)
{
    array_splice(
        $links,
        0,
        0,
        '<a href="' .admin_url('options-general.php?page=sink') .
            '">' . __('Settings') . '</a>'
    );
    return $links;
}

// Boot of Sink
if (is_admin()) {
    add_action('admin_menu', 'sink_register_menu_entry');
    add_action('admin_init', 'sink_register_settings');
    add_action('admin_init', 'sink_check_if_options_exist');
    add_filter(
        'plugin_action_links_'.plugin_basename(__FILE__),
        'sink_setting_button_adder'
    );
    (new Updater())->bootUpdateService();
}
