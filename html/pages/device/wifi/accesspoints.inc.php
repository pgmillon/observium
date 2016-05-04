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
  $i = 1;

  $accesspoints = dbFetchRows('SELECT * FROM `wifi_accesspoints` WHERE `device_id` = ? ORDER BY `location`, `name` ASC', array($device['device_id']));
  $accesspoints_count = count($accesspoints);

  // Pagination
  echo(pagination($vars, $accesspoints_count));

  if ($vars['pageno'])
  {
    $accesspoints = array_chunk($accesspoints, $vars['pagesize']);
    $accesspoints = $accesspoints[$vars['pageno']-1];
  }

  echo('<table class="table table-hover  table-condensed  table-striped" style="vertical-align: middle; margin-top: 5px; margin-bottom: 10px;">');
  echo('<thead><tr><th>Name</th><th>Model</th><th>Location</th><th>Serial/Fingerprint</th></tr></thead>');

  foreach ($accesspoints as $accesspoint)
  {
    echo('<tr><td>');
    echo('<h3><a href="'. generate_url(array('page' => 'device', 'device' => $device['device_id'], 'tab' => 'wifi', 'view' => 'accesspoint', 'accesspoint' => $accesspoint['wifi_accesspoint_id'])).'">' . $accesspoint['name'] .'</a></h4>');

    echo($accesspoint['ap_number'].'</td><td>'.$accesspoint['model'].'</td><td>'.$accesspoint['location'].'</td><td>'.$accesspoint['serial'].'<br />'.$accesspoint['fingerprint']);

    echo('</td></tr>');
  }

  echo('</table>');
  echo(pagination($vars, $accesspoints_count));
}

$page_title[] = 'Access-points';

// EOF
