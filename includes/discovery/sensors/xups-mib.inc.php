<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage discovery
 * @copyright  (C) 2006-2014 Adam Armstrong
 *
 */

// CLEANME Rename code can go in r6000.

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

  ## Rename code for older revisions
  $old_rrd  = $config['rrd_dir'] . "/".$device['hostname']."/" . safename("sensor-voltage-xups-3.4.1.2." . $index . ".rrd");
  $new_rrd  = $config['rrd_dir'] . "/".$device['hostname']."/" . safename("sensor-voltage-xups-xupsInputEntry." . $index . ".rrd");
  if (is_file($old_rrd)) { rename($old_rrd,$new_rrd); echo("Moved RRD "); }

  ## Input current
  $oid   = "1.3.6.1.4.1.534.1.3.4.1.3.$index"; # XUPS-MIB:xupsInputCurrent.$index
  $value = $xups_array[$phase]['xupsInputCurrent'];

  if ($value < 10000) # xupsInputCurrent.1 = 136137420 ? really? You're nuts.
  {
    discover_sensor($valid['sensor'], 'current', $device, $oid, "xupsInputEntry.".$index, 'xups', $descr, 1, $value);
  }

  ## Rename code for older revisions
  $old_rrd  = $config['rrd_dir'] . "/".$device['hostname']."/" . safename("sensor-current-xups-3.4.1.3." . $index . ".rrd");
  $new_rrd  = $config['rrd_dir'] . "/".$device['hostname']."/" . safename("sensor-current-xups-xupsInputEntry." . $index . ".rrd");
  if (is_file($old_rrd)) { rename($old_rrd,$new_rrd); echo("Moved RRD "); }

  ## Input power
  $oid   = "1.3.6.1.4.1.534.1.3.4.1.4.$index"; # XUPS-MIB:xupsInputWatts.$index
  $value = $xups_array[$phase]['xupsInputWatts'];
  discover_sensor($valid['sensor'], 'power', $device, $oid, "xupsInputEntry.".$index, 'xups', $descr, 1, $value);

  ## No rename code for input power, this is a new measurement

  # Output
  $index = $xups_array[$phase]['xupsOutputPhase'];
  $descr = "Output"; if ($xups_array[0]['xupsOutputNumPhases'] > 1) { $descr .= " Phase $index"; }

  ## Output voltage
  $oid   = "1.3.6.1.4.1.534.1.4.4.1.2.$index"; # XUPS-MIB:xupsOutputVoltage.$index
  $value = $xups_array[$phase]['xupsOutputVoltage'];
  discover_sensor($valid['sensor'], 'voltage', $device, $oid, "xupsOutputEntry.".$index, 'xups', $descr, 1, $value);

  ## Rename code for older revisions
  $old_rrd  = $config['rrd_dir'] . "/".$device['hostname']."/" . safename("sensor-voltage-xups-4.4.1.2." . $index . ".rrd");
  $new_rrd  = $config['rrd_dir'] . "/".$device['hostname']."/" . safename("sensor-voltage-xups-xupsOutputEntry." . $index . ".rrd");
  if (is_file($old_rrd)) { rename($old_rrd,$new_rrd); echo("Moved RRD "); }

  ## Output current
  $oid   = "1.3.6.1.4.1.534.1.4.4.1.3.$index"; # XUPS-MIB:xupsOutputCurrent.$index
  $value = $xups_array[$phase]['xupsOutputCurrent'];
  discover_sensor($valid['sensor'], 'current', $device, $oid, "xupsOutputEntry.".$index, 'xups', $descr, 1, $value);

  ## Rename code for older revisions
  $old_rrd  = $config['rrd_dir'] . "/".$device['hostname']."/" . safename("sensor-current-xups-4.4.1.3." . $index . ".rrd");
  $new_rrd  = $config['rrd_dir'] . "/".$device['hostname']."/" . safename("sensor-current-xups-xupsOutputEntry." . $index . ".rrd");
  if (is_file($old_rrd)) { rename($old_rrd,$new_rrd); echo("Moved RRD "); }

  ## Output power
  $oid   = "1.3.6.1.4.1.534.1.4.4.1.4.$index"; # XUPS-MIB:xupsOutputWatts.$index
  $value = $xups_array[$phase]['xupsOutputWatts'];
  discover_sensor($valid['sensor'], 'power', $device, $oid, "xupsOutputEntry.".$index, 'xups', $descr, 1, $value);

  ## No rename code for output power, this is a new measurement

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

  ## Rename code for older revisions
  $old_rrd  = $config['rrd_dir'] . "/".$device['hostname']."/" . safename("sensor-voltage-xups-5.3.1.2." . $index . ".rrd");
  $new_rrd  = $config['rrd_dir'] . "/".$device['hostname']."/" . safename("sensor-voltage-xups-xupsBypassEntry." . $index . ".rrd");
  if (is_file($old_rrd)) { rename($old_rrd,$new_rrd); echo("Moved RRD "); }
}

