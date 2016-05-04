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

echo(" XUPS-MIB ");

echo("Caching OIDs: ");
$xups_array = array();
echo("xupsInput ");
$xups_array = snmpwalk_cache_multi_oid($device, "xupsInput", $xups_array, "XUPS-MIB");
echo("xupsOutput ");
$xups_array = snmpwalk_cache_multi_oid($device, "xupsOutput", $xups_array, "XUPS-MIB");
echo("xupsBypass ");
$xups_array = snmpwalk_cache_multi_oid($device, "xupsBypass", $xups_array, "XUPS-MIB");

foreach (array_slice(array_keys($xups_array),1) as $phase)
{
  # Skip garbage output:
  # xupsOutput.6.0 = 0
  # xupsOutput.7.0 = 0
  # xupsOutput.8.0 = 0
  if (!isset($xups_array[$phase]['xupsInputPhase'])) { break; }

  # Input
  $index = $xups_array[$phase]['xupsInputPhase'];
  $descr = "Input"; if ($xups_array[0]['xupsInputNumPhases'] > 1) { $descr .= " Phase $index"; }

  ## Input voltage
  $oid   = "1.3.6.1.4.1.534.1.3.4.1.2.$index"; # XUPS-MIB:xupsInputVoltage.$index
  $value = $xups_array[$phase]['xupsInputVoltage'];

  discover_sensor($valid['sensor'], 'voltage', $device, $oid, "xupsInputEntry.".$index, 'xups', $descr, 1, $value);

  ## Input current
  $oid   = "1.3.6.1.4.1.534.1.3.4.1.3.$index"; # XUPS-MIB:xupsInputCurrent.$index
  $value = $xups_array[$phase]['xupsInputCurrent'];

  if ($value < 10000) # xupsInputCurrent.1 = 136137420 ? really? You're nuts.
  {
    discover_sensor($valid['sensor'], 'current', $device, $oid, "xupsInputEntry.".$index, 'xups', $descr, 1, $value);
  }

  ## Input power
  $oid   = "1.3.6.1.4.1.534.1.3.4.1.4.$index"; # XUPS-MIB:xupsInputWatts.$index
  $value = $xups_array[$phase]['xupsInputWatts'];
  discover_sensor($valid['sensor'], 'power', $device, $oid, "xupsInputEntry.".$index, 'xups', $descr, 1, $value);

  # Output
  $index = $xups_array[$phase]['xupsOutputPhase'];
  $descr = "Output"; if ($xups_array[0]['xupsOutputNumPhases'] > 1) { $descr .= " Phase $index"; }

  ## Output voltage
  $oid   = "1.3.6.1.4.1.534.1.4.4.1.2.$index"; # XUPS-MIB:xupsOutputVoltage.$index
  $value = $xups_array[$phase]['xupsOutputVoltage'];
  discover_sensor($valid['sensor'], 'voltage', $device, $oid, "xupsOutputEntry.".$index, 'xups', $descr, 1, $value);

  ## Output current
  $oid   = "1.3.6.1.4.1.534.1.4.4.1.3.$index"; # XUPS-MIB:xupsOutputCurrent.$index
  $value = $xups_array[$phase]['xupsOutputCurrent'];
  discover_sensor($valid['sensor'], 'current', $device, $oid, "xupsOutputEntry.".$index, 'xups', $descr, 1, $value);

  ## Output power
  $oid   = "1.3.6.1.4.1.534.1.4.4.1.4.$index"; # XUPS-MIB:xupsOutputWatts.$index
  $value = $xups_array[$phase]['xupsOutputWatts'];
  discover_sensor($valid['sensor'], 'power', $device, $oid, "xupsOutputEntry.".$index, 'xups', $descr, 1, $value);

  ## Output Load
  $oid   = "1.3.6.1.4.1.534.1.4.1.0.$index"; # XUPS-MIB:xupsOutputLoad.$index
  $descr = "Output Load";
  $value = $xups_array[$phase]['xupsOutputLoad'];
  discover_sensor($valid['sensor'], 'capacity', $device, $oid, "xupsOutputLoad.".$index, 'xups', $descr, 1, $value);

  # Bypass
  $index = $xups_array[$phase]['xupsBypassPhase'];
  $descr = "Bypass"; if ($xups_array[0]['xupsBypassNumPhases'] > 1) { $descr .= " Phase $index"; }

  ## Bypass voltage
  $oid   = "1.3.6.1.4.1.534.1.5.3.1.2.$index"; # XUPS-MIB:xupsBypassVoltage.$index
  $value = $xups_array[$phase]['xupsBypassVoltage'];
  discover_sensor($valid['sensor'], 'voltage', $device, $oid, "xupsBypassEntry.".$index, 'xups', $descr, 1, $value);
}

