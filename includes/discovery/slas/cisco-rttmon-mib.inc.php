<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage discovery
 * @copyright  (C) 2006-2013 Adam Armstrong, (C) 2013-2016 Observium Limited
 *
 */

$mib = 'CISCO-RTTMON-MIB';
echo("$mib ");

$oids = snmpwalk_cache_multi_oid($device, "rttMonCtrl", array(), $mib, mib_dirs('cisco'));

foreach ($oids as $sla_index => $entry)
{
  if (!isset($entry['rttMonCtrlAdminStatus'])) { continue; } // Skip additional multiindex entries from table

  $data = array(
    'device_id'  => $device['device_id'],
    'sla_mib'    => $mib,
    'sla_index'  => $sla_index,
    'sla_owner'  => $entry['rttMonCtrlAdminOwner'],
    'sla_tag'    => $entry['rttMonCtrlAdminTag'],
    'rtt_type'   => $entry['rttMonCtrlAdminRttType'], // Possible: echo, pathEcho, fileIO, script, udpEcho, tcpConnect, http, dns, jitter, dlsw, dhcp,
                                                      // ftp, voip, rtp, lspGroup, icmpjitter, lspPing, lspTrace, ethernetPing, ethernetJitter,
                                                      // lspPingPseudowire, video, y1731Delay, y1731Loss, mcastJitter
    'sla_status' => $entry['rttMonCtrlAdminStatus'],  // Possible: active, notInService, notReady, createAndGo, createAndWait, destroy
    'deleted'    => 0,
  );

  // Use jitter or simple echo graph for SLA
  if (stripos($data['rtt_type'], 'jitter') !== FALSE)
  {
    $data['sla_graph'] = 'jitter';
  } else {
    $data['sla_graph'] = 'echo';
  }

  // Some fallbacks for when the tag is empty
  if (!$data['sla_tag'])
  {
    switch ($data['rtt_type'])
    {
      case 'http':
      case 'ftp':
        $data['sla_tag'] = $entry['rttMonEchoAdminURL'];
        break;
      case 'dns':
        $data['sla_tag'] = $entry['rttMonEchoAdminTargetAddressString'];
        break;
      case 'echo':
      case 'jitter':
      case 'icmpjitter':
        $data['sla_tag'] = hex2ip($entry['rttMonEchoAdminTargetAddress']);
        break;
    }
  }

  // Limits
  $data['sla_limit_high']      = ($entry['rttMonCtrlAdminTimeout']   > 0 ? $entry['rttMonCtrlAdminTimeout']   : 5000);
  $data['sla_limit_high_warn'] = ($entry['rttMonCtrlAdminThreshold'] > 0 ? $entry['rttMonCtrlAdminThreshold'] : 625);
  if ($data['sla_limit_high_warn'] >= $data['sla_limit_high'])
  {
    $data['sla_limit_high_warn'] = intval($data['sla_limit_high'] / 8);
  }

  $sla_table[$mib][$sla_index] = $data; // Pull to array for main processing
}

// EOF
