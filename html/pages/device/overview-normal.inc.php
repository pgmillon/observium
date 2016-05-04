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

$overview = 1;

$ports['total']    = dbFetchCell("SELECT COUNT(*) FROM `ports` WHERE device_id = ?", array($device['device_id']));
$ports['up']       = dbFetchCell("SELECT COUNT(*) FROM `ports` WHERE device_id = ? AND `ifAdminStatus` = 'up' AND (`ifOperStatus` = 'up' OR `ifOperStatus` = 'monitoring')", array($device['device_id']));
$ports['down']     = dbFetchCell("SELECT COUNT(*) FROM `ports` WHERE device_id = ? AND `ifAdminStatus` = 'up' AND (`ifOperStatus` = 'lowerLayerDown' OR `ifOperStatus` = 'down')", array($device['device_id']));
$ports['disabled'] = dbFetchCell("SELECT COUNT(*) FROM `ports` WHERE device_id = ? AND `ifAdminStatus` = 'down'", array($device['device_id']));

$services['total']    = dbFetchCell("SELECT COUNT(service_id) FROM `services` WHERE `device_id` = ?", array($device['device_id']));
$services['up']       = dbFetchCell("SELECT COUNT(service_id) FROM `services` WHERE `device_id` = ? AND `service_status` = '1' AND `service_ignore` ='0'", array($device['device_id']));
$services['down']     = dbFetchCell("SELECT COUNT(service_id) FROM `services` WHERE `device_id` = ? AND `service_status` = '0' AND `service_ignore` = '0'", array($device['device_id']));
$services['disabled'] = dbFetchCell("SELECT COUNT(service_id) FROM `services` WHERE `device_id` = ? AND `service_ignore` = '1'", array($device['device_id']));

if ($services['down']) { $services_colour = $warn_colour_a; } else { $services_colour = $list_colour_a; }
if ($ports['down']) { $ports_colour = $warn_colour_a; } else { $ports_colour = $list_colour_a; }
?>

<div class="row">
<div class="col-md-6">

<?php
/* Begin Left Pane */

include("overview/information.inc.php");

include("overview/ports.inc.php");

if ($services['total'])
{
?>

<div class="box box-solid info_box">
    <div class="title"><i class="oicon-network-ethernet"></i> Services</div>
    <div class="content">

<?php

  echo("
<table class='table table-condensed table-striped '>
<tr bgcolor=$services_colour align=center><td></td>
<td width=25%><img src='images/16/cog.png' align=absmiddle> ".$services['total']."</td>
<td width=25% class=green><img src='images/16/cog_go.png' align=absmiddle> ".$services['up']."</td>
<td width=25% class=red><img src='images/16/cog_error.png' align=absmiddle> ".$services['down']."</td>
<td width=25% class=grey><img src='images/16/cog_disable.png' align=absmiddle> ".$services['disabled']."</td></tr>
</table>");

  echo("<div style='padding: 8px; font-size: 11px; font-weight: bold;'>");

  foreach (dbFetchRows("SELECT * FROM services WHERE device_id = ? ORDER BY service_type", array($device['device_id'])) as $data)
  {
    if ($data['service_status'] == "0" && $data['service_ignore'] == "1") { $status = "grey"; }
    if ($data['service_status'] == "1" && $data['service_ignore'] == "1") { $status = "green"; }
    if ($data['service_status'] == "0" && $data['service_ignore'] == "0") { $status = "red"; }
    if ($data['service_status'] == "1" && $data['service_ignore'] == "0") { $status = "blue"; }
    echo("$break<a class=$status>" . strtolower($data['service_type']) . "</a>");
    $break = ", ";
  }

  echo("</div>");
  echo("</div></div>");
}

  include("overview/alertlog.inc.php");

if ($config['enable_syslog'])
{
  if (dbFetchCell("SELECT COUNT(*) from `syslog` WHERE `device_id` = ?", array($device['device_id'])))
  {
    include("overview/syslog.inc.php");
  }
}

echo("</div>");
/* End Left Pane */

/* Begin Right Pane */
echo('<div class="col-md-6">');

if ($device['os_group'] == "unix")
{
  include("overview/processors-unix.inc.php");
} else {
  include("overview/processors.inc.php");
}

if (is_array($device_state['ucd_mem']))
{
  include("overview/ucd_mem.inc.php");
} else {
  include("overview/mempools.inc.php");
}

include("overview/storage.inc.php");

if (is_array($entity_state['group']['c6kxbar']))
{
  include("overview/c6kxbar.inc.php");
}

include("overview/toner.inc.php");
include("overview/status.inc.php");
include("overview/sensors.inc.php");

include("overview/events.inc.php");

/* End Right Pane */

?>

  </div>
</div>

<?php

// EOF