$scale = 0.1;

## Input frequency
$oid   = "1.3.6.1.4.1.534.1.3.1.0.$index"; # XUPS-MIB:xupsInputFrequency.0
$value = $xups_array[0]['xupsInputFrequency'];
discover_sensor($valid['sensor'], 'frequency', $device, $oid, "xupsInputFrequency.0", 'xups', "Input", $scale, $value);

## Output Frequency
$oid   = "1.3.6.1.4.1.534.1.4.2.0"; # XUPS-MIB:xupsOutputFrequency.0
$value = $xups_array[0]['xupsOutputFrequency'];
discover_sensor($valid['sensor'], 'frequency', $device, $oid, "xupsOutputFrequency.0", 'xups', "Output", $scale, $value);

## Bypass Frequency
$oid   = "1.3.6.1.4.1.534.1.5.1.0"; # XUPS-MIB:xupsBypassFrequency.0
$value = $xups_array[0]['xupsBypassFrequency'];
discover_sensor($valid['sensor'], 'frequency', $device, $oid, "xupsBypassFrequency.0", 'xups', "Bypass", $scale, $value);

$xups_array = array();
$xups_array = snmpwalk_cache_multi_oid($device, "xupsBattery", $xups_array, "XUPS-MIB");
$xups_array = snmpwalk_cache_multi_oid($device, "xupsEnvironment", $xups_array, "XUPS-MIB");

if (isset($xups_array[0]['xupsBatTimeRemaining']))
{
  $oid = "1.3.6.1.4.1.534.1.2.1"; # XUPS-MIB:xupsBatTimeRemaining.0
  $scale = 1/60;
  discover_sensor($valid['sensor'], 'runtime', $device, $oid, "xupsBatTimeRemaining.0", 'xups', "Battery Runtime Remaining", $scale, $xups_array[0]['xupsBatTimeRemaining']);
}

if (isset($xups_array[0]['xupsBatCapacity']))
{
  $oid = "1.3.6.1.4.1.534.1.2.4"; # XUPS-MIB:xupsBatCapacity.0
  discover_sensor($valid['sensor'], 'capacity', $device, $oid, "xupsBatCapacity.0", 'xups', "Battery Capacity", 1, $xups_array[0]['xupsBatCapacity']);
}

if (isset($xups_array[0]['xupsBatCurrent']))
{
  $oid = "1.3.6.1.4.1.534.1.2.3.0"; # XUPS-MIB:xupsBatCurrent.0

  discover_sensor($valid['sensor'], 'current', $device, $oid, "xupsBatCurrent.0", 'xups', "Battery", 1, $xups_array[0]['xupsBatCurrent']);
}

if (isset($xups_array[0]['xupsBatVoltage']))
{
  $oid = "1.3.6.1.4.1.534.1.2.2.0"; # XUPS-MIB:xupsBatVoltage.0

  discover_sensor($valid['sensor'], 'current', $device, $oid, "xupsBatVoltage.0", 'xups', "Battery", 1, $xups_array[0]['xupsBatVoltage']);
}

if (isset($xups_array[0]['xupsEnvAmbientTemp']))
{
  $oid  = ".1.3.6.1.4.1.534.1.6.1.0"; # XUPS-MIB:xupsEnvAmbientTemp.0

  $lowlimit = $xups_array[0]['upsEnvAmbientLowerLimit'];
  $highlimit = $xups_array[0]['upsEnvAmbientUpperLimit'];

  discover_sensor($valid['sensor'], 'temperature', $device, $oid, "xupsEnvAmbientTemp.0", 'xups', "Ambient", 1, $xups_array[0]['xupsEnvAmbientTemp']);
}

unset($xups_array);

// EOF
