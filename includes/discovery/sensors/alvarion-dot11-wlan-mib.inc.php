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

echo(" ALVARION-DOT11-WLAN-MIB ");

$oids = snmpwalk_cache_multi_oid($device, "brzaccVLNewAdbUnitName", array(), "ALVARION-DOT11-WLAN-MIB", mib_dirs('alvarion'), OBS_SNMP_ALL_NUMERIC);
/// NOTE. New table preffer, because old use weird indexes
if ($oids)
{
  //ALVARION-DOT11-WLAN-MIB::brzaccVLNewAdbUnitName.0.16.231.20.145.216 = "Kern Waste Tehachapi"
  //ALVARION-DOT11-WLAN-MIB::brzaccVLNewAdbSNR.0.16.231.20.145.216 = 25
  //ALVARION-DOT11-WLAN-MIB::brzaccVLNewAdbRSSI.0.16.231.20.145.216 = -76
  $oids = snmpwalk_cache_multi_oid($device, "brzaccVLNewAdbSNR",        $oids, "ALVARION-DOT11-WLAN-MIB", mib_dirs('alvarion'), OBS_SNMP_ALL_NUMERIC);
  $oids = snmpwalk_cache_multi_oid($device, "brzaccVLNewAdbRSSI",       $oids, "ALVARION-DOT11-WLAN-MIB", mib_dirs('alvarion'), OBS_SNMP_ALL_NUMERIC);

  foreach ($oids as $index => $entry)
  {
    $descr = $entry['brzaccVLNewAdbUnitName'];

    // Signal-to-Noise Ratio
    if (is_numeric($entry['brzaccVLNewAdbSNR']))
    {
      $oid   = ".1.3.6.1.4.1.12394.1.1.11.5.1.3.1.26.$index";
      $value = $entry['brzaccVLNewAdbSNR'];
      discover_sensor($valid['sensor'], 'snr', $device, $oid, $index, 'alvarion-dot11', "$descr (SNR)", 1, $value);
    }

    // Received signal strength indication
    if (is_numeric($entry['brzaccVLNewAdbRSSI']))
    {
      $oid   = ".1.3.6.1.4.1.12394.1.1.11.5.1.3.1.54.$index";
      $value = $entry['brzaccVLNewAdbRSSI'];
      discover_sensor($valid['sensor'], 'dbm', $device, $oid, $index, 'alvarion-dot11', "$descr (RSSI)", 1, $value);
    }
  }
} else {
  //ALVARION-DOT11-WLAN-MIB::brzaccVLAdbUnitName.1 = STRING: "Kern Waste Tehachapi"
  //ALVARION-DOT11-WLAN-MIB::brzaccVLAdbSNR.1 = INTEGER: 28
  //ALVARION-DOT11-WLAN-MIB::brzaccVLAdbRSSI.1 = INTEGER: -75
  $oids = snmpwalk_cache_multi_oid($device, "brzaccVLAdbUnitName", array(), "ALVARION-DOT11-WLAN-MIB", mib_dirs('alvarion'));
  $oids = snmpwalk_cache_multi_oid($device, "brzaccVLAdbSNR",        $oids, "ALVARION-DOT11-WLAN-MIB", mib_dirs('alvarion'));
  $oids = snmpwalk_cache_multi_oid($device, "brzaccVLAdbRSSI",       $oids, "ALVARION-DOT11-WLAN-MIB", mib_dirs('alvarion'));

  foreach ($oids as $index => $entry)
  {
    $descr = $entry['brzaccVLAdbUnitName'];

    // Signal-to-Noise Ratio
    if (is_numeric($entry['brzaccVLAdbSNR']))
    {
      $oid   = ".1.3.6.1.4.1.12394.1.1.11.5.1.2.1.5.$index";
      $value = $entry['brzaccVLAdbSNR'];
      discover_sensor($valid['sensor'], 'snr', $device, $oid, $index, 'alvarion-dot11', "$descr (SNR)", 1, $value);
    }

    // Received signal strength indication
    if (is_numeric($entry['brzaccVLAdbRSSI']))
    {
      $oid   = ".1.3.6.1.4.1.12394.1.1.11.5.1.2.1.46.$index";
      $value = $entry['brzaccVLAdbRSSI'];
      discover_sensor($valid['sensor'], 'dbm', $device, $oid, $index, 'alvarion-dot11', "$descr (RSSI)", 1, $value);
    }
  }
}

//ALVARION-DOT11-WLAN-MIB::brzaccVLAverageReceiveSNR.0 = INTEGER: 23
//ALVARION-DOT11-WLAN-TST-MIB::brzLighteShowAuAvgSNR.0 = INTEGER: 23
$average_snr = snmp_get($device, "brzaccVLAverageReceiveSNR.0", "-OUqnv", "ALVARION-DOT11-WLAN-MIB", mib_dirs('alvarion'));
if (is_numeric($average_snr))
{
  $oid = ".1.3.6.1.4.1.12394.1.1.11.1.0";
  discover_sensor($valid['sensor'], 'snr', $device, $oid, 0, 'alvarion-dot11-average', "Average SNR", 1, $average_snr);
}

//ALVARION-DOT11-WLAN-TST-MIB::brzLighteAvgRssiRecieved.0 = INTEGER: 0
$average_rssi = snmp_get($device, "brzLighteAvgRssiRecieved.0", "-OUqnv", "ALVARION-DOT11-WLAN-TST-MIB", mib_dirs('alvarion'));
if (is_numeric($average_rssi) && $average_rssi)
{
  $oid = ".1.3.6.1.4.1.12394.3.2.3.2.1.0";
  discover_sensor($valid['sensor'], 'dbm', $device, $oid, 0, 'alvarion-dot11-average', "Average RSSI", 1, $average_rssi);
}

unset($oids, $oid, $value, $average_snr, $average_rssi, $descr);

// EOF
