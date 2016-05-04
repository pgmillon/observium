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

// FIXME, obsolete

if ($_SESSION['userlevel'] <= 7)
{
  print_error_permission();
  return;
}

// User level 8-9 only can see config
$readonly = $_SESSION['userlevel'] < 10;

  $page_title[] = "Delete service";

  if ($vars['delsrv'] && !$readonly)
  {
    include($config['html_dir']."/includes/service-delete.inc.php");

    if ($updated) { print_success("Service Deleted!"); }
  }

  foreach (dbFetchRows("SELECT * FROM `services` AS S, `devices` AS D WHERE S.device_id = D.device_id ORDER BY hostname") as $device)
  {
    $servicesform .= "<option value='" . $device['service_id'] . "'>" . $device['service_id'] .  "." . $device['hostname'] . " - " . $device['service_type'] .  "</option>";
  }

  echo("
<h4>Delete Service</h4>
<form id='addsrv' name='addsrv' method='post' action=''>
  <input type=hidden name='delsrv' value='yes'>
  <table width='300' border='0'>
    <tr>
      <td>
        Device
      </td>
      <td>
        <select name='service'>
          $servicesform
        </select>
      </td>
    </tr>
  </table>
<input type='submit' name='Submit' value='Delete' />
</form>");

// EOF
