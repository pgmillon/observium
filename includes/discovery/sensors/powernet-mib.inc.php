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

echo(" PowerNet-MIB ");

#### UPS #############################################################################################

$scale     = 0.1;  // Default scale
$scale_min = 1/60; // Scale for minutes
$inputs    = snmp_get($device, "upsPhaseNumInputs.0", "-Ovq", "PowerNet-MIB", mib_dirs('apc'));
$outputs   = snmp_get($device, "upsPhaseNumOutputs.0", "-Ovq", "PowerNet-MIB", mib_dirs('apc'));

echo("Caching OIDs: ");
$cache['apc'] = array();

// Check if we have values for these, if not, try other code paths below.
if ($inputs || $outputs)
{
  foreach (array("upsPhaseInputTable", "upsPhaseOutputTable", "upsPhaseInputPhaseTable", "upsPhaseOutputPhaseTable") as $table)
  {
    echo("$table ");
    $cache['apc'] = snmpwalk_cache_multi_oid($device, $table, $cache['apc'], "PowerNet-MIB", mib_dirs('apc'));
  }

  // Process each input, per phase
  for ($i = 1; $i <= $inputs; $i++)
  {
    $name   = snmp_hexstring($cache['apc'][$i]['upsPhaseInputName']);
    $phases = $cache['apc'][$i]['upsPhaseNumInputPhases'];
    $tindex = $cache['apc'][$i]['upsPhaseInputTableIndex'];
    $itype  = $cache['apc'][$i]['upsPhaseInputType'];

    if ($itype == "bypass") { $name = "Bypass"; } // Override "Input 2" in case of bypass.

    for ($p = 1;$p <= $phases;$p++)
    {
      $descr    = "$name Phase $p";

      $oid      = ".1.3.6.1.4.1.318.1.1.1.9.2.3.1.6.$tindex.1.$p";
      $value    = $cache['apc']["$tindex.1.$p"]['upsPhaseInputCurrent'];

      if ($value != '' && $value != -1)
      {
        discover_sensor($valid['sensor'], 'current', $device, $oid, "upsPhaseInputCurrent.$tindex.1.$p", 'apc', $descr, $scale, $value);
      }

      $oid      = ".1.3.6.1.4.1.318.1.1.1.9.2.3.1.3.$tindex.1.$p";
      $value    = $cache['apc']["$tindex.1.$p"]['upsPhaseInputVoltage'];

      if ($value != '' && $value != -1)
      {
        discover_sensor($valid['sensor'], 'voltage', $device, $oid, "upsPhaseInputVoltage.$tindex.1.$p", 'apc', $descr, 1, $value);
      }
    }

    // Frequency is reported only once per input
    $descr = $name;
    $index = "upsPhaseInputFrequency.$tindex";
    $oid   = ".1.3.6.1.4.1.318.1.1.1.9.2.2.1.4.$tindex";
    $value = $cache['apc'][$i]['upsPhaseInputFrequency'];

    if ($value != '' && $value != -1)
    {
      discover_sensor($valid['sensor'], 'frequency', $device, $oid, $index, 'apc', $descr, $scale, $value);
    }
  }

  // Process each output, per phase
  for ($o = 1; $o <= $outputs; $o++)
  {
    $name = "Output"; if ($outputs > 1) { $name .= " $o"; } // Output doesn't have a name in the MIB, add number if >1
    $phases = $cache['apc'][$o]['upsPhaseNumOutputPhases'];
    $tindex = $cache['apc'][$o]['upsPhaseOutputTableIndex'];

    for ($p = 1; $p <= $phases; $p++)
    {
      $descr     = "$name Phase $p";

      $oid      = ".1.3.6.1.4.1.318.1.1.1.9.3.3.1.4.$tindex.1.$p";
      $value    = $cache['apc']["$tindex.1.$p"]['upsPhaseOutputCurrent'];

      if ($value != '' && $value != -1)
      {
        discover_sensor($valid['sensor'], 'current', $device, $oid, "upsPhaseOutputCurrent.$tindex.1.$p", 'apc', $descr, $scale, $value);
      }

      $oid      = ".1.3.6.1.4.1.318.1.1.1.9.3.3.1.3.$tindex.1.$p";
      $value    = $cache['apc']["$tindex.1.$p"]['upsPhaseOutputVoltage'];

      if ($value != '' && $value != -1)
      {
        discover_sensor($valid['sensor'], 'voltage', $device, $oid, "upsPhaseOutputVoltage.$tindex.1.$p", 'apc', $descr, 1, $value);
      }

      $oid      = ".1.3.6.1.4.1.318.1.1.1.9.3.3.1.16.$tindex.1.$p";
      $value    = $cache['apc']["$tindex.1.$p"]['upsPhaseOutputPercentPower'];

      if ($value != '' && $value != -1)
      {
        discover_sensor($valid['sensor'], 'capacity', $device, $oid, "upsPhaseOutputPercentPower.$tindex.1.$p", 'apc', "$descr Load", 1, $value);
      }
    }

    // Frequency is reported only once per output
    $descr = $name;
    $oid   = ".1.3.6.1.4.1.318.1.1.1.9.3.2.1.4.$tindex";
    $value = $cache['apc'][$o]['upsPhaseOutputFrequency'];

    if ($value != '' && $value != -1)
    {
      discover_sensor($valid['sensor'], 'frequency', $device, $oid, "upsPhaseOutputFrequency.$tindex", 'apc', $descr, $scale, $value);
    }
  }
} else {
  // Try older UPS tables: "HighPrec" table first, with fallback to "Adv".
  foreach (array("upsHighPrecInput", "upsHighPrecOutput", "upsAdvInput", "upsAdvOutput") as $table)
  {
    echo("$table ");
    $cache['apc'] = snmpwalk_cache_multi_oid($device, $table, $cache['apc'], "PowerNet-MIB", mib_dirs('apc'));
  }

  foreach ($cache['apc'] as $index => $entry)
  {
    if (isset($entry['upsHighPrecInputLineVoltage']))
    {
      $oid   = ".1.3.6.1.4.1.318.1.1.1.3.3.1.$index";
      $descr = "Input";
      $value = $entry['upsHighPrecInputLineVoltage'];

      if ($value != '' && $value != -1)
      {
        discover_sensor($valid['sensor'], 'voltage', $device, $oid, "upsHighPrecInputLineVoltage.$index", 'apc', $descr, $scale, $value);
      }

      $oid   = ".1.3.6.1.4.1.318.1.1.1.3.3.4.$index";
      $descr = "Input";
      $value = $entry['upsHighPrecInputFrequency'];

      if ($value != '' && $value != -1)
      {
        discover_sensor($valid['sensor'], 'frequency', $device, $oid, "upsHighPrecInputFrequency.$index", 'apc', $descr, $scale, $value);
      }

      $oid   = ".1.3.6.1.4.1.318.1.1.1.4.3.1.$index";
      $descr = "Output";
      $value = $entry['upsHighPrecOutputVoltage'];

      if ($value != '' && $value != -1)
      {
        discover_sensor($valid['sensor'], 'voltage', $device, $oid, "upsHighPrecOutputVoltage.$index", 'apc', $descr, $scale, $value);
      }

      $oid = ".1.3.6.1.4.1.318.1.1.1.4.3.4.$index";
      $descr = "Output";
      $value = $entry['upsHighPrecOutputCurrent'];

      if ($value != '' && $value != -1)
      {
        discover_sensor($valid['sensor'], 'current', $device, $oid, "upsHighPrecOutputCurrent.$index", 'apc', $descr, $scale, $value);
      }

      $oid   = ".1.3.6.1.4.1.318.1.1.1.4.3.2.$index";
      $descr = "Output";
      $value = $entry['upsHighPrecOutputFrequency'];

      if ($value != '' && $value != -1)
      {
        discover_sensor($valid['sensor'], 'frequency', $device, $oid, "upsHighPrecOutputFrequency.$index", 'apc', $descr, $scale, $value);
      }

      $oid   = ".1.3.6.1.4.1.318.1.1.1.4.3.3.$index";
      $descr = "Output Load";
      $value = $entry['upsHighPrecOutputLoad'];

      if ($value != '' && $value != -1)
      {
        $limits = array('limit_high' => 85, 'limit_high_warn' => 70);
        discover_sensor($valid['sensor'], 'capacity', $device, $oid, "upsHighPrecOutputLoad.$index", 'apc', $descr, $scale, $value, $limits);
      }
    }
    elseif (isset($entry['upsAdvInputLineVoltage']))
    {
      // Fallback to lower precision table if HighPrec table is not available and Adv table is.
      $oid   = ".1.3.6.1.4.1.318.1.1.1.3.2.1.$index";
      $descr = "Input";
      $value = $entry['upsAdvInputLineVoltage'];

      if ($value != '' && $value != -1)
      {
        discover_sensor($valid['sensor'], 'voltage', $device, $oid, "upsAdvInputLineVoltage.$index", 'apc', $descr, 1, $value);
      }

      $oid   = ".1.3.6.1.4.1.318.1.1.1.3.2.4.$index";
      $descr = "Input";
      $value = $entry['upsAdvInputFrequency'];

      if ($value != '' && $value != -1)
      {
        discover_sensor($valid['sensor'], 'frequency', $device, $oid, "upsAdvInputFrequency.$index", 'apc', $descr, 1, $value);
      }

      $oid   = ".1.3.6.1.4.1.318.1.1.1.4.2.1.$index";
      $value = $entry['upsAdvOutputVoltage'];
      $descr = "Output";

      if ($value != '' && $value != -1)
      {
        discover_sensor($valid['sensor'], 'voltage', $device, $oid, "upsAdvOutputVoltage.$index", 'apc', $descr, 1, $value);
      }

      $oid   = ".1.3.6.1.4.1.318.1.1.1.4.2.4.$index";
      $descr = "Output";
      $value = $entry['upsAdvOutputCurrent'];

      if ($value != '' && $value != -1)
      {
        discover_sensor($valid['sensor'], 'current', $device, $oid, "upsAdvOutputCurrent.$index", 'apc', $descr, 1, $value);
      }

      $oid   = ".1.3.6.1.4.1.318.1.1.1.4.2.2.$index";
      $descr = "Output";
      $value = $entry['upsAdvOutputFrequency'];

      if ($value != '' && $value != -1)
      {
        discover_sensor($valid['sensor'], 'frequency', $device, $oid, "upsAdvOutputFrequency.$index", 'apc', $descr, 1, $value);
      }

      $oid   = ".1.3.6.1.4.1.318.1.1.1.4.2.3.$index";
      $descr = "Output Load";
      $value = $entry['upsAdvOutputLoad'];

      if ($value != '' && $value != -1)
      {
        $limits = array('limit_high' => 85, 'limit_high_warn' => 70);
        discover_sensor($valid['sensor'], 'capacity', $device, $oid, "upsAdvOutputLoad.$index", 'apc', $descr, 1, $value, $limits);
      }
    }

    if ($entry['upsAdvInputLineFailCause'])
    {
      $descr = "Last InputLine Fail Cause";
      $oid   = ".1.3.6.1.4.1.318.1.1.1.3.2.5.$index";

      discover_status($device, $oid, "upsAdvInputLineFailCause.$index", 'powernet-upsadvinputfail-state', $descr, $entry['upsAdvInputLineFailCause'], array('entPhysicalClass' => 'other'));
    }
  }
}

// Try UPS battery tables: "HighPrec" table first, with fallback to "Adv".
$cache['apc'] = array();

foreach (array("upsHighPrecBattery", "upsAdvBattery", "upsBasicBattery") as $table)
{
  echo("$table ");
  $cache['apc'] = snmpwalk_cache_multi_oid($device, $table, $cache['apc'], "PowerNet-MIB", mib_dirs('apc'));
  if ($table == 'upsAdvBattery' && $GLOBALS['snmp_status'] == FALSE) { break; } // Do not query BasicBattery if Adv empty
}

