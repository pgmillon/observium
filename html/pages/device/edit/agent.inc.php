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
    $agent_port = $_POST['agent_port'];

    if ($agent_port == "")
    {
      del_dev_attrib($device, 'agent_port');
      $updated = 1;
      $update_message = "Agent settings updated.";
    }
    elseif (!is_numeric($agent_port))
    {
      $update_message = "Agent port must be numeric!";
      $updated = 0;
    }
    else
    {
      set_dev_attrib($device, 'agent_port', $agent_port);
      $updated = 1;
      $update_message = "Agent settings updated.";
    }
  }
}

$device = dbFetchRow("SELECT * FROM `devices` WHERE `device_id` = ?", array($device['device_id']));
$descr  = $device['purpose'];

if ($updated && $update_message)
{
  print_message($update_message);
  log_event('Device Agent configuration changed.', $device['device_id'], 'device');
} elseif ($update_message) {
  print_error($update_message);
}

?>

<form id="edit" name="edit" method="post" class="form-horizontal" action="">
  <input type=hidden name="editing" value="yes">

  <div id="agent">
    <fieldset>
      <legend>Agent Connectivity</legend>
      <div class="control-group">
        <label class="control-label" for="agent_port">Agent Port</label>
        <div class="controls">
          <input type=text name="agent_port" size="32" value="<?php echo(htmlspecialchars(get_dev_attrib($device, 'agent_port'))); ?>"/>
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
