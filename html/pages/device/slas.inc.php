<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage webui
 * @copyright  (C) 2006-2013 Adam Armstrong, (C) 2013-2016 Observium Limited
 *
 */

$link_array = array('page'    => 'device',
                    'device'  => $device['device_id'],
                    'tab'     => 'health');

//if (!$vars['metric']) { $vars['metric'] = "overview"; }

$navbar['brand'] = "SLAs";
$navbar['class'] = "navbar-narrow";

if (!isset($vars['rtt_type'])) { $navbar['options']['all']['class'] = "active"; }
$navbar['options']['all']['url']  = generate_url(array('page' => 'device', 'device' => $device['device_id'], 'tab' => 'slas', 'rtt_type' => NULL));
$navbar['options']['all']['text'] = "All SLAs";

$vars_type = $vars;
unset($vars_type['rtt_type']); // Do not filter rtt_type

$sql = generate_sla_query($vars_type);

$slas = dbFetchRows($sql);

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
  if ($vars['rtt_type'] == $type) { $navbar['options'][$type]['class'] = "active"; }
  $navbar['options'][$type]['url']  = generate_url(array('page' => 'device', 'device' => $device['device_id'], 'tab' => 'slas', 'rtt_type' => $type));
  $navbar['options'][$type]['text'] = $text;
}

$navbar['options']['graphs']['text']  = 'Graphs';
$navbar['options']['graphs']['icon']  = 'oicon-chart-up';
$navbar['options']['graphs']['right'] = TRUE;

if ($vars['view'] == "graphs")
{
  $navbar['options']['graphs']['class'] = 'active';
  $navbar['options']['graphs']['url']   = generate_url($vars, array('view' => NULL));
} else {
  $navbar['options']['graphs']['url']    = generate_url($vars, array('view' => "graphs"));
}

print_navbar($navbar);

print_sla_table($vars);

$page_title[] = "SLAs";

// EOF