foreach ($cache['apc'] as $index => $entry)
{
  $descr = "Battery";

  if ($entry['upsHighPrecBatteryTemperature'] && $entry['upsHighPrecBatteryTemperature'] != -1)
  {
    $oid   = ".1.3.6.1.4.1.318.1.1.1.2.3.2.$index";
    $value = $entry['upsHighPrecBatteryTemperature'];

    discover_sensor($valid['sensor'], 'temperature', $device, $oid, "upsHighPrecBatteryTemperature.$index", 'apc', $descr, $scale, $value);
  } elseif ($entry['upsAdvBatteryTemperature'] && $entry['upsAdvBatteryTemperature'] != -1)
  {
    $oid   = ".1.3.6.1.4.1.318.1.1.1.2.2.2.$index";
    $value = $entry['upsAdvBatteryTemperature'];

    discover_sensor($valid['sensor'], 'temperature', $device, $oid, "upsAdvBatteryTemperature.$index", 'apc', $descr, 1, $value);
  }

  $descr = "Battery Nominal Voltage";

  if ($entry['upsHighPrecBatteryNominalVoltage'] && $entry['upsHighPrecBatteryNominalVoltage'] != -1)
  {
    $oid   = ".1.3.6.1.4.1.318.1.1.1.2.3.3.$index";
    $value = $entry['upsHighPrecBatteryNominalVoltage'];
    discover_sensor($valid['sensor'], 'voltage', $device, $oid, "upsHighPrecBatteryNominalVoltage.$index", 'apc', $descr, $scale, $value);
  } elseif ($entry['upsAdvBatteryNominalVoltage'] && $entry['upsAdvBatteryNominalVoltage'] != -1)
  {
    $oid   = ".1.3.6.1.4.1.318.1.1.1.2.2.7.$index";
    $value = $entry['upsAdvBatteryNominalVoltage'];
    discover_sensor($valid['sensor'], 'voltage', $device, $oid, "upsAdvBatteryNominalVoltage.$index", 'apc', $descr, 1, $value);
  }

  $descr = "Battery Actual Voltage";

  if ($entry['upsHighPrecBatteryActualVoltage'] && $entry['upsHighPrecBatteryActualVoltage'] != -1)
  {
    $oid   = ".1.3.6.1.4.1.318.1.1.1.2.3.4.$index";
    $value = $entry['upsHighPrecBatteryActualVoltage'];
    discover_sensor($valid['sensor'], 'voltage', $device, $oid, "upsHighPrecBatteryActualVoltage.$index", 'apc', $descr, $scale, $value);
  } elseif ($entry['upsAdvBatteryActualVoltage'] && $entry['upsAdvBatteryActualVoltage'] != -1)
  {
    $oid   = ".1.3.6.1.4.1.318.1.1.1.2.2.8.$index";
    $value = $entry['upsAdvBatteryActualVoltage'];
    discover_sensor($valid['sensor'], 'voltage', $device, $oid, "upsAdvBatteryActualVoltage.$index", 'apc', $descr, 1, $value);
  }

  $descr = "Battery";

  if ($entry['upsHighPrecBatteryCurrent'] && $entry['upsHighPrecBatteryCurrent'] != -1)
  {
    $oid   = ".1.3.6.1.4.1.318.1.1.1.2.3.5.$index";
    $value = $entry['upsHighPrecBatteryCurrent'];

    discover_sensor($valid['sensor'], 'current', $device, $oid, "upsHighPrecBatteryCurrent.$index", 'apc', $descr, $scale, $value);
  } elseif ($entry['upsAdvBatteryCurrent'] && $entry['upsAdvBatteryCurrent'] != -1)
  {
    $oid   = ".1.3.6.1.4.1.318.1.1.1.2.2.9.$index";
    $value = $entry['upsAdvBatteryCurrent'];

    discover_sensor($valid['sensor'], 'current', $device, $oid, "upsAdvBatteryCurrent.$index", 'apc', $descr, 1, $value);
  }

  $descr = "Total DC";

  if ($entry['upsHighPrecTotalDCCurrent'] && $entry['upsHighPrecTotalDCCurrent'] != -1)
  {
    $oid   = ".1.3.6.1.4.1.318.1.1.1.2.3.6.$index";
    $value = $entry['upsHighPrecTotalDCCurrent'];

    discover_sensor($valid['sensor'], 'current', $device, $oid, "upsHighPrecTotalDCCurrent.$index", 'apc', $descr, $scale, $value);
  } elseif ($entry['upsAdvTotalDCCurrent'] && $entry['upsAdvTotalDCCurrent'] != -1)
  {
    $oid   = ".1.3.6.1.4.1.318.1.1.1.2.2.10.$index";
    $value = $entry['upsAdvTotalDCCurrent'];

    discover_sensor($valid['sensor'], 'current', $device, $oid, "upsAdvTotalDCCurrent.$index", 'apc', $descr, 1, $value);
  }

  $descr = "Battery Capacity";

  if ($entry['upsHighPrecBatteryCapacity'] && $entry['upsHighPrecBatteryCapacity'] != -1)
  {
    $oid    = ".1.3.6.1.4.1.318.1.1.1.2.3.1.$index";
    $value  = $entry['upsHighPrecBatteryCapacity'];
    $limits = array('limit_low' => 15, 'limit_low_warn' => 30);
    discover_sensor($valid['sensor'], 'capacity', $device, $oid, "upsHighPrecBatteryCapacity.$index", 'apc', $descr, $scale, $value, $limits);
  }
  elseif ($entry['upsAdvBatteryCapacity'] && $entry['upsAdvBatteryCapacity'] != -1)
  {
    $oid   = ".1.3.6.1.4.1.318.1.1.1.2.2.1.$index";
    $value = $entry['upsAdvBatteryCapacity'];
    $limits = array('limit_low' => 15, 'limit_low_warn' => 30);
    discover_sensor($valid['sensor'], 'capacity', $device, $oid, "upsAdvBatteryCapacity.$index", 'apc', $descr, 1, $value, $limits);
  }

  $descr = "Battery Runtime Remaining";

  if ($entry['upsAdvBatteryRunTimeRemaining'])
  {
    // Runtime stores data in minuntes
    $oid       = ".1.3.6.1.4.1.318.1.1.1.2.2.3.$index";
    $value     = timeticks_to_sec($entry['upsAdvBatteryRunTimeRemaining']);
    $limit_low = snmp_get($device, "upsAdvConfigLowBatteryRunTime.$index", "-Ovq", "PowerNet-MIB", mib_dirs('apc'));
    $limit_low = timeticks_to_sec($limit_low);
    $limits    = array('limit_low' => (is_numeric($limit_low) ? $limit_low * $scale_min : 2));

    discover_sensor($valid['sensor'], 'runtime', $device, $oid, "upsAdvBatteryRunTimeRemaining.$index", 'apc', $descr, $scale_min, $value, $limits);
  }

  $descr = "Battery Replace";
  if ($entry['upsAdvBatteryReplaceIndicator'])
  {
    $oid = ".1.3.6.1.4.1.318.1.1.1.2.2.4.$index";
    if ($entry['upsBasicBatteryLastReplaceDate'])
    {
      $descr .= ' (last ' . reformat_us_date($entry['upsBasicBatteryLastReplaceDate']) . ')';
    }

    discover_status($device, $oid, "upsAdvBatteryReplaceIndicator.$index", 'powernet-upsbatteryreplace-state', $descr, $entry['upsAdvBatteryReplaceIndicator'], array('entPhysicalClass' => 'other'));
  }
}

// State sensors

// PowerNet-MIB::upsAdvTestDiagnosticSchedule.0 = INTEGER: biweekly(2)
// PowerNet-MIB::upsAdvTestDiagnostics.0 = INTEGER: noTestDiagnostics(1)
// PowerNet-MIB::upsAdvTestDiagnosticsResults.0 = INTEGER: ok(1)
// PowerNet-MIB::upsAdvTestLastDiagnosticsDate.0 = STRING: "05/27/2015"

$cache['apc'] = snmp_get_multi($device, 'upsAdvTestDiagnosticSchedule.0 upsAdvTestDiagnosticsResults.0 upsAdvTestLastDiagnosticsDate.0', '-OQUs', 'PowerNet-MIB', mib_dirs('apc'));

if (isset($cache['apc'][0]) && $cache['apc'][0]['upsAdvTestDiagnosticSchedule'] != 'never')
{
  $oid = ".1.3.6.1.4.1.318.1.1.1.7.2.3.0";
  $descr = "Diagnostics Results";
  if ($cache['apc'][0]['upsAdvTestLastDiagnosticsDate'])
  {
    $descr .= ' (last ' . reformat_us_date($cache['apc'][0]['upsAdvTestLastDiagnosticsDate']) . ')';
  }

  discover_status($device, $oid, "upsAdvTestDiagnosticsResults.0", 'powernet-upstest-state', $descr, $cache['apc'][0]['upsAdvTestDiagnosticsResults'], array('entPhysicalClass' => 'other'));
}

// PowerNet-MIB::upsBasicOutputStatus.0 = INTEGER: onLine(2)

$value = snmp_get($device, "upsBasicOutputStatus.0", "-Oqv", "PowerNet-MIB", mib_dirs('apc'));

if ($value !== '')
{
  $oid = ".1.3.6.1.4.1.318.1.1.1.4.1.1.0";
  $descr = "Output Status";

  discover_sensor($valid['sensor'], 'state', $device, $oid, "upsBasicOutputStatus.0", 'powernet-upsbasicoutput-state', $descr, NULL, $value, array('entPhysicalClass' => 'power'));
}

#### ATS #############################################################################################

$inputs = snmp_get($device, "atsNumInputs.0", "-Ovq", "PowerNet-MIB", mib_dirs('apc'));
$outputs = snmp_get($device, "atsNumOutputs.0", "-Ovq", "PowerNet-MIB", mib_dirs('apc'));

