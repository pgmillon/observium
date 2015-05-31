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

// RFC1628 UPS
echo(" UPS-MIB ");

echo("Caching OIDs: ");
$ups_array = array();
echo("upsInput ");
$ups_array = snmpwalk_cache_multi_oid($device, "upsInput", $ups_array, "UPS-MIB");
echo("upsOutput ");
$ups_array = snmpwalk_cache_multi_oid($device, "upsOutput", $ups_array, "UPS-MIB");
echo("upsBypass ");
$ups_array = snmpwalk_cache_multi_oid($device, "upsBypass", $ups_array, "UPS-MIB");

$scale = 0.1;
foreach (array_slice(array_keys($ups_array),1) as $phase)
{
  # Input
  $index = $ups_array[$phase]['upsInputLineIndex'];

  # Workaround if no upsInputLineIndex
  if ($index == '') { $index = $phase; }

  $descr = "Input"; if ($ups_array[0]['upsInputNumLines'] > 1) { $descr .= " Phase $index"; }

  ## Input voltage
  $oid   = "1.3.6.1.2.1.33.1.3.3.1.3.$index"; # UPS-MIB:upsInputVoltage.$index
  $value = $ups_array[$phase]['upsInputVoltage'];

  # FIXME maybe use upsConfigLowVoltageTransferPoint and upsConfigHighVoltageTransferPoint as limits? (upsConfig table)

  discover_sensor($valid['sensor'], 'voltage', $device, $oid, "upsInputEntry.".$index, 'ups-mib', $descr, 1, $value);

  ## Rename code for older revisions
  $old_rrd  = $config['rrd_dir'] . "/".$device['hostname']."/" . safename("sensor-voltage-rfc1628-" . (100+$index) . ".rrd");
  $new_rrd  = $config['rrd_dir'] . "/".$device['hostname']."/" . safename("sensor-voltage-ups-mib-upsInputEntry." . $index . ".rrd");
  if (is_file($old_rrd)) { rename($old_rrd,$new_rrd); echo("Moved RRD "); }

  ## Input frequency
  $oid   = "1.3.6.1.2.1.33.1.3.3.1.2.$index"; # UPS-MIB:upsInputFrequency.$index
  $value = $ups_array[$phase]['upsInputFrequency'];
  discover_sensor($valid['sensor'], 'frequency', $device, $oid, "upsInputEntry.".$index, 'ups-mib', $descr, $scale, $value * $scale);

  ## Rename code for older revisions
  $old_rrd  = $config['rrd_dir'] . "/".$device['hostname']."/" . safename("sensor-frequency-rfc1628-3.2.0." . $index . ".rrd");
  $new_rrd  = $config['rrd_dir'] . "/".$device['hostname']."/" . safename("sensor-frequency-ups-mib-upsInputEntry." . $index . ".rrd");
  if (is_file($old_rrd)) { rename($old_rrd,$new_rrd); echo("Moved RRD "); }

  ## Input current
  $oid   = "1.3.6.1.2.1.33.1.3.3.1.4.$index"; # UPS-MIB:upsInputCurrent.$index
  $value = $ups_array[$phase]['upsInputCurrent'];
  discover_sensor($valid['sensor'], 'current', $device, $oid, "upsInputEntry.".$index, 'ups-mib', $descr, $scale, $value * $scale);

  ## Rename code for older revisions
  $old_rrd  = $config['rrd_dir'] . "/".$device['hostname']."/" . safename("sensor-current-rfc1628-" . (100+$index) . ".rrd");
  $new_rrd  = $config['rrd_dir'] . "/".$device['hostname']."/" . safename("sensor-current-ups-mib-upsInputEntry." . $index . ".rrd");
  if (is_file($old_rrd)) { rename($old_rrd,$new_rrd); echo("Moved RRD "); }

  ## Input power
  $oid   = "1.3.6.1.2.1.33.1.3.3.1.5.$index"; # UPS-MIB:upsInputTruePower.$index
  $value = $ups_array[$phase]['upsInputTruePower'];

  if ($value != 0 || $ups_array[$phase]['upsInputCurrent'] == 0)
  {
    discover_sensor($valid['sensor'], 'power', $device, $oid, "upsInputEntry.".$index, 'ups-mib', $descr, $scale, $value * $scale);
  }

  ## Rename code for older revisions
  $old_rrd  = $config['rrd_dir'] . "/".$device['hostname']."/" . safename("sensor-power-ups-mib-upsInputEntry." . $index . ".rrd");
  $new_rrd  = $config['rrd_dir'] . "/".$device['hostname']."/" . safename("sensor-apower-ups-mib-upsInputEntry." . $index . ".rrd");
  if (is_file($old_rrd)) { rename($old_rrd,$new_rrd); echo("Moved RRD "); }

  # Output
  $index = $ups_array[$phase]['upsOutputLineIndex'];

  # Workaround if no upsOutputLineIndex
  if ($index == '') { $index = $phase; }

  $descr = "Output"; if ($ups_array[0]['upsOutputNumLines'] > 1) { $descr .= " Phase $index"; }

  ## Output voltage
  $oid   = "1.3.6.1.2.1.33.1.4.4.1.2.$index"; # UPS-MIB:upsOutputVoltage.$index
  $value = $ups_array[$phase]['upsOutputVoltage'];
  discover_sensor($valid['sensor'], 'voltage', $device, $oid, "upsOutputEntry.".$index, 'ups-mib', $descr, 1, $value);

  ## Rename code for older revisions
  $old_rrd  = $config['rrd_dir'] . "/".$device['hostname']."/" . safename("sensor-voltage-rfc1628-" . $index . ".rrd");
  $new_rrd  = $config['rrd_dir'] . "/".$device['hostname']."/" . safename("sensor-voltage-ups-mib-upsOutputEntry." . $index . ".rrd");
  if (is_file($old_rrd)) { rename($old_rrd,$new_rrd); echo("Moved RRD "); }

  ## Output current
  $oid   = "1.3.6.1.2.1.33.1.4.4.1.3.$index"; # UPS-MIB:upsOutputCurrent.$index
  $value = $ups_array[$phase]['upsOutputCurrent'];
  discover_sensor($valid['sensor'], 'current', $device, $oid, "upsOutputEntry.".$index, 'ups-mib', $descr, $scale, $value * $scale);

  ## Rename code for older revisions
  $old_rrd  = $config['rrd_dir'] . "/".$device['hostname']."/" . safename("sensor-current-rfc1628-" . $index . ".rrd");
  $new_rrd  = $config['rrd_dir'] . "/".$device['hostname']."/" . safename("sensor-current-ups-mib-upsOutputEntry." . $index . ".rrd");
  if (is_file($old_rrd)) { rename($old_rrd,$new_rrd); echo("Moved RRD "); }

  ## Output power
  $oid   = "1.3.6.1.2.1.33.1.4.4.1.4.$index"; # UPS-MIB:upsOutputPower.$index
  $value = $ups_array[$phase]['upsOutputPower'];
  discover_sensor($valid['sensor'], 'apower', $device, $oid, "upsOutputEntry.".$index, $type, $descr, 1, $value);

  ## Rename code for older revisions
  $old_rrd  = $config['rrd_dir'] . "/".$device['hostname']."/" . safename("sensor-power-ups-mib-upsOutputEntry." . $index . ".rrd");
  $new_rrd  = $config['rrd_dir'] . "/".$device['hostname']."/" . safename("sensor-apower-ups-mib-upsOutputEntry." . $index . ".rrd");
  if (is_file($old_rrd)) { rename($old_rrd,$new_rrd); echo("Moved RRD "); }

  if ($value != 0 || $ups_array[$phase]['upsOutputCurrent'] == 0)
  {
    discover_sensor($valid['sensor'], 'power', $device, $oid, "upsOutputEntry.".$index, 'ups-mib', $descr, 1, $value);
  }

  ## No rename code for output power, this is a new measurement

  $oid   = ".1.3.6.1.2.1.33.1.4.4.1.5.$index"; # UPS-MIB:upsOutputPercentLoad.$index
  $value = $ups_array[$phase]['upsOutputPercentLoad'];
  discover_sensor($valid['sensor'], 'capacity', $device, $oid, "upsOutputPercentLoad.$index", 'ups-mib', $descr, 1, $value);

  # Bypass

  if ($ups_array[0]['upsBypassNumLines'] > 0)
  {
    $descr = "Bypass"; if ($ups_array[0]['upsBypassNumLines'] > 1) { $descr .= " Phase $index"; }

    ## Bypass voltage
    $oid   = "1.3.6.1.2.1.33.1.5.3.1.2.$index"; # UPS-MIB:upsBypassVoltage.$index
    $value = $ups_array[$phase]['upsBypassVoltage'];
    discover_sensor($valid['sensor'], 'voltage', $device, $oid, "upsBypassEntry.".$index, 'ups-mib', $descr, 1, $value);

    ## Rename code for older revisions
    $old_rrd  = $config['rrd_dir'] . "/".$device['hostname']."/" . safename("sensor-voltage-rfc1628-" . (200+$index) . ".rrd");
    $new_rrd  = $config['rrd_dir'] . "/".$device['hostname']."/" . safename("sensor-voltage-ups-mib-upsBypassEntry." . $index . ".rrd");
    if (is_file($old_rrd)) { rename($old_rrd,$new_rrd); echo("Moved RRD "); }

    ## Bypass current
    $oid   = "1.3.6.1.2.1.33.1.5.3.1.3.$index"; # UPS-MIB:upsBypassCurrent.$index
    $value = $ups_array[$phase]['upsBypassCurrent'];
    discover_sensor($valid['sensor'], 'current', $device, $oid, "upsBypassEntry.".$index, 'ups-mib', $descr, $scale, $value * $scale);

    ## Rename code for older revisions
    $old_rrd  = $config['rrd_dir'] . "/".$device['hostname']."/" . safename("sensor-current-rfc1628-" . (200+$index) . ".rrd");
    $new_rrd  = $config['rrd_dir'] . "/".$device['hostname']."/" . safename("sensor-current-ups-mib-upsBypassEntry." . $index . ".rrd");
    if (is_file($old_rrd)) { rename($old_rrd,$new_rrd); echo("Moved RRD "); }

    ## Bypass power
    $oid   = "1.3.6.1.2.1.33.1.5.3.1.4.$index"; # UPS-MIB:upsBypassPower.$index
    $value = $ups_array[$phase]['upsBypassPower'];

    ## Rename code for older revisions
    $old_rrd  = $config['rrd_dir'] . "/".$device['hostname']."/" . safename("sensor-power-ups-mib-upsBypassEntry." . $index . ".rrd");
    $new_rrd  = $config['rrd_dir'] . "/".$device['hostname']."/" . safename("sensor-apower-ups-mib-upsBypassEntry." . $index . ".rrd");
    if (is_file($old_rrd)) { rename($old_rrd,$new_rrd); echo("Moved RRD "); }

    if ($value != 0 || $ups_array[$phase]['upsBypassCurrent'] == 0)
    {
      discover_sensor($valid['sensor'], 'power', $device, $oid, "upsBypassEntry.".$index, 'ups-mib', $descr, 1, $value);
    }

    ## No rename code for bypass power, this is a new measurement
  }
}

