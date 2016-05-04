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

?>

  <div class="box box-solid">
    <div class="box-header ">
      <i class="oicon-arrow-switch"></i><h3 class="box-title">c6500/7600 Crossbar</h3>
    </div>
    <div class="box-body no-padding">

<?php

echo('<table class="table table-condensed table-striped">');

foreach ($entity_state['group']['c6kxbar'] as $index => $entry)
{
  if (empty($entry['']['cc6kxbarModuleModeSwitchingMode'])) { continue; }

  // FIXME i'm not sure if this is the correct way to decide what entphysical index it is. slotnum+1? :>
  $entity = dbFetchRow("SELECT * FROM entPhysical WHERE device_id = ? AND entPhysicalIndex = ?", array($device['device_id'], $index+1));

  echo("<tr bgcolor=$row_colour>
        <td colspan=5 width=200><strong>".$entity['entPhysicalName']."</strong></td>
        <td colspan=2>");

  switch ($entry['']['cc6kxbarModuleModeSwitchingMode'])
  {
    case "busmode":
     # echo '<a title="Modules in this mode don't use fabric. Backplane is used for both lookup and data forwarding.">Bus</a>';
      break;
    case "crossbarmode":
      echo '<a class="label label-info" title="Modules in this mode use backplane for forwarding decision and fabric for data forwarding.">Crossbar</a>';
      break;
    case "dcefmode":
      echo '<a class="label label-success" title="Modules in this mode use fabric for data forwarding and local forwarding is enabled.">DCEF</a>';
      break;
    default:
      echo '<span class="label">'.$entry['']['cc6kxbarModuleModeSwitchingMode'].'</span>';
  }

  echo("</td>
      </tr>");

  foreach ($entity_state['group']['c6kxbar'][$index] as $subindex => $fabric)
  {
    if (is_numeric($subindex))
    {
      if ($fabric['cc6kxbarModuleChannelFabStatus'] == "ok")
      {
        $fabric['mode_class'] = "success";
      } else {
        $fabric['mode_class'] = "warning";
      }

      $percent_in = $fabric['cc6kxbarStatisticsInUtil'];
      $background_in = get_percentage_colours($percent_in);

      $percent_out = $fabric['cc6kxbarStatisticsOutUtil'];
      $background_out = get_percentage_colours($percent_out);

      $graph_array           = array();
      $graph_array['height'] = "100";
      $graph_array['width']  = "210";
      $graph_array['to']     = $config['time']['now'];
      $graph_array['device']     = $device['device_id'];
      $graph_array['mod']    = $index;
      $graph_array['chan']   = $subindex;
      $graph_array['type']   = "c6kxbar_util";
      $graph_array['from']   = $config['time']['day'];
      $graph_array['legend'] = "no";

      $link_array = $graph_array;
      $link_array['page'] = "graphs";
      unset($link_array['height'], $link_array['width'], $link_array['legend']);
      $link = generate_url($link_array);

      $text_descr = $entity['entPhysicalName'] . " - Fabric " . $subindex;

      $overlib_content = generate_overlib_content($graph_array, $device['hostname'] . " - " . $text_descr);

      $graph_array['width'] = 80; $graph_array['height'] = 20; $graph_array['bg'] = 'ffffff00'; # the 00 at the end makes the area transparent.
      $graph_array['style'][] = 'margin-top: -6px';

      $minigraph =  generate_graph_tag($graph_array);

      echo('<tr class="'.$background['class'].'">
          <td class="state-marker"></td>
          <td width=150><strong>Fabric '.$subindex.'</strong></td>
          <td><span style="font-weight: bold;" class="label label-'.$fabric['mode_class'].'">'.$fabric['cc6kxbarModuleChannelFabStatus']."</span></td>
          <td>".formatRates($fabric['cc6kxbarModuleChannelSpeed']*1000000)."</td>
          <td>".overlib_link($link, $minigraph, $overlib_content)."</td>
          <td width=125>".print_percentage_bar (125, 20, $percent_in, "Ingress", "ffffff", $background['left'], $percent_in . "%", "ffffff", $background['right'])."</td>
          <td width=125>".print_percentage_bar (125, 20, $percent_out, "Egress", "ffffff", $background['left'], $percent_out . "%", "ffffff", $background['right'])."</td>
          </tr>");
    }
  }
}

echo("</table>");
echo("</div></div>");

// EOF
