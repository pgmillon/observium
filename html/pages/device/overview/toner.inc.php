<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage webui
 * @author     Adam Armstrong <adama@observium.org>
 * @copyright  (C) 2006-2013 Adam Armstrong, (C) 2013-2016 Observium Limited
 *
 */

$graph_type = "toner_usage";

$toners = dbFetchRows("SELECT * FROM `toner` WHERE `device_id` = ?", array($device['device_id']));

if (count($toners))
{
?>
  <div class="box box-solid">
    <div class="box-header ">
      <a href="<?php echo(generate_url(array('page' => 'device', 'device' => $device['device_id'], 'tab' => 'printing'))); ?>">
        <i class="oicon-contrast"></i><h3 class="box-title">Toner</h3>
      </a>
    </div>
    <div class="box-body no-padding">

<?php
  echo('<table class="table table-condensed table-striped">');

  foreach ($toners as $toner)
  {
    $percent = round($toner['toner_current'], 0);
    //$total = formatStorage($toner['toner_size']);
    //$free = formatStorage($toner['toner_free']);
    //$used = formatStorage($toner['toner_used']);

    $background = toner_to_colour($toner['toner_descr'], $percent);

    $background_percent = get_percentage_colours($percent - 100);

    $graph_array           = array();
    $graph_array['height'] = "100";
    $graph_array['width']  = "210";
    $graph_array['to']     = $config['time']['now'];
    $graph_array['id']     = $toner['toner_id'];
    $graph_array['type']   = $graph_type;
    $graph_array['from']   = $config['time']['day'];
    $graph_array['legend'] = "no";

    $link_array = $graph_array;
    $link_array['page'] = "graphs";
    unset($link_array['height'], $link_array['width'], $link_array['legend']);
    $link = generate_url($link_array);

    $overlib_content = generate_overlib_content($graph_array, $device['hostname'] . " - " . $toner['toner_descr']);

    $graph_array['width'] = 80; $graph_array['height'] = 20; $graph_array['bg'] = 'ffffff00'; # the 00 at the end makes the area transparent.
    $graph_array['style'][] = 'margin-top: -6px';

    $minigraph =  generate_graph_tag($graph_array);

    $percent_text = ($percent < 0 ? "Unknown" : $percent . "%");
    echo('<tr class="'.$background_percent['class'].'">
           <td class="state-marker"></td>
           <td class="entity">'.overlib_link($link, $toner['toner_descr'], $overlib_content)."</td>
           <td style='width: 90px;'>".overlib_link($link, $minigraph, $overlib_content)."</td>
           <td style='width: 200px;'>".overlib_link($link, print_percentage_bar(400, 20, $percent, $percent_text, 'ffffff', $background['right'], $free, "ffffff", $background['left']), $overlib_content)."</td>
         </tr>");
  }

  echo("</table>");
  echo("</div></div>");
}

unset ($toner_rows);

// EOF
