<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage webui
 * @copyright  (C) 2006-2013 Adam Armstrong, (C) 2013-2016 Observium Limited
 *
 */

if ($device['type'] == 'wireless')
{
  $radios = dbFetchRows("SELECT * FROM `wifi_accesspoints` LEFT JOIN `wifi_radios` ON `wifi_accesspoints`.`wifi_accesspoint_id` = `wifi_radios`.`radio_ap` WHERE `wifi_accesspoints`.`device_id` = ? ", array($device['device_id']));

  foreach ($radios as $radio)
  {
    if (!in_array($radio['location'],$locations)) $locations[] = $radio['location'];
    if (!in_array($radio['model'],$models)) $models[] = $radio['model'];
    if (!in_array($radio['wifi_accesspoint_id'],$aps_ids)) $aps_ids[] = $radio['wifi_accesspoint_id'];
  }

  echo('<table class="table table-striped  table-condensed ">');
  echo('<tr><td style="width: 350px;">');
  echo("<span>" . count($aps_ids) . " APs</span><br /><span>" . count($radios) . " radios</span><br />");
  echo('</td><td><span>Models: ' . implode(", ",$models) .'</span><br /><span>Sites: ' . implode(", ",$locations) . '</span></td>');
  //  echo('<td><span>'. $radios[0]['serial'] ." </span></br><span>" . $radios[0]['fingerprint']. "</span></td></tr>");
  echo("</table>");
}

// EOF
