<?php

namespace Sink;

class Options
{

  /**
   * Defines plugin options and some properties for each in order to create
   * dynamically the options page
   */
    public $config_map = [
    [
        'id' => 'aws_region',
        'required' => true,
        'type' => 'string',
        'placeholder' => 'eu-west-1',
        'title' => 'AWS Region',
        'value' => null,
    ],
    [
        'id' => 'aws_bucket',
        'required' => true,
        'type' => 'string',
        'placeholder' => 'caffeina',
        'title' => 'AWS Bucket',
        'value' => null,
    ],
    [
        'id' => 'aws_access_id',
        'required' => true,
        'type' => 'string',
        'placeholder' => '',
        'title' => 'AWS Access Key ID',
        'value' => null,
    ],
    [
        'id' => 'aws_secret',
        'required' => true,
        'type' => 'string',
        'placeholder' => '',
        'title' => 'AWS Secret',
        'password' => true,
        'value' => null,
    ],
    [
        'id' => 'aws_uploads_path',
        'required' => true,
        'type' => 'string',
        'placeholder' => '',
        'title' => 'AWS uploads path',
        'value' => null,
    ],
    [
        'id' => 'keep_site_domain',
        'required' => false,
        'type' => 'boolean',
        'placeholder' => '',
        'title' => 'Do not override website domain.',
        'value' => null,
    ],
    [
        'id' => 'cdn_endpoint',
        'required' => false,
        'type' => 'string',
        'placeholder' => '',
        'title' => 'Imgix or Cloudfront URL (Leave empty to use S3 default domain)',
        'value' => null,
    ],
    [
        'id' => 'http_proxy_url',
        'required' => false,
        'type' => 'string',
        'placeholder' => 'http://127.0.0.1',
        'title' => 'HTTP Proxy URL',
        'value' => null,
    ],
    [
        'id' => 'http_proxy_port',
        'required' => false,
        'type' => 'number',
        'placeholder' => '8080',
        'title' => 'HTTP Proxy port',
        'value' => null,
    ]
  ];

    public $plugin_name;
    protected static $instance;

    public function __construct($plugin_name)
    {
        $this->plugin_name = $plugin_name;
        $this->registerSettings();

        return self::$instance = $this;
    }

    public function getOptionName($option)
    {
        if (!$option) {
            return null;
        }

        return $this->plugin_name."_".$option['id'];
    }

    public function getWPConfigOptionName($option)
    {
        if (!$option) {
            return null;
        }

        return strtoupper($this->getOptionName($option));
    }

    public function getValueForOption($option)
    {
        if (!$option) {
            return null;
        }

        $name = $this->getOptionName($option);
        $wp_name = $this->getWPConfigOptionName($option);
        $wp_value = null;

        if (defined($wp_name)) {
            $wp_value = constant($wp_name);
        }

        return $wp_value ?: get_option($name, $wp_value);
    }

    public function isWPConfigDefined($option)
    {
        $wp_name = $this->getWPConfigOptionName($option);
        $wp_value = null;

        if (defined($wp_name)) {
            $wp_value = constant($wp_name);
        }
        return !!$wp_value;
    }

    public function loadOptions()
    {
        $continue = true;
        foreach ($this->config_map as $key => $config) {
            if (! empty($config['value'])) {
                continue;
            }

            $config['value'] = $this->getValueForOption($config);
            if ($config['required'] && empty($config['value'])) {
                $continue = false;
            }
        }

        return $continue ?  $this->config_map : false;
    }

    /**
     * Loops through config options to save them on the database
     * @uses `register_setting` wp global function
     * @return void
     */
    public function registerSettings()
    {
        foreach ($this->config_map as $key => $config) {
            register_setting(
                $this->plugin_name.'_options',
                $this->getOptionName($config),
                [
                    'type' => $config['type']
                ]
            );
        }
    }
}
