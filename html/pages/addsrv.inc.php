<?php

/**
 * Observium Network Management and Monitoring System
 * Copyright (C) 2006-2015, Adam Armstrong - http://www.observium.org
 *
 * @package    observium
 * @subpackage webui
 * @author     Adam Armstrong <adama@observium.org>
 * @copyright  (C) 2006-2013 Adam Armstrong, (C) 2013-2016 Observium Limited
 *
 */

// Global write permissions required.
if ($_SESSION['userlevel'] < 10)
{
  print_error_permission();
  return;
}

  if ($_POST['addsrv'])
  {
    if ($_SESSION['userlevel'] == '10')
    {
      $updated = '1';

      #FIXME should call add_service (needs more parameters)
      $service_id = dbInsert(array('device_id' => $_POST['device'], 'service_ip' => $_POST['ip'], 'service_type' => $_POST['type'], 'service_desc' => $_POST['descr'], 'service_param' => $_POST['params'], 'service_ignore' => '0'), 'services');

      if ($service_id)
      {
        $message .= $message_break . "Service added (".$service_id.")!";
        $message_break .= "<br />";
      }
    }
  }

  if ($handle = opendir($config['install_dir'] . "/includes/services/"))
  {
    while (false !== ($file = readdir($handle)))
    {
      if ($file != "." && $file != ".." && !strstr($file, "."))
      {
        $servicesform .= "<option value='$file'>$file</option>";
      }
    }
    closedir($handle);
  }

  foreach (dbFetchRows("SELECT * FROM `devices` ORDER BY `hostname`") as $device)
  {
    $devicesform .= "<option value='" . $device['device_id'] . "'>" . $device['hostname'] . "</option>";
  }

  if ($updated) { print_message("Device Settings Saved"); }

  $page_title[] = "Add service";

  echo("
<h4>Add Service</h4>
<form id='addsrv' name='addsrv' method='post' action=''>
  <input type=hidden name='addsrv' value='yes'>
  <table width='200' border='0'>
    <tr>
      <td>
        Device
      </td>
      <td>
        <select name='device'>
          $devicesform
        </select>
      </td>
    </tr>
    <tr>
      <td>
        Type
      </td>
      <td>
        <select name='type'>
          $servicesform
        </select>
      </td>
    </tr>
    <tr>
      <td width='300'><div align='right'>Description</div></td>
      <td colspan='2'><textarea name='descr' cols='50'></textarea></td>
    </tr>
    <tr>
      <td width='300'><div align='right'>IP Address</div></td>
      <td colspan='2'><input name='ip'></textarea></td>
    </tr>
    <tr>
      <td width='300'><div align='right'>Parameters</div></td>
      <td colspan='2'><input name='params'></textarea></td>
    </tr>
   <tr>
  </table>
  <input type='submit' name='Submit' value='Add' />
  <label><br />
  </label>
</form>");

// EOF
