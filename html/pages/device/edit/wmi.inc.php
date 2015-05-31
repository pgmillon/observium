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
    $wmi_override = $_POST['wmi_override'];
    if ($wmi_override)
    {
      $wmi_hostname = $_POST['wmi_hostname'];
      $wmi_domain   = $_POST['wmi_domain'];
      $wmi_username = $_POST['wmi_username'];
      $wmi_password = $_POST['wmi_password'];
    }

    if ($wmi_override)         { set_dev_attrib($device, 'wmi_override', $wmi_override); } else { del_dev_attrib($device, 'wmi_override'); }
    if (!empty($wmi_hostname)) { set_dev_attrib($device, 'wmi_hostname', $wmi_hostname); } else { del_dev_attrib($device, 'wmi_hostname'); }
    if (!empty($wmi_domain))   { set_dev_attrib($device, 'wmi_domain', $wmi_domain); } else { del_dev_attrib($device, 'wmi_domain'); }
    if (!empty($wmi_username)) { set_dev_attrib($device, 'wmi_username', $wmi_username); } else { del_dev_attrib($device, 'wmi_username'); }
    if (!empty($wmi_password)) { set_dev_attrib($device, 'wmi_password', $wmi_password); } else { del_dev_attrib($device, 'wmi_password'); }

    $update_message = "Device WMI data updated.";
    $updated = 1;
  }
  else
  {
    include("includes/error-no-perm.inc.php");
  }
}

if($_POST['toggle_poller'] && isset($GLOBALS['config']['wmi']['modules'][$_POST['toggle_poller']]))
{
  $module = $_POST['toggle_poller'];
  if (isset($attribs['wmi_poll_'.$module]) && $attribs['wmi_poll_'.$module] != $GLOBALS['config']['wmi']['modules'][$_POST['toggle_poller']])
  {
    del_dev_attrib($device, 'wmi_poll_' . $module);
  } elseif ($GLOBALS['config']['wmi']['modules'][$_POST['toggle_poller']] == 0) {
    set_dev_attrib($device, 'wmi_poll_' . $module, "1");
  } else {
    set_dev_attrib($device, 'wmi_poll_' . $module, "0");
  }
  $attribs = get_dev_attribs($device['device_id']);
}

?>

<script type="text/javascript">
  $(document).ready(function() {
    toggleDisable();
    $("#wmi_override").change(function() {
      toggleDisable();
    });
  });

  function toggleDisable() {
    if (!$("#wmi_override").is(":checked"))
    {
      $('#edit input[type=text], #edit input[type=password]').prop("disabled", true);
    }
    else
    {
      $('#edit input[type=text], #edit input[type=password]').prop("disabled", false);
    }
  }
</script>
<fieldset><legend>WMI Settings</legend></fieldset>
<div class="row">
  <div class="col-md-6">
    <div class="well info_box">
      <div class="title"><i class="oicon-key"></i> Authentication</div>
      <form id="edit" name="edit" method="post" action="" class="form-horizontal">
        <fieldset>
          <input type="hidden" name="editing" value="yes">
          <div class="control-group">
            <label class="control-label" for="wmi_override">Override WMI Config</label>
            <div class="controls">
              <input type="checkbox" id="wmi_override" name="wmi_override" <?php if (get_dev_attrib($device,'wmi_override')) { echo(' checked="1"'); } ?> />
            </div>
          </div>

          <div class="control-group">
            <label class="control-label" for="wmi_hostname">WMI Hostname</label>
            <div class="controls">
              <input name="wmi_hostname" type="text" size="32" value="<?php echo(htmlspecialchars(get_dev_attrib($device,'wmi_hostname'))); ?>" />
            </div>
          </div>

          <div class="control-group">
            <label class="control-label" for="wmi_domain">WMI Domain</label>
            <div class="controls">
              <input name="wmi_domain" type="text" size="32" value="<?php echo(htmlspecialchars(get_dev_attrib($device,'wmi_domain'))); ?>" />
            </div>
          </div>

          <div class="control-group">
            <label class="control-label" for="wmi_username">WMI Username</label>
            <div class="controls">
              <input name="wmi_username" type="text" size="32" value="<?php echo(htmlspecialchars(get_dev_attrib($device,'wmi_username'))); ?>" />
            </div>
          </div>

          <div class="control-group">
            <label class="control-label" for="wmi_password">WMI Password</label>
            <div class="controls">
              <input name="wmi_password" type="password" size="32" value="<?php echo(htmlspecialchars(get_dev_attrib($device,'wmi_password'))); ?>" />
            </div>
          </div>

          <div class="form-actions">
            <button type="submit" class="btn btn-primary" name="submit" value="save"><i class="icon-ok icon-white"></i> Save Changes</button>
          </div>
        </fieldset>
      </form>
    </div>
  </div>
  <div class="col-md-6">
    <div class="well info_box">
      <div class="title"><i class="oicon-gear"></i> Poller Modules</div>
      <table class="table table-bordered table-striped table-condensed table-rounded">
        <thead>
        <tr>
          <th>Module</th>
          <th style="width: 80;">Global</th>
          <th style="width: 80;">Device</th>
          <th style="width: 80;"></th>
        </tr>
        </thead>
        <tbody>
<?php

foreach ($GLOBALS['config']['wmi']['modules'] as $module => $module_status)
{
  echo('<tr><td><b>'.$module.'</b></td><td>');

  echo(($module_status ? '<span class=green>enabled</span>' : '<span class=red>disabled</span>' ));

  echo('</td><td>');

  if (isset($attribs['wmi_poll_'.$module]))
  {
    if ($attribs['wmi_poll_'.$module]) { echo("<span class=green>enabled</span>"); $toggle = "Disable"; $btn_class = "btn-danger";
    } else { echo('<span class=red>disabled</span>'); $toggle = "Enable"; $btn_class = "btn-success";}
  } else {
    if ($module_status) { echo("<span class=green>enabled</span>"); $toggle = "Disable"; $btn_class = "btn-danger";
    } else { echo('<span class=red>disabled</span>'); $toggle = "Enable"; $btn_class = "btn-success";}
  }

  echo('</td><td>');

  echo('<form id="toggle_poller" name="toggle_poller" method="post" action="">
          <input type=hidden name="toggle_poller" value="'.$module.'" />
          <button type="submit" class="btn btn-mini '.$btn_class.'" name="Submit" value="Toggle">'.$toggle.'</button>
          </label>
        </form>');
  echo('</td></tr>');
}

?>
        </tbody>
      </table>
    </div>
  </div>
</div>
<?php

// EOF