// Check if we have values for these, if not, try other code paths below.
if ($inputs || $outputs)
{
  echo(' ');
  $cache['apc'] = array();

  foreach (array("atsInputTable", "atsOutputTable", "atsInputPhaseTable", "atsOutputPhaseTable") as $table)
  {
    echo("$table ");
    // FIXME, not sure, that here required numeric index, seems as the remains of old snmp code with caching (added in r4685)
    $cache['apc'] = snmpwalk_cache_threepart_oid($device, $table, $cache['apc'], "PowerNet-MIB", mib_dirs('apc'), OBS_SNMP_ALL_NUMERIC);
  }
  foreach (array("atsInputTable", "atsOutputTable") as $table)
  {
    echo("$table ");
    // FIXME, not sure, that here required numeric index, seems as the remains of old snmp code with caching (added in r4685)
    $cache['apc'] = snmpwalk_cache_multi_oid($device, $table, $cache['apc'], "PowerNet-MIB", mib_dirs('apc'), OBS_SNMP_ALL_NUMERIC);
  }

  // Not tested with threephase, but I don't see any on the APC product list anyway, so...

  // FIXME - Not monitored:
  // [atsOutputLoad] => 364 (VA)
  // [atsOutputPercentLoad] => 9 (%)
  // [atsOutputPercentPower] => 9 (%)

  foreach ($cache['apc'] as $index => $entry)
  {
    $descr = $entry['atsInputName'];

    $oid   = ".1.3.6.1.4.1.318.1.1.8.5.3.3.1.3.$index.1.1";
    $value = $entry[1][1]['atsInputVoltage'];

    if ($value != '' && $value != -1)
    {
      discover_sensor($valid['sensor'], 'voltage', $device, $oid, "atsInputVoltage.$index.1.1", 'apc', $descr, 1, $value);
    }

    $oid   = ".1.3.6.1.4.1.318.1.1.8.5.3.3.1.9.$index.1.1";
    $value = $entry[1][1]['atsInputPower'];

    if ($value != '' && $value != -1)
    {
      discover_sensor($valid['sensor'], 'power', $device, $oid, "atsInputPower.$index.1.1", 'apc', $descr, 1, $value);
    }

    $oid   = ".1.3.6.1.4.1.318.1.1.8.5.3.3.1.6.$index.1.1";
    $value = $entry[1][1]['atsInputCurrent'];

    if ($value != '' && $value != -1)
    {
      $limits = array('limit_high'      => snmp_get($device, "atsConfigPhaseOverLoadThreshold.1",     "-Oqv", "PowerNet-MIB", mib_dirs('apc')),
                      'limit_low'       => snmp_get($device, "atsConfigPhaseLowLoadThreshold.1",      "-Oqv", "PowerNet-MIB", mib_dirs('apc')),
                      'limit_high_warn' => snmp_get($device, "atsConfigPhaseNearOverLoadThreshold.1", "-Oqv", "PowerNet-MIB", mib_dirs('apc')));
      discover_sensor($valid['sensor'], 'current', $device, $oid, "atsInputCurrent.$index.1.1", 'apc', $descr, $scale, $value, $limits);
    }

    $oid   = ".1.3.6.1.4.1.318.1.1.8.5.3.2.1.4.$index";
    $value = $entry['atsInputFrequency'];

    if ($value != '' && $value != -1)
    {
      discover_sensor($valid['sensor'], 'frequency', $device, $oid, "atsInputFrequency.$index", 'apc', $descr, 1, $value);
    }

    $descr = "Output"; // No check for multiple output feeds, currently - I don't think this exists.

    $oid   = ".1.3.6.1.4.1.318.1.1.8.5.4.3.1.3.$index.1.1";
    $value = $entry[1][1]['atsOutputVoltage'];

    if ($value != '' && $value != -1)
    {
      discover_sensor($valid['sensor'], 'voltage', $device, $oid, "atsOutputVoltage.$index.1.1", 'apc', $descr, 1, $value);
    }

    $oid   = ".1.3.6.1.4.1.318.1.1.8.5.4.3.1.13.$index.1.1";
    $value = $entry[1][1]['atsOutputPower'];

    if ($value != '' && $value != -1)
    {
      discover_sensor($valid['sensor'], 'power', $device, $oid, "atsOutputPower.$index.1.1", 'apc', $descr, 1, $value);
    }

    $oid   = ".1.3.6.1.4.1.318.1.1.8.5.4.3.1.4.$index.1.1";
    $value = $entry[1][1]['atsOutputCurrent'];

    if ($value != '' && $value != -1)
    {
      $limits = array('limit_high'      => snmp_get($device, "atsConfigPhaseOverLoadThreshold.1",     "-Oqv", "PowerNet-MIB", mib_dirs('apc')),
                      'limit_low'       => snmp_get($device, "atsConfigPhaseLowLoadThreshold.1",      "-Oqv", "PowerNet-MIB", mib_dirs('apc')),
                      'limit_high_warn' => snmp_get($device, "atsConfigPhaseNearOverLoadThreshold.1", "-Oqv", "PowerNet-MIB", mib_dirs('apc')));

      discover_sensor($valid['sensor'], 'current', $device, $oid, "atsOutputCurrent.$index.1.1", 'apc', $descr, $scale, $value, $limits);
    }

    $oid   = ".1.3.6.1.4.1.318.1.1.8.5.4.2.1.4.$index";
    $value = $entry['atsOutputFrequency'];

    if ($value != '' && $value != -1)
    {
      discover_sensor($valid['sensor'], 'frequency', $device, $oid, "atsOutputFrequency.$index", 'apc', $descr, 1, $value);
    }
  }

  // State sensors
  $cache['apc'] = array();

  foreach (array("atsStatusDeviceStatus") as $table)
  {
    echo("$table ");
    // FIXME, not sure, that here required numeric index, seems as the remains of old snmp code with caching (added in r4685)
    $cache['apc'] = snmpwalk_cache_multi_oid($device, $table, $cache['apc'], "PowerNet-MIB", mib_dirs('apc'), OBS_SNMP_ALL_NUMERIC);
  }

  foreach ($cache['apc'] as $index => $entry)
  {
    $descr = "Switch Status";
    $oid   = ".1.3.6.1.4.1.318.1.1.8.5.1.10.$index";
    $value = $entry['atsStatusSwitchStatus'];
    discover_sensor($valid['sensor'], 'state', $device, $oid, "atsStatusSwitchStatus.$index", 'powernet-status-state', $descr, NULL, $value, array('entPhysicalClass' => 'other'));

    $descr = "Source A";
    $oid   = ".1.3.6.1.4.1.318.1.1.8.5.1.12.$index";
    $value = $entry['atsStatusSourceAStatus'];
    discover_sensor($valid['sensor'], 'state', $device, $oid, "atsStatusSourceAStatus.$index", 'powernet-status-state', $descr, NULL, $value, array('entPhysicalClass' => 'power'));

    $descr = "Source B";
    $oid   = ".1.3.6.1.4.1.318.1.1.8.5.1.13.$index";
    $value = $entry['atsStatusSourceBStatus'];
    discover_sensor($valid['sensor'], 'state', $device, $oid, "atsStatusSourceBStatus.$index", 'powernet-status-state', $descr, NULL, $value, array('entPhysicalClass' => 'power'));

    $descr = "Phase Sync";
    $oid   = ".1.3.6.1.4.1.318.1.1.8.5.1.14.$index";
    $value = $entry['atsStatusPhaseSyncStatus'];
    discover_sensor($valid['sensor'], 'state', $device, $oid, "atsStatusPhaseSyncStatus.$index", 'powernet-sync-state', $descr, NULL, $value, array('entPhysicalClass' => 'power'));

    $descr = "Hardware";
    $oid   = ".1.3.6.1.4.1.318.1.1.8.5.1.16.$index";
    $value = $entry['atsStatusHardwareStatus'];
    discover_sensor($valid['sensor'], 'state', $device, $oid, "atsStatusHardwareStatus.$index", 'powernet-status-state', $descr, NULL, $value, array('entPhysicalClass' => 'other'));
  }

/*
PowerNet-MIB::atsStatusRedundancyState.0 = INTEGER: atsFullyRedundant(2)
PowerNet-MIB::atsStatusOverCurrentState.0 = INTEGER: atsCurrentOK(2)
PowerNet-MIB::atsStatus5VPowerSupply.0 = INTEGER: atsPowerSupplyOK(2)
PowerNet-MIB::atsStatus24VPowerSupply.0 = INTEGER: atsPowerSupplyOK(2)
PowerNet-MIB::atsStatus24VSourceBPowerSupply.0 = INTEGER: atsPowerSupplyOK(2)
PowerNet-MIB::atsStatusPlus12VPowerSupply.0 = INTEGER: atsPowerSupplyOK(2)
PowerNet-MIB::atsStatusMinus12VPowerSupply.0 = INTEGER: atsPowerSupplyOK(2)
*/
}

#### PDU #############################################################################################

$outlets = snmp_get($device, "rPDUIdentDeviceNumOutlets.0", "-Ovq", "PowerNet-MIB", mib_dirs('apc'));
$banks   = snmp_get($device, "rPDULoadDevNumBanks.0", "-Ovq", "PowerNet-MIB", mib_dirs('apc'));
$loadDev = snmpwalk_cache_multi_oid($device, "rPDULoadDevice", array(), "PowerNet-MIB", mib_dirs('apc'));

