<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage graphs
 * @copyright  (C) 2006-2015 Adam Armstrong
 *
 */

// NOTE. This used together with graphs defined from definitions ONLY

switch ($subtype)
{
  case 'sla':
  case 'sla_echo':
  case 'sla_jitter':
    $sla = dbFetchRow("SELECT * FROM `slas` WHERE `device_id` = ? AND `sla_id` = ?", array($device['device_id'], $vars['id']));
    $index        = $sla['sla_index'];
    $graph_title .= ' :: SLA '.$index; // hostname :: SLA XX
    $unit_text    = 'SLA '.$index;
    if ($sla['sla_tag'])
    {
      $unit_text .= ': '.$sla['sla_tag'];
    }
    if ($sla['sla_owner'])
    {
      $unit_text .= " (Owner: ". $sla['sla_owner'] .")";
    }
    break;
}

// EOL
