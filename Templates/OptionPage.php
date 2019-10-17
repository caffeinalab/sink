<div class="wrap">
<h1>Sink</h1>
<form method="post" action="options.php">
<?php
  settings_fields( 'sink_options' );
  do_settings_sections( 'sink_options' );
?>

<table class="form-table">
</tr>
<tr valign="top">
<th scope="row">AWS Region</th>
<td>
  <select type="text" name="aws_region" value="<?= get_option('aws_region'); ?>">
    <option <?= get_option('aws_region') == 'eu-west-1' ? "selected" : "" ?> value="eu-west-1">eu-west-1</option>
    <option <?= get_option('aws_region') == 'eu-west-2' ? "selected" : "" ?> value="eu-west-2">eu-west-2</option>
    <option <?= get_option('aws_region') == 'eu-west-3' ? "selected" : "" ?> value="eu-west-3">eu-west-3</option>
    <option <?= get_option('aws_region') == 'us-east-2' ? "selected" : "" ?> value="us-east-2">us-east-2</option>
    <option <?= get_option('aws_region') == 'us-east-1' ? "selected" : "" ?> value="us-east-1">us-east-1</option>
    <option <?= get_option('aws_region') == 'us-west-1' ? "selected" : "" ?> value="us-west-1">us-west-1</option>
    <option <?= get_option('aws_region') == 'us-west-2' ? "selected" : "" ?> value="us-west-2">us-west-2</option>
    <option <?= get_option('aws_region') == 'ap-east-1' ? "selected" : "" ?> value="ap-east-1">ap-east-1</option>
    <option <?= get_option('aws_region') == 'ap-south-1' ? "selected" : "" ?> value="ap-south-1">ap-south-1</option>
    <option <?= get_option('aws_region') == 'ap-northeast-3' ? "selected" : "" ?> value="ap-northeast-3">ap-northeast-3</option>
    <option <?= get_option('aws_region') == 'ap-northeast-2' ? "selected" : "" ?> value="ap-northeast-2">ap-northeast-2</option>
    <option <?= get_option('aws_region') == 'ap-southeast-1' ? "selected" : "" ?> value="ap-southeast-1">ap-southeast-1</option>
    <option <?= get_option('aws_region') == 'ap-southeast-2' ? "selected" : "" ?> value="ap-southeast-2">ap-southeast-2</option>
    <option <?= get_option('aws_region') == 'ap-northeast-1' ? "selected" : "" ?> value="ap-northeast-1">ap-northeast-1</option>
    <option <?= get_option('aws_region') == 'ca-central-1' ? "selected" : "" ?> value="ca-central-1">ca-central-1</option>
    <option <?= get_option('aws_region') == 'cn-north-1' ? "selected" : "" ?> value="cn-north-1">cn-north-1</option>
    <option <?= get_option('aws_region') == 'cn-northwest-1' ? "selected" : "" ?> value="cn-northwest-1">cn-northwest-1</option>
    <option <?= get_option('aws_region') == 'eu-central-1' ? "selected" : "" ?> value="eu-central-1">eu-central-1</option>
    <option <?= get_option('aws_region') == 'eu-north-1' ? "selected" : "" ?> value="eu-north-1">eu-north-1</option>
    <option <?= get_option('aws_region') == 'sa-east-1' ? "selected" : "" ?> value="sa-east-1">sa-east-1</option>
  </select>
</td>
</tr>

</tr>
<tr valign="top">
<th scope="row">AWS Endpoint</th>
<td><input type="text" name="aws_endpoint" value="<?= get_option('aws_endpoint'); ?>" placeholder="s3.amazonaws.com" /></td>
</tr>

</tr>
<tr valign="top">
<th scope="row">AWS Bucket</th>
<td><input type="text" name="aws_bucket" value="<?= get_option('aws_bucket'); ?>" /></td>
</tr>

</tr>
<tr valign="top">
<th scope="row">AWS Access Key ID</th>
<td><input type="text" name="aws_access_id" value="<?= get_option('aws_access_id'); ?>" /></td>
</tr>

</tr>
<tr valign="top">
<th scope="row">AWS secret</th>
<td><input type="password" name="aws_secret" value="<?= get_option('aws_secret'); ?>" /></td>
</tr>

</tr>
<tr valign="top">
<th scope="row">Delete Original</th>
<td><input type="checkbox" name="delete_original" <?= get_option('delete_original') ? "checked" : ""; ?> /></td>
</tr>

<tr valign="top">
<th scope="row">Resize for Wordpress</th>
<td><input type="checkbox" name="resize_wordpress" <?= get_option('resize_wordpress') ? "checked" : ""; ?> /></td>
</tr>

<tr valign="top">
<th scope="row">CDN Endpoint</th>
<td><input type="text" name="cdn_endpoint" value="<?= get_option('cdn_endpoint'); ?>" placeholder="imgix.net" /></td>
</tr>

<tr valign="top">
<th scope="row">HTTP Proxy</th>
<td>
  <input type="text" name="http_proxy_url" value="<?= get_option('http_proxy_url'); ?>" placeholder="URL" /> :
  <input type="text" name="http_proxy_port" value="<?= get_option('http_proxy_port'); ?>" placeholder="port" />
</td>
</tr>

</table>
<?php
submit_button();
?>
</form>
</div>
