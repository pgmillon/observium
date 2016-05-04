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

$datas = array('overview' => array('icon' => 'oicon-application-list'));

if (dbFetchCell("SELECT COUNT(*) FROM `processors` WHERE `device_id` = ?", array($device['device_id']))) { $datas['processor'] = array('icon' => $config['entities']['processor']['icon']); }
if (dbFetchCell("SELECT COUNT(*) FROM `mempools` WHERE `device_id` = ?", array($device['device_id']))) { $datas['mempool'] = array('icon' => $config['entities']['mempool']['icon']); }
if (dbFetchCell("SELECT COUNT(*) FROM `storage` WHERE `device_id` = ?", array($device['device_id']))) { $datas['storage'] = array('icon' => $config['entities']['storage']['icon']); }
if (dbFetchCell("SELECT COUNT(*) FROM `ucd_diskio` WHERE `device_id` = ?", array($device['device_id']))) { $datas['diskio'] = array('icon' => 'oicon-drive--arrow'); }
if (dbFetchCell("SELECT COUNT(*) FROM `status` WHERE `device_id` = ?", array($device['device_id']))) { $datas['status'] = array('icon' => $config['entities']['status']['icon']); }

$sensors_device = dbFetchRows("SELECT `sensor_class` FROM `sensors` WHERE device_id = ? GROUP BY `sensor_class`", array($device['device_id']));
foreach ($sensors_device as $sensor) { $datas[$sensor['sensor_class']] = array('icon' => $config['sensor_types'][$sensor['sensor_class']]['icon']); }

$link_array = array('page'    => 'device',
                    'device'  => $device['device_id'],
                    'tab'     => 'health');

if (!$vars['metric']) { $vars['metric'] = "overview"; }
if (!$vars['view'])   { $vars['view']   = "details"; }

$navbar['brand'] = "Health";
$navbar['class'] = "navbar-narrow";

$navbar_count = count($datas);
foreach ($datas as $type => $options)
{
  if ($vars['metric'] == $type) { $navbar['options'][$type]['class'] = "active"; }
  else if ($navbar_count > 8 && $type != 'overview') { $navbar['options'][$type]['class'] = "icon"; } // Show only icons if too many items in navbar
  if (isset($options['icon']))
  {
    $navbar['options'][$type]['icon'] = $options['icon'];
  }
  $navbar['options'][$type]['url']  = generate_url(array('page' => 'device', 'device' => $device['device_id'], 'tab' => 'health', 'metric' => $type));
  $navbar['options'][$type]['text'] = nicecase($type);
}

$navbar['options']['graphs']['text']  = 'Graphs';
$navbar['options']['graphs']['icon']  = 'oicon-chart-up';
$navbar['options']['graphs']['right'] = TRUE;

if ($vars['view'] == "graphs")
{
  $navbar['options']['graphs']['class'] = 'active';
  $navbar['options']['graphs']['url']   = generate_url($vars, array('view' => "detail"));
} else {
  $navbar['options']['graphs']['url']    = generate_url($vars, array('view' => "graphs"));
}

print_navbar($navbar);
unset($navbar);

if ($config['sensor_types'][$vars['metric']] || $vars['metric'] == "sensors")
{
  include($config['html_dir']."/pages/device/health/sensors.inc.php");
}
elseif (is_file($config['html_dir']."/pages/device/health/".$vars['metric'].".inc.php"))
{
  include($config['html_dir']."/pages/device/health/".$vars['metric'].".inc.php");
} else {

  echo generate_box_open();

  echo('<table class="table table-condensed table-striped table-hover ">');

  foreach ($datas as $type => $options)
  {
    if ($type != "overview")
    {
      $graph_title = nicecase($type);
      $graph_array['type'] = "device_".$type;
      $graph_array['device'] = $device['device_id'];

      echo('<tr><td>');
      echo('<h3>' . $graph_title . '</h3>');
      print_graph_row($graph_array);
      echo('</td></tr>');
    }
  }
  echo('</table>');

  echo generate_box_close();

}

$page_title[] = "Health";

// EOF
