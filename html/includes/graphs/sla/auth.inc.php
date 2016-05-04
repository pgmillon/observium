<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage graphs
 * @copyright  (C) 2006-2013 Adam Armstrong, (C) 2013-2016 Observium Limited
 *
 */

if (is_numeric($vars['id']))
{
  $sla = dbFetchRow("SELECT * FROM `slas` WHERE `sla_id` = ?", array($vars['id']));

  if (is_numeric($sla['device_id']) && ($auth || device_permitted($sla['device_id'])))
  {
    $device = device_by_id_cache($sla['device_id']);
    $title  = generate_device_link($device);
    $title .= " :: IP SLA :: " . escape_html($sla['sla_index']);
    $auth = TRUE;

    if (!isset($sla['sla_mib'])) { $sla['sla_mib'] = 'cisco-rttmon-mib'; } // CLEANME, remove in r7500, but not before CE 0.16.1
    $mib_lower = strtolower($sla['sla_mib']);
    $index        = $mib_lower . '-' . $sla['sla_index'];
    $unit_text    = 'SLA '.$sla['sla_index'];
    if ($sla['sla_tag'])
    {
      $unit_text .= ' - '.$sla['sla_tag'];
    }
    if ($sla['sla_owner'])
    {
      $unit_text .= " (Owner: ". $sla['sla_owner'] .")";
      $index     .= '-' . $sla['sla_owner'];
    }
    $graph_title  = $device['hostname'] . ' :: ' . $unit_text; // hostname :: SLA XX

    if ($sla['sla_graph'] == 'jitter')
    {
      $sla['rtt_stddev'] = dbFetchCell("SELECT `rtt_stddev` FROM `slas-state` WHERE `sla_id` = ?", array($vars['id']));
    }
  }
}

// EOF