// Check if we have values for these, if not, try other code paths below.
if ($outlets)
{
  echo(' ');

  # v2 firmware: first bank is total
  # v3 firmware: last bank is total
  # v5 firmware: looks like first bank is total
  $baseversion = 2; /// FIXME. Use preg_match
  if (stristr($device['version'], 'v3') == TRUE) { $baseversion = 3; }
  elseif (stristr($device['version'], 'v4') == TRUE) { $baseversion = 4; }
  elseif (stristr($device['version'], 'v5') == TRUE) { $baseversion = 5; }
  elseif (stristr($device['version'], 'v6') == TRUE) { $baseversion = 6; }

  $cache['apc'] = snmpwalk_cache_multi_oid($device, "rPDU2DeviceStatusTable", array(), "PowerNet-MIB", mib_dirs('apc'));
  
  $units = count($cache['apc']); // Set this for use later

  // Try rPDU2 tree, as this supports slaving (rPDU2 devices also do rPDU table)
  if (count($cache['apc']))
  {
    foreach ($cache['apc'] as $index => $entry)
    {
      if ($units > 1)
      {
        // Multiple chained PDUs, prepend unit number to description
        $unit = 'Unit ' . $entry['rPDU2DeviceStatusModule'] . ' ';
      } else {
        // No prepend needed for single unit
        $unit = '';
      }

      // PowerNet-MIB::rPDU2DeviceStatusPowerSupply1Status.1 = normal
      // PowerNet-MIB::rPDU2DeviceStatusPowerSupply1Status.2 = normal
      // PowerNet-MIB::rPDU2DeviceStatusPowerSupply2Status.1 = notInstalled
      // PowerNet-MIB::rPDU2DeviceStatusPowerSupply2Status.2 = normal
      $descr = $unit . "Power Supply 1";
      $oid   = ".1.3.6.1.4.1.318.1.1.26.4.3.1.13.$index";
      $value = $entry['rPDU2DeviceStatusPowerSupply1Status'];
      discover_status($device, $oid, "rPDU2DeviceStatusPowerSupply1Status.$index", 'powernet-rpdu2supply-state', $descr, $value, array('entPhysicalClass' => 'power'));
      
      $descr = $unit . "Power Supply 2";
      $oid   = ".1.3.6.1.4.1.318.1.1.26.4.3.1.14.$index";
      $value = $entry['rPDU2DeviceStatusPowerSupply2Status'];
      discover_status($device, $oid, "rPDU2DeviceStatusPowerSupply2Status.$index", 'powernet-rpdu2supply-state', $descr, $value, array('entPhysicalClass' => 'power'));
      
      // PowerNet-MIB::rPDU2DeviceStatusPowerSupplyAlarm.1 = INTEGER: normal(1)
      $descr = $unit . "Power Supply Alarm";
      $oid   = ".1.3.6.1.4.1.318.1.1.26.4.3.1.12.$index";
      $value = $entry['rPDU2DeviceStatusPowerSupplyAlarm'];
      discover_status($device, $oid, "rPDU2DeviceStatusPowerSupplyAlarm.$index", 'powernet-rpdu2supplyalarm-state', $descr, $value, array('entPhysicalClass' => 'power'));
      
      // PowerNet-MIB::rPDU2DeviceStatusPower.1 = INTEGER: 185
      /* FIXME Disabled as currently duplicated by rPDU tree. We should decide if a device does rPDU2, and do only rPDU2 calls.
      $descr = $unit . "Output";
      $oid   = ".1.3.6.1.4.1.318.1.1.26.4.3.1.5.$index";
      $value = $entry['rPDU2DeviceStatusPower'];
      
      if ($value != 0)
      {
        discover_sensor($valid['sensor'], 'power', $device, $oid, "rPDU2DeviceStatusPower.$index", 'apc', $descr, 10, $value, array('entPhysicalClass' => 'power'));
      } */
      
      // PowerNet-MIB::rPDU2DeviceStatusApparentPower.1 = INTEGER: 198
      $descr = $unit . "Output";
      $oid   = ".1.3.6.1.4.1.318.1.1.26.4.3.1.16.$index";
      $value = $entry['rPDU2DeviceStatusApparentPower'];
      
      if ($value != 0)
      {
        discover_sensor($valid['sensor'], 'apower', $device, $oid, "rPDU2DeviceStatusApparentPower.$index", 'apc', $descr, 10, $value, array('entPhysicalClass' => 'power'));
      }

      // PowerNet-MIB::rPDU2DeviceStatusPowerFactor.1 = INTEGER: 93 -- Not used right now.
      // PowerNet-MIB::rPDU2DeviceStatusEnergy.1 = INTEGER: 170982 -- kWh counter, not supported right now

      // PowerNet-MIB::rPDU2PhaseConfigIndex.1 = INTEGER: 1
      // PowerNet-MIB::rPDU2PhaseConfigModule.1 = INTEGER: 1
      // PowerNet-MIB::rPDU2PhaseConfigNumber.1 = INTEGER: 1
      // PowerNet-MIB::rPDU2PhaseConfigOverloadRestriction.1 = INTEGER: notSupported(4)
      // PowerNet-MIB::rPDU2PhaseConfigLowLoadCurrentThreshold.1 = INTEGER: 0
      // PowerNet-MIB::rPDU2PhaseConfigNearOverloadCurrentThreshold.1 = INTEGER: 26
      // PowerNet-MIB::rPDU2PhaseConfigOverloadCurrentThreshold.1 = INTEGER: 32
      // PowerNet-MIB::rPDU2PhasePropertiesIndex.1 = INTEGER: 1
      // PowerNet-MIB::rPDU2PhasePropertiesModule.1 = INTEGER: 1
      // PowerNet-MIB::rPDU2PhasePropertiesNumber.1 = INTEGER: 1
      // PowerNet-MIB::rPDU2PhaseStatusIndex.1 = INTEGER: 1
      // PowerNet-MIB::rPDU2PhaseStatusModule.1 = INTEGER: 1
      // PowerNet-MIB::rPDU2PhaseStatusNumber.1 = INTEGER: 1
      // PowerNet-MIB::rPDU2PhaseStatusLoadState.1 = INTEGER: normal(2)
      // PowerNet-MIB::rPDU2PhaseStatusCurrent.1 = INTEGER: 28
      // PowerNet-MIB::rPDU2PhaseStatusVoltage.1 = INTEGER: 228
      // PowerNet-MIB::rPDU2PhaseStatusPower.1 = INTEGER: 57
      $phasetable = array();
      foreach (array("rPDU2PhaseStatusTable", "rPDU2PhaseConfigTable") as $table)
      {
        echo("$table ");
        // FIXME, not sure, that here required numeric index, seems as the remains of old snmp code with caching (added in r4685)
        $phasetable = snmpwalk_cache_multi_oid($device, $table, $phasetable, "PowerNet-MIB", mib_dirs('apc'), OBS_SNMP_ALL_NUMERIC);
      }
    
      if (count($phasetable))
      {
        //rPDU2BankStatusIndex.1 = 1
        //rPDU2BankStatusIndex.2 = 2
        //rPDU2BankStatusModule.1 = 1
        //rPDU2BankStatusModule.2 = 1
        //rPDU2BankStatusNumber.1 = 1
        //rPDU2BankStatusNumber.2 = 2
        //rPDU2BankStatusLoadState.1 = normal
        //rPDU2BankStatusLoadState.2 = normal
        //rPDU2BankStatusCurrent.1 = 7
        //rPDU2BankStatusCurrent.2 = 27
        if ($banks > 1)
        {
          echo("rPDU2BankStatusTable ");
          $cache['banks'] = snmpwalk_cache_multi_oid($device, 'rPDU2BankStatusTable', array(), "PowerNet-MIB", mib_dirs('apc'));
    
          foreach ($cache['banks'] as $index => $entry)
          {
            $oid      = ".1.3.6.1.4.1.318.1.1.26.8.3.1.5.$index";
            $value    = $entry['rPDU2BankStatusCurrent'];
            $bank     = $entry['rPDU2BankStatusNumber'];
            $descr    = "Bank $bank";
    
            if ($value != '' && $value != -1)
            {
              discover_sensor($valid['sensor'], 'current', $device, $oid, "rPDU2BankStatusCurrent.$index", 'apc', $descr, $scale, $value);
            }
          }
        }
    
        foreach ($phasetable as $index => $entry)
        {
          $oid     = ".1.3.6.1.4.1.318.1.1.26.6.3.1.5.$index";
          $value   = $entry['rPDU2PhaseStatusCurrent'];
          $limits  = array('limit_high'      => $entry['rPDU2PhaseConfigOverloadCurrentThreshold'],
                           'limit_low'       => $entry['rPDU2PhaseConfigLowLoadCurrentThreshold'],
                           'limit_high_warn' => $entry['rPDU2PhaseConfigNearOverloadCurrentThreshold']);
          $phase   = $entry['rPDU2PhaseStatusNumber'];
          $unit    = $entry['rPDU2PhaseStatusModule'];
    
          if ($loadDev[0]['rPDULoadDevNumPhases'] != 1)
          {
            // Multiple phases
            if ($units > 1)
            {
              // Multiple chained PDUs
              $descr = "Unit $unit Phase $phase";
            } else {
              $descr = "Phase $phase";
            }
          } else {
            // Single phase
            if ($units > 1)
            {
              // Multiple chained PDUs
              $descr = "Unit $unit Ouput";
            } else {
              $descr = "Output";
            }
          }
    
          if ($value != '' && $value != -1)
          {
            discover_sensor($valid['sensor'], 'current', $device, $oid, "rPDU2PhaseStatusCurrent.$index", 'apc', $descr, $scale, $value, $limits);
          }
    
          $oid       = ".1.3.6.1.4.1.318.1.1.26.6.3.1.6.$index";
          $value     = $entry['rPDU2PhaseStatusVoltage'];
    
          if ($value != '' && $value != -1)
          {
            discover_sensor($valid['sensor'], 'voltage', $device, $oid, "rPDU2PhaseStatusVoltage.$index", 'apc', $descr, 1, $value);
          }
    
          $oid       = ".1.3.6.1.4.1.318.1.1.26.6.3.1.7.$index";
          $value     = $entry['rPDU2PhaseStatusPower'];
    
          if ($value != '' && $value != -1)
          {
            discover_sensor($valid['sensor'], 'power', $device, $oid, "rPDU2PhaseStatusPower.$index", 'apc', $descr, 10, $value);
          }
    
          // PowerNet-MIB::rPDU2PhaseStatusLoadState.1 = INTEGER: normal(2)
        }
      }
    }
  } else {
    // Fall back to rPDU tree only if no rPDU2 data is available

    // PowerNet-MIB::rPDUPowerSupply1Status.0 = INTEGER: powerSupplyOneOk(1)
    // PowerNet-MIB::rPDUPowerSupply2Status.0 = INTEGER: powerSupplyTwoNotPresent(3)
    // PowerNet-MIB::rPDUPowerSupplyAlarm.0 = INTEGER: allAvailablePowerSuppliesOK(1) -- FIXME not used right now
    $cache['apc'] = snmp_get_multi($device, 'rPDUPowerSupply1Status.0 rPDUPowerSupply2Status.0', '-OQUs', 'PowerNet-MIB', mib_dirs('apc'));
    if (isset($cache['apc'][0]))
    {
      // FIXME ugly code. Just hardcode as above instead of weird index and unit calculations.
      $index = 0;
      foreach ($cache['apc'][0] as $key => $value)
      {
        $unit  = ('rPDUPowerSupply1Status' == $key ? 1 : 2);
        $type  = 'powernet-rpdusupply'.$unit.'-state';
        $descr = 'Power Supply '.$unit;
        $oid   = ".1.3.6.1.4.1.318.1.1.12.4.1.$unit.$index";

        discover_sensor($valid['sensor'], 'state', $device, $oid, "$key.$index", $type, $descr, NULL, $value, array('entPhysicalClass' => 'power'));
      }
    }

    //rPDULoadStatusIndex.1 = 1
    //rPDULoadStatusIndex.2 = 2
    //rPDULoadStatusIndex.3 = 3
    //rPDULoadStatusLoad.1 = 114
    //rPDULoadStatusLoad.2 = 58
    //rPDULoadStatusLoad.3 = 58
    //rPDULoadStatusLoadState.1 = phaseLoadNormal
    //rPDULoadStatusLoadState.2 = phaseLoadNormal
    //rPDULoadStatusLoadState.3 = phaseLoadNormal
    //rPDULoadStatusPhaseNumber.1 = 1
    //rPDULoadStatusPhaseNumber.2 = 1
    //rPDULoadStatusPhaseNumber.3 = 1
    //rPDULoadStatusBankNumber.1 = 0
    //rPDULoadStatusBankNumber.2 = 1
    //rPDULoadStatusBankNumber.3 = 2
    foreach (array("rPDUStatusPhaseTable", "rPDULoadStatus", "rPDULoadPhaseConfig") as $table)
    {
      echo("$table ");
      // FIXME, not sure, that here required numeric index, seems as the remains of old snmp code with caching (added in r4685)
      $cache['apc'] = snmpwalk_cache_multi_oid($device, $table, $cache['apc'], "PowerNet-MIB", mib_dirs('apc'), OBS_SNMP_ALL_NUMERIC);
    }

    foreach ($cache['apc'] as $index => $entry)
    {
      $oid     = ".1.3.6.1.4.1.318.1.1.12.2.3.1.1.2.$index";
      $value   = $entry['rPDULoadStatusLoad'];
      $limits  = array('limit_high'      => $entry['rPDULoadPhaseConfigOverloadThreshold'],
                       'limit_low'       => $entry['rPDULoadPhaseConfigLowLoadThreshold'],
                       'limit_high_warn' => $entry['rPDULoadPhaseConfigNearOverloadThreshold']);
      $bank    = $entry['rPDULoadStatusBankNumber'];
      $phase   = $entry['rPDUStatusPhaseNumber'];

      if (!$banks)
      {
        // No bank support on device
        if ($loadDev[0]['rPDULoadDevNumPhases'] != 1) { $descr = "Phase $phase"; } else { $descr = "Output"; }
      } else {
        // Bank support. Not sure that depends on $baseversion
        // http://jira.observium.org/browse/OBSERVIUM-772
        if ($bank == '0')
        {
          $bank = "Total";
        }
        $descr = "Bank $bank";
      }

      if ($value != '' && $value != -1)
      {
        discover_sensor($valid['sensor'], 'current', $device, $oid, "rPDULoadStatusLoad.$index", 'apc', $descr, $scale, $value, $limits);
      }

      // [rPDUStatusPhaseState] => phaseLoadNormal
      // [rPDULoadStatusLoadState] => phaseLoadNormal
      // [rPDULoadPhaseConfigAlarm] => noLoadAlarm
    }
  }

  // PowerNet-MIB::rPDUIdentDeviceLinetoLineVoltage.0 = INTEGER: 400
  // PowerNet-MIB::rPDUIdentDevicePowerWatts.0 = INTEGER: 807
  // PowerNet-MIB::rPDUIdentDevicePowerFactor.0 = INTEGER: 1000 - currently not used (1000=1) - FUTUREME
  // PowerNet-MIB::rPDUIdentDevicePowerVA.0 = INTEGER: 807 - no VA sensor type yet
  $cache['apc'] = array();
  foreach (array("rPDUIdent", "rPDU2Ident") as $table)
  {
    echo("$table ");
    // FIXME, not sure, that here required numeric index, seems as the remains of old snmp code with caching (added in r4685)
    $cache['apc'] = snmpwalk_cache_multi_oid($device, $table, $cache['apc'], "PowerNet-MIB", mib_dirs('apc'), OBS_SNMP_ALL_NUMERIC);
  }

  if (count($cache['apc']) == 1)
  { // Skip this section if rPDU2Ident table is present (it has index 1, so count() will be 2)
    // All data reported in rPDUIdent is duplicated in the rPDU2 tables we poll below.
    // FIXME omg. do this differently!
    foreach ($cache['apc'] as $index => $entry)
    {
      $descr = "Input";

      /// NOTE. rPDUIdentDeviceLinetoLineVoltage - is not actual voltage from device.
      //DESCRIPTION
      //   "Getting/Setting this OID will return/set the Line to Line Voltage.
      //    This OID defaults to the nominal input line voltage in volts AC.
      //    This setting is used to calculate total power and must be configured for best accuracy.
      //    This OID does not apply to AP86XX, AP88XX, or AP89XX SKUs.
      $oid   = ".1.3.6.1.4.1.318.1.1.12.1.15.$index";
      $value = $entry['rPDUIdentDeviceLinetoLineVoltage'];

      if ($value != '' && $value != -1)
      {
        discover_sensor($valid['sensor'], 'voltage', $device, $oid, "rPDUIdentDeviceLinetoLineVoltage.$index", 'apc', 'Line-to-Line', 1, $value);
      }

      $oid   = ".1.3.6.1.4.1.318.1.1.12.1.16.$index";
      $value = $entry['rPDUIdentDevicePowerWatts'];

      if ($value != '' && $value != -1)
      {
        discover_sensor($valid['sensor'], 'power', $device, $oid, "rPDUIdentDevicePowerWatts.$index", 'apc', $descr, 1, $value);
      }
    }
  }

  // FIXME METERED PDU CODE BELOW IS COMPLETELY UNTESTED
  $cache['apc'] = array();

  foreach (array("rPDU2OutletMeteredStatusTable") as $table)
  {
    echo("$table ");
    // FIXME, not sure, that here required numeric index, seems as the remains of old snmp code with caching (added in r4685)
    $cache['apc'] = snmpwalk_cache_multi_oid($device, $table, $cache['apc'], "PowerNet-MIB", mib_dirs('apc'), OBS_SNMP_ALL_NUMERIC);
  }

  foreach ($cache['apc'] as $index => $entry)
  {
    $oid     = ".1.3.6.1.4.1.318.1.1.26.9.4.3.1.6.$index";
    $value   = $entry['rPDU2OutletMeteredStatusCurrent'];
    $limits  = array('limit_high'      => $entry['rPDU2OutletMeteredConfigOverloadCurrentThreshold'],
                     'limit_low'       => $entry['rPDU2OutletMeteredConfigLowLoadCurrentThreshold'],
                     'limit_high_warn' => $entry['rPDU2OutletMeteredConfigNearOverloadCurrentThreshold']);
    $descr   = "Outlet " . $index . " - " . $entry['rPDU2OutletMeteredStatusName'];

    if ($value != '' && $value != -1)
    {
      discover_sensor($valid['sensor'], 'current', $device, $oid, "rPDU2OutletMeteredStatusCurrent.$index", 'apc', $descr, $scale, $value, $limits);
    }

    $oid       = ".1.3.6.1.4.1.318.1.1.26.9.4.3.1.7.$index";
    $value     = $entry['rPDU2OutletMeteredStatusPower'];

    if ($value != '' && $value != -1)
    {
      discover_sensor($valid['sensor'], 'power', $device, $oid, "rPDU2OutletMeteredStatusPower.$index", 'apc', $descr, 1, $value);
    }

    // Not currently supported: kWh reading: rPDU2OutletMeteredStatusEnergy - "A user resettable energy meter measuring Rack PDU load energy consumption in tenths of kilowatt-hours"
  }
}

#### MODULAR DISTRIBUTION SYSTEM #####################################################################

// FIXME This section needs a rewrite, but I can't find a device -TL

echo(' ');

$oids = snmp_walk($device, "isxModularDistSysVoltageLtoN", "-OsqnU", "PowerNet-MIB", mib_dirs('apc'));
if ($oids)
{
  echo(" Voltage In ");
  foreach (explode("\n", $oids) as $data)
  {
    list($oid,$value) = explode(' ',$data);
    $split_oid = explode('.',$oid);
    $phase = $split_oid[count($split_oid)-1];
    $index = "LtoN:".$phase;
    $descr = "Phase $phase Line to Neutral";

    discover_sensor($valid['sensor'], 'voltage', $device, $oid, $index, 'apc', $descr, $scale, $value);
  }
}

