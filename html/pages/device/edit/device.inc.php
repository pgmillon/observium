<?php

/**
 * Observium Network Management and Monitoring System
 * Copyright (C) 2006-2014, Adam Armstrong - http://www.observium.org
 *
 * @package    observium
 * @subpackage webui
 * @author     Adam Armstrong <adama@memetic.org>
 * @copyright  (C) 2006-2014 Adam Armstrong
 *
 */

if ($_POST['editing'])
{
  if ($_SESSION['userlevel'] > "7")
  {
    $updated = 0;

    $override_sysLocation_bool = $_POST['override_sysLocation'];
    if (isset($_POST['sysLocation'])) { $override_sysLocation_string = $_POST['sysLocation']; }

    if (get_dev_attrib($device,'override_sysLocation_bool') != $override_sysLocation_bool
     || get_dev_attrib($device,'override_sysLocation_string') != $override_sysLocation_string)
    {
      $updated = 2;
    }

    if ($override_sysLocation_bool) { set_dev_attrib($device, 'override_sysLocation_bool', '1'); } else { del_dev_attrib($device, 'override_sysLocation_bool'); }
    if (isset($override_sysLocation_string)) { set_dev_attrib($device, 'override_sysLocation_string', $override_sysLocation_string); };

    # FIXME needs more sanity checking! and better feedback
    # FIXME -- update location too? Need to trigger geolocation!

    $param = array('purpose' => $_POST['descr'], 'type' => $_POST['type'], 'ignore' => $_POST['ignore'], 'disabled' => $_POST['disabled']);

    $rows_updated = dbUpdate($param, 'devices', '`device_id` = ?', array($device['device_id']));

    if ($rows_updated > 0 || $updated)
    {
      if ((bool)$_POST['ignore'] != (bool)$device['ignore'])
      {
        log_event('Device '.((bool)$_POST['ignore'] ? 'ignored' : 'attended').': '.$device['hostname'], $device['device_id'], 'device');
      }
      if ((bool)$_POST['disabled'] != (bool)$device['disabled'])
      {
        log_event('Device '.((bool)$_POST['disabled'] ? 'disabled' : 'enabled').': '.$device['hostname'], $device['device_id'], 'device');
      }
      $update_message = "Device record updated.";
      if ($updated == 2) { $update_message.= " Please note that the updated sysLocation string will only be visible after the next poll."; }
      $updated = 1;
      $device = dbFetchRow("SELECT * FROM `devices` WHERE `device_id` = ?", array($device['device_id']));
    } elseif ($rows_updated = '-1') {
      $update_message = "Device record unchanged. No update necessary.";
      $updated = -1;
    } else {
      $update_message = "Device record update error.";
    }
  }
  else
  {
    include("includes/error-no-perm.inc.php");
  }
}

$descr = $device['purpose'];

$override_sysLocation_bool = get_dev_attrib($device,'override_sysLocation_bool');
$override_sysLocation_string = get_dev_attrib($device,'override_sysLocation_string');

if ($updated && $update_message)
{
  print_message($update_message);
} elseif ($update_message) {
  print_error($update_message);
}

?>

 <form id="edit" name="edit" method="post" class="form-horizontal" action="<?php echo($url); ?>">

  <fieldset>
  <legend>Device Properties</legend>
  <input type=hidden name="editing" value="yes">
  <div class="control-group">
    <label class="control-label" for="descr">Description</label>
    <div class="controls">
      <input name="descr" type=text size="32" value="<?php echo(htmlspecialchars($device['purpose'])); ?>" />
    </div>
  </div>

  <div class="control-group">
    <label class="control-label" for="type">Type</label>
    <div class="controls">
      <select class="selectpicker" name="type">
<?php
$unknown = 1;
foreach ($config['device_types'] as $type)
{
  echo('          <option value="'.$type['type'].'"');
  if ($device['type'] == $type['type']) { echo(' selected="selected"'); $unknown = 0; }
  echo(' >' . ucfirst($type['type']) . '</option>');
}
if ($unknown) { echo('          <option value="other">Other</option>'); }

?>
              </select>
            </div>
  </div>

  <div class="control-group">
    <label class="control-label" for="sysLocation">Override sysLocation</label>

    <div class="controls">
      <input id="location_check" type="checkbox" onclick="edit.sysLocation.disabled=!edit.override_sysLocation.checked"
            name="override_sysLocation" <?php if ($override_sysLocation_bool) { echo(' checked="checked"'); } ?> data-id="location_check" data-label="Use custom location below.">
    </div>
  </div>

<script>

$('#location_check').click(function() {
    $('#location_text').attr('disabled',! this.checked)
});

</script>

  <div class="control-group">
    <label class="control-label" for="sysLocation">Custom location</label>
    <div class="controls" id="location_text">
      <input type=text name="sysLocation" size="32" <?php if (!$override_sysLocation_bool) { echo(' disabled="disabled"'); } ?>
              value="<?php echo(htmlspecialchars($override_sysLocation_string)); ?>" />
    </div>
  </div>

  <div class="control-group">
    <label class="control-label" for="disabled">Disable</label>
    <div class="controls">
      <input name="disabled" type="checkbox" id="disabled" value="1" <?php if ($device["disabled"]) { echo("checked=checked"); } ?> />
      <span class="help-inline">Disables polling and discovery.</span>
    </div>
  </div>
  <?php // FIXME (Mike): $device['ignore'] and get_dev_attrib($device,'disable_notify') it is same/redundant options? ?>
  <div class="control-group">
    <label class="control-label" for="sysLocation">Device ignore</label>
    <div class="controls">
      <input name="ignore" type="checkbox" id="disable" value="1" <?php if ($device['ignore']) { echo("checked=checked"); } ?> />
      <span class="help-inline">Device ignore.</span>
    </div>
  </div>
  </fieldset>

  <div class="form-actions">
    <button type="submit" class="btn btn-primary" name="submit" value="save"><i class="icon-ok icon-white"></i> Save Changes</button>
  </div>

</form>

<?php

#print_optionbar_start();
#list($sizeondisk, $numrrds) = foldersize($config['rrd_dir']."/".$device['hostname']);
#echo("Size on Disk: <b>" . formatStorage($sizeondisk) . "</b> in <b>" . $numrrds . " RRD files</b>.");
#print_optionbar_end();

// EOF
