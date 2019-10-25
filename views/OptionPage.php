<div class="wrap">
<h1>Sink</h1>
<form method="post" action="options.php">
<?php
  settings_fields('sink_options');
  do_settings_sections('sink_options');
  $config_map = $this->options->config_map;
  $opts = $this->options->loadOptions() ?: $config_map;

  $aws_region = $this->options->getValueForOption($config_map[0]);
?>

<table class="form-table">
<tr valign="top">
<th scope="row">
  <label for="<?= $this->options->getOptionName($config_map[0]); ?>">AWS Region</label>
</th>
<td>
  <select type="text" name="<?= $this->options->getOptionName($config_map[0]); ?>"
    <?= $this->options->isWPConfigDefined($config) ? "disabled" : ""; ?>
  >
    <option <?= $aws_region == 'eu-west-1' ? "selected" : "" ?> value="eu-west-1">eu-west-1</option>
    <option <?= $aws_region == 'eu-west-2' ? "selected" : "" ?> value="eu-west-2">eu-west-2</option>
    <option <?= $aws_region == 'eu-west-3' ? "selected" : "" ?> value="eu-west-3">eu-west-3</option>
    <option <?= $aws_region == 'us-east-2' ? "selected" : "" ?> value="us-east-2">us-east-2</option>
    <option <?= $aws_region == 'us-east-1' ? "selected" : "" ?> value="us-east-1">us-east-1</option>
    <option <?= $aws_region == 'us-west-1' ? "selected" : "" ?> value="us-west-1">us-west-1</option>
    <option <?= $aws_region == 'us-west-2' ? "selected" : "" ?> value="us-west-2">us-west-2</option>
    <option <?= $aws_region == 'ap-east-1' ? "selected" : "" ?> value="ap-east-1">ap-east-1</option>
    <option <?= $aws_region == 'ap-south-1' ? "selected" : "" ?> value="ap-south-1">ap-south-1</option>
    <option <?= $aws_region == 'ap-northeast-3' ? "selected" : "" ?> value="ap-northeast-3">ap-northeast-3</option>
    <option <?= $aws_region == 'ap-northeast-2' ? "selected" : "" ?> value="ap-northeast-2">ap-northeast-2</option>
    <option <?= $aws_region == 'ap-southeast-1' ? "selected" : "" ?> value="ap-southeast-1">ap-southeast-1</option>
    <option <?= $aws_region == 'ap-southeast-2' ? "selected" : "" ?> value="ap-southeast-2">ap-southeast-2</option>
    <option <?= $aws_region == 'ap-northeast-1' ? "selected" : "" ?> value="ap-northeast-1">ap-northeast-1</option>
    <option <?= $aws_region == 'ca-central-1' ? "selected" : "" ?> value="ca-central-1">ca-central-1</option>
    <option <?= $aws_region == 'cn-north-1' ? "selected" : "" ?> value="cn-north-1">cn-north-1</option>
    <option <?= $aws_region == 'cn-northwest-1' ? "selected" : "" ?> value="cn-northwest-1">cn-northwest-1</option>
    <option <?= $aws_region == 'eu-central-1' ? "selected" : "" ?> value="eu-central-1">eu-central-1</option>
    <option <?= $aws_region == 'eu-north-1' ? "selected" : "" ?> value="eu-north-1">eu-north-1</option>
    <option <?= $aws_region == 'sa-east-1' ? "selected" : "" ?> value="sa-east-1">sa-east-1</option>
  </select>
</td>
</tr>

<?php foreach ($config_map as $index => $config): ?>
  <?php if ($index == 0) continue;?>
  <tr valign="top">
    <th scope="row">
      <label for="<?= $this->options->getOptionName($config); ?>"><?= $config['title']; ?></label>
    </th>
    <td>
      <input
        <?= @$config['password'] == true ? 'type="password"' : '' ?>
        type="<?= $config['type'] == 'string' ? 'text' : ($config['type'] == 'boolean' ? 'checkbox' : $config['type']); ?>"
        name="<?= $this->options->getOptionName($config); ?>"
        id="<?= $this->options->getOptionName($config); ?>"
        value="<?= $this->options->getValueForOption($config); ?>"
        <?= $config['type'] == 'boolean' && $this->options->getValueForOption($config) ? 'checked' : ''; ?>
        placeholder="<?= @$data['placeholder']; ?>"
        <?= $this->options->isWPConfigDefined($config) ? "disabled" : ""; ?>
        />
    </td>
  </tr>
<?php endforeach; ?>

</table>
<?php
  submit_button();
?>
</form>
</div>