$oids = snmp_walk($device, "isxModularDistModuleBreakerCurrent", "-OsqnU", "PowerNet-MIB", mib_dirs('apc'));
if ($oids)
{
  echo(" Modular APC Out ");
  foreach (explode("\n", $oids) as $data)
  {
    $data = trim($data);
    if ($data)
    {
      list($oid,$value) = explode(' ', $data);
      $split_oid = explode('.',$oid);
      $phase = $split_oid[count($split_oid)-1];
      $breaker = $split_oid[count($split_oid)-2];
      $index = str_pad($breaker, 2, "0", STR_PAD_LEFT) . "-" . $phase;
      $descr = "Breaker $breaker Phase $phase";
      discover_sensor($valid['sensor'], 'current', $device, $oid, $index, 'apc', $descr, $scale, $value);
    }
  }

  $oids = snmp_walk($device, "isxModularDistSysCurrentAmps", "-OsqnU", "PowerNet-MIB", mib_dirs('apc'));
  foreach (explode("\n", $oids) as $data)
  {
    $data = trim($data);
    if ($data)
    {
      list($oid,$value) = explode(' ', $data);
      $split_oid = explode('.',$oid);
      $phase = $split_oid[count($split_oid)-1];
      $index = ".$phase";
      $descr = "Phase $phase overall";
      discover_sensor($valid['sensor'], 'current', $device, $oid, $index, 'apc', $descr, $scale, $value);
    }
  }
}

#### ENVIRONMENTAL ###################################################################################

echo(' ');

$cache['apc'] = array();

foreach (array("emsProbeStatusTable") as $table)
{
  echo("$table ");
  $cache['apc'] = snmpwalk_cache_multi_oid($device, $table, $cache['apc'], "PowerNet-MIB", mib_dirs('apc'));
}
$temp_units = snmp_get($device, "emsStatusSysTempUnits.0", "-Ovq", "PowerNet-MIB", mib_dirs('apc'));

foreach ($cache['apc'] as $index => $entry)
{
  $descr   = $entry['emsProbeStatusProbeName'];

  $status  = $entry['emsProbeStatusProbeCommStatus'];
  if ($status != 'commsEstablished') { continue; }

  // Humidity
  $value   = $entry['emsProbeStatusProbeHumidity'];
  $oid     = ".1.3.6.1.4.1.318.1.1.10.3.13.1.1.6.$index";
  $limits  = array('limit_high'      => $entry['emsProbeStatusProbeMaxHumidityThresh'],
                   'limit_low'       => $entry['emsProbeStatusProbeMinHumidityThresh'],
                   'limit_high_warn' => $entry['emsProbeStatusProbeHighHumidityThresh'],
                   'limit_low_warn'  => $entry['emsProbeStatusProbeLowHumidityThresh']);

  if ($value != '' && $value > 0) // Humidity = 0 or -1 -> Sensor not available
  {
    discover_sensor($valid['sensor'], 'humidity', $device, $oid, "emsProbeStatusProbeHumidity.$index", 'apc', $descr, 1, $value, $limits);
  }

  // Temperature
  $value   = $entry['emsProbeStatusProbeTemperature'];
  $oid     = ".1.3.6.1.4.1.318.1.1.10.3.13.1.1.3.$index";
  $options = array('limit_high'      => $entry['emsProbeStatusProbeMaxTempThresh'],
                   'limit_low'       => $entry['emsProbeStatusProbeMinTempThresh'],
                   'limit_high_warn' => $entry['emsProbeStatusProbeHighTempThresh'],
                   'limit_low_warn'  => $entry['emsProbeStatusProbeLowTempThresh']);

  if ($value != '' && $value != -1) // Temperature = -1 -> Sensor not available
  {
    $scale_temp = 1;
    if ($temp_units == 'fahrenheit')
    {
      $options['sensor_unit'] = 'F';
      foreach (array('limit_low', 'limit_low_warn', 'limit_high_warn', 'limit_high') as $param)
      {
        $options[$param] = f2c($options[$param]); // Convert limits from fahrenheit to celsius
      }
    } else {
      $options['sensor_unit'] = 'C';
    }

    discover_sensor($valid['sensor'], 'temperature', $device, $oid, "emsProbeStatusProbeTemperature.$index", 'apc', $descr, $scale_temp, $value, $options);
  }
}

$cache['apc'] = array();

// emConfigProbesTable may also be used? Perhaps on older devices? Not on mine...
foreach (array("iemConfigProbesTable", "iemStatusProbesTable") as $table)
{
  echo("$table ");
  $cache['apc'] = snmpwalk_cache_multi_oid($device, $table, $cache['apc'], "PowerNet-MIB", mib_dirs('apc'));
}

foreach ($cache['apc'] as $index => $entry)
{
  $descr           = $entry['iemStatusProbeName'];
  $temp_units      = $entry['iemStatusProbeTempUnits'];

  $status          = $entry['iemStatusProbeStatus'];
  if ($status != 'connected') { continue; } // Skip unconnected sensors entirely

  // Humidity
  $value           = $entry['iemStatusProbeCurrentHumid'];
  $oid             = ".1.3.6.1.4.1.318.1.1.10.2.3.2.1.6.$index";
  $limits          = array('limit_high'      => $entry['iemConfigProbeMaxHumidThreshold'],
                           'limit_low'       => $entry['iemConfigProbeMinHumidThreshold'],
                           'limit_high_warn' => $entry['iemConfigProbeHighHumidThreshold'],
                           'limit_low_warn'  => $entry['iemConfigProbeLowHumidThreshold']);

  if ($value != '' && $value > 0) // Humidity = 0 or -1 -> Sensor not available
  {
    discover_sensor($valid['sensor'], 'humidity', $device, $oid, "iemStatusProbeCurrentHumid.$index", 'apc', $descr, 1, $value, $limits);
    $iem_sensors['humidity'][] = $descr; // Store for later use in uio code below
  }

  // Temperature
  $value           = $entry['iemStatusProbeCurrentTemp'];
  $oid             = ".1.3.6.1.4.1.318.1.1.10.2.3.2.1.4.$index";
  $options         = array('limit_high'      => $entry['iemConfigProbeMaxTempThreshold'],
                           'limit_low'       => $entry['iemConfigProbeMinTempThreshold'],
                           'limit_high_warn' => $entry['iemConfigProbeHighTempThreshold'],
                           'limit_low_warn'  => $entry['iemConfigProbeLowTempThreshold']);

  if ($value != '' && $value != -1) // Temperature = -1 -> Sensor not available
  {
    $scale_temp = 1;
    if ($temp_units == 'fahrenheit')
    {
      $options['sensor_unit'] = 'F';
      foreach (array('limit_low', 'limit_low_warn', 'limit_high_warn', 'limit_high') as $param)
      {
        $options[$param] = f2c($options[$param]); // Convert limits from fahrenheit to celsius
      }
    } else {
      $options['sensor_unit'] = 'C';
    }

    discover_sensor($valid['sensor'], 'temperature', $device, $oid, "iemStatusProbeCurrentTemp.$index", 'apc', $descr, $scale_temp, $value, $options);
    $iem_sensors['temperature'][] = $descr; // Store for later use in uio code below
  }
}

// Universal I/O sensors

// Apparently on newer cards (maybe a bug?) only the first UIO port's sensor is sent in the iem table above.
// Both UIO ports are exported through the uioSensorStatusTable. However, we don't get threshold information
// in this table, so we use the iem[Config|Status]ProbesTable table if we can, then add any missing sensors
// we find below through the uioSensorStatusTable by checking against the contents of the $iem_sensors array.

// PowerNet-MIB::uioSensorStatusPortID.1.1 = INTEGER: 1
// PowerNet-MIB::uioSensorStatusPortID.2.1 = INTEGER: 2
// PowerNet-MIB::uioSensorStatusSensorID.1.1 = INTEGER: 1
// PowerNet-MIB::uioSensorStatusSensorID.2.1 = INTEGER: 1
// PowerNet-MIB::uioSensorStatusSensorName.1.1 = STRING: "UPS"
// PowerNet-MIB::uioSensorStatusSensorName.2.1 = STRING: "Rack"
// PowerNet-MIB::uioSensorStatusSensorLocation.1.1 = STRING: "Port 1"
// PowerNet-MIB::uioSensorStatusSensorLocation.2.1 = STRING: "Port 2"
// PowerNet-MIB::uioSensorStatusTemperatureDegC.1.1 = INTEGER: 27
// PowerNet-MIB::uioSensorStatusTemperatureDegC.2.1 = INTEGER: 22
// PowerNet-MIB::uioSensorStatusHumidity.1.1 = INTEGER: -1
// PowerNet-MIB::uioSensorStatusHumidity.2.1 = INTEGER: 49
// PowerNet-MIB::uioSensorStatusViolationStatus.1.1 = INTEGER: 0
// PowerNet-MIB::uioSensorStatusViolationStatus.2.1 = INTEGER: 0
// PowerNet-MIB::uioSensorStatusAlarmStatus.1.1 = INTEGER: uioNormal(1)
// PowerNet-MIB::uioSensorStatusAlarmStatus.2.1 = INTEGER: uioNormal(1)
// PowerNet-MIB::uioSensorStatusCommStatus.1.1 = INTEGER: commsOK(2)
// PowerNet-MIB::uioSensorStatusCommStatus.2.1 = INTEGER: commsOK(2)

echo(' ');

$cache['apc'] = array();

foreach (array("uioSensorStatusTable") as $table)
{
  echo("$table ");
  $cache['apc'] = snmpwalk_cache_multi_oid($device, $table, $cache['apc'], "PowerNet-MIB", mib_dirs('apc'));
}

foreach ($cache['apc'] as $index => $entry)
{
  $descr           = $entry['uioSensorStatusSensorName'];

  $status          = $entry['uioSensorStatusCommStatus'];
  if ($status != 'commsOK') { continue; } // Skip unconnected sensors entirely

  // Humidity
  $value           = $entry['uioSensorStatusHumidity'];
  $oid             = ".1.3.6.1.4.1.318.1.1.25.1.2.1.7.$index";
  // No thresholds in the uio MIB table :(

  if ($value != '' && $value > 0) // Humidity = 0 or -1 -> Sensor not available
  {
    // Skip if already discovered through iem
    if (!in_array($descr, $iem_sensors['humidity']))
    {
      discover_sensor($valid['sensor'], 'humidity', $device, $oid, "uioSensorStatusHumidity.$index", 'apc', $descr, 1, $value);
    } else {
      print_debug("Sensor was already found through iem table, skipping uio");
    }
  }

  // Temperature
  $value           = $entry['uioSensorStatusTemperatureDegC'];
  $oid             = ".1.3.6.1.4.1.318.1.1.25.1.2.1.6.$index";
  // No thresholds in the uio MIB table :(

  if ($value != '' && $value != -1) // Temperature = -1 -> Sensor not available
  {
    // Skip if already discovered through iem
    if (!in_array($descr, $iem_sensors['temperature']))
    {
      discover_sensor($valid['sensor'], 'temperature', $device, $oid, "uioSensorStatusTemperatureDegC.$index", 'apc', $descr, 1, $value);
    } else {
      print_debug("Sensor was already found through iem table, skipping uio");
    }
  }

  // FIXME we could add the state sensors here too (ViolationStatus, AlarmStatus)
}

unset($iem_sensors); // Unset variable used by iem/uio deduplication code

// Environmental monitoring on rPDU2
$cache['apc'] = snmpwalk_cache_oid($device, "rPDU2SensorTempHumidityConfigTable", array(), "PowerNet-MIB", mib_dirs('apc'));
$cache['apc'] = snmpwalk_cache_oid($device, "rPDU2SensorTempHumidityStatusTable", $cache['apc'], "PowerNet-MIB", mib_dirs('apc'));

foreach ($cache['apc'] as $index => $entry)
{
  $descr           = $entry['rPDU2SensorTempHumidityStatusName'];

  // Humidity
  $value           = $entry['rPDU2SensorTempHumidityStatusRelativeHumidity'];
  $oid             = ".1.3.6.1.4.1.318.1.1.26.10.2.2.1.10.$index";
  $limits = array('limit_low'       => $entry['rPDU2SensorTempHumidityConfigHumidityMinThresh'],
                  'limit_low_warn'  => $entry['rPDU2SensorTempHumidityConfigHumidityLowThresh']);

  if ($value != '' && $value != -1 && $entry['rPDU2SensorTempHumidityStatusHumidityStatus'] != 'notPresent')
  {
    discover_sensor($valid['sensor'], 'humidity', $device, $oid, "rPDU2SensorTempHumidityStatusRelativeHumidity.$index", 'apc', $descr, 1, $value, $limits);
  }

  // Temperature
  $value           = $entry['rPDU2SensorTempHumidityStatusTempC'];
  $oid             = ".1.3.6.1.4.1.318.1.1.26.10.2.2.1.8.$index";
  $limits = array('limit_high'      => $entry['rPDU2SensorTempHumidityConfigTempMaxThreshC'],
                  'limit_high_warn' => $entry['rPDU2SensorTempHumidityConfigTempHighThreshC']);

  if ($value != '' && $value != -1)
  {
    discover_sensor($valid['sensor'], 'temperature', $device, $oid, "rPDU2SensorTempHumidityStatusTempC.$index", 'apc', $descr, $scale, $value, $limits);
  }
}

#### NETBOTZ #########################################################################################

echo(' ');

