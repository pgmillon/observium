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

if ($_SESSION['userlevel'] < '5')
{
  include("includes/error-no-perm.inc.php");
} else {

  $pagetitle[] = "Delete service";

  if ($_POST['delsrv'])
  {
    if ($_SESSION['userlevel'] > "5")
    {
      include("includes/service-delete.inc.php");
    }
  }

  foreach (dbFetchRows("SELECT * FROM `services` AS S, `devices` AS D WHERE S.device_id = D.device_id ORDER BY hostname") as $device)
  {
    $servicesform .= "<option value='" . $device['service_id'] . "'>" . $device['service_id'] .  "." . $device['hostname'] . " - " . $device['service_type'] .  "</option>";
  }

  if ($updated) { print_message("Service Deleted!"); }

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

}

// EOF