$scale = 0.1;

## Input frequency
$oid   = "1.3.6.1.4.1.534.1.3.1.0.$index"; # XUPS-MIB:xupsInputFrequency.0
$value = $xups_array[0]['xupsInputFrequency'];
discover_sensor($valid['sensor'], 'frequency', $device, $oid, "xupsInputFrequency.0", 'xups', "Input", $scale, $value * $scale);

## Rename code for older revisions
$old_rrd  = $config['rrd_dir'] . "/".$device['hostname']."/" . safename("sensor-frequency-xups-3.1.0.rrd");
$new_rrd  = $config['rrd_dir'] . "/".$device['hostname']."/" . safename("sensor-frequency-xups-xupsInputFrequency.0.rrd");
if (is_file($old_rrd)) { rename($old_rrd,$new_rrd); echo("Moved RRD "); }

## Output Frequency
$oid   = "1.3.6.1.4.1.534.1.4.2.0"; # XUPS-MIB:xupsOutputFrequency.0
$value = $xups_array[0]['xupsOutputFrequency'];
discover_sensor($valid['sensor'], 'frequency', $device, $oid, "xupsOutputFrequency.0", 'xups', "Output", $scale, $value * $scale);

## Rename code for older revisions
$old_rrd  = $config['rrd_dir'] . "/".$device['hostname']."/sensor-frequency-xups-4.2.0.rrd";
$new_rrd  = $config['rrd_dir'] . "/".$device['hostname']."/sensor-frequency-xups-xupsOutputFrequency.0.rrd";
if (is_file($old_rrd)) { rename($old_rrd,$new_rrd); echo("Moved RRD "); }

## Bypass Frequency
$oid   = "1.3.6.1.4.1.534.1.5.1.0"; # XUPS-MIB:xupsBypassFrequency.0
$value = $xups_array[0]['xupsBypassFrequency'];
discover_sensor($valid['sensor'], 'frequency', $device, $oid, "xupsBypassFrequency.0", 'xups', "Bypass", $scale, $value * $scale);

## Rename code for older revisions
$old_rrd  = $config['rrd_dir'] . "/".$device['hostname']."/sensor-frequency-xups-5.1.0.rrd";
$new_rrd  = $config['rrd_dir'] . "/".$device['hostname']."/sensor-frequency-xups-xupsBypassFrequency.0.rrd";
if (is_file($old_rrd)) { rename($old_rrd,$new_rrd); echo("Moved RRD "); }

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

  ## Rename code for older revisions
  $old_rrd  = $config['rrd_dir'] . "/".$device['hostname']."/sensor-current-xups-1.2.3.0.rrd";
  $new_rrd  = $config['rrd_dir'] . "/".$device['hostname']."/sensor-current-xups-xupsBatCurrent.0.rrd";
  if (is_file($old_rrd)) { rename($old_rrd,$new_rrd); echo("Moved RRD "); }
}

if (isset($xups_array[0]['xupsBatVoltage']))
{
  $oid = "1.3.6.1.4.1.534.1.2.2.0"; # XUPS-MIB:xupsBatVoltage.0

  discover_sensor($valid['sensor'], 'current', $device, $oid, "xupsBatVoltage.0", 'xups', "Battery", 1, $xups_array[0]['xupsBatVoltage']);

  ## Rename code for older revisions
  $old_rrd  = $config['rrd_dir'] . "/".$device['hostname']."/sensor-current-xups-1.2.5.0.rrd";
  $new_rrd  = $config['rrd_dir'] . "/".$device['hostname']."/sensor-current-xups-xupsBatVoltage.0.rrd";
  if (is_file($old_rrd)) { rename($old_rrd,$new_rrd); echo("Moved RRD "); }
}

if (isset($xups_array[0]['xupsEnvAmbientTemp']))
{
  $oid  = ".1.3.6.1.4.1.534.1.6.1.0"; # XUPS-MIB:xupsEnvAmbientTemp.0

  $lowlimit = $xups_array[0]['upsEnvAmbientLowerLimit'];
  $highlimit = $xups_array[0]['upsEnvAmbientUpperLimit'];

  discover_sensor($valid['sensor'], 'temperature', $device, $oid, "xupsEnvAmbientTemp.0", 'xups', "Ambient", 1, $xups_array[0]['xupsEnvAmbientTemp']);

  ## Rename code for older revisions
  $old_rrd  = $config['rrd_dir'] . "/".$device['hostname']."/sensor-temperature-powerware-1.6.1.0.rrd";
  $new_rrd  = $config['rrd_dir'] . "/".$device['hostname']."/sensor-temperature-xups-xupsEnvAmbientTemp.0.rrd";
  if (is_file($old_rrd)) { rename($old_rrd,$new_rrd); echo("Moved RRD "); }
}

unset($xups_array);

// EOF
