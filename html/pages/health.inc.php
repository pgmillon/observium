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

$datas = array('processor' => array('icon' => $config['entities']['processor']['icon']),
               'mempool'   => array('icon' => $config['entities']['mempool']['icon']),
               'storage'   => array('icon' => $config['entities']['storage']['icon']),
               'status'    => array('icon' => $config['entities']['status']['icon']));
if (isset($health_items['toner'])) { $datas['toner'] = array('icon' => 'oicon-contrast'); }

foreach (array_keys($config['sensor_types']) as $type)
{
  if ($cache['sensor_types'][$type]) { $datas[$type] = $config['sensor_types'][$type]; }
}

if (!$vars['metric']) { $vars['metric'] = "processor"; }
if (!$vars['view'])   { $vars['view']   = "detail"; }

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

// Add filter by Physical Class for statuses
if ($vars['metric'] == 'status')
{
  $navbar['options']['class'] = array('text' => 'Physical Class', 'right' => 'true');
  $sql = 'SELECT DISTINCT `entPhysicalClass` FROM `status` WHERE 1' . $cache['where']['devices_permitted'];
  $classes = dbFetchColumn($sql);
  asort($classes);
  foreach ($classes as $class)
  {
    if ($class == '') { $class = OBS_VAR_UNSET; }
    $name = nicecase($class);

    if (isset($navbar['options']['class']['suboptions'][$class])) { continue; } // Heh, class can be NULL and ''
    if ($class == $vars['class'] || (is_array($vars['class']) && in_array($class, $vars['class'])))
    {
      $navbar['options']['class']['class'] = 'active';
      $navbar['options']['class']['text'] .= " (".$name.')';
      $navbar['options']['class']['suboptions'][$class]['url'] = generate_url($vars, array('class' => NULL));
      $navbar['options']['class']['suboptions'][$class]['class'] = 'active';
    } else {
      $navbar['options']['class']['suboptions'][$class]['url'] = generate_url($vars, array('class' => $class));
    }
    $navbar['options']['class']['suboptions'][$class]['text'] = $name;
  }
}

$groups = get_type_groups($vars['metric']);

$navbar['options']['group'] = array('text' => 'Groups', 'right' => TRUE, 'community' => FALSE);
foreach ($groups as $group)
{
  if ($group['group_id'] == $vars['group'] || in_array($group['group_id'], $vars['group']) )
  {
    $navbar['options']['group']['class'] = 'active';
    $navbar['options']['group']['text'] .= ' ('.escape_html($group['group_name']).')';
    $navbar['options']['group']['suboptions'][$group['group_id']]['url'] = generate_url($vars, array('group' => NULL));
    $navbar['options']['group']['suboptions'][$group['group_id']]['class'] = 'active';
  } else {
    $navbar['options']['group']['suboptions'][$group['group_id']]['url'] = generate_url($vars, array('group' => $group['group_id']));
  }
  $navbar['options']['group']['suboptions'][$group['group_id']]['text'] = escape_html($group['group_name']);
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

if (isset($datas[$vars['metric']]) || $vars['metric'] == "sensors")
{
  if (is_file('pages/health/'.$vars['metric'].'.inc.php'))
  {
    include($config['html_dir'].'/pages/health/'.$vars['metric'].'.inc.php');
  } else {
    $sensor_type = $vars['metric'];

    include($config['html_dir'].'/pages/health/sensors.inc.php');
  }
} else {
  print_warning("No sensors of type " . $vars['metric'] . " found.");
}

$page_title[] = "Health";

// EOF
