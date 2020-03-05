<?php

namespace Sink;

require_once __DIR__.'/options.php';

class UI
{
    protected $options_template = __DIR__.'/../views/OptionPage.php';
    protected $notices_template = __DIR__.'/../views/AdminNotice.php';
    protected $plugin_name;
    protected $plugin_slug;
    protected $options;
    protected static $instance;

    public static function init($plugin_name, $plugin_slug)
    {
        return self::getInstance($plugin_name, $plugin_slug);
    }

    public static function getInstance($plugin_name, $plugin_slug)
    {
        $class = __CLASS__;
        if (self::$instance == null) {
            self::$instance = new $class($plugin_name, $plugin_slug);
        }

        return self::$instance;
    }

    public function __construct($plugin_name, $plugin_slug)
    {
        if (is_admin()) {
            add_action('admin_menu', [$this, 'addOptionsMenu']);
            add_filter('plugin_action_links_'.$plugin_slug, [$this, 'renderSettingsButton']);
        }

        $this->plugin_name = $plugin_name;
        $this->plugin_slug = $plugin_slug;
        $this->options = Options::init($plugin_name);

        return $this;
    }

    public function pluginNameToClassName()
    {
        $class_name = strtoupper(substr($this->plugin_name, 0, 1)).substr($this->plugin_name, 1);

        return $class_name;
    }

    public function renderOptionsPage()
    {
        $key = 'ignore_existing_media';

        if (in_array($key, array_keys($_REQUEST)) && !is_null($_REQUEST[$key])) {
            \update_option($this->plugin_name.'_'.$key, $_REQUEST[$key]);
        }

        require $this->options_template;
    }

    public function addOptionsMenu()
    {
        add_options_page(
            $this->pluginNameToClassName().' settings',
            $this->pluginNameToClassName(),
            'manage_options',
            $this->plugin_name,
            [$this, 'renderOptionsPage']
        );
    }

    public function renderNotice($type, $message)
    {
        if (!is_admin()) {
            return;
        }
        add_action(
            'admin_notices',
            function () use ($type, $message) {
                require $this->notices_template;
            }
        );
    }

    public function renderSettingsButton($links)
    {
        if (!$this->plugin_slug) {
            return $links;
        }

        array_splice(
            $links,
            0,
            0,
            '<a href="'.admin_url('options-general.php?page='.$this->plugin_name).
              '">'.__('Settings').'</a>'
        );

        return $links;
    }
}
