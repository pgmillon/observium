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

// If only there was a valid (syntactically correct) MIB (and not one per controller sharing OIDs!)...
// This file would have been a lot cleaner, walking a complete sensor table, and picking values...

echo(" ARECA-SNMP-MIB "); // Observium includes the SAS controller MIB. Requires "mibAllowUnderline yes" in snmp.conf

// This is the SATA MIB.
$oids = snmp_walk($device, ".1.3.6.1.4.1.18928.1.2.2.1.9.1.2", "-OsqnU", "");
if ($debug) { echo($oids."\n"); }
if ($oids) echo("Areca Controller ");
foreach (explode("\n", $oids) as $data)
{
  $data = trim($data);
  if ($data)
  {
    list($oid,$descr) = explode(" ", $data,2);
    $split_oid = explode('.',$oid);
    $index = $split_oid[count($split_oid)-1];
    $oid  = ".1.3.6.1.4.1.18928.1.2.2.1.9.1.3." . $index;
    $value = snmp_get($device, $oid, "-Oqv", "");

    discover_sensor($valid['sensor'], 'fanspeed', $device, $oid, $index, 'areca', trim($descr,'"'), 1, $value);
  }
}

// This is the SATA MIB.
$oids = snmp_walk($device, ".1.3.6.1.4.1.18928.1.1.2.14.1.2", "-Osqn", "");
if ($debug) { echo($oids."\n"); }
$oids = trim($oids);
if ($oids) echo("Areca Harddisk ");
foreach (explode("\n", $oids) as $data)
{
  $data = trim($data);
  if ($data)
  {
    list($oid,$descr) = explode(" ", $data,2);
    $split_oid = explode('.',$oid);
    $temperature_id = $split_oid[count($split_oid)-1];
    $temperature_oid  = ".1.3.6.1.4.1.18928.1.1.2.14.1.2.$temperature_id";
    $temperature  = snmp_get($device, $temperature_oid, "-Oqv", "");
    $descr = "Hard disk $temperature_id";
    if ($temperature != -128) # -128 = not measured/present
    {
      discover_sensor($valid['sensor'], 'temperature', $device, $temperature_oid, zeropad($temperature_id), 'areca', $descr, 1, $temperature);
    }
  }
}

// FIXME does this work on SAS as well? (battery status) if not, need SAS battery status check too.
$oids = snmp_walk($device, ".1.3.6.1.4.1.18928.1.2.2.1.8.1.2", "-OsqnU", "");
if ($debug) { echo($oids."\n"); }
if ($oids) echo("Areca ");
foreach (explode("\n", $oids) as $data)
{
  $data = trim($data);
  if ($data)
  {
    list($oid,$descr) = explode(" ", $data,2);
    $split_oid = explode('.',$oid);
    $index = $split_oid[count($split_oid)-1];
    $oid  = ".1.3.6.1.4.1.18928.1.2.2.1.8.1.3." . $index;
    $value = snmp_get($device, $oid, "-Oqv", "");
    if (trim($descr,'"') == 'Battery Status') # Battery Status is charge percentage, or 255 when no BBU
    {
      if ($value != 255)
      {
        discover_sensor($valid['sensor'], 'capacity', $device, $oid, $index, 'areca', trim($descr,'"'), 1, $value);
      }
    } else { # Not a battery
      discover_sensor($valid['sensor'], 'voltage', $device, $oid, $index, 'areca', trim($descr,'"'), 0.001, $value);
    }
  }
}

$oids = snmp_walk($device, ".1.3.6.1.4.1.18928.1.2.2.1.10.1.2", "-OsqnU", "");
if ($debug) { echo($oids."\n"); }
if ($oids) echo("Areca Controller ");
foreach (explode("\n", $oids) as $data)
{
  $data = trim($data);
  if ($data)
  {
    list($oid,$descr) = explode(" ", $data,2);
    $split_oid = explode('.',$oid);
    $index = $split_oid[count($split_oid)-1];
    $oid  = ".1.3.6.1.4.1.18928.1.2.2.1.10.1.3." . $index;
    $value = snmp_get($device, $oid, "-Oqv", "");

    discover_sensor($valid['sensor'], 'temperature', $device, $oid, $index, 'areca', trim($descr,'"'), 1, $value);
  }
}

// SAS enclosure sensors

