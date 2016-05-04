<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage poller
 * @copyright  (C) 2006-2013 Adam Armstrong, (C) 2013-2016 Observium Limited
 *
 */

$mib = 'CISCO-RTTMON-MIB';
echo(" $mib ");

$sla_states = &$GLOBALS['config']['mibs'][$mib]['sla_states']; // Events from MIB definitions

$sla_oids = array(
  'jitter'     => array('rttMonLatestJitterOperRTTMin', 'rttMonLatestJitterOperRTTMax', 'rttMonLatestJitterOperNumOfRTT', 'rttMonLatestJitterOperPacketLossSD', 'rttMonLatestJitterOperPacketLossDS'),
  'icmpjitter' => array('rttMonLatestIcmpJitterRTTMin', 'rttMonLatestIcmpJitterRTTMax', 'rttMonLatestIcmpJitterNumRTT', 'rttMonLatestIcmpJitterPktLoss'),
);

$sla_poll = snmpwalk_cache_multi_oid($device, "rttMonLatestRttOperEntry", array(), $mib, mib_dirs('cisco'));
foreach (dbFetchColumn("SELECT DISTINCT `rtt_type` FROM `slas` WHERE `device_id` = ? AND `rtt_type` != ? AND `deleted` = 0 AND `sla_status` = 'active';", array($device['device_id'], 'echo')) as $rtt_type)
{
  switch ($rtt_type)
  {
    case 'jitter': // Additional data for Jitter
      $sla_poll = snmpwalk_cache_multi_oid($device, "rttMonLatestJitterOperEntry", $sla_poll, $mib, mib_dirs('cisco'));
      break;
    case 'icmpjitter': // Additional data for ICMP jitter
      $sla_poll = snmpwalk_cache_multi_oid($device, "rttMonLatestIcmpJitterOperEntry", $sla_poll, 'CISCO-RTTMON-ICMP-MIB', mib_dirs('cisco'));
      break;
  }
}

// Uptime offset for timestamps
$uptime = timeticks_to_sec($poll_device['sysUpTime']);
$uptime_offset = time() - intval($uptime) / 100; /// WARNING. System timezone BOMB

foreach ($sla_poll as $sla_index => $entry)
{
  if (!isset($entry['rttMonLatestRttOperCompletionTime']) && !isset($entry['rttMonLatestRttOperSense']))
  {
    // Skip additional multiindex entries from table
    continue;
  }

  // Convert timestamps to unixtime
  $entry['UnixTime'] = intval(timeticks_to_sec($entry['rttMonLatestRttOperTime']) / 100 + $uptime_offset);

  $sla_state = array(
    'rtt_value'    => $entry['rttMonLatestRttOperCompletionTime'],
    'rtt_sense'    => $entry['rttMonLatestRttOperSense'],
    'rtt_unixtime' => $entry['UnixTime'],
  );

  // SLA event
  $sla_state['rtt_event'] = $sla_states[$sla_state['rtt_sense']]['event'];

  switch($sla_db[$mib_lower][$sla_index]['rtt_type'])
  {
    case 'jitter':
      if (is_numeric($entry['rttMonLatestJitterOperNumOfRTT']))
      {
        $sla_state['rtt_minimum'] = $entry['rttMonLatestJitterOperRTTMin'];
        $sla_state['rtt_maximum'] = $entry['rttMonLatestJitterOperRTTMax'];
        $sla_state['rtt_success'] = $entry['rttMonLatestJitterOperNumOfRTT'];
        $sla_state['rtt_loss']    = $entry['rttMonLatestJitterOperPacketLossSD'] + $entry['rttMonLatestJitterOperPacketLossDS'];
      }
      break;
    case 'icmpjitter':
      if (is_numeric($entry['rttMonLatestIcmpJitterNumRTT']))
      {
        $sla_state['rtt_minimum'] = $entry['rttMonLatestIcmpJitterRTTMin'];
        $sla_state['rtt_maximum'] = $entry['rttMonLatestIcmpJitterRTTMax'];
        $sla_state['rtt_success'] = $entry['rttMonLatestIcmpJitterNumRTT'];
        $sla_state['rtt_loss']    = $entry['rttMonLatestIcmpJitterPktLoss'];
      }
      break;
  }
  $cache_sla[$mib_lower][$sla_index] = $sla_state;
}

// EOF
