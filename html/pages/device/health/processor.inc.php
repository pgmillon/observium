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

$graph_type = "processor_usage";

echo("<div style='margin-top: 5px; padding: 0px;'>");

echo('<table class="table table-striped-two table-condensed table-bordered">');

echo("<thead><tr>
        <th>Processor</th>
        <th>History</th>
        <th>Usage</th>
      </tr></thead>");

$i = '1';

$sql  = "SELECT *, `processors`.`processor_id` as `processor_id`";
$sql .= " FROM  `processors`";
$sql .= " LEFT JOIN `processors-state` ON `processors`.processor_id = `processors-state`.processor_id";
$sql .= " WHERE `device_id` = ?";

foreach (dbFetchRows($sql, array($device['device_id'])) as $proc)
{
  $proc_url   = "graphs/id=".$proc['processor_id']."/type=".$graph_type;

  $mini_url = "graph.php?id=".$proc['processor_id']."&amp;type=".$graph_type."&amp;from=".$config['time']['day']."&amp;to=".$config['time']['now']."&amp;width=80&amp;height=20&amp;bg=f4f4f4";

  $text_descr = $proc['processor_descr'];

  $text_descr = rewrite_entity_name($text_descr);

  $proc_popup  = "onmouseover=\"return overlib('<div class=entity-title>".$device['hostname']." - ".$text_descr;
  $proc_popup .= "</div><img src=\'graph.php?id=" . $proc['processor_id'] . "&amp;type=".$graph_type."&amp;from=".$config['time']['month']."&amp;to=".$config['time']['now']."&amp;width=400&amp;height=125\'>";
  $proc_popup .= "', RIGHT".$config['overlib_defaults'].");\" onmouseout=\"return nd();\"";

  $percent = round($proc['processor_usage']);

  $background = get_percentage_colours($percent);

  echo("<tr>
         <td class='entity-title'><a href='".$proc_url."' $proc_popup>" . $text_descr . "</a></td>
         <td width=90><a href='".$proc_url."'  $proc_popup><img src='$mini_url'></a></td>
         <td width=200><a href='".$proc_url."' $proc_popup>
         ".print_percentage_bar (400, 20, $percent, $percent."%", "ffffff", $background['left'], (100 - $percent)."%" , "ffffff", $background['right'])."
          </a></td>
       </tr>");

  echo("<tr><td colspan=5>");

  $graph_array['id'] = $proc['processor_id'];
  $graph_array['type'] = $graph_type;

  print_graph_row($graph_array);
}

echo("</table>");
echo("</div>");

// EOF
