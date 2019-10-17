<!-- PROJECT LOGO -->
<br />
<p align="center">
  <a href="https://github.com/caffeinalab/sink">
    <img src="res/sink.png" alt="Logo" width="130" height="130">
  </a>
  <h1 align="center">Sink</h1>

  <p align="center">
    Sync media with an AWS S3 bucket
  </p>
</p>

<!-- TABLE OF CONTENTS -->
# Sink

Sync media with an AWS S3 bucket

## Table of Contents

- [Sink](#sink)
  - [Table of Contents](#table-of-contents)
  - [About The Project](#about-the-project)
    - [How it works](#how-it-works)
    - [Dependencies](#dependencies)
  - [Getting Started](#getting-started)
    - [Updates](#updates)
  - [Usage](#usage)
    - [Resizing](#resizing)
  - [Contributing](#contributing)
  - [License](#license)

<!-- ABOUT THE PROJECT -->
## About The Project

<!-- ![Product Name Screen Shot][screenshot]-->

We built **Sink** because in our web development architecture, we containerized and split all the services. This also meant that we decoupled software from data.
In the case of media files, we use usually AWS S3 and

**Sink** was built to make sending emails via WP the easiest it can get. It provides a setting page where you can set the SMTP parameters, but, in case of batch installations, you can even put a configuration file in your theme.

### How it works

The plugin uses the configuration to connect to the *S3 Bucket* and move media files there once uploaded. It listens to the `media_upload` hooks.

<!-- DEPENDENCIES -->
### Dependencies

The project depends on the [aws-sdk-php](https://github.com/aws/aws-sdk-php). It is imported in the project with composer, but to make it possible to install the plugin, it is pushed in the repository.

<!-- GETTING STARTED -->
## Getting Started

You can just clone this repository inside your `wp-content/plugins` folder, or [download the installable zip](https://github.com/caffeinalab/sink/releases/latest/download/sink.zip) and install it via the WordPress dashboard.

### Updates

You can update Sink directly from the WordPress' dashboard, like any other plugin.

<!-- USAGE EXAMPLES -->
## Usage

To use Sink, just install it and configure it in the settings page based on your needs.

You can also alternatively add these settings into `wp-config.php`

```php
define('SINK_AWS_REGION', "eu-west-2");
define('SINK_AWS_ENDPOINT', "some value");
define('SINK_AWS_BUCKET', "some value");
define('SINK_AWS_ACCESS_ID', "some value");
define('SINK_AWS_SECRET', "some value");
define('SINK_DELETE_ORIGINAL', "1");
define('SINK_RESIZE_WORDPRESS', "1");
define('SINK_CDN_ENDPOINT', "some value");
define('SINK_HTTP_PROXY_URL', "some value");
define('SINK_HTTP_PROXY_PORT', "8080");
```

Everything is now set up. You don't need to worry about anything else.

### Resizing

Images need resized and media in general needs to be distributed. It's not reasonable to have WordPress distribute media, especially if it's a huge website and needs to be scaled.

On one side, we are already saving the files to a distributed storage like *S3*, so now we can decide how to deliver them. Well being in the AWS ecosystem we can choose *AWS CloudFront*. But on the other hand, we may be using another S3 compatible service such as Minio. Also *CloudFront* doesn't offer image resizing. That's why we chose to use Imgix for our projects.

There are two types of image resizing though, the one that WordPress does automatically, and the one that is dynamic for the frontend website.

If the resize option is enabled in the plugin, the WordPress generated thumbnails (resized images) will be moved to S3.

> This approach isn't recommended because it creates unnecessary copies of the same file and it adds load to the server. Use a service to resize photos on the fly instead. And then simply proxy the images from Nginx like below.

```lua
server {
  # ...
  server_name ~^(www\.)?(?<domain>.+)$;

  location ~ ^/.*/uploads/(.+)\-([0-9]+)x([0-9]+)\.([^\.]+)$ {
    rewrite ^/.*/uploads/(.+)\-([0-9]+)x([0-9]+)\.([^\.]+)$ /uploads/$1.$4?$args&w=$2&h=$3;
  }

  location ~ ^/.*/uploads/(.+)\-([0-9]+)\.([^\.]+)$ {
    rewrite ^/.*/uploads/(.+)\-([0-9]+)\.([^\.]+)$ /uploads/$1.$3?$args&w=$2;
  }

  location ~ ^/.*/uploads/(.+)$ {
    rewrite ^/.*/uploads/(.+)$ /uploads/$1;
  }

  location ~ ^/uploads/.*$ {
    try_files $uri @imgix;
  }

  location @imgix {
    proxy_pass https://$domain.imgix.net;
  }

  # ...
  # WordPress configuration
}

```

<!-- CONTRIBUTING -->
## Contributing

Contributions are what make the open source community such an amazing place to be learn, inspire, and create. Any contributions you make are **greatly appreciated**.

1. Fork the Project
2. Create your Feature Branch (`git checkout -b feature/AmazingFeature`)
3. Commit your Changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the Branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

<!-- LICENSE -->
## License

Copyright 2014-2019 [Caffeina](http://caffeina.com) SpA under the [MIT license](LICENSE.md).

<!-- [screenshot]: res/screenshot.gif "Screenshot"-->
[logo]: res/sink.png
