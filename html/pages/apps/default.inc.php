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

$graph_array['height'] = "100";
$graph_array['width']  = "217";
$graph_array['to']     = $config['time']['now'];
$graph_array['from']        = $config['time']['day'];
$graph_array_zoom           = $graph_array;
$graph_array_zoom['height'] = "150";
$graph_array_zoom['width']  = "400";
$graph_array['legend']      = "no";

echo('<table class="table table-hover table-condensed table-striped table-bordered">');
$app_devices = dbFetchRows("SELECT * FROM `devices` AS D, `applications` AS A WHERE D.device_id = A.device_id AND A.app_type = ? ORDER BY hostname", array($vars['app']));

foreach ($app_devices as $app_device)
{
  echo('<tr>');
  echo('<td>');
  if (isset($app_device['app_instance'])) { $app_device['instance_text'] = ' ('.$app_device['app_instance'].')'; }
  echo('<h4>'.generate_device_link($app_device, $app_device['hostname'], array('tab'=>'apps','app'=>$vars['app'])).$app_device['instance_text'].'</h4>');
#  echo(''.$app_device['app_status'].'</div>');

  foreach ($config['app'][$vars['app']]['top'] as $graph_type)
  {
    $graph_array['type']   = "application_".$vars['app']."_".$graph_type;
    $graph_array['id']     = $app_device['app_id'];
    $graph_array_zoom['type']   = "application_".$vars['app']."_".$graph_type;
    $graph_array_zoom['id']     = $app_device['app_id'];

    // $link = generate_url(array('device' => $app_device['device_id'], 'tab' => 'apps','app' => $vars['app'], 'page' => 'device'));
    $link = generate_url(array('page' => 'graphs', 'id' => $graph_array['id'], 'type' => $graph_array['type'], 'from' => $graph_array['from'], 'to' => $graph_array['to']));
    echo(overlib_link($link, generate_graph_tag($graph_array), generate_graph_tag($graph_array_zoom),  NULL));
  }

  echo('</td>');
  echo('</tr>');
}

echo('</table>');

?>
