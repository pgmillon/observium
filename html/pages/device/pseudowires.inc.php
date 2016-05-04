<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage webui
 * @author     Adam Armstrong <adama@memetic.org>
 * @copyright  (C) 2006-2015 Adam Armstrong
 *
 */

$page_title[] = "Pseudowires";

if(!isset($vars['view'])) { $vars['view'] = 'basic'; }

$link_array = array('page'    => 'device',
                    'device'  => $device['device_id'],
                    'tab'     => 'pseudowires');

$navbar = array('brand' => "Pseudowires", 'class' => "navbar-narrow");

$navbar['options']['basic']['text']   = 'Basic';
// $navbar['options']['details']['text'] = 'Details';
$navbar['options']['minigraphs']     = array('text' => 'Mini Graphs', 'class' => 'pull-right', 'icon' => 'oicon-system-monitor');

foreach ($navbar['options'] as $option => $array)
{
  if ($vars['view'] == $option) { $navbar['options'][$option]['class'] .= " active"; }
  $navbar['options'][$option]['url'] = generate_url($link_array, array('view' => $option));
}

foreach (array('minigraphs') as $type)
{
  foreach ($config['graph_types']['port'] as $option => $data)
  {
    if ($vars['view'] == $type && $vars['graph'] == $option)
    {
      $navbar['options'][$type]['suboptions'][$option]['class'] = 'active';
      $navbar['options'][$type]['text'] .= ' ('.$data['name'].')';
    }
    $navbar['options'][$type]['suboptions'][$option]['text'] = $data['name'];
    $navbar['options'][$type]['suboptions'][$option]['url'] = generate_url($link_array, array('view' => $type, 'graph' => $option));
  }
}

print_navbar($navbar);
unset($navbar);

// Pagination
$vars['pagination'] = TRUE;

// Print events
print_pseudowires($vars);

// EOF
