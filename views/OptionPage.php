<div class="wrap">
<h1>Sink</h1>

<?php
  settings_fields('sink_options');
  do_settings_sections('sink_options');
  $config_map = $this->options->config_map;
  $opts = $this->options->loadOptions() ?: $config_map;

  $aws_region = $this->options->getValueForOption($config_map[0]);
?>

<div class="uk-grid-small uk-child-width-expand@s" uk-grid>

<form class="uk-form-stacked" method="post" action="options.php">

    <div class="uk-margin-small-top">
        <ul class="uk-flex uk-tab" uk-tab>
            <li class="uk-active"><a href="#">AWS</a></li>
            <li><a href="#">Domain</a></li>
            <li><a href="#">Proxy HTTP</a></li>
        </ul>
        <ul class="uk-switcher uk-margin">
            <li class="uk-active"><!-- This is a button toggling the modal -->
                <div class="uk-margin">
                    <label class="uk-form-label" for="<?= $this->options->getOptionName($config_map[0]); ?>">AWS Region</label>
                    <div class="uk-form-controls">
                        <select type="text" class="uk-select uk-form-width-medium" name="<?= $this->options->getOptionName($config_map[0]); ?>"
                            <?= $this->options->isWPConfigDefined($config_map[0]) ? 'disabled' : ''; ?>
                        >
                            <option <?= $aws_region == 'eu-west-1' ? 'selected' : ''; ?> value="eu-west-1">eu-west-1</option>
                            <option <?= $aws_region == 'eu-west-2' ? 'selected' : ''; ?> value="eu-west-2">eu-west-2</option>
                            <option <?= $aws_region == 'eu-west-3' ? 'selected' : ''; ?> value="eu-west-3">eu-west-3</option>
                            <option <?= $aws_region == 'us-east-2' ? 'selected' : ''; ?> value="us-east-2">us-east-2</option>
                            <option <?= $aws_region == 'us-east-1' ? 'selected' : ''; ?> value="us-east-1">us-east-1</option>
                            <option <?= $aws_region == 'us-west-1' ? 'selected' : ''; ?> value="us-west-1">us-west-1</option>
                            <option <?= $aws_region == 'us-west-2' ? 'selected' : ''; ?> value="us-west-2">us-west-2</option>
                            <option <?= $aws_region == 'ap-east-1' ? 'selected' : ''; ?> value="ap-east-1">ap-east-1</option>
                            <option <?= $aws_region == 'ap-south-1' ? 'selected' : ''; ?> value="ap-south-1">ap-south-1</option>
                            <option <?= $aws_region == 'ap-northeast-3' ? 'selected' : ''; ?> value="ap-northeast-3">ap-northeast-3</option>
                            <option <?= $aws_region == 'ap-northeast-2' ? 'selected' : ''; ?> value="ap-northeast-2">ap-northeast-2</option>
                            <option <?= $aws_region == 'ap-southeast-1' ? 'selected' : ''; ?> value="ap-southeast-1">ap-southeast-1</option>
                            <option <?= $aws_region == 'ap-southeast-2' ? 'selected' : ''; ?> value="ap-southeast-2">ap-southeast-2</option>
                            <option <?= $aws_region == 'ap-northeast-1' ? 'selected' : ''; ?> value="ap-northeast-1">ap-northeast-1</option>
                            <option <?= $aws_region == 'ca-central-1' ? 'selected' : ''; ?> value="ca-central-1">ca-central-1</option>
                            <option <?= $aws_region == 'cn-north-1' ? 'selected' : ''; ?> value="cn-north-1">cn-north-1</option>
                            <option <?= $aws_region == 'cn-northwest-1' ? 'selected' : ''; ?> value="cn-northwest-1">cn-northwest-1</option>
                            <option <?= $aws_region == 'eu-central-1' ? 'selected' : ''; ?> value="eu-central-1">eu-central-1</option>
                            <option <?= $aws_region == 'eu-north-1' ? 'selected' : ''; ?> value="eu-north-1">eu-north-1</option>
                            <option <?= $aws_region == 'sa-east-1' ? 'selected' : ''; ?> value="sa-east-1">sa-east-1</option>
                        </select>
                    </div>
                </div>

    <?php foreach ($config_map as $index => $config): ?>
    <?php if ($index == 0 || strrpos(strtolower($config['title']), 'aws') === false) {
    continue;
} ?>

                <div class="uk-margin">
                    <label class="uk-form-label" for="<?= $this->options->getOptionName($config); ?>"><?= $config['title']; ?></label>
                    <div class="uk-form-controls">
                        <input
                            class="uk-<?= $config['type'] == 'string' ? 'input' : ($config['type'] == 'boolean' ? 'checkbox' : 'input'); ?> uk-form-width-medium"
                            <?= @$config['password'] == true ? 'type="password"' : ''; ?>
                            type="<?= $config['type'] == 'string' ? 'text' : ($config['type'] == 'boolean' ? 'checkbox' : $config['type']); ?>"
                            name="<?= $this->options->getOptionName($config); ?>"
                            id="<?= $this->options->getOptionName($config); ?>"
                            value="<?= $this->options->getValueForOption($config); ?>"
                            <?= $config['type'] == 'boolean' && $this->options->getValueForOption($config) ? 'checked' : ''; ?>
                            placeholder="<?= @$data['placeholder']; ?>"
                            <?= $this->options->isWPConfigDefined($config) ? 'disabled' : ''; ?>>
                    </div>
                </div>

    <?php endforeach; ?>

            </li>
            <li class="">
            <?php foreach ($config_map as $index => $config): ?>
    <?php if ($index == 0 || strrpos(strtolower($config['title']), 'domain') === false) {
    continue;
} ?>

                <div class="uk-margin">
                    <label class="uk-form-label" for="<?= $this->options->getOptionName($config); ?>"><?= $config['title']; ?></label>
                    <div class="uk-form-controls">
                        <input
                            class="uk-<?= $config['type'] == 'string' ? 'input' : ($config['type'] == 'boolean' ? 'checkbox' : 'input'); ?> uk-form-width-medium"
                            <?= @$config['password'] == true ? 'type="password"' : ''; ?>
                            type="<?= $config['type'] == 'string' ? 'text' : ($config['type'] == 'boolean' ? 'checkbox' : $config['type']); ?>"
                            name="<?= $this->options->getOptionName($config); ?>"
                            id="<?= $this->options->getOptionName($config); ?>"
                            value="<?= $this->options->getValueForOption($config); ?>"
                            <?= $config['type'] == 'boolean' && $this->options->getValueForOption($config) ? 'checked' : ''; ?>
                            placeholder="<?= @$data['placeholder']; ?>"
                            <?= $this->options->isWPConfigDefined($config) ? 'disabled' : ''; ?>>
                    </div>
                </div>

    <?php endforeach; ?>
            </li>
            <li class="">
            <?php foreach ($config_map as $index => $config): ?>
    <?php if ($index == 0 || strrpos(strtolower($config['title']), 'proxy') === false) {
    continue;
} ?>

                <div class="uk-margin">
                    <label class="uk-form-label" for="<?= $this->options->getOptionName($config); ?>"><?= $config['title']; ?></label>
                    <div class="uk-form-controls">
                        <input
                            class="uk-<?= $config['type'] == 'string' ? 'input' : ($config['type'] == 'boolean' ? 'checkbox' : 'input'); ?> uk-form-width-medium"
                            <?= @$config['password'] == true ? 'type="password"' : ''; ?>
                            type="<?= $config['type'] == 'string' ? 'text' : ($config['type'] == 'boolean' ? 'checkbox' : $config['type']); ?>"
                            name="<?= $this->options->getOptionName($config); ?>"
                            id="<?= $this->options->getOptionName($config); ?>"
                            value="<?= $this->options->getValueForOption($config); ?>"
                            <?= $config['type'] == 'boolean' && $this->options->getValueForOption($config) ? 'checked' : ''; ?>
                            placeholder="<?= @$data['placeholder']; ?>"
                            <?= $this->options->isWPConfigDefined($config) ? 'disabled' : ''; ?>>
                    </div>
                </div>

    <?php endforeach; ?>
            </li>
        </ul>
    </div>
    <hr class="uk-hr"/>
