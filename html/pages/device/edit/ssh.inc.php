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
    $ssh_port = $_POST['ssh_port'];

    if (!is_numeric($ssh_port))
    {
      $update_message = "SSH port must be numeric!";
      $updated = 0;
    }
    else
    {
      $update = array(
        'ssh_port' => $ssh_port
      );

      $rows_updated = dbUpdate($update, 'devices', '`device_id` = ?',array($device['device_id']));

      if ($rows_updated > 0)
      {
        $update_message = $rows_updated . " Device record updated.";
        $updated = 1;
      } elseif ($rows_updated = '-1') {
        $update_message = "Device record unchanged. No update necessary.";
        $updated = -1;
      } else {
        $update_message = "Device record update error.";
        $updated = 0;
      }
    }
  }
}

$device = dbFetchRow("SELECT * FROM `devices` WHERE `device_id` = ?", array($device['device_id']));
$descr  = $device['purpose'];

if ($updated && $update_message)
{
  print_message($update_message);
} elseif ($update_message) {
  print_error($update_message);
}

print_warning("For now this option used only by 'libvirt-vminfo' discovery module (on linux devices).");

?>

<form id="edit" name="edit" method="post" class="form-horizontal" action="">
  <input type=hidden name="editing" value="yes">

  <div id="ssh">
    <fieldset>
      <legend>SSH Connectivity</legend>
      <div class="control-group">
        <label class="control-label" for="ssh_port">SSH Port</label>
        <div class="controls">
          <input type=text name="ssh_port" size="32" value="<?php echo(htmlspecialchars($device['ssh_port'])); ?>"/>
        </div>
      </div>
    </fieldset>
  </div>

  <div class="form-actions">
    <button type="submit" class="btn btn-primary" name="submit" value="save"><i class="icon-ok icon-white"></i> Save Changes</button>
  </div>

</form>
<?php

// EOF