$ups_array = snmpwalk_cache_multi_oid($device, "upsBattery", array(), "UPS-MIB");

if (isset($ups_array[0]['upsBatteryTemperature']) && $ups_array[0]['upsBatteryTemperature'] != 0) // Battery won't be freezing, 0 means no sensor.
{
  $oid = "1.3.6.1.2.1.33.1.2.7.0"; # UPS-MIB:upsBatteryTemperature.0

  discover_sensor($valid['sensor'], 'temperature', $device, $oid, "upsBatteryTemperature", 'ups-mib', "Battery", 1, $ups_array[0]['upsBatteryTemperature']);

  ## Rename code for older revisions
  $old_rrd  = $config['rrd_dir'] . "/".$device['hostname']."/sensor-temperature-rfc1628-0.rrd";
  $new_rrd  = $config['rrd_dir'] . "/".$device['hostname']."/sensor-temperature-ups-mib-upsBatteryTemperature.rrd";
  if (is_file($old_rrd)) { rename($old_rrd,$new_rrd); echo("Moved RRD "); }
}

if (isset($ups_array[0]['upsBatteryCurrent']))
{
  $oid = "1.3.6.1.2.1.33.1.2.6.0"; # UPS-MIB:upsBatteryCurrent.0

  discover_sensor($valid['sensor'], 'current', $device, $oid, "upsBatteryCurrent", 'ups-mib', "Battery", $scale, $ups_array[0]['upsBatteryCurrent'] * $scale);

  ## Rename code for older revisions
  $old_rrd  = $config['rrd_dir'] . "/".$device['hostname']."/sensor-current-rfc1628-500.rrd";
  $new_rrd  = $config['rrd_dir'] . "/".$device['hostname']."/sensor-current-ups-mib-upsBatteryCurrent.rrd";
  if (is_file($old_rrd)) { rename($old_rrd,$new_rrd); echo("Moved RRD "); }
}

