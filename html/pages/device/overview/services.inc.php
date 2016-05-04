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

$services['total']    = dbFetchCell("SELECT COUNT(service_id) FROM `services` WHERE `device_id` = ?", array($device['device_id']));
$services['up']       = dbFetchCell("SELECT COUNT(service_id) FROM `services` WHERE `device_id` = ? AND `service_status` = '1' AND `service_ignore` ='0'", array($device['device_id']));
$services['down']     = dbFetchCell("SELECT COUNT(service_id) FROM `services` WHERE `device_id` = ? AND `service_status` = '0' AND `service_ignore` = '0'", array($device['device_id']));
$services['disabled'] = dbFetchCell("SELECT COUNT(service_id) FROM `services` WHERE `device_id` = ? AND `service_ignore` = '1'", array($device['device_id']));

if ($services['down']) { $services_colour = $warn_colour_a; } else { $services_colour = $list_colour_a; }

if ($services['total'])
{
?>

  <div class="box box-solid">
    <div class="box-header">
      <i class="oicon-gear"></i><h3 class="box-title">Services</h3>
    </div>
    <div class="box-body no-padding">

<?php

  echo('
<table class="table table-condensed table-striped">
<tr bgcolor='.$services_colour.' align=center><td></td>
<td width=25%><img src="images/16/cog.png" align=absmiddle> '.$services['total'].'</td>
<td width=25% class=green><img src="images/16/cog_go.png" align=absmiddle> '.$services['up'].'</td>
<td width=25% class=red><img src="images/16/cog_error.png" align=absmiddle> '.$services['down'].'</td>
<td width=25% class=grey><img src="images/16/cog_disable.png" align=absmiddle> '.$services['disabled'].'</td></tr>
</table>
<div style="padding: 10px; padding-top: 0px;">

');

  foreach (dbFetchRows("SELECT * FROM services WHERE device_id = ? ORDER BY service_type", array($device['device_id'])) as $data)
  {
    if ($data['service_status'] == "0" && $data['service_ignore'] == "1") { $status = "grey"; }
    if ($data['service_status'] == "1" && $data['service_ignore'] == "1") { $status = "green"; }
    if ($data['service_status'] == "0" && $data['service_ignore'] == "0") { $status = "red"; }
    if ($data['service_status'] == "1" && $data['service_ignore'] == "0") { $status = "blue"; }
    $services['list'][] = '<a class="'.$status.'">' . strtolower($data['service_type']) . '</a>';
  }

  echo implode(', ', $services['list']);

  echo("</div></div></div>");
}

// EOF
