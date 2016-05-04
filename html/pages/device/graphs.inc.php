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
                    'tab' => 'graphs');

foreach ($device['graphs'] as $entry)
{
  if (isset($entry['enabled']) && !$entry['enabled']) { continue; } // Skip disabled graphs

  $section = $config['graph_types']['device'][$entry['graph']]['section'];
  if (in_array($section, $config['graph_sections']))
  {
    // Collect only enabled and exists graphs
    $graphs_sections[$section][$entry['graph']] = $entry['enabled'];
  }
}

if (OBSERVIUM_EDITION != 'community')
{
  // Custom OIDs
  $sql  = "SELECT * FROM `oids_assoc`";
  $sql .= " LEFT JOIN `oids` USING(`oid_id`)";
  $sql .= " WHERE `device_id` = ?";

  $custom_graphs = dbFetchRows($sql, array($device['device_id']));

  if (count($custom_graphs))
  {
    $graphs_sections['custom'] = TRUE;
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

echo('<div class="box box-solid"><table class="table table-condensed table-striped table-hover ">');

if ($vars['group'] == "custom" && $graphs_sections['custom'])
{
  foreach ($custom_graphs as $graph)
  {
    $graph_array = array();
      $graph_title         = $graph['oid_descr'];
      $graph_array['type'] = "customoid_graph";
      $graph_array['id']   = $graph['oid_assoc_id'];

      echo('<tr><td>');

      echo('<h3>' . $graph_title . '</h4>');

      print_graph_row($graph_array);

      echo('</td></tr>');

  }

} else {

  foreach ($graph_enable as $graph => $entry)
  {
    $graph_array = array();
    if ($graph_enable[$graph])
    {
      $graph_title = $config['graph_types']['device'][$graph]['descr'];
      $graph_array['type'] = "device_" . $graph;
      $graph_array['device'] = $device['device_id'];

      echo('<tr><td>');

      echo('<h3>' . $graph_title . '</h4>');

      print_graph_row($graph_array);

      echo('</td></tr>');
    }
  }
}

echo('</table></div>');

$page_title[] = "Graphs";

// EOF
