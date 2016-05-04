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

// hh3cTransceiverHardwareType.54 = STRING: "MM"
// hh3cTransceiverType.54 = STRING: "10G_BASE_SR_SFP"
// hh3cTransceiverWaveLength.54 = INTEGER: 850
// hh3cTransceiverVendorName.54 = STRING: "HP"
// hh3cTransceiverSerialNumber.54 = STRING: "210231A0A6X103000755"
// hh3cTransceiverFiberDiameterType.54 = INTEGER: fiber50(2)
// hh3cTransceiverTransferDistance.54 = INTEGER: 80
// hh3cTransceiverDiagnostic.54 = INTEGER: true(1)

// FIXME; Above data is (currently) not used here, serial number is present in ENTITY-MIB for inventory, other data is not.
//        Possibly useful to include there as well (somehow?)?

echo(" HH3C-TRANSCEIVER-INFO-MIB ");

$oids = snmpwalk_cache_oid($device, "h3cTransceiver", array(), "HH3C-TRANSCEIVER-INFO-MIB", mib_dirs('hh3c'));

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

    $ifDescr = $port['ifDescr'];
  } else {
    $ifDescr = "Port $index";
  }

  // hh3cTransceiverTemperature.54 = INTEGER: 39
  $descr = $ifDescr . " Temperature";
  $value = $entry['hh3cTransceiverTemperature'];
  $scale = 1;
  $oid   = "1.3.6.1.4.1.25506.2.70.1.1.1.15.$index";

  if ($value != 0 && $value < 2147483647)
  {
    discover_sensor($valid['sensor'], 'temperature', $device, $oid, "hh3cTransceiverTemperature.$index", 'hh3c-transceiver-info-mib', $descr, $scale, $value, $options);
  }

  // hh3cTransceiverBiasCurrent.54 = INTEGER: 532
  $descr = $ifDescr . " Bias Current";
  $value = $entry['hh3cTransceiverBiasCurrent'];
  $scale = 0.00001;
  $oid   = "1.3.6.1.4.1.25506.2.70.1.1.1.17.$index";

  if ($value != 0 && $value < 2147483647)
  {
    discover_sensor($valid['sensor'], 'current', $device, $oid, "hh3cTransceiverBiasCurrent.$index", 'hh3c-transceiver-info-mib', $descr, $scale, $value, $options);
  }

  // hh3cTransceiverVoltage.54 = INTEGER: 325
  $descr = $ifDescr . " Voltage";
  $value = $entry['hh3cTransceiverVoltage'];
  $scale = 0.01;
  $oid   = "1.3.6.1.4.1.25506.2.70.1.1.1.16.$index";

  if ($value != 0 && $value < 2147483647)
  {
    discover_sensor($valid['sensor'], 'voltage', $device, $oid, "hh3cTransceiverVoltage.$index", 'hh3c-transceiver-info-mib', $descr, $scale, $value, $options);
  }

  // hh3cTransceiverCurTXPower.54 = INTEGER: -251
  $descr = $ifDescr . " TX Power";
  $value = $entry['hh3cTransceiverCurTXPower'];
  $scale = 0.01;
  $oid   = "1.3.6.1.4.1.25506.2.70.1.1.1.9.$index";

  if ($value != 0 && $value < 2147483647)
  {
    discover_sensor($valid['sensor'], 'dbm', $device, $oid, "hh3cTransceiverCurTXPower.$index", 'hh3c-transceiver-info-mib', $descr, $scale, $value, $options);
  }

  // hh3cTransceiverCurRXPower.54 = INTEGER: -834
  $descr = $ifDescr . " RX Power";
  $value = $entry['hh3cTransceiverCurRXPower'];
  $scale = 0.01;
  $oid   = "1.3.6.1.4.1.25506.2.70.1.1.1.12.$index";

  if ($value != 0 && $value < 2147483647)
  {
    discover_sensor($valid['sensor'], 'dbm', $device, $oid, "hh3cTransceiverCurRXPower.$index", 'hh3c-transceiver-info-mib', $descr, $scale, $value, $options);
  }
}

// EOF
