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

$graph_type = "mempool_usage";

$sql  = "SELECT *, `mempools`.mempool_id as mempool_id";
$sql .= " FROM  `mempools`";
$sql .= " LEFT JOIN  `mempools-state` ON `mempools`.mempool_id = `mempools-state`.mempool_id";
$sql .= " WHERE `device_id` = ?";

$mempools = dbFetchRows($sql, array($device['device_id']));

if (count($mempools))
{ ?>

    <div class="box box-solid">
      <div class="box-header ">
        <a href="<?php echo(generate_url(array('page' => 'device', 'device' => $device['device_id'], 'tab' => 'health', 'metric' => 'mempool'))); ?>">
          <i class="oicon-memory"></i><h3 class="box-title">Memory</h3>
        </a>
      </div>
      <div class="box-body no-padding">

<?php
  echo('<table class="table table-condensed table-striped">');

  foreach ($mempools as $mempool)
  {
    $percent= round($mempool['mempool_perc'], 0);
    $text_descr = rewrite_entity_name($mempool['mempool_descr']);
    if ($mempool['mempool_total'] != '100')
    {
      $total = formatStorage($mempool['mempool_total']);
      $used  = formatStorage($mempool['mempool_used']);
      $free  = formatStorage($mempool['mempool_free']);
    } else {
      // If total == 100, than memory not have correct size and uses percents only
      $total = $mempool['mempool_total'].'%';
      $used  = $mempool['mempool_used'].'%';
      $free  = $mempool['mempool_free'].'%';
    }

    $background = get_percentage_colours($percent);

    $graph_array           = array();
    $graph_array['height'] = "100";
    $graph_array['width']  = "210";
    $graph_array['to']     = $config['time']['now'];
    $graph_array['id']     = $mempool['mempool_id'];
    $graph_array['type']   = $graph_type;
    $graph_array['from']   = $config['time']['day'];
    $graph_array['legend'] = "no";

    $link_array = $graph_array;
    $link_array['page'] = "graphs";
    unset($link_array['height'], $link_array['width'], $link_array['legend']);
    $link = generate_url($link_array);

    $overlib_content = generate_overlib_content($graph_array, $device['hostname'] . " - " . $text_descr);

    $graph_array['width'] = 80; $graph_array['height'] = 20; $graph_array['bg'] = 'ffffff00'; # the 00 at the end makes the area transparent.
//    $graph_array['style'][] = 'margin-top: -6px';

    $minigraph =  generate_graph_tag($graph_array);

    echo('<tr class="'.$background['class'].'">
           <td class="state-marker"></td>
           <td class="entity" style="max-width: 100px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;"><strong>'.generate_entity_link('mempool', $mempool).'</strong></td>
           <td style="width: 90px">'.overlib_link($link, $minigraph, $overlib_content).'</td>
           <td style="width: 200px">'.overlib_link($link, print_percentage_bar (200, 20, $percent, $used."/".$total." (".$percent . "%)", "ffffff", $background['left'],
                                                   $free . " (" . (100 - $percent) . "%)", "ffffff", $background['right']), $overlib_content).'</td>
         </tr>');
  }

  echo("</table>");
  echo("</div></div>");
}

// EOF
