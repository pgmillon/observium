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

$mib = 'DISMAN-PING-MIB';
echo("$mib ");

// Base results table
$sla_poll = snmpwalk_cache_multi_oid($device, "pingResultsEntry", array(), $mib, mib_dirs());

// Additional mibs for vendor specific Types
$vendor_mib = FALSE;
if (is_device_mib($device, 'JUNIPER-PING-MIB', FALSE))
{
  // JUNIPER-PING-MIB
  echo("JUNIPER-PING-MIB ");
  $vendor_mib = 'JUNIPER-PING-MIB';
  $sla_poll = snmpwalk_cache_multi_oid($device, "jnxPingResultsEntry", $sla_poll, $vendor_mib, mib_dirs("juniper"));
  //$sla_poll = snmpwalk_cache_multi_oid($device, "jnxPingResultsStatus", $sla_poll, $vendor_mib, mib_dirs("juniper"));
  //$sla_poll = snmpwalk_cache_multi_oid($device, "jnxPingResultsTime", $sla_poll, $vendor_mib, mib_dirs("juniper"));
}
else if (is_device_mib($device, 'HH3C-NQA-MIB', FALSE))
{
  // HH3C-NQA-MIB
  echo("HH3C-NQA-MIB ");
  $vendor_mib = 'HH3C-NQA-MIB';
  $sla_poll = snmpwalk_cache_multi_oid($device, "hh3cNqaResultsEntry", $sla_poll, $vendor_mib, mib_dirs("hh3c"));

  //$flags = OBS_SNMP_ALL ^ OBS_QUOTES_STRIP;
  //$sla_history = snmpwalk_cache_threepart_oid($device, "Hh3cNqaStatisticsResultsEntry", array(), $vendor_mib, mib_dirs("hh3c"), $flags);
  // walk of separate oids not do sppedup and in some situations longer
  //foreach ($sla_history as $sla_owner => $data)
  //{
  //  foreach ($data as $sla_name => $entry)
  //  {
  //    $index = $sla_owner . '.' . $sla_name;
  //    // Find last history entry (by highest key)
  //    $last = max(array_keys($entry));
  //
  //    // Add this entry to main poll array and get timestamp entry
  //    //$sla_poll[$index] = array_merge($sla_poll[$index], $entry[$last]);
  //  }
  //}
  //unset($sla_history, $last, $index);
} else {
  // Heh, DISMAN-PING-MIB stores correct timestamp and states in huge history table, here trick for get last one
  // FIXME need found more speedup way! but currently only vendor specific is best!
  $flags = OBS_SNMP_ALL ^ OBS_QUOTES_STRIP;
  $sla_history = snmpwalk_cache_threepart_oid($device, "pingProbeHistoryStatus", array(), $mib, mib_dirs(), $flags);
  //$sla_history = snmpwalk_cache_threepart_oid($device, "pingProbeHistoryTime", $sla_history, $mib, mib_dirs(), $flags);
  foreach ($sla_history as $sla_owner => $data)
  {
    foreach ($data as $sla_index => $entry)
    {
      $index = $sla_owner . '.' . $sla_index;
      // Find last history entry (by highest key)
      $last = max(array_keys($entry));

      // Add this entry to main poll array and get timestamp entry
      $sla_poll[$index]['pingProbeHistoryStatus'] = $entry[$last]['pingProbeHistoryStatus'];
      //$sla_poll[$index]['pingProbeHistoryTime']   = $entry[$last]['pingProbeHistoryTime'];
    }
  }
  unset($sla_history, $last, $index);
}
if (OBS_DEBUG > 1)
{
  print_vars($sla_poll);
}

// SLA states from MIB definitions
$sla_states = &$GLOBALS['config']['mibs'][$mib]['sla_states'];

