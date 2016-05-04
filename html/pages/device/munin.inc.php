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

// Sections are printed in the order they exist in $config['graph_sections']
// Graphs are printed in the order they exist in $config['graph_types']

$link_array = array('page'    => 'device',
                    'device'  => $device['device_id'],
                    'tab' => 'munin');

foreach (dbFetchRows("SELECT * FROM munin_plugins WHERE device_id = ? ORDER BY mplug_category, mplug_type", array($device['device_id'])) as $mplug)
{
#  if (strlen($mplug['mplug_category']) == 0) { $mplug['mplug_category'] = "general"; } else {  }
  $graph_enable[$mplug['mplug_category']][$mplug['mplug_type']]['id'] = $mplug['mplug_id'];
  $graph_enable[$mplug['mplug_category']][$mplug['mplug_type']]['title'] = $mplug['mplug_title'];
  $graph_enable[$mplug['mplug_category']][$mplug['mplug_type']]['plugin'] = $mplug['mplug_type'];
}

$navbar['brand'] = "Munin";
$navbar['class'] = "navbar-narrow";

foreach ($graph_enable as $section => $nothing)
{
  if (isset($graph_enable) && is_array($graph_enable[$section]))
  {
    $type = strtolower($section);
    if (!$vars['group']) { $vars['group'] = $type; }
    if ($vars['group'] == $type) { $navbar['options'][$type]['class'] = "active"; }

    $navbar['options'][$type]['url'] = generate_url(array('page' => 'device', 'device' => $device['device_id'], 'tab' => 'munin', 'group' => $type));
    $navbar['options'][$type]['text'] = escape_html(ucwords($section));

  }
}

print_navbar($navbar);

$graph_enable = $graph_enable[$vars['group']];

echo generate_box_open();

echo '<table class="table  table-condensed table-striped table-hover">';

#foreach ($config['graph_types']['device'] as $graph => $entry)
foreach ($graph_enable as $graph => $entry)
{
  $graph_array = array();
  if ($graph_enable[$graph])
  {
    if (!empty($entry['plugin']))
    {
      $graph_title = $entry['title'];
      $graph_array['type'] = "munin_graph";
      $graph_array['device'] = $device['device_id'];
      $graph_array['plugin'] = $entry['plugin'];
    } else {
      $graph_title = $config['graph_types']['device'][$graph]['descr'];
      $graph_array['type'] = "device_" . $graph;
    }

    include("includes/print-device-graph.php");
  }
}

echo '</table>';

echo generate_box_close();

$page_title[] = "Graphs";

// EOF
