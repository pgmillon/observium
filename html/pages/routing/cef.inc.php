<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage webui
 * @copyright  (C) 2006-2014 Adam Armstrong
 *
 */

$i_i = 0;

echo('<table width=100% border=0 cellpadding=10 class="table table-hover table-bordered table-striped table-condensed">');
echo('<thead><tr><th>Device</th>
         <th>AFI</th>
         <th>Prefixes</th>
         <th><a title="CEF Switching Paths">Paths</a></th>
         <th><a title="Number of packets dropped.">Drop</a></th>
         <th><a title="Number of packets that could not be switched in the normal path and were punted to the next-fastest switching vector.">Punt</a></th>
         <th><a title="Number of packets that could not be switched in the normal path and were punted to the host.<br />For switch paths other than a centralized turbo switch path, punt and punt2host function the same way. With punt2host from a centralized turbo switch path (PAS and RSP), punt will punt the packet to LES, but punt2host will bypass LES and punt directly to process switching.">Punt2Host</a></th>
     </tr></thead>');

// Loop Instances

$cef_query = 'SELECT `cef_switching`.`device_id`, `cef_switching`.`afi`, `cef_switching`.`entPhysicalIndex`,
 COUNT(cef_index) AS paths,
 SUM(`drop`) AS drops,
 SUM(`punt`) AS punts,
 SUM(`punt2host`) AS punt2host,
 `cef_pfx`
 FROM `cef_switching`
 LEFT JOIN `cef_prefix` ON `cef_switching`.`device_id` = `cef_switching`.`device_id`
   AND `cef_switching`.`entPhysicalIndex` = `cef_prefix`.`entPhysicalIndex`
   AND `cef_switching`.`afi` = `cef_prefix`.`afi`
 GROUP BY `cef_switching`.`device_id`, `cef_switching`.`afi`';

foreach (dbFetchRows($cef_query) as $instance)
{

  $device = device_by_id_cache($instance['device_id']);

  echo('<tr>');
  echo('  <td class="entity-title">'.generate_device_link($device, 0, array('tab' => 'routing', 'proto' => 'cef')). '</td>');
  echo '  <td>';
  if ($instance['afi'] == "ipv4") { echo '<span class="green">IPv4</span>'; } elseif($instance['afi'] == "ipv6") { echo '<span class="blue">IPv6</span>'; } else { echo $instance['afi']; }
  echo '</td>';
  echo('  <td>'.$instance['cef_pfx'] . '</td>');
  echo('  <td>'.$instance['paths'] . '</td>');
  echo('  <td>'.$instance['drops'] . '</td>');
  echo('  <td>'.$instance['punts'] . '</td>');
  echo('  <td>'.$instance['punt2host'] . '</td>');
  echo('</tr>');

} // End loop instances

echo('</table>');

// EOF
