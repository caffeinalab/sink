<?php

namespace Sink;

class UI
{
  protected $optionsTemplate = 'Templates/OptionPage.php';
  protected $noticesTemplate = 'Templates/AdminNotice.php';
  protected $plugin_name;

  public function __construct($plugin_name)
  {
    if (is_admin()) {
        add_action('admin_menu', [$this, 'addOptionsMenu']);
        add_filter('plugin_action_links_'.$plugin_name, [$this, 'renderSettingsButton']);
    }

    if ($plugin_name) {
      $this->plugin_name = $plugin_name;
    }
  }

  public function pluginNameToClassName()
  {
    $class_name = strtoupper(substr($this->plugin_name, 0, 1)).substr($this->plugin_name, 1);
    return $class_name;
  }

  public function renderOptionsPage()
  {
    $class = "\\".$this->pluginNameToClassName()."\\".$this->pluginNameToClassName();
    $configMap = (new $class)->configMap;
    require $this->optionsTemplate;
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
    add_action(
      'admin_notices',
      function () use ($type, $message) {
          require $this->noticesTemplate;
      }
    );
  }

  public function renderSettingsButton($links)
  {
    if (!$this->plugin_name) {
      return $links;
    }

    array_splice(
          $links,
          0,
          0,
          '<a href="' .admin_url('options-general.php?page='.$this->plugin_name) .
              '">' . __('Settings') . '</a>'
      );
      return $links;
  }
}
