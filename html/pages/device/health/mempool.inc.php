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

$graph_type = "mempool_usage";

echo("<div style='margin-top: 5px; padding: 0px;'>");
echo('<table class="table table-striped-two table-condensed table-bordered">');

echo("<thead><tr>
        <th>Memory Pool</th>
        <th>History</th>
        <th>Usage</th>
        <th>Used</th>
      </tr></thead>");

$sql  = "SELECT *, `mempools`.mempool_id as mempool_id";
$sql .= " FROM  `mempools`";
$sql .= " LEFT JOIN  `mempools-state` ON  `mempools`.mempool_id =  `mempools-state`.mempool_id";
$sql .= " WHERE `device_id` = ?";

foreach (dbFetchRows($sql, array($device['device_id'])) as $mempool)
{
  $text_descr = rewrite_entity_name($mempool['mempool_descr']);

  $mempool_url   = "graphs/id=".$mempool['mempool_id']."/type=".$graph_type;
  $mini_url = "graph.php?id=".$mempool['mempool_id']."&amp;type=".$graph_type."&amp;from=".$config['time']['day']."&amp;to=".$config['time']['now']."&amp;width=80&amp;height=20&amp;bg=f4f4f4";

  $mempool_popup  = "onmouseover=\"return overlib('<div class=entity-title>".$device['hostname']." - ".$text_descr;
  $mempool_popup .= "</div><img src=\'graph.php?id=" . $mempool['mempool_id'] . "&amp;type=".$graph_type."&amp;from=".$config['time']['month']."&amp;to=".$config['time']['now']."&amp;width=400&amp;height=125\'>";
  $mempool_popup .= "', RIGHT".$config['overlib_defaults'].");\" onmouseout=\"return nd();\"";

  $total = formatStorage($mempool['mempool_total']);
  $used = formatStorage($mempool['mempool_used']);
  $free = formatStorage($mempool['mempool_free']);

  $perc = round($mempool['mempool_used'] / $mempool['mempool_total'] * 100);

  $background = get_percentage_colours($percent);
  $right_background = $background['right'];
  $left_background  = $background['left'];

  echo("<tr><td><a class='entity-title' href='".$mempool_url."' $mempool_popup>" . $text_descr . "</a></td>
           <td width=90><a href='".$mempool_url."'  $mempool_popup><img src='$mini_url'></a></td>
           <td width=200><a href='".$mempool_url."' $mempool_popup>
           ".print_percentage_bar (400, 20, $perc, "$used / $total", "ffffff", $left_background, $free , "ffffff", $right_background)."
            </a></td>
            <td width=50>".$perc."%</td>
         </tr>");

  echo("<tr><td colspan=5>");

  $graph_array['id'] = $mempool['mempool_id'];
  $graph_array['type'] = $graph_type;

  print_graph_row($graph_array);

  echo("</td></tr>");
}

echo("</table>");
echo("</div>");

// EOF