<?php
submit_button();
?>
</form>
    <div class="uk-text-left uk-margin-small">
        <h3>Local Files</h3>
            <?php $localFiles = count(\Sink\Sink::init()->listLocalFiles()); ?>
            <blockquote class="uk-text-meta">
                If you see the plugin error message about having local files it means that you have uploaded media files before installing the plugin.
                Now you have 2 options.
            </blockquote>
            <ul class="uk-list uk-list-bullet">
                <li>Ignore the files (meaning you must upload them again);</li>
                <li>Move the files to the S3 Bucket</li>
            </ul>

            <h4>Currently on disk</h4>

            <p class="uk-text-small">
                <?php $localFiles_sp = '<span id="transfer-files-ignored" class="uk-badge">'.$localFiles.'</span>'; ?>
                There are
                <?= $localFiles == 1 ? $localFiles_sp.' file' : $localFiles > 1 ? $localFiles_sp.' files' : 'no files'; ?>
                files in the uploads directory that have been <strong>ignored</strong>.
            </p>
            <?php if (get_option($this->plugin_name.'_'.$key) == true && $localFiles > 0): ?>
                <a href="<?php echo admin_url('options-general.php?page='.$this->plugin_name.'&'.$key.'=0'); ?>">
                <button class="uk-button uk-button-primary" onclick="return">Transfer them</button></a>
            <?php elseif (get_option($this->plugin_name.'_'.$key) == false && $localFiles > 0): ?>
                <a href="<?php echo admin_url('options-general.php?page='.$this->plugin_name.'&'.$key.'=1'); ?>">
                <button class="uk-button uk-button-danger" onclick="return">Ignore media files</button></a>
            <?php endif; ?>

            <?php if (get_option($this->plugin_name.'_'.$key) != true && $localFiles > 0): ?>
                <h4>Transfer Files</h4>
                <?php $localFiles_sp = '<span id="transfer-files-left" class="uk-badge">'.$localFiles.'</span>'; ?>
                <p class="uk-text-small">
                    There are
                    <?= $localFiles == 1 ? $localFiles_sp.' file' : $localFiles > 1 ? $localFiles_sp.' files' : 'no files'; ?>
                    in the uploads directory left to be transferred.
                </p>

                <button class="uk-button uk-button-primary" onclick="startTransfer()">Start transfer</button>

                <progress id="js-progressbar" class="uk-progress" value="0" max="<?= $localFiles; ?>"></progress>
            <?php endif; ?>
            <button class='uk-button'>
    </div>

