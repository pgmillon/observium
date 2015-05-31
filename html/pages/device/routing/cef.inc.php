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

$link_array = array('page'    => 'device',
                    'device'  => $device['device_id'],
                    'tab'     => 'routing',
                    'proto'   => 'cef');

$navbar = array('brand' => "CEF", 'class' => "navbar-narrow");

$navbar['options']['basic']['text']   = 'Basic';
// $navbar['options']['details']['text'] = 'Details';
$navbar['options']['graphs']     = array('text' => 'Graphs', 'class' => 'pull-right', 'icon' => 'oicon-system-monitor');

foreach ($navbar['options'] as $option => $array)
{
  if ($vars['view'] == $option) { $navbar['options'][$option]['class'] .= " active"; }
  $navbar['options'][$option]['url'] = generate_url($link_array,array('view' => $option));
}

print_navbar($navbar);
unset($navbar);
?>
      <table class="table table-bordered table-striped">
      <thead>
      <tr><th><a title="Physical hardware entity">Entity</a></th>
          <th><a title="Address Family">AFI</a></th>
          <th><a title="CEF Switching Path">Path</a></th>
          <th><a title="Number of packets dropped.">Drop</a></th>
          <th><a title="Number of packets that could not be switched in the normal path and were punted to the next-fastest switching vector.">Punt</a></th>
          <th><a title="Number of packets that could not be switched in the normal path and were punted to the host.<br />For switch paths other than a centralized turbo switch path, punt and punt2host function the same way. With punt2host from a centralized turbo switch path (PAS and RSP), punt will punt the packet to LES, but punt2host will bypass LES and punt directly to process switching.">Punt2Host</a></th>
      </tr>
      </thead>

<?php

foreach (dbFetchRows("SELECT * FROM `cef_switching` WHERE `device_id` = ?  ORDER BY `entPhysicalIndex`, `afi`, `cef_index`", array($device['device_id'])) as $cef)
{

  $entity = dbFetchRow("SELECT * FROM `entPhysical` WHERE device_id = ? AND `entPhysicalIndex` = ?", array($device['device_id'], $cef['entPhysicalIndex']));

  $interval = $cef['updated'] - $cef['updated_prev'];

  if (!$entity['entPhysicalModelName'] && $entity['entPhysicalContainedIn'])
  {
    $parent_entity = dbFetchRow("SELECT * FROM `entPhysical` WHERE device_id = ? AND `entPhysicalIndex` = ?", array($device['device_id'], $entity['entPhysicalContainedIn']));
    $entity_name = $entity['entPhysicalName'] . " (" . $parent_entity['entPhysicalModelName'] .")";
  } else {
    $entity_name = $entity['entPhysicalName'] . " (" . $entity['entPhysicalModelName'] .")";
  }

  echo("<tr bgcolor=$bg_colour><td>".$entity_name."</td>
            <td>");
  if ($cef['afi'] == "ipv4") { echo '<span class="green">IPv4</span>'; } elseif($cef['afi'] == "ipv6") { echo '<span class="blue">IPv6</span>'; } else { echo $cef['afi']; }

  echo("</td>
            <td>");

  switch ($cef['cef_path']) {
    case "RP RIB":
      echo '<a title="Process switching with CEF assistance.">RP RIB</a>';
      break;
    case "RP LES":
      echo '<a title="Low-end switching. Centralized CEF switch path.">RP LES</a>';
      break;
    case "RP PAS":
      echo '<a title="CEF turbo switch path.">RP PAS</a>';
      break;
    default:
       echo $cef['cef_path'];
  }

  echo("</td>");
  echo("<td>".format_si($cef['drop']));
  if ($cef['drop'] > $cef['drop_prev']) { echo(" <span style='color:red;'>(".round(($cef['drop']-$cef['drop_prev'])/$interval,2)."/sec)</span>"); }
  echo("</td>");
  echo("<td>".format_si($cef['punt']));
  if ($cef['punt'] > $cef['punt_prev']) { echo(" <span style='color:red;'>(".round(($cef['punt']-$cef['punt_prev'])/$interval,2)."/sec)</span>"); }
  echo("</td>");
  echo("<td>".format_si($cef['punt2host']));
  if ($cef['punt2host'] > $cef['punt2host_prev']) { echo(" <span style='color:red;'>(".round(($cef['punt2host']-$cef['punt2host_prev'])/$interval,2)."/sec)</span>"); }
  echo("</td>");

        echo("</tr>
       ");

  if ($vars['view'] == "graphs")
  {
    $graph_array['height'] = "100";
    $graph_array['width']  = "215";
    $graph_array['to']     = $config['time']['now'];
    $graph_array['id']     = $cef['cef_switching_id'];
    $graph_array['type']   = "cefswitching_graph";

    echo("<tr bgcolor='$bg_colour'><td colspan=6>");

    print_graph_row($graph_array);

    echo("</td></tr>");
  }

}

echo("</table>");

// EOF
