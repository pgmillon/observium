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

// Force10 S-Series

#F10-S-SERIES-CHASSIS-MIB::chStackUnitTemp.1 = Gauge32: 47
#F10-S-SERIES-CHASSIS-MIB::chStackUnitModelID.1 = STRING: S25-01-GE-24V

echo(" F10-S-SERIES-CHASSIS-MIB ");

$oids = snmpwalk_cache_oid($device, "chStackUnitTemp",  array(), "F10-S-SERIES-CHASSIS-MIB", mib_dirs('force10'));
$oids = snmpwalk_cache_oid($device, "chStackUnitSysType", $oids, "F10-S-SERIES-CHASSIS-MIB", mib_dirs('force10'));

foreach ($oids as $index => $entry)
{
  $descr = "Unit " . strval($index - 1) . " " . $entry['chStackUnitSysType'];
  $oid   = ".1.3.6.1.4.1.6027.3.10.1.2.2.1.14.".$index;
  $value = $entry['chStackUnitTemp'];

  discover_sensor($valid['sensor'], 'temperature', $device, $oid, $index, 'ftos-sseries', $descr, 1, $value);
}

// DOM sensors

//chSysPortIfIndex      Integer32,
//chSysPortXfpRecvPower F10HundredthdB,
//chSysPortXfpRecvTemp  Integer32,
//chSysPortXfpTxPower   F10HundredthdB
//F10-S-SERIES-CHASSIS-MIB::chSysPortIfIndex.1.1 = INTEGER: 17105922
//F10-S-SERIES-CHASSIS-MIB::chSysPortIfIndex.1.2 = INTEGER: 17368066
//F10-S-SERIES-CHASSIS-MIB::chSysPortIfIndex.13.1 = INTEGER: 221528264
//F10-S-SERIES-CHASSIS-MIB::chSysPortXfpRecvPower.1.1 = INTEGER: 655.35 dB
//F10-S-SERIES-CHASSIS-MIB::chSysPortXfpRecvPower.1.2 = INTEGER: -8.50 dB
//F10-S-SERIES-CHASSIS-MIB::chSysPortXfpRecvPower.13.1 = INTEGER: .00 dB
//F10-S-SERIES-CHASSIS-MIB::chSysPortXfpRecvTemp.1.1 = INTEGER: 65535
//F10-S-SERIES-CHASSIS-MIB::chSysPortXfpRecvTemp.1.2 = INTEGER: 32
//F10-S-SERIES-CHASSIS-MIB::chSysPortXfpRecvTemp.13.1 = INTEGER: 0
//F10-S-SERIES-CHASSIS-MIB::chSysPortXfpTxPower.1.1 = INTEGER: 655.35 dB
//F10-S-SERIES-CHASSIS-MIB::chSysPortXfpTxPower.1.2 = INTEGER: -5.36 dB
//F10-S-SERIES-CHASSIS-MIB::chSysPortXfpTxPower.13.1 = INTEGER: .00 dB

$oids = snmpwalk_cache_oid($device, "chSysPortIfIndex",    array(), "F10-S-SERIES-CHASSIS-MIB", mib_dirs('force10'));
if (count($oids))
{
  $oids = snmpwalk_cache_oid($device, "chSysPortXfpRecvPower", $oids, "F10-S-SERIES-CHASSIS-MIB", mib_dirs('force10'));
  $oids = snmpwalk_cache_oid($device, "chSysPortXfpTxPower",   $oids, "F10-S-SERIES-CHASSIS-MIB", mib_dirs('force10'));
  $oids = snmpwalk_cache_oid($device, "chSysPortXfpRecvTemp",  $oids, "F10-S-SERIES-CHASSIS-MIB", mib_dirs('force10'));
  if (OBS_DEBUG > 1) { print_vars($oids); }

  foreach ($oids as $index => $entry)
  {
    if (($entry['chSysPortXfpRecvPower'] === '655.35' && $entry['chSysPortXfpTxPower'] === '655.35' && $entry['chSysPortXfpRecvTemp'] === '65535') ||
        ($entry['chSysPortXfpRecvPower'] ===    '.00' && $entry['chSysPortXfpTxPower'] ===    '.00' && $entry['chSysPortXfpRecvTemp'] === '0') ||
        ($entry['chSysPortXfpRecvPower'] ===    '.00' && !is_numeric($entry['chSysPortXfpTxPower']) && !is_numeric($entry['chSysPortXfpRecvTemp']))) // Fix for old S25/S50 series
    {
      continue;
    }
    list(, $entPhysicalIndex) = explode('.', $index);

    $port    = get_port_by_index_cache($device['device_id'], $entry['chSysPortIfIndex']);
    $options = array('entPhysicalIndex' => $entPhysicalIndex,
                     'measured_class'   => 'port',
                     'measured_entity'  => $port['port_id']);

    if (is_numeric($entry['chSysPortXfpRecvPower']))
    {
      $oid     = ".1.3.6.1.4.1.6027.3.10.1.2.5.1.6.".$index;
      $descr   = $port['ifDescr'] . " RX Power";
      $value   = $entry['chSysPortXfpRecvPower'] * 100;

      discover_sensor($valid['sensor'], 'dbm', $device, $oid, $index, 'f10-s-series-dom-rx', $descr, 0.01, $value, $options);
    }

    if (is_numeric($entry['chSysPortXfpTxPower']))
    {
      $oid     = ".1.3.6.1.4.1.6027.3.10.1.2.5.1.8.".$index;
      $descr   = $port['ifDescr'] . " TX Power";
      $value   = $entry['chSysPortXfpTxPower'] * 100;

      discover_sensor($valid['sensor'], 'dbm', $device, $oid, $index, 'f10-s-series-dom-tx', $descr, 0.01, $value, $options);
    }

    if (is_numeric($entry['chSysPortXfpRecvTemp']))
    {
      $oid     = ".1.3.6.1.4.1.6027.3.10.1.2.5.1.7.".$index;
      $descr   = $port['ifDescr'] . " DOM";
      $value   = $entry['chSysPortXfpRecvTemp'];

      discover_sensor($valid['sensor'], 'temperature', $device, $oid, $index, 'f10-s-series-dom', $descr, 1, $value, $options);
    }
  }
}

// EOF
