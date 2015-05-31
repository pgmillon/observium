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

$sql  = "SELECT *, `processors`.`processor_id` as `processor_id`";
$sql .= " FROM  `processors`";
$sql .= " LEFT JOIN `processors-state` ON `processors`.`processor_id` = `processors-state`.`processor_id`";
$sql .= " WHERE `processors`.`processor_type` != 'hr-average' AND `device_id` = ?";

$processors = dbFetchRows($sql, array($device['device_id']));

if (count($processors))
{
?>
<div class="well info_box">
    <div class="title"><a href="<?php echo(generate_url(array('page' => 'device', 'device' => $device['device_id'], 'tab' => 'health', 'metric' => 'processor'))); ?>">
       <i class="oicon-processor"></i> Processors</a></div>
    <div class="content">

<?php
    $graph_array = array();
    $graph_array['height'] = "100";
    $graph_array['width']  = "512";
    $graph_array['to']     = $config['time']['now'];
    $graph_array['device'] = $device['device_id'];
    $graph_array['type']   = 'device_processor';
    $graph_array['from']   = $config['time']['day'];
    $graph_array['legend'] = "no";
    $graph = generate_graph_tag($graph_array);

    $link_array = $graph_array;
    $link_array['page'] = "graphs";
    unset($link_array['height'], $link_array['width']);
    $graph_link = generate_url($link_array);

    $link = generate_url(array('page' => 'device', 'device' => $device['device_id'], 'tab' => 'health', 'metric' => 'processor'));

    $graph_array['width']  = "210";
    $overlib_content = generate_overlib_content($graph_array, $device['hostname'] . " - Processor Usage");

    echo(overlib_link($graph_link, $graph, $overlib_content, NULL));

    $graph_array['width'] = 80; $graph_array['height'] = 20; $graph_array['bg'] = 'ffffff00'; # the 00 at the end makes the area transparent.
    $graph_array['style'][] = 'margin-top: -6px';

    $minigraph = generate_graph_tag($graph_array);

#  echo('<table class="table table-condensed-more table-striped table-bordered">');

  $numprocs = 0;
  $total_percent = 0;

  foreach ($processors as $proc)
  {
    $text_descr = rewrite_entity_name($proc['processor_descr']);

    # disable short hrDeviceDescr. need to make this prettier.
    #$text_descr = rewrite_hrDevice($proc['processor_descr']);
    $percent = $proc['processor_usage'];
    $total_percent += $percent;
    ++$numprocs;

#    $graph_array           = array();
#    $graph_array['height'] = "100";
#    $graph_array['width']  = "210";
#    $graph_array['to']     = $config['time']['now'];
#    $graph_array['id']     = $proc['processor_id'];
#    $graph_array['type']   = $graph_type;
#    $graph_array['from']   = $config['time']['day'];
#    $graph_array['legend'] = "no";

#    $link_array = $graph_array;
#    $link_array['page'] = "graphs";
#    unset($link_array['height'], $link_array['width'], $link_array['legend']);
#    $link = generate_url($link_array);

#    $overlib_content = generate_overlib_content($graph_array, $device['hostname'] . " - " . $text_descr);

#    $graph_array['width'] = 80; $graph_array['height'] = 20; $graph_array['bg'] = 'ffffff00'; # the 00 at the end makes the area transparent.
#    $graph_array['style'][] = 'margin-top: -6px';

#    $minigraph =  generate_graph_tag($graph_array);

#    echo('<tr>
#           <td><span class="entity">'.overlib_link($link, $text_descr, $overlib_content).'</span></td>
#           <td width=90>'.overlib_link($link, $minigraph, $overlib_content).'</td>
#           <td width=200>'.overlib_link($link, print_percentage_bar (200, 20, $percent, NULL, "ffffff", $background['left'], $percent . "%", "ffffff", $background['right']), $overlib_content).'
#           </a></td>
#         </tr>');
  }

  $average_percent = round($total_percent / $numprocs);
  $background = get_percentage_colours($average_percent);

    echo('<table class="table table-condensed-more table-striped table-bordered">');
    echo('<tr>
           <td><span class="entity">'.overlib_link($link, $text_descr, $overlib_content).'</span><span class="label pull-right" style="margin-top: 2px; font-size: 11px;"><i class="icon-remove"></i> '.$numprocs.'</span></td>
           <td style="width: 200px">'.overlib_link($link, print_percentage_bar(200, 20, $average_percent, NULL, "ffffff", $background['left'], $average_percent . "%", "ffffff", $background['right']), $overlib_content).'</td>
         </tr>');

  echo("</table>");
  echo("</div></div>");
}

// EOF