// PowerNet-MIB::memSensorsStatusSensorNumber.0.7 = INTEGER: 7
// PowerNet-MIB::memSensorsStatusSensorName.0.7 = STRING: "Server Room"
// PowerNet-MIB::memSensorsTemperature.0.7 = INTEGER: 69
// PowerNet-MIB::memSensorsHumidity.0.7 = INTEGER: 55

$cache['apc'] = array();

foreach (array("memSensorsStatusTable") as $table)
{
  echo("$table ");
  $cache['apc'] = snmpwalk_cache_multi_oid($device, $table, $cache['apc'], "PowerNet-MIB", mib_dirs('apc'));
}
$temp_units = snmp_get($device, "memSensorsStatusSysTempUnits.0", "-Ovq", "PowerNet-MIB", mib_dirs('apc'));

foreach ($cache['apc'] as $index => $entry)
{
  $descr = $entry['memSensorsStatusSensorName'];

  $oid = ".1.3.6.1.4.1.318.1.1.10.4.2.3.1.5.$index";
  $value = $entry['memSensorsTemperature'];

  list(,$ems_index) = explode('.', $index);

  // Exclude already added sensor from emsProbeStatusTable
  if ($value != '' && $value != -1 && !isset($valid['sensor']['temperature']['apc']["emsProbeStatusProbeTemperature.$ems_index"]))
  {
    $scale_temp = 1;
    if ($temp_units == 'fahrenheit')
    {
      $options['sensor_unit'] = 'F';
    } else {
      $options['sensor_unit'] = 'C';
    }

    discover_sensor($valid['sensor'], 'temperature', $device, $oid, "memSensorsTemperature.$index", 'apc', $descr, $scale_temp, $value, $options);
  }

  $oid   = ".1.3.6.1.4.1.318.1.1.10.4.2.3.1.6.$index";
  $value = $entry['memSensorsHumidity'];

  // Exclude already added sensor from emsProbeStatusTable
  if ($value != '' && $value > 0 && !isset($valid['sensor']['humidity']['apc']["emsProbeStatusProbeHumidity.$ems_index"]))
  {
    discover_sensor($valid['sensor'], 'humidity', $device, $oid, "memSensorsHumidity.$index", 'apc', $descr, 1, $value);
  }
}

#### INROW CHILLER ###################################################################################

// Build array to cope with APC using different OID trees for different device series.
// $inrow array main key is the sysObjectId.0 of the device.

$inrow = array();

$inrow['airIRRC100Series']['group']['status']['index']    = "airIRRCGroupStatus";
$inrow['airIRRC100Series']['group']['status']['oid']      = ".1.3.6.1.4.1.318.1.1.13.3.2.1.1";
$inrow['airIRRC100Series']['group']['setpoints']['index'] = "airIRRCGroupSetpoints";
$inrow['airIRRC100Series']['group']['setpoints']['oid']   = ".1.3.6.1.4.1.318.1.1.13.3.2.1.2";
$inrow['airIRRC100Series']['unit']['status']['index']     = "airIRRCUnitStatus";
$inrow['airIRRC100Series']['unit']['status']['oid']       = ".1.3.6.1.4.1.318.1.1.13.3.2.2.2";
$inrow['airIRRC100Series']['unit']['thresholds']['index'] = "airIRRCUnitThresholds";

$inrow['airIRRP100Series']['group']['status']['index']    = "airIRRP100GroupStatus";
$inrow['airIRRP100Series']['group']['status']['oid']      = ".1.3.6.1.4.1.318.1.1.13.3.3.1.1.1";
$inrow['airIRRP100Series']['group']['setpoints']['index'] = "airIRRP100GroupSetpoints";
$inrow['airIRRP100Series']['group']['setpoints']['oid']   = ".1.3.6.1.4.1.318.1.1.13.3.3.1.1.2";
$inrow['airIRRP100Series']['unit']['status']['index']     = "airIRRP100UnitStatus";
$inrow['airIRRP100Series']['unit']['status']['oid']       = ".1.3.6.1.4.1.318.1.1.13.3.3.1.2.2";
$inrow['airIRRP100Series']['unit']['thresholds']['index'] = "airIRRP100UnitThresholds";

$inrow['airIRRP500Series']['group']['status']['index']    = "airIRRP500GroupStatus";
$inrow['airIRRP500Series']['group']['status']['oid']      = ".1.3.6.1.4.1.318.1.1.13.3.3.2.1.1";
$inrow['airIRRP500Series']['group']['setpoints']['index'] = "airIRRP500GroupSetpoints";
$inrow['airIRRP500Series']['group']['setpoints']['oid']   = ".1.3.6.1.4.1.318.1.1.13.3.3.2.1.2";
$inrow['airIRRP500Series']['unit']['status']['index']     = "airIRRP500UnitStatus";
$inrow['airIRRP500Series']['unit']['status']['oid']       = ".1.3.6.1.4.1.318.1.1.13.3.3.2.2.2";
$inrow['airIRRP500Series']['unit']['thresholds']['index'] = "airIRRP500UnitThresholds";

$inrow['airIRSC100Series']['group']['status']['index']    = "airIRSCGroupStatus";
$inrow['airIRSC100Series']['group']['status']['oid']      = ".1.3.6.1.4.1.318.1.1.13.3.4.2.1";
$inrow['airIRSC100Series']['group']['setpoints']['index'] = "airIRSCGroupSetpoints";
$inrow['airIRSC100Series']['group']['setpoints']['oid']   = ".1.3.6.1.4.1.318.1.1.13.3.4.2.2";
$inrow['airIRSC100Series']['unit']['status']['index']     = "airIRSCUnitStatus";
$inrow['airIRSC100Series']['unit']['status']['oid']       = ".1.3.6.1.4.1.318.1.1.13.3.4.1.2";
$inrow['airIRSC100Series']['unit']['thresholds']['index'] = "airIRSCUnitThresholds";

$inrow['airIRRD100Series']['group']['status']['index']    = "airIRG2GroupStatus";
$inrow['airIRRD100Series']['group']['status']['oid']      = "1.3.6.1.4.1.318.1.1.13.4.2.1";
$inrow['airIRRD100Series']['group']['setpoints']['index'] = "airIRG2GroupSetpoints";
$inrow['airIRRD100Series']['group']['setpoints']['oid']   = "1.3.6.1.4.1.318.1.1.13.4.2.2";
$inrow['airIRRD100Series']['unit']['status']['index']     = "airIRG2RDT2Status";
$inrow['airIRRD100Series']['unit']['status']['oid']       = "1.3.6.1.4.1.318.1.1.13.4.5.2.1";
$inrow['airIRRD100Series']['unit']['thresholds']['index'] = "airIRG2RDT2Thresholds";

$type = snmp_get($device, "sysObjectID.0", "-OUqsv", "PowerNet-MIB", mib_dirs("apc")); // Get the APC InRow model