// hwEnclosure02Installed.0 = 2
// hwEnclosure02Description.0 = "Areca   ARC-4036-.01.06.0106"
// hwEnclosure02NumberOfPower.0 = 2
// hwEnclosure02NumberOfVol.0 = 2
// hwEnclosure02NumberOfFan.0 = 2
// hwEnclosure02NumberOfTemp.0 = 2
// hwEnclosure02VolIndex.1 = 1
// hwEnclosure02VolDesc.1 = "1V    "
// hwEnclosure02VolValue.1 = 980
// hwEnclosure02FanIndex.1 = 1
// hwEnclosure02FanDesc.1 = "Fan 01"
// hwEnclosure02FanSpeed.1 = 2170
// hwEnclosure02TempIndex.1 = 1
// hwEnclosure02TempDesc.1 = "ENC. Temp  "
// hwEnclosure02TempValue.1 = 30
// hwEnclosure02PowerIndex.1 = INTEGER: 1
// hwEnclosure02PowerDesc.1 = STRING: "PowerSupply01"
// hwEnclosure02PowerState.1 = INTEGER: Ok(1)

for ($encNum = 1; $encNum <= 8; $encNum++)
{
  $cache['areca']["hwEnclosure$encNum"] = snmpwalk_cache_multi_oid($device, "hwEnclosure$encNum", array(), "ARECA-SNMP-MIB");

  foreach ($cache['areca']["hwEnclosure$encNum"] as $index => $entry)
  {
    // Index 0 is the main enclosure data, we check if the enclosure is connected, but it will
    // not have any sensors of its own, so we skip index 0.
    if ($index != 0 && $cache['areca']["hwEnclosure$encNum"][0]["hwEnclosure0${encNum}Installed"])
    {
      if ($entry["hwEnclosure0${encNum}VolIndex"])
      {
        $descr = $cache['areca']["hwEnclosure$encNum"][0]["hwEnclosure0${encNum}Description"] . ' (' . $encNum  . ') ' . $entry["hwEnclosure0${encNum}VolDesc"];
        $value = $entry["hwEnclosure0${encNum}VolValue"];
        $oid   = ".1.3.6.1.4.1.18928.1.2.2." . ($encNum+1) . ".8.1.3.$index";

        discover_sensor($valid['sensor'], 'voltage', $device, $oid, "hwEnclosure0${encNum}VolValue.$index", 'areca', $descr, 0.001, $value);
      }

      if ($entry["hwEnclosure0${encNum}FanIndex"])
      {
        $descr = $cache['areca']["hwEnclosure$encNum"][0]["hwEnclosure0${encNum}Description"] . ' (' . $encNum  . ') ' . $entry["hwEnclosure0${encNum}FanDesc"];
        $value = $entry["hwEnclosure0${encNum}FanSpeed"];
        $oid   = ".1.3.6.1.4.1.18928.1.2.2." . ($encNum+1) . ".9.1.3.$index";

        discover_sensor($valid['sensor'], 'fanspeed', $device, $oid, "hwEnclosure0${encNum}FanSpeed.$index", 'areca', $descr, 1, $value);
      }

      if ($entry["hwEnclosure0${encNum}TempIndex"])
      {
        $descr = $cache['areca']["hwEnclosure$encNum"][0]["hwEnclosure0${encNum}Description"] . ' (' . $encNum  . ') ' . $entry["hwEnclosure0${encNum}TempDesc"];
        $value = $entry["hwEnclosure0${encNum}TempValue"];
        $oid   = ".1.3.6.1.4.1.18928.1.2.2." . ($encNum+1) . ".10.1.3.$index";

        discover_sensor($valid['sensor'], 'temperature', $device, $oid, "hwEnclosure0${encNum}TempValue.$index", 'areca', $descr, 1, $value);
      }

      if ($entry["hwEnclosure0${encNum}PowerIndex"])
      {
        $descr = $cache['areca']["hwEnclosure$encNum"][0]["hwEnclosure0${encNum}Description"] . ' (' . $encNum  . ') ' . $entry["hwEnclosure0${encNum}PowerDesc"];
        $value = $entry["hwEnclosure0${encNum}PowerState"];
        $oid   = ".1.3.6.1.4.1.18928.1.2.2." . ($encNum+1) . ".7.1.3.$index";

        discover_sensor($valid['sensor'], 'state', $device, $oid, "hwEnclosure0${encNum}PowerState.$index", 'areca-power-state', $descr, NULL, $value, array('entPhysicalClass' => 'power'));
      }
    }
  }
}

// EOF
