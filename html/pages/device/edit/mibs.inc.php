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

include($config['install_dir'] . '/includes/polling/functions.inc.php');

// Fetch all MIBs we support for this specific OS
foreach ($config['os'][$device['os']]['mibs'] as $mib) { $mibs[$mib]++; }

// Fetch all MIBs we support for this specific OS group
foreach ($config['os_group'][$device['os_group']]['mibs'] as $mib) { $mibs[$mib]++; }

// Sort alphabetically
ksort($mibs);

// We don't use this for now.
// Could make a second table to force MIBs we don't support for the device.

// Fetch all MIBs we support for specific OSes
foreach ($config['os'] as $os => $data)
{
  foreach ($data['mibs'] as $mib)
  {
    if (in_array($mib, array_keys($mibs)) === FALSE) { $other_mibs[$mib]++; }
  }
}

// Fetch all MIBs we support for specific OS groups
foreach ($config['os_group'] as $os => $data)
{
  foreach ($data['mibs'] as $mib)
  {
    if (in_array($mib, array_keys($mibs)) === FALSE) { $other_mibs[$mib]++; }
  }
}

// Sort alphabetically
ksort($other_mibs);

$attribs = get_dev_attribs($device['device_id']);

if($_POST['toggle_mib'] && isset($mibs[$_POST['toggle_mib']]))
{
  $mib = $_POST['toggle_mib'];

  if (isset($attribs['mib_'.$mib]))
  {
    del_dev_attrib($device, 'mib_' . $mib);
  } else {
    set_dev_attrib($device, 'mib_' . $mib, "0");
  }

  $attribs = get_dev_attribs($device['device_id']);
}

print_message("This page allows you to disable certain MIBs to be polled for a device. This configuration disables all discovery modules using this MIB.");

?>

<div class="row"> <!-- begin row -->

  <div class="col-md-6"> <!-- begin poller options -->

<fieldset>
  <legend>Device MIBs</legend>
</fieldset>

<table class="table table-bordered table-striped table-condensed table-rounded">
  <thead>
    <tr>
      <th>Module</th>
      <th style="width: 80;">Status</th>
      <th style="width: 80;"></th>
    </tr>
  </thead>
  <tbody>

<?php

foreach ($mibs as $mib => $count)
{
  $attrib_set = isset($attribs['mib_'.$mib]);

  echo('<tr><td><strong>'.$mib.'</strong></td><td>');

  if ($attrib_set && $attribs['mib_'.$mib] == 0)
  {
    $attrib_status = '<span class="text-danger">disabled</span>'; $toggle = 'Enable';
    $btn_class = 'btn-success'; $btn_toggle = 'value="Toggle"';
  } else {
    $attrib_status = '<span class="text-success">enabled</span>'; $toggle = "Disable"; $btn_class = "btn-danger";
  }

  echo($attrib_status.'</td><td>');

  echo('<form id="toggle_mib" name="toggle_mib" style="margin: 0px;" method="post" action="">
  <input type="hidden" name="toggle_mib" value="'.$mib.'">
  <button type="submit" class="btn btn-mini '.$btn_class.'" name="Submit" '.$btn_toggle.'>'.$toggle.'</button>
</form>');

  echo('</td></tr>');
}
?>
  </tbody>
</table>

</div> <!-- end poller options -->

  </div> <!-- end row -->
</div> <!-- end container -->
<?php

// EOF
