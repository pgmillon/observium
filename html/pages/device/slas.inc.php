<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage webui
 * @copyright  (C) 2006-2015 Adam Armstrong
 *
 */

$rtt_types['all'] = 'All SLAs';

$slas = dbFetchRows("SELECT * FROM `slas` LEFT JOIN `slas-state` USING (`sla_id`) WHERE `device_id` = ? AND `deleted` = 0 ORDER BY `sla_index`", array($device['device_id']));

$link_array = array('page'    => 'device',
                    'device'  => $device['device_id'],
                    'tab'     => 'health');

if (!$vars['metric']) { $vars['metric'] = "overview"; }

$navbar['brand'] = "SLAs";
$navbar['class'] = "navbar-narrow";

$rtt_types = array('all' => 'All');
foreach ($slas as $sla)
{
  $rtt_type = $sla['rtt_type'];

  if (!in_array($rtt_type, $rtt_types))
    if (isset($config['sla_type_labels'][$rtt_type]))
    {
      $text = $config['sla_type_labels'][$rtt_type];
    } else {
      $text = nicecase($rtt_type);
    }

    $rtt_types[$rtt_type] = $text;
}
asort($rtt_types);

foreach ($rtt_types as $type => $text)
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
  {
    continue;
  }

  $name = "SLA #". $sla['sla_index'] ." - ". $rtt_types[$sla['rtt_type']];
  if ($sla['sla_tag'])   { $name .= ": ".$sla['sla_tag']; }
  $name .= ' [Status: '. $sla['sla_status'].', Sense: '.$sla['rtt_sense'] ."]";
  if ($sla['sla_owner']) { $name .= " (Owner: ". $sla['sla_owner'] .")"; }

  if (strpos($sla['rtt_type'], 'jitter') !== FALSE)
  {
    $graph_array['type'] = "device_sla_jitter";
  } else {
    $graph_array['type'] = "device_sla_echo";
  }
  $graph_array['id'] = $sla['sla_id'];
  $graph_array['device'] = $device['device_id'];
  if ($sla['sla_status'] != 'active')
  {
    echo('<tr class="ignore"><td>');
  }
  else if ($sla['rtt_sense'] != 'ok')
  {
    echo('<tr class="warning"><td>');
  } else {
    echo('<tr><td>');
  }
  echo('<h4>'.escape_html($name).'</h4>');

  print_graph_row($graph_array);

  echo('</td></tr>');
}

echo('</table>');

$page_title[] = "SLAs";

// EOF
