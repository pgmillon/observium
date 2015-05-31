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

$graph_type = "storage_usage";

echo('<table class="table table-striped-two table-condensed table-bordered">');

echo('<thead><tr>
        <th>Drive</th>
        <th style="width: 420px;">Usage</th>
        <th style="width: 50px;">Free</th>
      </tr></thead>');

$row = 1;

$sql  = "SELECT *, `storage`.`storage_id` as `storage_id`";
$sql .= " FROM  `storage`";
$sql .= " LEFT JOIN  `storage-state` ON  `storage`.storage_id =  `storage-state`.storage_id";
$sql .= " WHERE `device_id` = ?";

foreach (dbFetchRows($sql, array($device['device_id'])) as $drive)
{

  $total = $drive['storage_size'];
  $used  = $drive['storage_used'];
  $free  = $drive['storage_free'];
  $perc  = round($drive['storage_perc'], 0);
  $used = formatStorage($used);
  $total = formatStorage($total);
  $free = formatStorage($free);

  $fs_url   = "graphs/id=".$drive['storage_id']."/type=".$graph_type;

  $fs_popup  = "onmouseover=\"return overlib('<div class=entity-title>".$device['hostname']." - ".$drive['storage_descr'];
  $fs_popup .= "</div><img src=\'graph.php?id=" . $drive['storage_id'] . "&amp;type=".$graph_type."&amp;from=".$config['time']['month']."&amp;to=".$config['time']['now']."&amp;width=400&amp;height=125\'>";
  $fs_popup .= "', RIGHT, FGCOLOR, '#e5e5e5');\" onmouseout=\"return nd();\"";

  $background = get_percentage_colours($percent);

  echo("<tr><td><a href='$fs_url' $fs_popup>" . $drive['storage_descr'] . "</a></td>
          <td><a href='$fs_url' $fs_popup>".print_percentage_bar (400, 20, $perc, "$used / $total", "ffffff", $background['left'], $perc . "%", "ffffff", $background['right'])."</a>
          </td><td>" . $free . "</td></tr>");

  $graph_array['id'] = $drive['storage_id'];
  $graph_array['type'] = $graph_type;

  echo('<tr><td colspan="6">');

  print_graph_row($graph_array);

  echo('</td></tr>');

  $row++;
}

echo('</table>');

// EOF
