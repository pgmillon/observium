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

echo(" FOUNDRY-SN-AGENT-MIB ");

// FIXME This could do with a decent rewrite using SNMP multi functions, instead of trim() and str_replace() voodoo.

$oids = trim(snmp_walk($device, "snAgentTempSensorDescr", "-Osqn", "FOUNDRY-SN-AGENT-MIB:FOUNDRY-SN-ROOT-MIB", mib_dirs('foundry')));
$oids = str_replace(".1.3.6.1.4.1.1991.1.1.2.13.1.1.3.", "", $oids);

foreach (explode("\n", $oids) as $data)
{
  $data = trim($data);

  if ($data != "")
  {
    list($oid) = explode(" ", $data);
    $temperature_oid  = ".1.3.6.1.4.1.1991.1.1.2.13.1.1.4.$oid";
    $descr_oid = ".1.3.6.1.4.1.1991.1.1.2.13.1.1.3.$oid";
    $descr = snmp_get($device,$descr_oid,"-Oqv","");
    $temperature = snmp_get($device,$temperature_oid,"-Oqv","");

    if (!strstr($descr, "No") && !strstr($temperature, "No") && $descr != "" && $temperature != "0")
    {
      $descr = str_replace("\"", "", $descr);
      $descr = str_replace("temperature", "", $descr);
      $descr = str_replace("temperature", "", $descr);
      $descr = str_replace("sensor", "Sensor", $descr);
      $descr = str_replace("Line module", "Slot", $descr);
      $descr = str_replace("Switch Fabric module", "Fabric", $descr);
      $descr = str_replace("Active management module", "Mgmt Module", $descr);
      $descr = str_replace("  ", " ", $descr);
      $descr = trim($descr);

      $scale   = 0.5;
      $value = $temperature;

      discover_sensor($valid['sensor'], 'temperature', $device, $temperature_oid, $oid, 'ironware', $descr, $scale, $value);
    }
  }
}

// State sensors
$cache['fnsnagent'] = array();
$stackable = 0;

// Power Suplies

// Stackable Switches
foreach (array("snChasPwrSupply2Table") as $table)
{
  echo("$table ");
  $cache['fnsnagent'] = snmpwalk_cache_multi_oid($device, $table, $cache['fnsnagent'], "FOUNDRY-SN-AGENT-MIB:FOUNDRY-SN-ROOT-MIB", mib_dirs('foundry'), OBS_SNMP_ALL_NUMERIC);
}

foreach ($cache['fnsnagent'] as $index => $entry)
{
  $descr = "Power Supply $index";
  $oid   = ".1.3.6.1.4.1.1991.1.1.1.2.2.1.4.$index";
  $value = $entry['snChasPwrSupply2OperStatus'];
  discover_status($device, $oid, "snChasPwrSupply2OperStatus.$index", 'foundry-sn-agent-oper-state', $descr, $value, array('entPhysicalClass' => 'powerSupply'));
  $stackable = 1;
}

// Chassis and Non Stackable Switches
if ($stackable == 0)
{
  $cache['fnsnagent'] = array();

  foreach (array("snChasPwrSupplyTable") as $table)
  {
    echo("$table ");
    $cache['fnsnagent'] = snmpwalk_cache_multi_oid($device, $table, $cache['fnsnagent'], "FOUNDRY-SN-AGENT-MIB:FOUNDRY-SN-ROOT-MIB", mib_dirs('foundry'), OBS_SNMP_ALL_NUMERIC);
  }

  foreach ($cache['fnsnagent'] as $index => $entry)
  {
    $descr = "Power Supply $index";
    $oid   = ".1.3.6.1.4.1.1991.1.1.1.2.1.1.3.$index";
    $value = $entry['snChasPwrSupplyOperStatus'];
    discover_status($device, $oid, "snChasPwrSupplyOperStatus.$index", 'foundry-sn-agent-oper-state', $descr, $value, array('entPhysicalClass' => 'powerSupply'));
  }
}

// Fans

$cache['fnsnagent'] = array();
$stackable = 0;

// Stackable Switches
foreach (array("snChasFan2Table") as $table)
{
  echo("$table ");
  $cache['fnsnagent'] = snmpwalk_cache_multi_oid($device, $table, $cache['fnsnagent'], "FOUNDRY-SN-AGENT-MIB:FOUNDRY-SN-ROOT-MIB", mib_dirs('foundry'), OBS_SNMP_ALL_NUMERIC);
}

foreach ($cache['fnsnagent'] as $index => $entry)
{
  $descr = "Fan $index";
  $oid   = ".1.3.6.1.4.1.1991.1.1.1.3.2.1.4.$index";
  $value = $entry['snChasFan2OperStatus'];
  discover_status($device, $oid, "snChasFan2OperStatus.$index", 'foundry-sn-agent-oper-state', $descr, $value, array('entPhysicalClass' => 'fan'));
  $stackable = 1;
}

// Chassis and Non Stackable Switches
if ($stackable == 0)
{
  $cache['fnsnagent'] = array();

  foreach (array("snChasPwrSupplyTable") as $table)
  {
    echo("$table ");
    $cache['fnsnagent'] = snmpwalk_cache_multi_oid($device, $table, $cache['fnsnagent'], "FOUNDRY-SN-AGENT-MIB:FOUNDRY-SN-ROOT-MIB", mib_dirs('foundry'), OBS_SNMP_ALL_NUMERIC);
  }

  foreach ($cache['fnsnagent'] as $index => $entry)
  {
    $descr = "Fan $index";
    $oid   = ".1.3.6.1.4.1.1991.1.1.1.2.1.1.3.$index";
    $value = $entry['snChasFanOperStatus'];
    discover_status($device, $oid, "snChasFanOperStatus.$index", 'foundry-sn-agent-oper-state', $descr, $value, array('entPhysicalClass' => 'fan'));
  }
}

// EOF