</div> <!-- uk-grid-small -->
</div> <!-- wrap -->


<!-- UIkit CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/uikit@3.3.3/dist/css/uikit.min.css" />

<!-- UIkit JS -->
<script src="https://cdn.jsdelivr.net/npm/uikit@3.3.3/dist/js/uikit.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/uikit@3.3.3/dist/js/uikit-icons.min.js"></script>

<script type="text/javascript">
<?php if (get_option($this->plugin_name.'_'.$key) != true && $localFiles > 0): ?>
    function startTransfer() {
        var pb = document.getElementById("js-progressbar");
        var files = <?= json_encode(\Sink\Sink::init()->listLocalFiles()); ?>;
        var errors = false;

        jQuery(document).ready(function($) {
            for (var i = 0; i < files.length; i++) {
                var data = {
                    'action': 'sink_transfer',
                    'file_path': files[i]
                };

                // We can also pass the url value separately from ajaxurl for front end AJAX implementations
                jQuery.post('<?= admin_url('admin-ajax.php'); ?>', data, function(response) {
                    console.log('Got this from the server: ' + response);
                    var res = JSON.parse(response);
                    if (res.code == '200') {
                        var count = parseInt(jQuery('#transfer-files-left').html());
                        if (count-- == 0) {
                            jQuery('#transfer-files-left').html('no');
                        } else {
                            jQuery('#transfer-files-left').html(count);
                        }
                        pb.value++;
                    } else {
                        console.log(res.error);
                        errors++;
                    }
                });
            }

            jQuery('#transfer-files-left').on('change', function(e) {

            })
        });
    }
<?php endif; ?>
</script>
