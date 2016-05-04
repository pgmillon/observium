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

echo(" ZXR10-OPTICAL-INFO ");

$oids = snmpwalk_cache_oid($device, "zxr10OpticalTable", array(), "ZXR10OPTICALMIB", mib_dirs('zxr10'));

if (OBS_DEBUG > 1) { print_vars($oids); }

// Index = ifIndex
foreach ($oids as $index => $entry)
{
  // Fetch port data from database, by index
  $port = get_port_by_index_cache($device['device_id'], $index);

  if (is_array($port))
  {
    $options['measured_class']  = 'port';
    $options['measured_entity'] = $port['port_id'];

    $ifDescr = $port['ifName'];
  } else {
    $ifDescr = "Port $index";
  }

  // zxr10OpticalSTemperature.4 = STRING: "37.625"
  $descr = $ifDescr . " Temperature";
  $value = $entry['zxr10OpticalSTemperature'];
  $scale = 1;
  $oid   = ".1.3.6.1.4.1.3902.3.103.11.1.1.24.$index";

  if ($value != 0 && $value < 2147483647)
  {
    discover_sensor($valid['sensor'], 'temperature', $device, $oid, "zxr10OpticalSTemperature.$index", 'zte-optical-mib', $descr, $scale, $value, $options);
  }

  // zxr10OpticalSTxCurrent.4 = STRING: "15.728"
  $descr = $ifDescr . " Current";
  $value = $entry['zxr10OpticalSTxCurrent'];
  $scale = 0.001;
  $oid   = ".1.3.6.1.4.1.3902.3.103.11.1.1.22.$index";

  if ($value != 0 && $value < 2147483647)
  {
    discover_sensor($valid['sensor'], 'current', $device, $oid, "zxr10OpticalSTxCurrent.$index", 'zte-optical-mib', $descr, $scale, $value, $options);
  }

  // zxr10Optical33SVoltage.4 = STRING: "3.259"
  $descr = $ifDescr . " Voltage";
  $value = $entry['zxr10Optical33SVoltage'];
  $scale = 1;
  $oid   = ".1.3.6.1.4.1.3902.3.103.11.1.1.26.$index";

  if ($value != 0 && $value < 2147483647)
  {
    discover_sensor($valid['sensor'], 'voltage', $device, $oid, "zxr10Optical33SVoltage.$index", 'zte-optical-mib', $descr, $scale, $value, $options);
  }

  // zxr10OpticalSTxPower.4 = STRING: "-5.986"
  $descr = $ifDescr . " TX Power";
  $value = $entry['zxr10OpticalSTxPower'];
  $scale = 1;
  $oid   = ".1.3.6.1.4.1.3902.3.103.11.1.1.20.$index";

  if ($value != 0 && $value < 2147483647)
  {
    discover_sensor($valid['sensor'], 'dbm', $device, $oid, "zxr10OpticalSTxPower.$index", 'zte-optical-mib', $descr, $scale, $value, $options);
  }

  // zxr10OpticalSRxPower.4 = STRING: "-9.073"
  $descr = $ifDescr . " RX Power";
  $value = $entry['zxr10OpticalSRxPower'];
  $scale = 1;
  $oid   = ".1.3.6.1.4.1.3902.3.103.11.1.1.18.$index";

  if ($value != 0 && $value < 2147483647)
  {
    discover_sensor($valid['sensor'], 'dbm', $device, $oid, "zxr10OpticalSRxPower.$index", 'zte-optical-mib', $descr, $scale, $value, $options);
  }
}

// EOF
