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

$mib = 'DISMAN-PING-MIB';
echo("$mib ");

$flags = OBS_SNMP_ALL ^ OBS_QUOTES_STRIP;

// Additional mibs for vendor specific Types
if (is_device_mib($device, 'JUNIPER-PING-MIB'))
{
  // JUNIPER-PING-MIB
  $vendor_mib = 'JUNIPER-PING-MIB';
  echo("$vendor_mib ");
  $mibs     = $mib . ':' . $vendor_mib;
  $mib_dirs = mib_dirs('juniper');
}
else if (is_device_mib($device, 'HH3C-NQA-MIB'))
{
  // HH3C-NQA-MIB
  $vendor_mib = 'HH3C-NQA-MIB';
  echo("$vendor_mib ");
  $mibs     = $mib . ':' . $vendor_mib;
  $mib_dirs = mib_dirs('hh3c');
} else {
  $mibs     = $mib;
  $mib_dirs = mib_dirs();
}
$oids = snmpwalk_cache_twopart_oid($device, "pingCtlEntry", array(), $mibs, $mib_dirs, $flags);
//print_vars($oids);
if ($GLOBALS['snmp_status'] === FALSE)
{
  return;
}

$mib_lower = strtolower($mib);

foreach ($oids as $sla_owner => $entry2)
{
  foreach ($entry2 as $sla_name => $entry)
  {
    if (!isset($entry['pingCtlAdminStatus']) ||        // Skip additional multiindex entries from table
        ($sla_owner == 'imclinktopologypleaseignore')) // Skip this weird SLAs by HH3C-NQA-MIB
    {
      continue;
    }

    // Get full index
    $sla_index = snmp_translate('pingCtlRowStatus."'.$sla_owner.'"."'.$sla_name.'"', $mib);
    $sla_index = str_replace('.1.3.6.1.2.1.80.1.2.1.23.', '', $sla_index);

    $data = array(
      'device_id'  => $device['device_id'],
      'sla_mib'    => $mib,
      'sla_index'  => $sla_name, // FIXME. Here must be $sla_index, but migrate too hard
      'sla_owner'  => $sla_owner,
      //'sla_tag'    => $entry['pingCtlTargetAddress'],
      //'rtt_type'   => $entry['pingCtlType'],
      'sla_status' => $entry['pingCtlRowStatus'], // Possible: active, notInService, notReady, createAndGo, createAndWait, destroy
      'sla_graph'  => 'jitter', // Seems as all of this types support jitter graphs
      'deleted'    => 0,
    );

    if ($entry['pingCtlAdminStatus'] == 'disabled')
    {
      // If SLA administarively disabled, exclude from polling
      $data['deleted'] = 1;
    }

    // Type conversions
    // Standart types: pingIcmpEcho, pingUdpEcho, pingSnmpQuery, pingTcpConnectionAttempt
    // Juniper types:  jnxPingIcmpTimeStamp, jnxPingHttpGet, jnxPingHttpGetMetadata, jnxPingDnsQuery, jnxPingNtpQuery, jnxPingUdpTimestamp
    // HH3C types:
    $data['rtt_type'] = str_replace(array('ping', 'jnxPing', 'hh3cNqa'), '', $entry['pingCtlType']);

    // Tag / Target
    $data['sla_tag'] = (isHexString($entry['pingCtlTargetAddress'])? hex2ip($entry['pingCtlTargetAddress']) : $entry['pingCtlTargetAddress']);

    // Limits
    $data['sla_limit_high']      = ($entry['pingCtlTimeOut'] > 0 ? $entry['pingCtlTimeOut'] * 1000 : 5000);
    $data['sla_limit_high_warn'] = intval($data['sla_limit_high'] / 8);

    /*
    // Migrate old indexes
    if (isset($sla_db[$mib_lower][$sla_owner.'.'.$name]))
    {
      // Old (non numeric) indexes
      $sla_db[$mib_lower][$sla_index] = $sla_db[$mib_lower][$sla_owner.'.'.$name];
      unset($sla_db[$mib_lower][$sla_owner.'.'.$name]);
      dbUpdate(array('sla_index' => $sla_index, 'sla_mib' => $mib), 'slas', "`sla_id` = ?", array($sla_db[$mib_lower][$sla_index]['sla_id']));
    }
    */
    // Note, here used complex index (owner.index)
    $sla_table[$mib][$sla_owner.'.'.$sla_name] = $data; // Pull to array for main processing
  }
}

// EOF
