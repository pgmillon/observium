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

// LSI-MegaRAID-SAS-MIB

echo(" LSI-MegaRAID-SAS-MIB ");

// LSI-MegaRAID-SAS-MIB::temperatureROC.0 = INTEGER: -1
// LSI-MegaRAID-SAS-MIB::temperatureCtrl.0 = INTEGER: 0

// LSI-MegaRAID-SAS-MIB::tempSensorStatus.0 = INTEGER: status-ok(2)
// LSI-MegaRAID-SAS-MIB::enclosureTemperature.0 = INTEGER: 48

// BBU temperature. WTF @ (Normal) string, LSI.
// LSI-MegaRAID-SAS-MIB::temperature.0 = STRING: "35 (Normal)"

echo("physicalDriveTable ");
$cache['megaraid']['pd'] = snmpwalk_cache_multi_oid($device, "physicalDriveTable", array(), "LSI-MegaRAID-SAS-MIB", mib_dirs('lsi'));

echo("enclosureTable ");
$cache['megaraid']['encl'] = snmpwalk_cache_multi_oid($device, "enclosureTable", array(), "LSI-MegaRAID-SAS-MIB", mib_dirs('lsi'));

/*
FIXME

    [6] => Array
        (
            [pdIndex] => 8
            [physDevID] => 16

            [mediaErrCount] => 0
            [otherErrCount] => 0
            [predFailCount] => 0
-> raid monitoring

            [pdState] => 24
_> state sensor: lsi-megaraid-sas-pd-state

            [enclDeviceId] => 9
            [enclIndex] => 2

            [slotNumber] => 4
            [pdVendorID] => FUJITSU
            [pdProductID] => MBA3147RC
        )
*/

// Fix up enclosure indexes
foreach ($cache['megaraid']['encl'] as $oldindex => $data)
{
  $cache['megaraid']['enclosure'][$data['enclosureIndex']] = $data;
}

// Physical disk temperature and state
//
// LSI-MegaRAID-SAS-MIB::pdVendorID.0 = STRING: "FUJITSU "
// LSI-MegaRAID-SAS-MIB::pdProductID.0 = STRING: "MBA3300RC       "
// LSI-MegaRAID-SAS-MIB::pdTemperature.0 = INTEGER: 36
// LSI-MegaRAID-SAS-MIB::slotNumber.0 = INTEGER: 0
// LSI-MegaRAID-SAS-MIB::enclDeviceId.0 = INTEGER: 8

foreach ($cache['megaraid']['pd'] as $index => $pd)
{
  $encl  = trim(trim($cache['megaraid']['enclosure'][$pd['enclIndex']]['vendorID'], '.') . " " . trim($cache['megaraid']['enclosure'][$pd['enclIndex']]['productID'], '.'));
  if ($encl == '') { $encl = 'Enclosure'; } // Static string if no enclosure vendor/product ID
  $descr = $encl . " (" . $pd['enclIndex'] . ") Slot " . $pd['slotNumber'] . ": " . $pd['pdVendorID'] . " " . $pd['pdProductID'];
  $value = $pd['pdTemperature'];
  $oid   = ".1.3.6.1.4.1.3582.4.1.4.2.1.2.1.36.$index";

  if ($value != '')
  {
    discover_sensor($valid['sensor'], 'temperature', $device, $oid, "pdTemperature.$index", 'lsi-megaraid-sas-mib', $descr, 1, $value);
  }

  $value = $pd['pdState'];
  $oid   = ".1.3.6.1.4.1.3582.4.1.4.2.1.2.1.10.$index";

  if ($value !== '')
  {
    discover_sensor($valid['sensor'], 'state', $device, $oid, "pdState.$index", 'lsi-megaraid-sas-pd-state', $descr, 1, $value);
  }
}

// Enclosure power supplies

// LSI-MegaRAID-SAS-MIB::powerSupplyID.0 = INTEGER: 0
// LSI-MegaRAID-SAS-MIB::powerSupplyID.1 = INTEGER: 1
// LSI-MegaRAID-SAS-MIB::enclosureId-EPST.0 = INTEGER: 0
// LSI-MegaRAID-SAS-MIB::enclosureId-EPST.1 = INTEGER: 0
// LSI-MegaRAID-SAS-MIB::powerSupplyStatus.0 = INTEGER: status-ok(2)
// LSI-MegaRAID-SAS-MIB::powerSupplyStatus.1 = INTEGER: status-ok(2)

echo(" enclosurePowerSupplyTable ");
$cache['megaraid']['psu'] = snmpwalk_cache_multi_oid($device, "enclosurePowerSupplyTable", array(), "LSI-MegaRAID-SAS-MIB", mib_dirs('lsi'));

foreach ($cache['megaraid']['psu'] as $index => $psu)
{
  $encl  = trim(trim($cache['megaraid']['encl'][$psu['enclosureId-EPST']]['vendorID'], '.') . " " . trim($cache['megaraid']['encl'][$psu['enclosureId-EPST']]['productID'], '.'));
  if ($encl == '')
  {
    $encl = 'Enclosure (' . $cache['megaraid']['encl'][$psu['enclosureId-EPST']]['enclosureIndex'] . ')'; // Static string if no vendor/product ID for enclosure
  } else {
    $encl .= ' (' . $cache['megaraid']['encl'][$psu['enclosureId-EPST']]['enclosureIndex'] . ')';
  }
  $descr = $encl . " Power Supply " . (++$lsi_counter['psu'][$psu['enclosureId-EPST']]);

  $value = $psu['powerSupplyStatus'];
  $oid   = ".1.3.6.1.4.1.3582.4.1.5.5.1.3.$index";

  discover_sensor($valid['sensor'], 'state', $device, $oid, "powerSupplyStatus.$index", 'lsi-megaraid-sas-sensor-state', $descr, 1, $value);
}

