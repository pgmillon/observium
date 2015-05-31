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

// Sections are printed in the order they exist in $config['graph_sections']
// Graphs are printed in the order they exist in $config['graph_types']

$link_array = array('page'    => 'device',
                    'device'  => $device['device_id'],
                    'tab' => 'graphs');

foreach (dbFetchRows("SELECT * FROM `device_graphs` WHERE `device_id` = ? AND `enabled` = 1 ORDER BY `graph`", array($device['device_id'])) as $entry)
{
  $section = $config['graph_types']['device'][$entry['graph']]['section'];
  if ($section)
  {
    // Collect only enabled and exists graphs
    $graphs_sections[$section][$entry['graph']] = $entry['enabled'];
  }
}

$navbar['brand'] = "Graphs";
$navbar['class'] = "navbar-narrow";

foreach ($graphs_sections as $section => $text)
{
  $type = strtolower($section);
  if (empty($config['graph_sections'][$section])) { $text = nicecase($type); } else { $text = $config['graph_sections'][$section]; }
  if (!$vars['group']) { $vars['group'] = $type; }
  if ($vars['group'] == $type) { $navbar['options'][$section]['class'] = "active"; }
  $navbar['options'][$section]['url'] = generate_url(array('page' => 'device', 'device' => $device['device_id'], 'tab' => 'graphs', 'group' => $type));
  $navbar['options'][$section]['text'] = $text;
}

print_navbar($navbar);

$graph_enable = $graphs_sections[$vars['group']];

echo('<table class="table table-condensed table-striped table-hover table-bordered">');

foreach ($graph_enable as $graph => $entry)
{
  $graph_array = array();
  if ($graph_enable[$graph])
  {
    $graph_title = $config['graph_types']['device'][$graph]['descr'];
    $graph_array['type'] = "device_" . $graph;

    include("includes/print-device-graph.php");
  }
}

echo('</table>');

$pagetitle[] = "Graphs";

// EOF
