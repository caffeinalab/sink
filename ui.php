<?php

namespace Sink;

use \Sink\Sink;

class UI
{

  protected $optionsTemplate = 'Templates/OptionPage.php';
  protected $noticesTemplate = 'Templates/AdminNotice.php';

  public function __construct()
  {
    if (is_admin()) {
        add_action('admin_menu', [$this, 'addOptionsMenu']);
        add_filter('plugin_action_links_'.plugin_basename(__FILE__), [$this, 'renderSettingsButton']);
    }
  }

  public function renderOptionsPage()
  {
    $configMap = (new Sink())->configMap;
    require $this->optionsTemplate;
  }

  public function addOptionsMenu()
  {
    add_options_page(
      'Sink settings',
      'Sink',
      'manage_options',
      'sink',
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
    array_splice(
          $links,
          0,
          0,
          '<a href="' .admin_url('options-general.php?page=sink') .
              '">' . __('Settings') . '</a>'
      );
      return $links;
  }
}
