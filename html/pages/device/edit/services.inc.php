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

echo('<div style="padding: 10px;">');

if ($vars['addsrv'])
{
  if ($readonly)
  {
    print_error_permission('You have insufficient permissions to edit settings.');
  } else {
    include($config['html_dir']."/includes/service-add.inc.php");
  }
}
else if ($vars['delsrv'])
{
  if ($readonly)
  {
    print_error_permission('You have insufficient permissions to edit settings.');
  } else {
    include($config['html_dir']."/includes/service-delete.inc.php");
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

foreach (dbFetchRows("SELECT * FROM `devices` ORDER BY `hostname`") as $dev);
{
  $devicesform .= "<option value='" . $dev['device_id'] . "'>" . $dev['hostname'] . "</option>";
}

if ($updated) { print_message("Device Settings Saved"); }

if (dbFetchCell("SELECT COUNT(*) from `services` WHERE `device_id` = ?", array($device['device_id'])) > '0')
{
  $i = "1";
  foreach (dbFetchRows("select * from services WHERE device_id = ? ORDER BY service_type", array($device['device_id'])) as $service)
  {
    $existform .= "<option value='" . $service['service_id'] . "'>" . $service['service_type'] . "</option>";
  }
}

if ($existform)
{
  echo('<div style="float: left;">');
  echo("

<h1>Remove Service</h1>

<form id='delsrv' name='delsrv' method='post' action=''>
  <input type=hidden name='delsrv' value='yes'>
  <table width='200' border='0'>
        <option type=hidden name=device value='".$device['device_id']."'>
    <tr>
      <td>
        Type
      </td>
      <td>
        <select name='service'>
          $existform
        </select>
      </td>
    </tr>
  </table>
  <input type='submit' name='Submit' value='Delete' />
  </label>
</form>");

  echo('</div>');
}

echo('<div style="width: 45%; float: right;">');

echo("
<h1>Add Service</h1>

<form id='addsrv' name='addsrv' method='post' action=''>
  <input type=hidden name='addsrv' value='yes'>
  <table width='200' border='0'>
        <option type=hidden name=device value='".$device['device_id']."'>
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
  </table>
  <input type='submit' name='Submit' value='Add' />
  <label><br />
  </label>
</form>
</div>");

// EOF