if (isset($ups_array[0]['upsBatteryVoltage']))
{
  $oid = "1.3.6.1.2.1.33.1.2.5.0"; # UPS-MIB:upsBatteryVoltage.0

  discover_sensor($valid['sensor'], 'voltage', $device, $oid, "upsBatteryVoltage", 'ups-mib', "Battery", $scale, $ups_array[0]['upsBatteryVoltage'] * $scale);

  ## Rename code for older revisions
  $old_rrd  = $config['rrd_dir'] . "/".$device['hostname']."/sensor-current-rfc1628-1.2.5.0.rrd";
  $new_rrd  = $config['rrd_dir'] . "/".$device['hostname']."/sensor-current-ups-mib-upsBatteryVoltage.rrd";
  if (is_file($old_rrd)) { rename($old_rrd,$new_rrd); echo("Moved RRD "); }

  ## Rename code for older revisions, part 2
  $old_rrd  = $config['rrd_dir'] . "/".$device['hostname']."/sensor-current-ups-mib-upsBatteryVoltage.rrd";
  $new_rrd  = $config['rrd_dir'] . "/".$device['hostname']."/sensor-voltage-ups-mib-upsBatteryVoltage.rrd";
  if (is_file($old_rrd)) { rename($old_rrd,$new_rrd); echo("Moved RRD "); }
}

