<div class="wrap">
<h1>Sink</h1>
<form method="post" action="options.php">
<?php
  settings_fields( 'sink_options' );
  do_settings_sections( 'sink_options' );
?>

<table class="form-table">
<tr valign="top">
<th scope="row"><label for="aws_region">AWS Region</label></th>
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

<?php array_shift($configMap); ?>

<?php foreach($configMap as $config => $data): ?>
  <tr valign="top">
    <th scope="row"><label for="<?= $config; ?>"><?= $data['title']; ?></label></th>
    <td>
      <input
        <?= $data['password'] == true ? 'type="password"' : '' ?>
        type="<?= $data['type'] == 'string' ? 'text' : ($data['type'] == 'boolean' ? 'checkbox' : $data['type']); ?>"
        name="<?= $config; ?>"
        id="<?= $config; ?>"
        value="<?= get_option($config); ?>"
        <?= $type == 'boolean' && get_option($config) ? 'checked' : ''; ?>
        placeholder="<?= $data['placeholder']; ?>" />
    </td>
  </tr>
<?php endforeach; ?>

</table>
<?php
  submit_button();
?>
</form>
</div>
