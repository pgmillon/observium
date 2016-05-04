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
        <a href="<?php echo(generate_url(array('page' => 'device', 'device' => $device['device_id'], 'tab' => 'health', 'metric' => 'mempool'))); ?>">
           <i class="oicon-memory"></i><h3 class="box-title">Memory</h3>
        </a>
      </div>
      <div class="box-body no-padding">

<?php
$mem_used_total = $device_state['ucd_mem']['mem_total'] - $device_state['ucd_mem']['mem_avail'];
$mem_used       = $mem_used_total - ($device_state['ucd_mem']['mem_cached'] + $device_state['ucd_mem']['mem_buffer']);

$used_perc = round(($mem_used / $device_state['ucd_mem']['mem_total']) * 100);
$used_perc_total = round(($mem_used_total / $device_state['ucd_mem']['mem_total']) * 100);
$cach_perc = round(($device_state['ucd_mem']['mem_cached'] / $device_state['ucd_mem']['mem_total']) * 100);
$buff_perc = round(($device_state['ucd_mem']['mem_buffer'] / $device_state['ucd_mem']['mem_total']) * 100);
$avai_perc = round(($device_state['ucd_mem']['mem_avail'] / $device_state['ucd_mem']['mem_total']) * 100);

$graph_array = array();
$graph_array['height'] = "100";
$graph_array['width']  = "509";
$graph_array['to']     = $config['time']['now'];
$graph_array['device'] = $device['device_id'];
$graph_array['type']   = 'device_ucd_memory';
$graph_array['from']   = $config['time']['day'];
$graph_array['legend'] = "no";
$graph = generate_graph_tag($graph_array);

$link_array = $graph_array;
$link_array['page'] = "graphs";
unset($link_array['height'], $link_array['width']);
$link = generate_url($link_array);

$graph_array['width'] = "210";
$overlib_content = generate_overlib_content($graph_array, $device['hostname'] . " - Memory Usage");

// echo(overlib_link($link, $graph, $overlib_content, NULL));

$percentage_bar            = array();
$percentage_bar['border']  = "#E25A00";
$percentage_bar['bg']      = "#f0f0f0";
$percentage_bar['width']   = "100%";
$percentage_bar['text']    = $avai_perc."%";
$percentage_bar['text_c']  = "#E25A00";
$percentage_bar['bars'][0] = array('percent' => $used_perc, 'colour' => '#FFAA66', 'text' => $used_perc_total.'%');
$percentage_bar['bars'][1] = array('percent' => $buff_perc, 'colour' => '#cc0000', 'text' => '');
$percentage_bar['bars'][2] = array('percent' => $cach_perc, 'colour' => '#f0e0a0', 'text' => '');

$swap_used = $device_state['ucd_mem']['swap_total'] - $device_state['ucd_mem']['swap_avail'];
$swap_perc = round(($swap_used / $device_state['ucd_mem']['swap_total']) * 100);
$swap_free_perc = 100 - $swap_perc;

?>