if (isset($ups_array[0]['upsEstimatedMinutesRemaining']))
{
  $descr  = "Battery Runtime Remaining";
  $oid    = ".1.3.6.1.2.1.33.1.2.3.0";
  $limits = array('limit_low' => snmp_get($device, "upsConfigLowBattTime.0", "-Oqc", "UPS-MIB"));
  $value  = $ups_array[0]['upsEstimatedMinutesRemaining'];

  discover_sensor($valid['sensor'], 'runtime', $device, $oid, "upsEstimatedMinutesRemaining.0", 'mge', $descr, 1, $value, $limits);
}

## Output Frequency
$oid   = "1.3.6.1.2.1.33.1.4.2.0"; # UPS-MIB:upsOutputFrequency.0
$value = snmp_get($device, $oid, "-Oqv");
discover_sensor($valid['sensor'], 'frequency', $device, $oid, "upsOutputFrequency", 'ups-mib', "Output", $scale, $value * $scale);

## Rename code for older revisions
$old_rrd  = $config['rrd_dir'] . "/".$device['hostname']."/sensor-frequency-rfc1628-4.2.0.rrd";
$new_rrd  = $config['rrd_dir'] . "/".$device['hostname']."/sensor-frequency-ups-mib-upsOutputFrequency.rrd";
if (is_file($old_rrd)) { rename($old_rrd,$new_rrd); echo("Moved RRD "); }

## Bypass Frequency
$oid   = "1.3.6.1.2.1.33.1.5.1.0"; # UPS-MIB:upsBypassFrequency.0
$value = snmp_get($device, $oid, "-Oqv");
discover_sensor($valid['sensor'], 'frequency', $device, $oid, "upsBypassFrequency", 'ups-mib', "Bypass", $scale, $value * $scale);

## Rename code for older revisions
$old_rrd  = $config['rrd_dir'] . "/".$device['hostname']."/sensor-frequency-rfc1628-5.1.0.rrd";
$new_rrd  = $config['rrd_dir'] . "/".$device['hostname']."/sensor-frequency-ups-mib-upsBypassFrequency.rrd";
if (is_file($old_rrd)) { rename($old_rrd,$new_rrd); echo("Moved RRD "); }

unset($ups_array);

// EOF