foreach ($sla_poll as $index => $entry)
{
  if (($vendor_mib == 'JUNIPER-PING-MIB' && !isset($entry['jnxPingResultsStatus'])) || !isset($entry['pingResultsOperStatus']))
  {
    // Skip additional multiindex entries from table
    continue;
  }

  list($sla_owner, $sla_index) = explode('.', $index, 2);

  $sla_state = array(
    'rtt_value'    => $entry['pingResultsAverageRtt'],
    'rtt_minimum'  => $entry['pingResultsMinRtt'],
    'rtt_maximum'  => $entry['pingResultsMaxRtt'],
    'rtt_success'  => $entry['pingResultsProbeResponses'],
    'rtt_loss'     => $entry['pingResultsSentProbes'] - $entry['pingResultsProbeResponses'],
  );

  // Vendor specific changes
  switch ($vendor_mib)
  {
    case 'JUNIPER-PING-MIB':
      $sla_state['rtt_value']   = $entry['jnxPingResultsRttUs'] / 1000;
      $sla_state['rtt_minimum'] = $entry['jnxPingResultsMinRttUs'] / 1000;
      $sla_state['rtt_maximum'] = $entry['jnxPingResultsMaxRttUs'] / 1000;
      // Standard deviation
      $sla_state['rtt_stddev']  = $entry['jnxPingResultsStdDevRttUs'] / 1000;

      $sla_state['rtt_sense'] = $entry['jnxPingResultsStatus'];
      $entry['UnixTime']      = $entry['jnxPingResultsTime'];
      break;
    case 'HH3C-NQA-MIB':
      // Note, Stats table is not correct place for values, because in stats used long intervals > 200-300 sec
      //$sla_state['rtt_value']   = $entry['hh3cNqaStatResAverageRtt'];
      //$sla_state['rtt_minimum'] = $entry['hh3cNqaStatResMinRtt'];
      //$sla_state['rtt_maximum'] = $entry['hh3cNqaStatResMaxRtt'];

      // HH3C-NQA-MIB not has any status/sense entry, use pseudo sense
      // FIXME. Need more snmpwalks examples with other errors
      //HH3C-NQA-MIB::hh3cNqaResultsRttNumDisconnects."cncback"."1" = Gauge32: 0
      //HH3C-NQA-MIB::hh3cNqaResultsRttNumDisconnects."cncmaster"."oper" = Gauge32: 0
      //HH3C-NQA-MIB::hh3cNqaResultsRttNumDisconnects."imcl2topo"."ping" = Gauge32: 0
      //HH3C-NQA-MIB::hh3cNqaResultsRttTimeouts."cncback"."1" = Gauge32: 0
      //HH3C-NQA-MIB::hh3cNqaResultsRttTimeouts."cncmaster"."oper" = Gauge32: 0
      //HH3C-NQA-MIB::hh3cNqaResultsRttTimeouts."imcl2topo"."ping" = Gauge32: 1
      //HH3C-NQA-MIB::hh3cNqaResultsRttBusies."cncback"."1" = Gauge32: 0
      //HH3C-NQA-MIB::hh3cNqaResultsRttBusies."cncmaster"."oper" = Gauge32: 0
      //HH3C-NQA-MIB::hh3cNqaResultsRttBusies."imcl2topo"."ping" = Gauge32: 0
      //HH3C-NQA-MIB::hh3cNqaResultsRttNoConnections."cncback"."1" = Gauge32: 0
      //HH3C-NQA-MIB::hh3cNqaResultsRttNoConnections."cncmaster"."oper" = Gauge32: 0
      //HH3C-NQA-MIB::hh3cNqaResultsRttNoConnections."imcl2topo"."ping" = Gauge32: 0
      //HH3C-NQA-MIB::hh3cNqaResultsRttDrops."cncback"."1" = Gauge32: 0
      //HH3C-NQA-MIB::hh3cNqaResultsRttDrops."cncmaster"."oper" = Gauge32: 0
      //HH3C-NQA-MIB::hh3cNqaResultsRttDrops."imcl2topo"."ping" = Gauge32: 0
      //HH3C-NQA-MIB::hh3cNqaResultsRttSequenceErrors."cncback"."1" = Gauge32: 0
      //HH3C-NQA-MIB::hh3cNqaResultsRttSequenceErrors."cncmaster"."oper" = Gauge32: 0
      //HH3C-NQA-MIB::hh3cNqaResultsRttSequenceErrors."imcl2topo"."ping" = Gauge32: 0
      //HH3C-NQA-MIB::hh3cNqaResultsRttStatsErrors."cncback"."1" = Gauge32: 0
      //HH3C-NQA-MIB::hh3cNqaResultsRttStatsErrors."cncmaster"."oper" = Gauge32: 0
      //HH3C-NQA-MIB::hh3cNqaResultsRttStatsErrors."imcl2topo"."ping" = Gauge32: 0
      if ($sla_state['rtt_success'] > 0)
      {
        if ($sla_state['rtt_value'] > 0)
        {
          $sla_state['rtt_sense'] = 'responseReceived';
        }
        else if ($entry['hh3cNqaResultsRttTimeouts'] > 0)
        {
          $sla_state['rtt_sense'] = 'requestTimedOut';
        } else {
          $sla_state['rtt_sense'] = 'internalError'; // or 'unknown'
        }
      } else {
        if ($entry['hh3cNqaResultsRttTimeouts'] > 0)
        {
          $sla_state['rtt_sense'] = 'requestTimedOut';
        } else{
          $sla_state['rtt_sense'] = 'noRouteToTarget';
        }
      }

      //$sla_state['rtt_sense'] = $entry['pingResultsOperStatus'];
      $entry['UnixTime']      = $entry['pingResultsLastGoodProbe'];
      break;
    default:
      // FIXME. in DISMAN-PING-MIB exist only 'pingResultsRttSumOfSquares', I not know how calculate STDDEV from it
      $sla_state['rtt_sense'] = $entry['pingProbeHistoryStatus'];
      $entry['UnixTime']      = $entry['pingResultsLastGoodProbe'];
  }

  $sla_state['rtt_unixtime'] = datetime_to_unixtime($entry['UnixTime']);

  // SLA event
  $sla_state['rtt_event'] = $sla_states[$sla_state['rtt_sense']]['event'];

  // Note, here used complex index (owner.index)
  $cache_sla[$mib_lower][$index] = $sla_state;
}

// EOF