<table class="table table-striped">

  <tr>
    <td colspan="2"><?php echo(overlib_link($link, $graph, $overlib_content, NULL)); ?></td>
  </tr>

  <tr>
    <td class="entity">RAM</td>
    <td style="width: 90%;"><?php echo(percentage_bar($percentage_bar)); ?></td>
  </tr>

  <tr class="small">
    <td colspan="2">
      <div class="row" style="margin-left: 5px;">
         <div class="col-sm-4"><i style="font-size: 7px; line-height: 7px; background-color: #FFAA66; border: 1px #aaa solid;">&nbsp;&nbsp;&nbsp;</i>
          <strong>Used:</strong>    <?php echo(formatStorage($mem_used * 1024).' ('.$used_perc.'%)'); ?></div>
         <div class="col-sm-4"><i style="font-size: 7px; line-height: 7px; background-color: #cc0000; border: 1px #aaa solid;">&nbsp;&nbsp;&nbsp;</i>
          <strong>Buffers:</strong> <?php echo(formatStorage($device_state['ucd_mem']['mem_buffer'] * 1024).' ('.$buff_perc.'%)'); ?></div>
         <div class="col-sm-4"><i style="font-size: 7px; line-height: 7px; background-color: #f0e0a0; border: 1px #aaa solid;">&nbsp;&nbsp;&nbsp;</i>
          <strong>Cached:</strong>  <?php echo(formatStorage($device_state['ucd_mem']['mem_cached'] * 1024).' ('.$cach_perc.'%)'); ?></div>
         <div class="col-sm-4"><i style="font-size: 7px; line-height: 7px; background-color: #ddd;    border: 1px #aaa solid;">&nbsp;&nbsp;&nbsp;</i>
          <strong>Free:</strong>    <?php echo(formatStorage($device_state['ucd_mem']['mem_avail'] * 1024).' ('.$avai_perc.'%)'); ?></div>
         <div class="col-sm-4"><i style="font-size: 7px; line-height: 7px; background-color: #ddd;    border: 1px #fff solid;">&nbsp;&nbsp;&nbsp;</i>
          <strong>Total:</strong>   <?php echo(formatStorage($device_state['ucd_mem']['mem_total'] * 1024)); ?></div>
         <div class="col-sm-4"><i style="font-size: 7px; line-height: 7px; background-color: #356AA0; border: 1px #aaa solid;">&nbsp;&nbsp;&nbsp;</i>
          <strong>Swap:</strong>  <?php echo(formatStorage($swap_used * 1024).' ('.$swap_perc.'%)'); ?></div>
      </div>
    </td>
  </tr>

<?php

/**

$swap_used = $device_state['ucd_mem']['swap_total'] - $device_state['ucd_mem']['swap_avail'];
$swap_perc = round(($swap_used / $device_state['ucd_mem']['swap_total']) * 100);
$swap_free_perc = 100 - $swap_perc;

$background = get_percentage_colours('40');

$percentage_bar            = array();
$percentage_bar['border']  = "#356AA0";
$percentage_bar['bg']      = "#f0f0f0";
$percentage_bar['width']   = "100%";
$percentage_bar['text']    = $swap_free_perc."%";
$percentage_bar['text_c']  = "#356AA0";
$percentage_bar['bars'][0] = array('percent' => $swap_perc, 'colour' => '#356AA0', 'text' => $swap_perc.'%');
?>
  <tr>
    <td class="entity">Swap</td>
    <td><?php echo(percentage_bar($percentage_bar)); ?></td>
  </tr>

  <tr class="small">
    <td colspan="2">
      <div class="row" style="margin-left: 5px;">
         <div class="col-sm-4"><i style="font-size: 7px; line-height: 7px; background-color: #356AA0; border: 1px #aaa solid;">&nbsp;&nbsp;&nbsp;</i>
          <strong>Used:</strong>  <?php echo(formatStorage($swap_used * 1024).' ('.$swap_perc.'%)'); ?></div>
         <div class="col-sm-4"><i style="font-size: 7px; line-height: 7px; background-color: #ddd;    border: 1px #aaa solid;">&nbsp;&nbsp;&nbsp;</i>
          <strong>Free:</strong>  <?php echo(formatStorage($device_state['ucd_mem']['swap_avail'] * 1024).' ('.$swap_free_perc.'%)'); ?></div>
         <div class="col-sm-4"><i style="font-size: 7px; line-height: 7px; background-color: #ddd;    border: 1px #fff solid;">&nbsp;&nbsp;&nbsp;</i>
          <strong>Total:</strong> <?php echo(formatStorage($device_state['ucd_mem']['swap_total'] * 1024)); ?></div>
      </div>
    </td>
  </tr>

*/

?>

</table>

    </div>
  </div>

<?php

// EOF