// Enclosure temperature sensors

// LSI-MegaRAID-SAS-MIB::tempSensorID.0 = INTEGER: 0
// LSI-MegaRAID-SAS-MIB::tempSensorID.1 = INTEGER: 1
// LSI-MegaRAID-SAS-MIB::tempSensorID.2 = INTEGER: 2
// LSI-MegaRAID-SAS-MIB::enclosureId-ETST.0 = INTEGER: 0
// LSI-MegaRAID-SAS-MIB::enclosureId-ETST.1 = INTEGER: 0
// LSI-MegaRAID-SAS-MIB::enclosureId-ETST.2 = INTEGER: 0
// LSI-MegaRAID-SAS-MIB::tempSensorStatus.0 = INTEGER: status-ok(2)
// LSI-MegaRAID-SAS-MIB::tempSensorStatus.1 = INTEGER: status-ok(2)
// LSI-MegaRAID-SAS-MIB::tempSensorStatus.2 = INTEGER: status-ok(2)
// LSI-MegaRAID-SAS-MIB::enclosureTemperature.0 = INTEGER: 26
// LSI-MegaRAID-SAS-MIB::enclosureTemperature.1 = INTEGER: 22
// LSI-MegaRAID-SAS-MIB::enclosureTemperature.2 = INTEGER: 211

echo(" enclosureTempSensorTable ");
$cache['megaraid']['temp'] = snmpwalk_cache_multi_oid($device, "enclosureTempSensorTable", array(), "LSI-MegaRAID-SAS-MIB", mib_dirs('lsi'));

foreach ($cache['megaraid']['temp'] as $index => $temp)
{
  $encl  = trim(trim($cache['megaraid']['encl'][$temp['enclosureId-ETST']]['vendorID'], '.') . " " . trim($cache['megaraid']['encl'][$temp['enclosureId-ETST']]['productID'], '.'));
  if ($encl == '')
  {
    $encl = 'Enclosure (' . $cache['megaraid']['encl'][$temp['enclosureId-ETST']]['enclosureIndex'] . ')'; // Static string if no vendor/product ID for enclosure
  } else {
    $encl .= ' (' . $cache['megaraid']['encl'][$temp['enclosureId-ETST']]['enclosureIndex'] . ')';
  }
  $descr = $encl . " Temperature sensor " . (++$lsi_counter['temp'][$temp['enclosureId-ETST']]);

  $value = $temp['tempSensorStatus'];
  $oid   = ".1.3.6.1.4.1.3582.4.1.5.6.1.3.$index";

  discover_sensor($valid['sensor'], 'state', $device, $oid, "tempSensorStatus.$index", 'lsi-megaraid-sas-sensor-state', $descr, 1, $value);

  $value = $temp['enclosureTemperature'];
  $oid   = ".1.3.6.1.4.1.3582.4.1.5.6.1.4.$index";

  if ($value < 200) // Filter out some silly values, possibly *10'd?
  {
    discover_sensor($valid['sensor'], 'temperature', $device, $oid, "enclosureTemperature.$index", 'lsi-megaraid-sas-mib', $descr, 1, $value);
  }
}

// LSI-MegaRAID-SAS-MIB::fanID.0 = INTEGER: 0
// LSI-MegaRAID-SAS-MIB::enclosureId.0 = INTEGER: 0
// LSI-MegaRAID-SAS-MIB::fanStatus.0 = INTEGER: status-ok(2)

echo(" enclosureFanTable ");
$cache['megaraid']['fan'] = snmpwalk_cache_multi_oid($device, "enclosureFanTable", array(), "LSI-MegaRAID-SAS-MIB", mib_dirs('lsi'));

foreach ($cache['megaraid']['fan'] as $index => $fan)
{
  $encl  = trim(trim($cache['megaraid']['encl'][$fan['enclosureId']]['vendorID'], '.') . " " . trim($cache['megaraid']['encl'][$fan['enclosureId']]['productID'], '.'));
  if ($encl == '')
  {
    $encl = 'Enclosure (' . $cache['megaraid']['encl'][$fan['enclosureId']]['enclosureIndex'] . ')'; // Static string if no vendor/product ID for enclosure
  } else {
    $encl .= ' (' . $cache['megaraid']['encl'][$fan['enclosureId']]['enclosureIndex'] . ')';
  }
  $descr = $encl . " Fan " . (++$lsi_counter['fan'][$fan['enclosureId']]);

  $value = $fan['fanStatus'];
  $oid   = ".1.3.6.1.4.1.3582.4.1.5.3.1.3.$index";

  discover_sensor($valid['sensor'], 'state', $device, $oid, "fanStatus.$index", 'lsi-megaraid-sas-sensor-state', $descr, 1, $value);
}

/*
enclosure data:
    [1] => Array
        (
            [enclosureID] => 0
            [deviceId] => 8
            [enclosureIndex] => 1
            [slotCount] => 12
            [psCount] => 2
            [fanCount] => 10
            [tempSensorCount] => 6
            [alarmCount] => 1
            [simCount] => 7
            [isFault] => 0
            [pdCount] => 10
            [pdIds] => 21 22 23 24 31 26 27 28 29 30
            [adapterID-ET] => 0
            [pdCountSpinup60] => 0
            [enclosureType] => 0
            [enclFirmwareVersion] => 30 31 41 20 01
            [enclSerialNumber] => N/A
            [vendorID] => Intel
            [productID] => SSR212MC
            [eSMSerialNumber] => N/A
            [eSMFRU] => N/A
            [enclosureZoningMode] => N/A
            [eSMFRUPartInfo] => N/A
        )
*/

unset($cache['megaraid'], $lsi_counter);

// EOF
