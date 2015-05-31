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

$datas = array('processor' => array('icon' => 'oicon-processor'),
               'mempool'   => array('icon' => 'oicon-memory'),
               'storage'   => array('icon' => 'oicon-drive'));
if (isset($health_items['toner'])) { $datas['toner'] = array('icon' => 'oicon-contrast'); }

foreach (array_keys($config['sensor_types']) as $type)
{
  if ($cache['sensor_types'][$type]) { $datas[$type] = $config['sensor_types'][$type]; }
}

if (!$vars['metric']) { $vars['metric'] = "processor"; }
if (!$vars['view']) { $vars['view'] = "detail"; }

$link_array = array('page' => 'health');

$navbar['brand'] = "Health";
$navbar['class'] = "navbar-narrow";

$navbar_count = count($datas);
foreach ($datas as $type => $options)
{
  if ($vars['metric'] == $type) { $navbar['options'][$type]['class'] = "active"; }
  else if ($navbar_count > 5)   { $navbar['options'][$type]['class'] = "icon"; } // Show only icons if too many items in navbar
  if (isset($options['icon']))
  {
    $navbar['options'][$type]['icon'] = $options['icon'];
  }
  $navbar['options'][$type]['url']  = generate_url($link_array, array('metric'=> $type, 'view' => $vars['view']));
  $navbar['options'][$type]['text'] = nicecase($type);
}

$navbar['options']['group'] = array('text' => 'Groups', 'right' => 'true');

$groups = get_type_groups($vars['metric']);

foreach (get_type_groups($vars['metric']) as $group)
{
  if ($group['group_id'] == $vars['group'] || in_array($group['group_id'], $vars['group']) )
  {
    $navbar['options']['group']['class'] = 'active';
    $navbar['options']['group']['text'] .= " (".$group['group_name'].')';
    $navbar['options']['group']['suboptions'][$group['group_id']]['url'] = generate_url($vars, array('group' => NULL));
  }
  $navbar['options']['group']['suboptions'][$group['group_id']]['text'] = $group['group_name'];
  $navbar['options']['group']['suboptions'][$group['group_id']]['url'] = generate_url($vars, array('group' => $group['group_id']));
}

$navbar['options']['graphs']['text']  = 'Graphs';
$navbar['options']['graphs']['icon']  = 'oicon-chart-up';
$navbar['options']['graphs']['right'] = TRUE;

if ($vars['view'] == "graphs")
{
  $navbar['options']['graphs']['class'] = 'active';
  $navbar['options']['graphs']['url']   = generate_url($link_array, array('metric'=> $vars['metric'], 'view' => "detail"));
} else {
  $navbar['options']['graphs']['url']    = generate_url($link_array, array('metric'=> $vars['metric'], 'view' => "graphs"));
}

print_navbar($navbar);

if (isset($datas[$vars['metric']]))
{
  if (is_file('pages/health/'.$vars['metric'].'.inc.php'))
  {
    include('pages/health/'.$vars['metric'].'.inc.php');
  } else {
    $sensor_type = $vars['metric'];

    include('pages/health/sensors.inc.php');
  }
} else {
  print_warning("No sensors of type " . $vars['metric'] . " found.");
}

$pagetitle[] = "Health";

// EOF