if (array_key_exists($type, $inrow)) // Check if the device is a supported APC InRow model as specifed above
{
  // APC InRow, Group Statistics
  echo($inrow[$type]['group']['status']['index'] . ' ');
  $cache['apc'] = snmpwalk_cache_multi_oid($device, $inrow[$type]['group']['status']['index'], array(), "PowerNet-MIB", mib_dirs("apc"));
  echo($inrow[$type]['group']['setpoints']['index'] . ' ');
  $cache['apc'] = snmpwalk_cache_multi_oid($device, $inrow[$type]['group']['setpoints']['index'], $cache['apc'], "PowerNet-MIB", mib_dirs("apc"));

  foreach ($cache['apc'] as $index => $entry)
  {
    // airIRxxGroupStatusCoolOutput.x [.1.3.6.1.4.1.318.1.1.13.x.x.x.1.x]
    $descr = "Group Cooling Output";
    $oid   = $inrow[$type]['group']['status']['oid'] . ".1." . $index;
    $name  = $inrow[$type]['group']['status']['index'] . "CoolOutput";
    $value = $entry[$name];

    if (is_numeric($value))
    {
      discover_sensor($valid['sensor'], 'power', $device, $oid, "$name.$index", 'apc', $descr, 100, $value);
    }

    // airIRxxGroupStatusCoolDemand.x [.1.3.6.1.4.1.318.1.1.13.x.x.x.2.x]
    $descr = "Group Cooling Demand";
    $oid   = $inrow[$type]['group']['status']['oid'] . ".2." . $index;
    $name  = $inrow[$type]['group']['status']['index'] . "CoolDemand";
    $value = $entry[$name];

    if (is_numeric($value))
    {
      discover_sensor($valid['sensor'], 'power', $device, $oid, "$name.$index", 'apc', $descr, 100, $value);
    }

    // airIRxxGroupStatusAirFlowUS.x [.1.3.6.1.4.1.318.1.1.13.x.x.x.3.x]
    $descr = "Group Air Flow";
    $oid   = $inrow[$type]['group']['status']['oid'] . ".3." . $index;
    $name  = $inrow[$type]['group']['status']['index'] . "AirFlowUS";
    $value = $entry[$name];

    if (is_numeric($value))
    {
      discover_sensor($valid['sensor'], 'airflow', $device, $oid, "$name.$index", 'apc', $descr, 1, $value);
    }

    // airIRxxGroupStatusMaxRackInletTempMetric.x [.1.3.6.1.4.1.318.1.1.13.x.x.x.6.x]
    $descr = "Group Maximum Rack Inlet Temperature";
    $oid   = $inrow[$type]['group']['status']['oid'] . ".6." . $index;
    $name  = $inrow[$type]['group']['status']['index'] . "MaxRackInletTempMetric";
    $value = $entry[$name];

    if (is_numeric($value))
    {
      discover_sensor($valid['sensor'], 'temperature', $device, $oid, "$name.$index", 'apc', $descr, 0.1, $value);
    }

    // airIRxxGroupStatusMinRackInletTempMetric.x [.1.3.6.1.4.1.318.1.1.13.x.x.x.8.x]
    $descr = "Group Minimum Rack Inlet Temperature";
    $oid   = $inrow[$type]['group']['status']['oid'] . ".8." . $index;
    $name  = $inrow[$type]['group']['status']['index'] . "MinRackInletTempMetric";
    $value = $entry[$name];

    if (is_numeric($value))
    {
      discover_sensor($valid['sensor'], 'temperature', $device, $oid, "$name.$index", 'apc', $descr, 0.1, $value);
    }

    // airIRxxGroupStatusMaxReturnAirTempMetric.x [.1.3.6.1.4.1.318.1.1.13.x.x.x.10.x]
    $descr = "Group Maximum Return Air Temperature";
    $oid   = $inrow[$type]['group']['status']['oid'] . ".10." . $index;
    $name  = $inrow[$type]['group']['status']['index'] . "MaxReturnAirTempMetric";
    $value = $entry[$name];

    if (is_numeric($value))
    {
      discover_sensor($valid['sensor'], 'temperature', $device, $oid, "$name.$index", 'apc', $descr, 0.1, $value);
    }

    // airIRxxGroupStatusMinReturnAirTempMetric.x [.1.3.6.1.4.1.318.1.1.13.x.x.x.12.x]
    $descr = "Group Minimum Return Air Temperature";
    $oid   = $inrow[$type]['group']['status']['oid'] . ".12." . $index;
    $name  = $inrow[$type]['group']['status']['index'] . "MinReturnAirTempMetric";
    $value = $entry[$name];

    if (is_numeric($value))
    {
      discover_sensor($valid['sensor'], 'temperature', $device, $oid, "$name.$index", 'apc', $descr, 0.1, $value);
    }

    // airIRxxGroupSetpointsCoolMetric.x [.1.3.6.1.4.1.318.1.1.13.x.x.x.x.2.x]
    $descr = "Group Cooling Setpoint";
    $oid   = $inrow[$type]['group']['setpoints']['oid'] . ".2." . $index;
    $name  = $inrow[$type]['group']['setpoints']['index'] . "CoolMetric";
    $value = $entry[$name];

    if (is_numeric($value))
    {
      discover_sensor($valid['sensor'], 'temperature', $device, $oid, "$name.$index", 'apc', $descr, 0.1, $value);
    }

    // airIRxxGroupSetpointsSupplyAirMetric.x [.1.3.6.1.4.1.318.1.1.13.x.x.x.x.4.x]
    $descr = "Group Supply Setpoint";
    $oid   = $inrow[$type]['group']['setpoints']['oid'] . ".4." . $index;
    $name  = $inrow[$type]['group']['setpoints']['index'] . "SupplyAirMetric";
    $value = $entry[$name];

    if (is_numeric($value))
    {
      discover_sensor($valid['sensor'], 'temperature', $device, $oid, "$name.$index", 'apc', $descr, 0.1, $value);
    }
  }

  echo(' ');

  // APC InRow, Unit Statistics
  echo($inrow[$type]['unit']['status']['index'] . ' ');
  $cache['apc'] = snmpwalk_cache_multi_oid($device, $inrow[$type]['unit']['status']['index'], array(), "PowerNet-MIB", mib_dirs("apc"));
  echo($inrow[$type]['unit']['thresholds']['index'] . ' ');
  $cache['apc'] = snmpwalk_cache_multi_oid($device, $inrow[$type]['unit']['thresholds']['index'], $cache['apc'], "PowerNet-MIB", mib_dirs("apc"));

  foreach ($cache['apc'] as $index => $entry)
  {
    // If there are multiple units found, use the unit number as description prefix
    $unit = count($cache['apc']) != 1 ? "Unit " . ($index + 1) : "Unit";

    // airIRxxUnitStatusCoolOutput.x [.1.3.6.1.4.1.318.1.1.13.x.x.x.2.x]
    $descr = $unit . " Cooling Output";
    $oid   = $inrow[$type]['unit']['status']['oid'] . ".2." . $index;
    $name  = $inrow[$type]['unit']['status']['index'] . "CoolOutput";
    $value = $entry[$name];

    if (is_numeric($value))
    {
      discover_sensor($valid['sensor'], 'power', $device, $oid, "$name.$index", 'apc', $descr, 100, $value);
    }

    // airIRxxUnitStatusCoolDemand.x [.1.3.6.1.4.1.318.1.1.13.x.x.x.3.x]
    $descr = $unit . " Cooling Demand";
    $oid   = $inrow[$type]['unit']['status']['oid'] . ".3." . $index;
    $name  = $inrow[$type]['unit']['status']['index'] . "CoolDemand";
    $value = $entry[$name];

    if (is_numeric($value))
    {
      discover_sensor($valid['sensor'], 'power', $device, $oid, "$name.$index", 'apc', $descr, 100, $value);
    }

    // airIRxxUnitStatusAirFlowUS.x [.1.3.6.1.4.1.318.1.1.13.x.x.x.4.x]
    $descr = $unit . " Air Flow";
    $oid   = $inrow[$type]['unit']['status']['oid'] . ".4." . $index;
    $name  = $inrow[$type]['unit']['status']['index'] . "AirFlowUS";
    $value = $entry[$name];

    if (is_numeric($value))
    {
      discover_sensor($valid['sensor'], 'airflow', $device, $oid, "$name.$index", 'apc', $descr, 1, $value);
    }

    // airIRxxUnitStatusRackInletTempMetric.x [.1.3.6.1.4.1.318.1.1.13.x.x.x.7.x]
    $descr = $unit . " Rack Inlet Temperature";
    $oid   = $inrow[$type]['unit']['status']['oid'] . ".7." . $index;
    $name  = $inrow[$type]['unit']['status']['index'] . "RackInletTempMetric";
    $value = $entry[$name];
    $limit = array('limit_high' => $entry[$inrow[$type]['unit']['thresholds']['index'] . 'RackInletHighTempMetric'] / 10);

    if (is_numeric($value))
    {
      discover_sensor($valid['sensor'], 'temperature', $device, $oid, "$name.$index", 'apc', $descr, 0.1, $value, $limit);
    }

    // airIRxxUnitStatusSupplyAirTempMetric.x [.1.3.6.1.4.1.318.1.1.13.x.x.x.9.x]
    $descr = $unit . " Supply Air Temperature";
    $oid   = $inrow[$type]['unit']['status']['oid'] . ".9." . $index;
    $name  = $inrow[$type]['unit']['status']['index'] . "SupplyAirTempMetric";
    $value = $entry[$name];
    $limit = array('limit_high' => $entry[$inrow[$type]['unit']['thresholds']['index'] . 'SupplyAirHighTempMetric'] / 10);

    if (is_numeric($value))
    {
      discover_sensor($valid['sensor'], 'temperature', $device, $oid, "$name.$index", 'apc', $descr, 0.1, $value, $limit);
    }

    // airIRxxUnitStatusReturnAirTempMetric.x [.1.3.6.1.4.1.318.1.1.13.x.x.x.11.x]
    $descr = $unit . " Return Air Temperature";
    $oid   = $inrow[$type]['unit']['status']['oid'] . ".11." . $index;
    $name  = $inrow[$type]['unit']['status']['index'] . "ReturnAirTempMetric";
    $value = $entry[$name];
    $limit = array('limit_high' => $entry[$inrow[$type]['unit']['thresholds']['index'] . 'ReturnAirHighTempMetric'] / 10);

    if (is_numeric($value))
    {
      discover_sensor($valid['sensor'], 'temperature', $device, $oid, "$name.$index", 'apc', $descr, 0.1, $value, $limit);
    }

    // airIRxxUnitStatusSuctionTempMetric.x [.1.3.6.1.4.1.318.1.1.13.x.x.x.13.x]
    $descr = $unit . " Suction Temperature";
    $oid   = $inrow[$type]['unit']['status']['oid'] . ".13." . $index;
    $name  = $inrow[$type]['unit']['status']['index'] . "SuctionTempMetric";
    $value = $entry[$name];

    if (is_numeric($value))
    {
      discover_sensor($valid['sensor'], 'temperature', $device, $oid, "$name.$index", 'apc', $descr, 0.1, $value);
    }

    // airIRxxUnitStatusFilterDPMetric.x [.1.3.6.1.4.1.318.1.1.13.x.x.x.13.x]
    $descr = $unit . " Air Filter Pressure";
    $oid   = $inrow[$type]['unit']['status']['oid'] . ".13." . $index;
    $name  = $inrow[$type]['unit']['status']['index'] . "FilterDPMetric";
    $value = $entry[$name];

    if (is_numeric($value))
    {
      discover_sensor($valid['sensor'], 'pressure', $device, $oid, "$name.$index", 'apc', $descr, 1, $value);
    }

    // airIRxxUnitStatusContainmtDPMetric.x [.1.3.6.1.4.1.318.1.1.13.x.x.x.15.x]
    $descr = $unit . " Containment Pressure";
    $oid   = $inrow[$type]['unit']['status']['oid'] . ".15." . $index;
    $name  = $inrow[$type]['unit']['status']['index'] . "ContainmtDPMetric";
    $value = $entry[$name];

    if (is_numeric($value))
    {
      discover_sensor($valid['sensor'], 'pressure', $device, $oid, "$name.$index", 'apc', $descr, 1, $value);
    }

    // airIRxxUnitStatusEnteringFluidTemperatureMetric.x [.1.3.6.1.4.1.318.1.1.13.x.x.x.24.x]
    $descr = $unit . " Entering Fluid Temperature";
    $oid   = $inrow[$type]['unit']['status']['oid'] . ".24." . $index;
    $name  = $inrow[$type]['unit']['status']['index'] . "EnteringFluidTemperatureMetric";
    $value = $entry[$name];
    $limit = array('limit_high' => $entry[$inrow[$type]['unit']['thresholds']['index'] . 'EnteringFluidHighTempMetric'] / 10);

    if (is_numeric($value))
    {
      discover_sensor($valid['sensor'], 'temperature', $device, $oid, "$name.$index", 'apc', $descr, 0.1, $value, $limit);
    }

    // airIRxxUnitStatusLeavingFluidTemperatureMetric.x [.1.3.6.1.4.1.318.1.1.13.x.x.x.26.x]
    $descr = $unit . " Leaving Fluid Temperature";
    $oid   = $inrow[$type]['unit']['status']['oid'] . ".26." . $index;
    $name  = $inrow[$type]['unit']['status']['index'] . "LeavingFluidTemperatureMetric";
    $value = $entry[$name];

    if (is_numeric($value))
    {
      discover_sensor($valid['sensor'], 'temperature', $device, $oid, "$name.$index", 'apc', $descr, 0.1, $value);
    }
  }
}

unset($type, $inrow);

#### NEW GENERATION INROW CHILLER ####################################################################

$cache['apc'] = array();

// APC took a different approach here, with generic sensor descr/value in a table.
// According to documentation, it looks like the OIDs are hard linked to the sensor type,
// but I don't think we should rely on this to be true in the future as well.
// We map the units to sensor types through the following array.
// This does make it harder to link limits/setpoints to values though. :[

$apc_unit_map = array(
  'C' => 'temperature',
  'F' => '', // Ignored, we use C instead
  'CFM' => 'airflow',
  'GPM' => '', // Gallons per minute, no sensor type for water flow right now
  'kW' => 'power',
  'W' => 'power',
  '%' => 'capacity',
  'Pa' => 'pressure',
  'kWh' => '', // Currently not supported
  'hr' => '', // Hours? Currently not supported
  'weeks' => '', // Currently not supported
);

// PowerNet-MIB::coolingUnitStatusAnalogDescription.1.1 = STRING: "Supply Air Temperature"
// PowerNet-MIB::coolingUnitStatusAnalogValue.1.1 = INTEGER: 526
// PowerNet-MIB::coolingUnitStatusAnalogUnits.1.1 = STRING: "F"
// PowerNet-MIB::coolingUnitStatusAnalogScale.1.1 = INTEGER: 10

foreach (array("coolingUnitStatusAnalogTable") as $table)
{
  echo("$table ");
  $cache['apc'] = snmpwalk_cache_multi_oid($device, $table, $cache['apc'], "PowerNet-MIB", mib_dirs('apc'));
}

foreach ($cache['apc'] as $index => $entry)
{
  if ($apc_unit_map[$entry['coolingUnitStatusAnalogUnits']])
  {
    // Proceed if we can map this to a local sensor type

    $descr = $entry['coolingUnitStatusAnalogDescription'];
    $oid   = ".1.3.6.1.4.1.318.1.1.27.1.4.1.2.1.3.$index";
    $scale = 1 / $entry['coolingUnitStatusAnalogScale'];
    $value = $entry['coolingUnitStatusAnalogValue'];

    // Workaround for kW vs W
    if ($entry['coolingUnitStatusAnalogUnits'] == 'kW')
    {
      $scale = 1000 / $entry['coolingUnitStatusAnalogScale']; // 1kW = 1000W
    }

    discover_sensor($valid['sensor'], $apc_unit_map[$entry['coolingUnitStatusAnalogUnits']], $device, $oid, "coolingUnitStatusAnalogValue.$index", 'apc', $descr, $scale, $value);
  }
}

echo(' ');

// PowerNet-MIB::coolingUnitExtendedAnalogDescription.1.1 = STRING: "Chilled Water Valve Position"
// PowerNet-MIB::coolingUnitExtendedAnalogValue.1.1 = INTEGER: 21
// PowerNet-MIB::coolingUnitExtendedAnalogUnits.1.1 = STRING: "%"
// PowerNet-MIB::coolingUnitExtendedAnalogScale.1.1 = INTEGER: 1

foreach (array("coolingUnitExtendedAnalogTable") as $table)
{
  echo("$table ");
  $cache['apc'] = snmpwalk_cache_multi_oid($device, $table, $cache['apc'], "PowerNet-MIB", mib_dirs('apc'));
}

foreach ($cache['apc'] as $index => $entry)
{
  if ($apc_unit_map[$entry['coolingUnitExtendedAnalogUnits']])
  {
    // Proceed if we can map this to a local sensor type

    $descr = $entry['coolingUnitExtendedAnalogDescription'];
    $oid   = ".1.3.6.1.4.1.318.1.1.27.1.6.1.2.1.3.$index";
    $scale = 1 / $entry['coolingUnitExtendedAnalogScale'];
    $value = $entry['coolingUnitExtendedAnalogValue'];

    // Workaround for kW vs W
    if ($entry['coolingUnitExtendedAnalogUnits'] == 'kW')
    {
      $scale = 1000 / $entry['coolingUnitStatusAnalogScale']; // 1kW = 1000W
    }

    discover_sensor($valid['sensor'], $apc_unit_map[$entry['coolingUnitExtendedAnalogUnits']], $device, $oid, "coolingUnitExtendedAnalogValue.$index", 'apc', $descr, $scale, $value);
  }
}

unset($apc_unit_map);

echo(' ');

$cache['apc'] = array();

// PowerNet-MIB::coolingUnitStatusDiscreteDescription.1.1 = STRING: "Operating Mode"
// PowerNet-MIB::coolingUnitStatusDiscreteDescription.1.2 = STRING: "Active Flow Control Status"
// PowerNet-MIB::coolingUnitStatusDiscreteValueAsString.1.1 = STRING: "On"
// PowerNet-MIB::coolingUnitStatusDiscreteValueAsString.1.2 = STRING: "NA"
// PowerNet-MIB::coolingUnitStatusDiscreteValueAsInteger.1.1 = INTEGER: 1
// PowerNet-MIB::coolingUnitStatusDiscreteValueAsInteger.1.2 = INTEGER: 3
// PowerNet-MIB::coolingUnitStatusDiscreteIntegerReferenceKey.1.1 = STRING: "Standby(0),On(1),Idle(2),Maintenance(3)"
// PowerNet-MIB::coolingUnitStatusDiscreteIntegerReferenceKey.1.2 = STRING: "Under(0),Okay(1),Over(2),NA(3)"

