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

$sla_types['all'] = 'All SLAs';

$slas = dbFetchRows("SELECT * FROM `slas` WHERE `device_id` = ? AND `deleted` = 0 ORDER BY `sla_nr`", array($device['device_id']));

$link_array = array('page'    => 'device',
                    'device'  => $device['device_id'],
                    'tab'     => 'health');

if (!$vars['metric']) { $vars['metric'] = "overview"; }

$navbar['brand'] = "SLAs";
$navbar['class'] = "navbar-narrow";

$sla_types = array('all' => 'All');
foreach ($slas as $sla)
{
  $sla_type = $sla['rtt_type'];

  if (!in_array($sla_type, $sla_types))
    if (isset($config['sla_type_labels'][$sla_type]))
    {
      $text = $config['sla_type_labels'][$sla_type];
    } else {
      $text = ucfirst($sla_type);
    }

    $sla_types[$sla_type] = $text;
}
asort($sla_types);

foreach ($sla_types as $type => $text)
{

  if (!$vars['view']) { $vars['view'] = $type; }

  if ($vars['view'] == $type) { $navbar['options'][$type]['class'] = "active"; }
  $navbar['options'][$type]['url']  = generate_url(array('page' => 'device', 'device' => $device['device_id'], 'tab' => 'slas', 'view' => $type));
  $navbar['options'][$type]['text'] = $text;
}

print_navbar($navbar);

echo('<table class="table table-bordered table-condensed table-striped">');

foreach ($slas as $sla)
{
  if ($vars['view'] != 'all' && $vars['view'] != $sla['rtt_type'])
    continue;

  $name = "SLA #". $sla['sla_nr'] ." - ". $sla_types[$sla['rtt_type']];
  if ($sla['tag'])
    $name .= ": ".$sla['tag'];
  if ($sla['owner'])
    $name .= " (Owner: ". $sla['owner'] .")";

  $graph_array['type'] = "device_sla";
  $graph_array['id'] = $sla['sla_id'];
  $graph_array['device'] = $device['device_id'];
  echo('<tr><td>');
  echo('<h4>'.htmlentities($name).'</h4>');

  print_graph_row($graph_array);

  echo('</td></tr>');
}

echo('</table>');

$pagetitle[] = "SLAs";

// EOF