$apc_discrete_map = array(
  "Open(0),Closed(1)" => 'powernet-cooling-input-state',
  "Abnormal(0),Normal(1)" => 'powernet-cooling-output-state',
  "Primary (0),Secondary(1)" => 'powernet-cooling-powersource-state',
  "Undefined(0),Standard(1),HighTemp(2)" => 'powernet-cooling-unittype-state',
  "Standby(0),On(1),Idle(2),Maintenance(3)" => 'powernet-cooling-opmode-state',
  "Under(0),Okay(1),Over(2),NA(3)" => 'powernet-cooling-flowcontrol-state',
);

foreach (array("coolingUnitStatusDiscreteTable") as $table)
{
  echo("$table ");
  $cache['apc'] = snmpwalk_cache_multi_oid($device, $table, $cache['apc'], "PowerNet-MIB", mib_dirs('apc'));
}

foreach ($cache['apc'] as $index => $entry)
{
  $descr = $entry['coolingUnitStatusDiscreteDescription'];
  $oid   = ".1.3.6.1.4.1.318.1.1.27.1.4.2.2.1.4.$index";
  $value = $entry['coolingUnitStatusDiscreteValue'];

  // If we have a state mapped, add status, if not, well... help.
  if ($apc_discrete_map[$entry['coolingUnitStatusDiscreteIntegerReferenceKey']])
  {
    discover_status($device, $oid, "coolingUnitStatusDiscreteValueAsInteger.$index", $apc_discrete_map[$entry['coolingUnitStatusDiscreteIntegerReferenceKey']], $descr, 1, $value);
  }
}

echo(' ');

$cache['apc'] = array();

// PowerNet-MIB::coolingUnitExtendedDiscreteDescription.1.1 = STRING: "Standby Input State"
// PowerNet-MIB::coolingUnitExtendedDiscreteDescription.1.2 = STRING: "Output 1 State"
// PowerNet-MIB::coolingUnitExtendedDiscreteValueAsString.1.1 = STRING: "Open"
// PowerNet-MIB::coolingUnitExtendedDiscreteValueAsString.1.2 = STRING: "Normal"
// PowerNet-MIB::coolingUnitExtendedDiscreteValueAsInteger.1.1 = INTEGER: 0
// PowerNet-MIB::coolingUnitExtendedDiscreteValueAsInteger.1.2 = INTEGER: 1
// PowerNet-MIB::coolingUnitExtendedDiscreteIntegerReferenceKey.1.1 = STRING: "Open(0),Closed(1)"
// PowerNet-MIB::coolingUnitExtendedDiscreteIntegerReferenceKey.1.2 = STRING: "Abnormal(0),Normal(1)"
// PowerNet-MIB::coolingUnitExtendedDiscreteIntegerReferenceKey.1.6 = STRING: "Primary (0),Secondary(1)"
// PowerNet-MIB::coolingUnitExtendedDiscreteIntegerReferenceKey.1.7 = STRING: "Undefined(0),Standard(1),HighTemp(2)"

foreach (array("coolingUnitExtendedDiscreteTable") as $table)
{
  echo("$table ");
  $cache['apc'] = snmpwalk_cache_multi_oid($device, $table, $cache['apc'], "PowerNet-MIB", mib_dirs('apc'));
}

foreach ($cache['apc'] as $index => $entry)
{
  $descr = $entry['coolingUnitExtendedDiscreteDescription'];
  $oid   = ".1.3.6.1.4.1.318.1.1.27.1.6.2.2.1.4.$index";
  $value = $entry['coolingUnitExtendedDiscreteValue'];

  // If we have a state mapped, add status, if not, well... help.
  if ($apc_discrete_map[$entry['coolingUnitExtendedDiscreteIntegerReferenceKey']])
  {
    discover_status($device, $oid, "coolingUnitExtendedDiscreteValueAsInteger.$index", $apc_discrete_map[$entry['coolingUnitExtendedDiscreteIntegerReferenceKey']], $descr, 1, $value);
  }
}

unset($apc_discrete_map);

#### Legacy mUpsEnvironment Sensors (AP9312TH) #######################################################

echo(' ');

$cache['apc'] = snmp_get_multi($device, "mUpsEnvironAmbientTemperature.0 mUpsEnvironRelativeHumidity.0 mUpsEnvironAmbientTemperature2.0 mUpsEnvironRelativeHumidity2.0", "-OUQs", "PowerNet-MIB", mib_dirs('apc'));

foreach ($cache['apc'] as $index => $entry)
{
  if (is_numeric($entry['mUpsEnvironAmbientTemperature']))
  {
    $descr = "Probe 1 Temperature";
    $oid   = ".1.3.6.1.4.1.318.1.1.2.1.1.$index";
    $value = $entry['mUpsEnvironAmbientTemperature'];

    discover_sensor($valid['sensor'], 'temperature', $device, $oid, "mUpsEnvironAmbientTemperature.$index", 'apc', $descr, 1, $value);
  }

  if (is_numeric($entry['mUpsEnvironRelativeHumidity']))
  {
    $descr = "Probe 1 Humidity";
    $oid   = ".1.3.6.1.4.1.318.1.1.2.1.2.$index";
    $value = $entry['mUpsEnvironRelativeHumidity'];

    discover_sensor($valid['sensor'], 'humidity', $device, $oid, "mUpsEnvironRelativeHumidity.$index", 'apc', $descr, 1, $value);
  }

  if (is_numeric($entry['mUpsEnvironAmbientTemperature2']))
  {
    $descr = "Probe 2 Temperature";
    $oid   = ".1.3.6.1.4.1.318.1.1.2.1.3.$index";
    $value = $entry['mUpsEnvironAmbientTemperature2'];

    discover_sensor($valid['sensor'], 'temperature', $device, $oid, "mUpsEnvironAmbientTemperature2.$index", 'apc', $descr, 1, $value);
  }

  if (is_numeric($entry['mUpsEnvironRelativeHumidity2']))
  {
    $descr = "Probe 2 Humidity";
    $oid   = ".1.3.6.1.4.1.318.1.1.2.1.4.$index";
    $value = $entry['mUpsEnvironRelativeHumidity2'];

    discover_sensor($valid['sensor'], 'humidity', $device, $oid, "mUpsEnvironRelativeHumidity2.$index", 'apc', $descr, 1, $value);
  }
}

$cache['apc'] = array();

foreach (array("mUpsContactTable") as $table)
{
  echo("$table ");
  $cache['apc'] = snmpwalk_cache_multi_oid($device, $table, $cache['apc'], "PowerNet-MIB", mib_dirs('apc'));
}

foreach ($cache['apc'] as $index => $entry)
{
  if ($entry['monitoringStatus'] == "enabled")
  {
    $descr = $entry['description'];
    $oid   = ".1.3.6.1.4.1.318.1.1.2.2.2.1.5.$index";
    $value = $entry['currentStatus'];

    discover_sensor($valid['sensor'], 'state', $device, $oid, "currentStatus.$index", 'powernet-mupscontact-state', $descr, NULL, $value, array('entPhysicalClass' => 'other'));
  }
}

#### NETBOTZ PX ACCESS CONTROL #######################################################################

$cache['apc'] = array();

// accessPXIdentProductNumber.0 = STRING: "AP9361"
// accessPXIdentHardwareRev.0 = STRING: "04"
// accessPXIdentDateOfManufacture.0 = STRING: "04/29/2010"
// accessPXIdentSerialNumber.0 = STRING: "QA1018180304"
// accessPXConfigCardReaderEnableDisableAction.0 = INTEGER: enable(2)
// accessPXConfigAutoRelockTime.0 = INTEGER: 60
// accessPXConfigCardFormat.0 = INTEGER: hidStd26(1)
// accessPXConfigBeaconName.0 = STRING: "Beacon Name"
// accessPXConfigBeaconLocation.0 = STRING: "Beacon Location"
// accessPXConfigBeaconAction.0 = INTEGER: disconnectedReadOnly(4)
// accessPXStatusBeaconName.0 = STRING: "Beacon Name"
// accessPXStatusBeaconLocation.0 = STRING: "Beacon Location"
// accessPXStatusBeaconCurrentState.0 = INTEGER: disconnected(4)

foreach (array("accessPX") as $table)
{
  echo("$table ");
  $cache['apc'] = snmpwalk_cache_multi_oid($device, $table, $cache['apc'], "PowerNet-MIB", mib_dirs('apc'));
}

foreach ($cache['apc'] as $index => $entry)
{
  // accessPXIdentAlarmStatus.0 = INTEGER: 3
  if ($entry['accessPXIdentAlarmStatus'])
  {
    $descr = "Access PX Alarm Status";
    $oid   = ".1.3.6.1.4.1.318.1.1.20.1.1.1.$index";

    discover_status($device, $oid, "accessPXIdentAlarmStatus.$index", 'powernet-accesspx-state', $descr, $entry['accessPXIdentAlarmStatus']);
  }

  // accessPXConfigFrontDoorLockControl.0 = INTEGER: lock(2)
  // accessPXConfigFrontDoorMaxOpenTime.0 = INTEGER: 10
  // accessPXStatusFrontDoorLock.0 = INTEGER: locked(2)
  // accessPXStatusFrontDoor.0 = INTEGER: closed(2)
  // accessPXStatusFrontDoorHandle.0 = INTEGER: closed(2)
  // accessPXStatusFrontDoorMaxOpenTime.0 = INTEGER: 10
  // accessPXStatusFrontDoorAlarmStatus.0 = INTEGER: 1

  if ($entry['accessPXStatusFrontDoorLock'])
  {
    $descr = "Front Door Lock";
    $oid   = ".1.3.6.1.4.1.318.1.1.20.1.4.1.$index";

    discover_status($device, $oid, "accessPXStatusFrontDoorLock.$index", 'powernet-door-lock-state', $descr, $entry['accessPXStatusFrontDoorLock']);
  }

  if ($entry['accessPXStatusFrontDoor'])
  {
    $descr = "Front Door";
    $oid   = ".1.3.6.1.4.1.318.1.1.20.1.4.2.$index";

    discover_status($device, $oid, "accessPXStatusFrontDoor.$index", 'powernet-door-state', $descr, $entry['accessPXStatusFrontDoor']);
  }

  if ($entry['accessPXStatusFrontDoorHandle'])
  {
    $descr = "Front Door Handle";
    $oid   = ".1.3.6.1.4.1.318.1.1.20.1.4.3.$index";

    discover_status($device, $oid, "accessPXStatusFrontDoorHandle.$index", 'powernet-door-state', $descr, $entry['accessPXStatusFrontDoorHandle']);
  }

  if ($entry['accessPXStatusFrontDoorAlarmStatus'])
  {
    $descr = "Front Door Alarm Status";
    $oid   = ".1.3.6.1.4.1.318.1.1.20.1.4.5.$index";

    discover_status($device, $oid, "accessPXStatusFrontDoorAlarmStatus.$index", 'powernet-door-alarm-state', $descr, $entry['accessPXStatusFrontDoorAlarmStatus']);
  }

  // accessPXConfigRearDoorLockControl.0 = INTEGER: lock(2)
  // accessPXConfigRearDoorMaxOpenTime.0 = INTEGER: 10
  // accessPXStatusRearDoorLock.0 = INTEGER: locked(2)
  // accessPXStatusRearDoor.0 = INTEGER: closed(2)
  // accessPXStatusRearDoorHandle.0 = INTEGER: closed(2)
  // accessPXStatusRearDoorMaxOpenTime.0 = INTEGER: 10
  // accessPXStatusRearDoorAlarmStatus.0 = INTEGER: 1

  if ($entry['accessPXStatusRearDoorLock'])
  {
    $descr = "Rear Door Lock";
    $oid   = ".1.3.6.1.4.1.318.1.1.20.1.6.1.$index";

    discover_status($device, $oid, "accessPXStatusRearDoorLock.$index", 'powernet-door-lock-state', $descr, $entry['accessPXStatusRearDoorLock']);
  }

  if ($entry['accessPXStatusRearDoor'])
  {
    $descr = "Rear Door";
    $oid   = ".1.3.6.1.4.1.318.1.1.20.1.6.2.$index";

    discover_status($device, $oid, "accessPXStatusRearDoor.$index", 'powernet-door-state', $descr, $entry['accessPXStatusRearDoor']);
  }

  if ($entry['accessPXStatusRearDoorHandle'])
  {
    $descr = "Rear Door Handle";
    $oid   = ".1.3.6.1.4.1.318.1.1.20.1.6.3.$index";

    discover_status($device, $oid, "accessPXStatusRearDoorHandle.$index", 'powernet-door-state', $descr, $entry['accessPXStatusRearDoorHandle']);
  }

  if ($entry['accessPXStatusRearDoorAlarmStatus'])
  {
    $descr = "Rear Door Alarm Status";
    $oid   = ".1.3.6.1.4.1.318.1.1.20.1.6.5.$index";

    discover_status($device, $oid, "accessPXStatusRearDoorAlarmStatus.$index", 'powernet-door-alarm-state', $descr, $entry['accessPXStatusRearDoorAlarmStatus']);
  }
}

// EOF
