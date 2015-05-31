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
  for ($i = 1;$i <= $inputs;$i++)
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
        discover_sensor($valid['sensor'], 'current', $device, $oid, "upsPhaseInputCurrent.$tindex.1.$p", 'apc', $descr, $scale, $value * $scale);

        ## Rename code for older revisions
        $old_rrd  = $config['rrd_dir'] . "/".$device['hostname']."/" . safename("sensor-current-apc-6.$tindex.1.$p.rrd");
        $new_rrd  = $config['rrd_dir'] . "/".$device['hostname']."/" . safename("sensor-current-apc-upsPhaseInputCurrent.$tindex.1.$p.rrd");
        if (is_file($old_rrd)) { rename($old_rrd,$new_rrd); print_warning("Moved RRD"); }
      }

      $oid      = ".1.3.6.1.4.1.318.1.1.1.9.2.3.1.3.$tindex.1.$p";
      $value    = $cache['apc']["$tindex.1.$p"]['upsPhaseInputVoltage'];

      if ($value != '' && $value != -1)
      {
        discover_sensor($valid['sensor'], 'voltage', $device, $oid, "upsPhaseInputVoltage.$tindex.1.$p", 'apc', $descr, 1, $value);

        ## Rename code for older revisions
        $old_rrd  = $config['rrd_dir'] . "/".$device['hostname']."/" . safename("sensor-voltage-apc-2.3.1.3.$tindex.1.$p.rrd");
        $new_rrd  = $config['rrd_dir'] . "/".$device['hostname']."/" . safename("sensor-voltage-apc-upsPhaseInputVoltage.$tindex.1.$p.rrd");
        if (is_file($old_rrd)) { rename($old_rrd,$new_rrd); print_warning("Moved RRD"); }
      }
    }

    // Frequency is reported only once per input
    $descr = $name;
    $index = "upsPhaseInputFrequency.$tindex";
    $oid   = ".1.3.6.1.4.1.318.1.1.1.9.2.2.1.4.$tindex";
    $value = $cache['apc'][$i]['upsPhaseInputFrequency'];

    if ($value != '' && $value != -1)
    {
      discover_sensor($valid['sensor'], 'frequency', $device, $oid, $index, 'apc', $descr, $scale, $value * $scale);
    }
  }

  // Process each output, per phase
  for ($o = 1;$o <= $outputs;$o++)
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
        discover_sensor($valid['sensor'], 'current', $device, $oid, "upsPhaseOutputCurrent.$tindex.1.$p", 'apc', $descr, $scale, $value * $scale);

        ## Rename code for older revisions
        $old_rrd  = $config['rrd_dir'] . "/".$device['hostname']."/" . safename("sensor-current-apc-4.$tindex.1.$p.rrd");
        $new_rrd  = $config['rrd_dir'] . "/".$device['hostname']."/" . safename("sensor-current-apc-upsPhaseOutputCurrent.$tindex.1.$p.rrd");
        if (is_file($old_rrd)) { rename($old_rrd,$new_rrd); print_warning("Moved RRD"); }
      }

      $oid      = ".1.3.6.1.4.1.318.1.1.1.9.3.3.1.3.$tindex.1.$p";
      $value    = $cache['apc']["$tindex.1.$p"]['upsPhaseOutputVoltage'];

      if ($value != '' && $value != -1)
      {
        discover_sensor($valid['sensor'], 'voltage', $device, $oid, "upsPhaseOutputVoltage.$tindex.1.$p", 'apc', $descr, 1, $value);

        ## Rename code for older revisions
        $old_rrd  = $config['rrd_dir'] . "/".$device['hostname']."/" . safename("sensor-voltage-apc-3.3.1.3.$tindex.1.$p.rrd");
        $new_rrd  = $config['rrd_dir'] . "/".$device['hostname']."/" . safename("sensor-voltage-apc-upsPhaseOutputVoltage.$tindex.1.$p.rrd");
        if (is_file($old_rrd)) { rename($old_rrd,$new_rrd); print_warning("Moved RRD"); }
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
      discover_sensor($valid['sensor'], 'frequency', $device, $oid, "upsPhaseOutputFrequency.$tindex", 'apc', $descr, $scale, $value * $scale);
    }
  }
}
else
{
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
        ## Rename code for older revisions
        $old_rrd  = $config['rrd_dir'] . "/".$device['hostname']."/" . safename("sensor-voltage-apc-3.3.1.$index.rrd");
        $new_rrd  = $config['rrd_dir'] . "/".$device['hostname']."/" . safename("sensor-voltage-apc-upsHighPrecInputLineVoltage.$index.rrd");
        if (is_file($old_rrd)) { rename($old_rrd,$new_rrd); print_warning("Moved RRD"); }

        discover_sensor($valid['sensor'], 'voltage', $device, $oid, "upsHighPrecInputLineVoltage.$index", 'apc', $descr, $scale, $value * $scale);
      }

      $oid   = ".1.3.6.1.4.1.318.1.1.1.3.3.4.$index";
      $descr = "Input";
      $value = $entry['upsHighPrecInputFrequency'];

      if ($value != '' && $value != -1)
      {
        ## Rename code for older revisions
        $old_rrd  = $config['rrd_dir'] . "/".$device['hostname']."/" . safename("sensor-frequency-apc-3.3.4.$index.rrd");
        $new_rrd  = $config['rrd_dir'] . "/".$device['hostname']."/" . safename("sensor-frequency-apc-upsHighPrecInputFrequency.$index.rrd");
        if (is_file($old_rrd)) { rename($old_rrd,$new_rrd); print_warning("Moved RRD"); }

        discover_sensor($valid['sensor'], 'frequency', $device, $oid, "upsHighPrecInputFrequency.$index", 'apc', $descr, $scale, $value * $scale);
      }

      $oid   = ".1.3.6.1.4.1.318.1.1.1.4.3.1.$index";
      $descr = "Output";
      $value = $entry['upsHighPrecOutputVoltage'];

      if ($value != '' && $value != -1)
      {
        ## Rename code for older revisions
        $old_rrd  = $config['rrd_dir'] . "/".$device['hostname']."/" . safename("sensor-voltage-apc-4.3.1.$index.rrd");
        $new_rrd  = $config['rrd_dir'] . "/".$device['hostname']."/" . safename("sensor-voltage-apc-upsHighPrecOutputVoltage.$index.rrd");
        if (is_file($old_rrd)) { rename($old_rrd,$new_rrd); print_warning("Moved RRD"); }

        discover_sensor($valid['sensor'], 'voltage', $device, $oid, "upsHighPrecOutputVoltage.$index", 'apc', $descr, $scale, $value * $scale);
      }

      $oid = ".1.3.6.1.4.1.318.1.1.1.4.3.4.$index";
      $descr = "Output";
      $value = $entry['upsHighPrecOutputCurrent'];

      if ($value != '' && $value != -1)
      {
        ## Rename code for older revisions
        $old_rrd  = $config['rrd_dir'] . "/".$device['hostname']."/" . safename("sensor-current-apc-4.3.4.$index.rrd");
        $new_rrd  = $config['rrd_dir'] . "/".$device['hostname']."/" . safename("sensor-current-apc-upsHighPrecOutputCurrent.$index.rrd");
        if (is_file($old_rrd)) { rename($old_rrd,$new_rrd); print_warning("Moved RRD"); }

        discover_sensor($valid['sensor'], 'current', $device, $oid, "upsHighPrecOutputCurrent.$index", 'apc', $descr, $scale, $value * $scale);
      }

      $oid   = ".1.3.6.1.4.1.318.1.1.1.4.3.2.$index";
      $descr = "Output";
      $value = $entry['upsHighPrecOutputFrequency'];

      if ($value != '' && $value != -1)
      {
        ## Rename code for older revisions
        $old_rrd  = $config['rrd_dir'] . "/".$device['hostname']."/" . safename("sensor-frequency-apc-4.3.2." . $index . ".rrd");
        $new_rrd  = $config['rrd_dir'] . "/".$device['hostname']."/" . safename("sensor-frequency-apc-upsHighPrecOutputFrequency." . $index . ".rrd");
        if (is_file($old_rrd)) { rename($old_rrd,$new_rrd); print_warning("Moved RRD"); }

        discover_sensor($valid['sensor'], 'frequency', $device, $oid, "upsHighPrecOutputFrequency.$index", 'apc', $descr, $scale, $value * $scale);
      }

      $oid   = ".1.3.6.1.4.1.318.1.1.1.4.3.3.$index";
      $descr = "Output Load";
      $value = $entry['upsHighPrecOutputLoad'];

      if ($value != '' && $value != -1)
      {
        $limits = array('limit_high' => 85, 'limit_high_warn' => 70);
        discover_sensor($valid['sensor'], 'capacity', $device, $oid, "upsHighPrecOutputLoad.$index", 'apc', $descr, $scale, $value * $scale, $limits);
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
        ## Rename code for older revisions
        $old_rrd  = $config['rrd_dir'] . "/".$device['hostname']."/" . safename("sensor-voltage-apc-3.2.1." . $index . ".rrd");
        $new_rrd  = $config['rrd_dir'] . "/".$device['hostname']."/" . safename("sensor-voltage-apc-upsAdvInputLineVoltage." . $index . ".rrd");
        if (is_file($old_rrd)) { rename($old_rrd,$new_rrd); print_warning("Moved RRD"); }

        discover_sensor($valid['sensor'], 'voltage', $device, $oid, "upsAdvInputLineVoltage.$index", 'apc', $descr, 1, $value);
      }

      $oid   = ".1.3.6.1.4.1.318.1.1.1.3.2.4.$index";
      $descr = "Input";
      $value = $entry['upsAdvInputFrequency'];

      if ($value != '' && $value != -1)
      {
        ## Rename code for older revisions
        $old_rrd  = $config['rrd_dir'] . "/".$device['hostname']."/" . safename("sensor-frequency-apc-3.2.4." . $index . ".rrd");
        $new_rrd  = $config['rrd_dir'] . "/".$device['hostname']."/" . safename("sensor-frequency-apc-upsAdvInputFrequency." . $index . ".rrd");
        if (is_file($old_rrd)) { rename($old_rrd,$new_rrd); print_warning("Moved RRD"); }

        discover_sensor($valid['sensor'], 'frequency', $device, $oid, "upsAdvInputFrequency.$index", 'apc', $descr, 1, $value);
      }

      $oid   = ".1.3.6.1.4.1.318.1.1.1.4.2.1.$index";
      $value = $entry['upsAdvOutputVoltage'];
      $descr = "Output";

      if ($value != '' && $value != -1)
      {
        ## Rename code for older revisions
        $old_rrd  = $config['rrd_dir'] . "/".$device['hostname']."/" . safename("sensor-voltage-apc-4.2.1." . $index . ".rrd");
        $new_rrd  = $config['rrd_dir'] . "/".$device['hostname']."/" . safename("sensor-voltage-apc-upsAdvOutputVoltage." . $index . ".rrd");
        if (is_file($old_rrd)) { rename($old_rrd,$new_rrd); print_warning("Moved RRD"); }

        discover_sensor($valid['sensor'], 'voltage', $device, $oid, "upsAdvOutputVoltage.$index", 'apc', $descr, 1, $value);
      }

      $oid   = ".1.3.6.1.4.1.318.1.1.1.4.2.4.$index";
      $descr = "Output";
      $value = $entry['upsAdvOutputCurrent'];

      if ($value != '' && $value != -1)
      {
        ## Rename code for older revisions
        $old_rrd  = $config['rrd_dir'] . "/".$device['hostname']."/" . safename("sensor-current-apc-4.2.4." . $index . ".rrd");
        $new_rrd  = $config['rrd_dir'] . "/".$device['hostname']."/" . safename("sensor-current-apc-upsAdvOutputCurrent." . $index . ".rrd");
        if (is_file($old_rrd)) { rename($old_rrd,$new_rrd); print_warning("Moved RRD"); }

        discover_sensor($valid['sensor'], 'current', $device, $oid, "upsAdvOutputCurrent.$index", 'apc', $descr, 1, $value);
      }

      $oid   = ".1.3.6.1.4.1.318.1.1.1.4.2.2.$index";
      $descr = "Output";
      $value = $entry['upsAdvOutputFrequency'];

      if ($value != '' && $value != -1)
      {
        ## Rename code for older revisions
        $old_rrd  = $config['rrd_dir'] . "/".$device['hostname']."/" . safename("sensor-frequency-apc-4.2.2." . $index . ".rrd");
        $new_rrd  = $config['rrd_dir'] . "/".$device['hostname']."/" . safename("sensor-frequency-apc-upsAdvOutputFrequency." . $index . ".rrd");
        if (is_file($old_rrd)) { rename($old_rrd,$new_rrd); print_warning("Moved RRD"); }

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
  }
}

// Try UPS battery tables: "HighPrec" table first, with fallback to "Adv".
$cache['apc'] = array();

foreach (array("upsHighPrecBattery", "upsAdvBattery") as $table)
{
  echo("$table ");
  $cache['apc'] = snmpwalk_cache_multi_oid($device, $table, $cache['apc'], "PowerNet-MIB", mib_dirs('apc'));
}

foreach ($cache['apc'] as $index => $entry)
{
  $descr = "Battery";

  if ($entry['upsHighPrecBatteryTemperature'] && $entry['upsHighPrecBatteryTemperature'] != -1)
  {
    $oid   = ".1.3.6.1.4.1.318.1.1.1.2.3.2.$index";
    $value = $entry['upsHighPrecBatteryTemperature'];

    discover_sensor($valid['sensor'], 'temperature', $device, $oid, "upsHighPrecBatteryTemperature.$index", 'apc', $descr, $scale, $value * $scale);
  } elseif ($entry['upsAdvBatteryTemperature'] && $entry['upsAdvBatteryTemperature'] != -1)
  {
    $oid   = ".1.3.6.1.4.1.318.1.1.1.2.2.2.$index";
    $value = $entry['upsAdvBatteryTemperature'];

    ## Rename code for older revisions
    $old_rrd  = $config['rrd_dir'] . "/".$device['hostname']."/" . safename("sensor-temperature-apc-" . $index . ".rrd");
    $new_rrd  = $config['rrd_dir'] . "/".$device['hostname']."/" . safename("sensor-temperature-apc-upsAdvBatteryTemperature." . $index . ".rrd");
    if (is_file($old_rrd)) { rename($old_rrd,$new_rrd); print_warning("Moved RRD"); }

    discover_sensor($valid['sensor'], 'temperature', $device, $oid, "upsAdvBatteryTemperature.$index", 'apc', $descr, 1, $value);
  }

  $descr = "Battery Nominal Voltage";

  if ($entry['upsHighPrecBatteryNominalVoltage'] && $entry['upsHighPrecBatteryNominalVoltage'] != -1)
  {
    $oid   = ".1.3.6.1.4.1.318.1.1.1.2.3.3.$index";
    $value = $entry['upsHighPrecBatteryNominalVoltage'];
    discover_sensor($valid['sensor'], 'voltage', $device, $oid, "upsHighPrecBatteryNominalVoltage.$index", 'apc', $descr, $scale, $value * $scale);
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
    discover_sensor($valid['sensor'], 'voltage', $device, $oid, "upsHighPrecBatteryActualVoltage.$index", 'apc', $descr, $scale, $value * $scale);
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

    discover_sensor($valid['sensor'], 'current', $device, $oid, "upsHighPrecBatteryCurrent.$index", 'apc', $descr, $scale, $value * $scale);
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

    discover_sensor($valid['sensor'], 'current', $device, $oid, "upsHighPrecTotalDCCurrent.$index", 'apc', $descr, $scale, $value * $scale);
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
    discover_sensor($valid['sensor'], 'capacity', $device, $oid, "upsHighPrecBatteryCapacity.$index", 'apc', $descr, $scale, $value * $scale, $limits);
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

    discover_sensor($valid['sensor'], 'runtime', $device, $oid, "upsAdvBatteryRunTimeRemaining.$index", 'apc', $descr, $scale_min, $value * $scale_min, $limits);
  }
}

// State sensors

// PowerNet-MIB::upsBasicOutputStatus.0 = INTEGER: onLine(2)

$value = snmp_get($device, "upsBasicOutputStatus.0", "-Oqv", "PowerNet-MIB", mib_dirs('apc'));

if ($value != '')
{
  $oid = ".1.3.6.1.4.1.318.1.1.1.4.1.1.0";
  $descr = "Output Status";
  $value = state_string_to_numeric('powernet-upsbasicoutput-state', $value);

  discover_sensor($valid['sensor'], 'state', $device, $oid, "upsBasicOutputStatus.0", 'powernet-upsbasicoutput-state', $descr, NULL, $value, array('entPhysicalClass' => 'power'));
}

#### ATS #############################################################################################

$inputs = snmp_get($device, "atsNumInputs.0", "-Ovq", "PowerNet-MIB", mib_dirs('apc'));
$outputs = snmp_get($device, "atsNumOutputs.0", "-Ovq", "PowerNet-MIB", mib_dirs('apc'));

// Check if we have values for these, if not, try other code paths below.
if ($inputs || $outputs)
{
  echo(" ");
  $cache['apc'] = array();

  foreach (array("atsInputTable", "atsOutputTable", "atsInputPhaseTable", "atsOutputPhaseTable") as $table)
  {
    echo("$table ");
    $cache['apc'] = snmpwalk_cache_threepart_oid($device, $table, $cache['apc'], "PowerNet-MIB", mib_dirs('apc'), TRUE);
  }
  foreach (array("atsInputTable", "atsOutputTable") as $table)
  {
    echo("$table ");
    $cache['apc'] = snmpwalk_cache_multi_oid($device, $table, $cache['apc'], "PowerNet-MIB", mib_dirs('apc'), TRUE);
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

      ## Rename code for older revisions
      $old_rrd  = $config['rrd_dir'] . "/".$device['hostname']."/" . safename("sensor-voltage-apc-3.3.1.3." . $index . ".rrd");
      $new_rrd  = $config['rrd_dir'] . "/".$device['hostname']."/" . safename("sensor-voltage-apc-atsInputVoltage." . $index . ".1.1.rrd");
      if (is_file($old_rrd)) { rename($old_rrd,$new_rrd); print_warning("Moved RRD"); }
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
      discover_sensor($valid['sensor'], 'current', $device, $oid, "atsInputCurrent.$index.1.1", 'apc', $descr, $scale, $value * $scale, $limits);
    }

    $oid   = ".1.3.6.1.4.1.318.1.1.8.5.3.2.1.4.$index";
    $value = $entry['atsInputFrequency'];

    if ($value != '' && $value != -1)
    {
      discover_sensor($valid['sensor'], 'frequency', $device, $oid, "atsInputFrequency.$index", 'apc', $descr, 1, $value);

      ## Rename code for older revisions
      $old_rrd  = $config['rrd_dir'] . "/".$device['hostname']."/" . safename("sensor-frequency-apc-3.2.1.4." . $index . ".rrd");
      $new_rrd  = $config['rrd_dir'] . "/".$device['hostname']."/" . safename("sensor-frequency-apc-atsInputFrequency." . $index . ".rrd");
      if (is_file($old_rrd)) { rename($old_rrd,$new_rrd); print_warning("Moved RRD"); }
    }

    $descr = "Output"; // No check for multiple output feeds, currently - I don't think this exists.

    $oid   = ".1.3.6.1.4.1.318.1.1.8.5.4.3.1.3.$index.1.1";
    $value = $entry[1][1]['atsOutputVoltage'];

    if ($value != '' && $value != -1)
    {
      discover_sensor($valid['sensor'], 'voltage', $device, $oid, "atsOutputVoltage.$index.1.1", 'apc', $descr, 1, $value);

      ## Rename code for older revisions
      $old_rrd  = $config['rrd_dir'] . "/".$device['hostname']."/" . safename("sensor-voltage-apc-4.3.1.3." . $index . ".rrd");
      $new_rrd  = $config['rrd_dir'] . "/".$device['hostname']."/" . safename("sensor-voltage-apc-atsOutputVoltage." . $index . ".1.1.rrd");
      if (is_file($old_rrd)) { rename($old_rrd,$new_rrd); print_warning("Moved RRD"); }
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

      discover_sensor($valid['sensor'], 'current', $device, $oid, "atsOutputCurrent.$index.1.1", 'apc', $descr, $scale, $value * $scale, $limits);

      ## Rename code for older revisions
      $old_rrd  = $config['rrd_dir'] . "/".$device['hostname']."/" . safename("sensor-current-apc-" . $index . ".rrd");
      $new_rrd  = $config['rrd_dir'] . "/".$device['hostname']."/" . safename("sensor-current-apc-atsOutputCurrent." . $index . ".1.1.rrd");
      if (is_file($old_rrd)) { rename($old_rrd,$new_rrd); print_warning("Moved RRD"); }
    }

    $oid   = ".1.3.6.1.4.1.318.1.1.8.5.4.2.1.4.$index";
    $value = $entry['atsOutputFrequency'];

    if ($value != '' && $value != -1)
    {
      discover_sensor($valid['sensor'], 'frequency', $device, $oid, "atsOutputFrequency.$index", 'apc', $descr, 1, $value);

      ## Rename code for older revisions
      $old_rrd  = $config['rrd_dir'] . "/".$device['hostname']."/" . safename("sensor-current-apc-4.2.1.4." . $index . ".rrd");
      $new_rrd  = $config['rrd_dir'] . "/".$device['hostname']."/" . safename("sensor-current-apc-atsOutputFrequency." . $index . ".rrd");
      if (is_file($old_rrd)) { rename($old_rrd,$new_rrd); print_warning("Moved RRD"); }
    }
  }

  // State sensors
  $cache['apc'] = array();

  foreach (array("atsStatusDeviceStatus") as $table)
  {
    echo("$table ");
    $cache['apc'] = snmpwalk_cache_multi_oid($device, $table, $cache['apc'], "PowerNet-MIB", mib_dirs('apc'), TRUE);
  }

  foreach ($cache['apc'] as $index => $entry)
  {
    $descr = "Switch Status";
    $oid   = ".1.3.6.1.4.1.318.1.1.8.5.1.10.$index";
    $value = state_string_to_numeric('powernet-status-state',$entry['atsStatusSwitchStatus']);
    discover_sensor($valid['sensor'], 'state', $device, $oid, "atsStatusSwitchStatus.$index", 'powernet-status-state', $descr, NULL, $value, array('entPhysicalClass' => 'other'));

    $descr = "Source A";
    $oid   = ".1.3.6.1.4.1.318.1.1.8.5.1.12.$index";
    $value = state_string_to_numeric('powernet-status-state',$entry['atsStatusSourceAStatus']);
    discover_sensor($valid['sensor'], 'state', $device, $oid, "atsStatusSourceAStatus.$index", 'powernet-status-state', $descr, NULL, $value, array('entPhysicalClass' => 'power'));

    $descr = "Source B";
    $oid   = ".1.3.6.1.4.1.318.1.1.8.5.1.13.$index";
    $value = state_string_to_numeric('powernet-status-state',$entry['atsStatusSourceBStatus']);
    discover_sensor($valid['sensor'], 'state', $device, $oid, "atsStatusSourceBStatus.$index", 'powernet-status-state', $descr, NULL, $value, array('entPhysicalClass' => 'power'));

    $descr = "Phase Sync";
    $oid   = ".1.3.6.1.4.1.318.1.1.8.5.1.14.$index";
    $value = state_string_to_numeric('powernet-sync-state',$entry['atsStatusPhaseSyncStatus']);
    discover_sensor($valid['sensor'], 'state', $device, $oid, "atsStatusPhaseSyncStatus.$index", 'powernet-sync-state', $descr, NULL, $value, array('entPhysicalClass' => 'power'));

    $descr = "Hardware";
    $oid   = ".1.3.6.1.4.1.318.1.1.8.5.1.16.$index";
    $value = state_string_to_numeric('powernet-status-state',$entry['atsStatusHardwareStatus']);
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
  echo(" ");

  # v2 firmware: first bank is total
  # v3 firmware: last bank is total
  # v5 firmware: looks like first bank is total
  $baseversion = 2; /// FIXME. Use preg_match
  if (stristr($device['version'], 'v3') == TRUE) { $baseversion = 3; }
  elseif (stristr($device['version'], 'v4') == TRUE) { $baseversion = 4; }
  elseif (stristr($device['version'], 'v5') == TRUE) { $baseversion = 5; }
  elseif (stristr($device['version'], 'v6') == TRUE) { $baseversion = 6; }

  // PowerNet-MIB::rPDUPowerSupply1Status.0 = powerSupplyOneOk
  // PowerNet-MIB::rPDUPowerSupply2Status.0 = powerSupplyTwoOk
  // PowerNet-MIB::rPDU2DeviceStatusPowerSupply1Status.1 = normal
  // PowerNet-MIB::rPDU2DeviceStatusPowerSupply2Status.1 = notInstalled
  $cache['apc'] = snmp_get_multi($device, 'rPDUPowerSupply1Status.0 rPDUPowerSupply2Status.0 rPDU2DeviceStatusPowerSupply1Status.1 rPDU2DeviceStatusPowerSupply2Status.1', '-OQUs', 'PowerNet-MIB', mib_dirs('apc'));

  if (isset($cache['apc'][1]))
  {
    $index = 1;
    foreach ($cache['apc'][1] as $key => $value)
    {
      $unit  = ('rPDU2DeviceStatusPowerSupply1Status' == $key ? 1 : 2);
      $type  = 'powernet-rpdu2supply-state';
      $descr = 'Power Supply '.$unit;
      $oid   = '.1.3.6.1.4.1.318.1.1.26.4.3.1.'.(12+$unit).'.'.$index;
      $value = state_string_to_numeric($type, $value);
      if ($value != 3)
      {
        discover_sensor($valid['sensor'], 'state', $device, $oid, "$key.$index", $type, $descr, NULL, $value, array('entPhysicalClass' => 'power'));
      }
    }
  }
  else if (isset($cache['apc'][0]))
  {
    $index = 0;
    foreach ($cache['apc'][0] as $key => $value)
    {
      $unit  = ('rPDUPowerSupply1Status' == $key ? 1 : 2);
      $type  = 'powernet-rpdusupply'.$unit.'-state';
      $descr = 'Power Supply '.$unit;
      $oid   = ".1.3.6.1.4.1.318.1.1.12.4.1.$unit.$index";
      $value = state_string_to_numeric($type, $value);
      if ($value != 3)
      {
        discover_sensor($valid['sensor'], 'state', $device, $oid, "$key.$index", $type, $descr, NULL, $value, array('entPhysicalClass' => 'power'));
      }
    }
  }

  // PowerNet-MIB::rPDUIdentDeviceLinetoLineVoltage.0 = INTEGER: 400
  // PowerNet-MIB::rPDUIdentDevicePowerWatts.0 = INTEGER: 807
  // PowerNet-MIB::rPDUIdentDevicePowerFactor.0 = INTEGER: 1000 - currently not used (1000=1)
  // PowerNet-MIB::rPDUIdentDevicePowerVA.0 = INTEGER: 807 - no VA sensor type yet
  $cache['apc'] = array();
  foreach (array("rPDUIdent", "rPDU2Ident") as $table)
  {
    echo("$table ");
    $cache['apc'] = snmpwalk_cache_multi_oid($device, $table, $cache['apc'], "PowerNet-MIB", mib_dirs('apc'), TRUE);
  }

  if (count($cache['apc']) == 1)
  { // Skip this section if rPDU2Ident table is present (it has index 1, so count() will be 2)
    // All data reported in rPDUIdent is duplicated in the rPDU2 tables we poll below.
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

        ## Rename code for older revisions
        $old_rrd  = $config['rrd_dir'] . "/".$device['hostname']."/" . safename("sensor-voltage-apc-" . ($index+1) . ".rrd");
        $new_rrd  = $config['rrd_dir'] . "/".$device['hostname']."/" . safename("sensor-voltage-apc-rPDUIdentDeviceLinetoLineVoltage." . $index . ".rrd");
        if (is_file($old_rrd)) { rename($old_rrd,$new_rrd); print_warning("Moved RRD"); }
      }

      $oid   = ".1.3.6.1.4.1.318.1.1.12.1.16.$index";
      $value = $entry['rPDUIdentDevicePowerWatts'];

      if ($value != '' && $value != -1)
      {
        discover_sensor($valid['sensor'], 'power', $device, $oid, "rPDUIdentDevicePowerWatts.$index", 'apc', $descr, 1, $value);
      }
    }
  }

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
  $cache['apc'] = array();
  foreach (array("rPDU2PhaseStatusTable", "rPDU2PhaseConfigTable") as $table)
  {
    echo("$table ");
    $cache['apc'] = snmpwalk_cache_multi_oid($device, $table, $cache['apc'], "PowerNet-MIB", mib_dirs('apc'), TRUE);
  }

  if (count($cache['apc']))
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
          discover_sensor($valid['sensor'], 'current', $device, $oid, "rPDU2BankStatusCurrent.$index", 'apc', $descr, $scale, $value * $scale);
        }
      }
    }

    foreach ($cache['apc'] as $index => $entry)
    {
      $oid     = ".1.3.6.1.4.1.318.1.1.26.6.3.1.5.$index";
      $value   = $entry['rPDU2PhaseStatusCurrent'];
      $limits  = array('limit_high'      => $entry['rPDU2PhaseConfigOverloadCurrentThreshold'],
                       'limit_low'       => $entry['rPDU2PhaseConfigLowLoadCurrentThreshold'],
                       'limit_high_warn' => $entry['rPDU2PhaseConfigNearOverloadCurrentThreshold']);
      $phase   = $entry['rPDU2PhaseStatusNumber'];

      if ($loadDev[0]['rPDULoadDevNumPhases'] != 1)
      {
        $descr = "Phase $phase";
      } else {
        $descr = "Output";
      }

      if ($value != '' && $value != -1)
      {
        discover_sensor($valid['sensor'], 'current', $device, $oid, "rPDU2PhaseStatusCurrent.$index", 'apc', $descr, $scale, $value * $scale, $limits);
      }

      ## Rename code for older revisions
      $old_rrd  = $config['rrd_dir'] . "/".$device['hostname']."/" . safename("sensor-current-apc-" . $index . ".rrd");
      $new_rrd  = $config['rrd_dir'] . "/".$device['hostname']."/" . safename("sensor-current-apc-rPDU2PhaseStatusCurrent." . $index . ".rrd");
      if (is_file($old_rrd)) { rename($old_rrd,$new_rrd); print_warning("Moved RRD"); }

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
        discover_sensor($valid['sensor'], 'power', $device, $oid, "rPDU2PhaseStatusPower.$index", 'apc', $descr, 10, $value * 10);
      }

      // PowerNet-MIB::rPDU2PhaseStatusLoadState.1 = INTEGER: normal(2)
    }
  }
  else
  {
    // $baseversion == 3
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
      $cache['apc'] = snmpwalk_cache_multi_oid($device, $table, $cache['apc'], "PowerNet-MIB", mib_dirs('apc'), TRUE);
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
        discover_sensor($valid['sensor'], 'current', $device, $oid, "rPDULoadStatusLoad.$index", 'apc', $descr, $scale, $value * $scale, $limits);
      }

      ## Rename code for older revisions
      $old_rrd  = $config['rrd_dir'] . "/".$device['hostname']."/" . safename("sensor-current-apc-" . $index . ".rrd");
      $new_rrd  = $config['rrd_dir'] . "/".$device['hostname']."/" . safename("sensor-current-apc-rPDULoadStatusLoad." . $index . ".rrd");
      if (is_file($old_rrd)) { rename($old_rrd,$new_rrd); print_warning("Moved RRD"); }

      // [rPDUStatusPhaseState] => phaseLoadNormal
      // [rPDULoadStatusLoadState] => phaseLoadNormal
      // [rPDULoadPhaseConfigAlarm] => noLoadAlarm
    }
  }

  unset($baseversion, $banks);

  // PowerNet-MIB::rPDUPowerSupply1Status.0 = INTEGER: powerSupplyOneOk(1)
  // PowerNet-MIB::rPDUPowerSupply2Status.0 = INTEGER: powerSupplyTwoNotPresent(3)
  // PowerNet-MIB::rPDUPowerSupplyAlarm.0 = INTEGER: allAvailablePowerSuppliesOK(1)

  // FIXME METERED PDU CODE BELOW IS COMPLETELY UNTESTED
  $cache['apc'] = array();

  foreach (array("rPDU2OutletMeteredStatusTable") as $table)
  {
    echo("$table ");
    $cache['apc'] = snmpwalk_cache_multi_oid($device, $table, $cache['apc'], "PowerNet-MIB", mib_dirs('apc'), TRUE);
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
      discover_sensor($valid['sensor'], 'current', $device, $oid, "rPDU2OutletMeteredStatusCurrent.$index", 'apc', $descr, $scale, $value * $scale, $limits);
    }

    ## Rename code for older revisions
    $old_rrd  = $config['rrd_dir'] . "/".$device['hostname']."/" . safename("sensor-current-apc-" . $index . ".rrd");
    $new_rrd  = $config['rrd_dir'] . "/".$device['hostname']."/" . safename("sensor-current-apc-rPDU2OutletMeteredStatusCurrent." . $index . ".rrd");
    if (is_file($old_rrd)) { rename($old_rrd,$new_rrd); print_warning("Moved RRD"); }

    $oid       = ".1.3.6.1.4.1.318.1.1.26.9.4.3.1.7.$index";
    $value     = $entry['rPDU2OutletMeteredStatusPower'];

    if ($value != '' && $value != -1)
    {
      discover_sensor($valid['sensor'], 'power', $device, $oid, "rPDU2OutletMeteredStatusPower.$index", 'apc', $descr, 1, $value);  // FIXME *10 ?
    }

    // FIXME: rPDU2OutletMeteredStatusEnergy - "A user resettable energy meter measuring Rack PDU load energy consumption in tenths of kilowatt-hours"
  }
}

#### MODULAR DISTRIBUTION SYSTEM #####################################################################

// FIXME This section needs a rewrite, but I can't find a device -TL

echo(" ");

$oids = snmp_walk($device, "isxModularDistSysVoltageLtoN", "-OsqnU", "PowerNet-MIB", mib_dirs('apc'));
if ($oids)
{
  echo(" Voltage In ");
  foreach (explode("\n", $oids) as $data)
  {
    list($oid,$value) = explode(" ",$data);
    $split_oid = explode('.',$oid);
    $phase = $split_oid[count($split_oid)-1];
    $index = "LtoN:".$phase;
    $descr = "Phase $phase Line to Neutral";

    discover_sensor($valid['sensor'], 'voltage', $device, $oid, $index, 'apc', $descr, $scale, $value * $scale);
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
      list($oid,$value) = explode(" ", $data);
      $split_oid = explode('.',$oid);
      $phase = $split_oid[count($split_oid)-1];
      $breaker = $split_oid[count($split_oid)-2];
      $index = str_pad($breaker, 2, "0", STR_PAD_LEFT) . "-" . $phase;
      $descr = "Breaker $breaker Phase $phase";
      discover_sensor($valid['sensor'], 'current', $device, $oid, $index, 'apc', $descr, $scale, $value * $scale);
    }
  }

  $oids = snmp_walk($device, "isxModularDistSysCurrentAmps", "-OsqnU", "PowerNet-MIB", mib_dirs('apc'));
  foreach (explode("\n", $oids) as $data)
  {
    $data = trim($data);
    if ($data)
    {
      list($oid,$value) = explode(" ", $data);
      $split_oid = explode('.',$oid);
      $phase = $split_oid[count($split_oid)-1];
      $index = ".$phase";
      $descr = "Phase $phase overall";
      discover_sensor($valid['sensor'], 'current', $device, $oid, $index, 'apc', $descr, $scale, $value * $scale);
    }
  }
}

#### ENVIRONMENTAL ###################################################################################

echo(" ");

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
  $value           = $entry['emsProbeStatusProbeTemperature'];
  $oid             = ".1.3.6.1.4.1.318.1.1.10.3.13.1.1.3.$index";
  $limits  = array('limit_high'      => $entry['emsProbeStatusProbeMaxTempThresh'],
                   'limit_low'       => $entry['emsProbeStatusProbeMinTempThresh'],
                   'limit_high_warn' => $entry['emsProbeStatusProbeHighTempThresh'],
                   'limit_low_warn'  => $entry['emsProbeStatusProbeLowTempThresh']);

  if ($value != '' && $value != -1) // Temperature = -1 -> Sensor not available
  {
    $scale_temp = 1;
    if ($temp_units == 'fahrenheit')
    {
      $scale_temp = 5/9;
      $value = f2c($value);
      foreach (array('limit_low', 'limit_low_warn', 'limit_high_warn', 'limit_high') as $param)
      {
        $limits[$param] = f2c($limits[$param]); // Convert from fahrenheit to celsius
      }
      print_debug('TEMP sensor: Fahrenheit -> Celsius');
    }

    discover_sensor($valid['sensor'], 'temperature', $device, $oid, "emsProbeStatusProbeTemperature.$index", 'apc', $descr, $scale_temp, $value, $limits);
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

    ## Rename code for older revisions
    $old_rrd  = $config['rrd_dir'] . "/".$device['hostname']."/" . safename("sensor-humidity-apc-" . $index . ".rrd");
    $new_rrd  = $config['rrd_dir'] . "/".$device['hostname']."/" . safename("sensor-humidity-apc-iemStatusProbeCurrentHumid." . $index . ".rrd");
    if (is_file($old_rrd)) { rename($old_rrd,$new_rrd); print_warning("Moved RRD"); }
  }

  // Temperature
  $value           = $entry['iemStatusProbeCurrentTemp'];
  $oid             = ".1.3.6.1.4.1.318.1.1.10.2.3.2.1.4.$index";
  $limits          = array('limit_high'      => $entry['iemConfigProbeMaxTempThreshold'],
                           'limit_low'       => $entry['iemConfigProbeMinTempThreshold'],
                           'limit_high_warn' => $entry['iemConfigProbeHighTempThreshold'],
                           'limit_low_warn'  => $entry['iemConfigProbeLowTempThreshold']);

  if ($value != '' && $value != -1) // Temperature = -1 -> Sensor not available
  {
    $scale_temp = 1;
    if ($temp_units == 'fahrenheit')
    {
      $scale_temp = 5/9;
      $value = f2c($value);
      foreach (array('limit_low', 'limit_low_warn', 'limit_high_warn', 'limit_high') as $param)
      {
        $limits[$param] = f2c($limits[$param]); // Convert from fahrenheit to celsius
      }
      print_debug('TEMP sensor: Fahrenheit -> Celsius');
    }

    discover_sensor($valid['sensor'], 'temperature', $device, $oid, "iemStatusProbeCurrentTemp.$index", 'apc', $descr, $scale_temp, $value, $limits);
    $iem_sensors['temperature'][] = $descr; // Store for later use in uio code below

    ## Rename code for older revisions
    $old_rrd  = $config['rrd_dir'] . "/".$device['hostname']."/" . safename("sensor-temperature-apc-" . $index . ".rrd");
    $new_rrd  = $config['rrd_dir'] . "/".$device['hostname']."/" . safename("sensor-temperature-apc-iemStatusProbeCurrentTemp." . $index . ".rrd");
    if (is_file($old_rrd)) { rename($old_rrd,$new_rrd); print_warning("Moved RRD"); }
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

echo(" ");

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

    ## Rename code for older revisions
    $old_rrd  = $config['rrd_dir'] . "/".$device['hostname']."/" . safename("sensor-humidity-apc-" . $index . ".rrd");
    $new_rrd  = $config['rrd_dir'] . "/".$device['hostname']."/" . safename("sensor-humidity-apc-rPDU2SensorTempHumidityStatusRelativeHumidity." . $index . ".rrd");
    if (is_file($old_rrd)) { rename($old_rrd,$new_rrd); print_warning("Moved RRD"); }
  }

  // Temperature
  $value           = $entry['rPDU2SensorTempHumidityStatusTempC'];
  $oid             = ".1.3.6.1.4.1.318.1.1.26.10.2.2.1.8.$index";
  $limits = array('limit_high'      => $entry['rPDU2SensorTempHumidityConfigTempMaxThreshC'],
                  'limit_high_warn' => $entry['rPDU2SensorTempHumidityConfigTempHighThreshC']);

  if ($value != '' && $value != -1)
  {
    discover_sensor($valid['sensor'], 'temperature', $device, $oid, "rPDU2SensorTempHumidityStatusTempC.$index", 'apc', $descr, $scale, $value * $scale, $limits);

    ## Rename code for older revisions
    $old_rrd  = $config['rrd_dir'] . "/".$device['hostname']."/" . safename("sensor-temperature-apc-" . $index . ".rrd");
    $new_rrd  = $config['rrd_dir'] . "/".$device['hostname']."/" . safename("sensor-temperature-apc-rPDU2SensorTempHumidityStatusTempC." . $index . ".rrd");
    if (is_file($old_rrd)) { rename($old_rrd,$new_rrd); print_warning("Moved RRD"); }
  }
}

#### NETBOTZ #########################################################################################

echo(" ");

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
      $scale_temp = 5/9;
      $value = f2c($value); // Convert from fahrenheit to celsius
      print_debug('TEMP sensor: Fahrenheit -> Celsius');
    }

    ## Rename code for older revisions
    $old_rrd  = $config['rrd_dir'] . "/".$device['hostname']."/" . safename("sensor-temperature-apc-$index.rrd");
    $new_rrd  = $config['rrd_dir'] . "/".$device['hostname']."/" . safename("sensor-temperature-apc-memSensorsTemperature.$index.rrd");
    if (is_file($old_rrd)) { rename($old_rrd,$new_rrd); print_warning("Moved RRD"); }

    discover_sensor($valid['sensor'], 'temperature', $device, $oid, "memSensorsTemperature.$index", 'apc', $descr, $scale_temp, $value);
  }

  $oid   = ".1.3.6.1.4.1.318.1.1.10.4.2.3.1.6.$index";
  $value = $entry['memSensorsHumidity'];

  // Exclude already added sensor from emsProbeStatusTable
  if ($value != '' && $value > 0 && !isset($valid['sensor']['humidity']['apc']["emsProbeStatusProbeHumidity.$ems_index"]))
  {
    ## Rename code for older revisions
    $old_rrd  = $config['rrd_dir'] . "/".$device['hostname']."/" . safename("sensor-humidity-apc-$index.rrd");
    $new_rrd  = $config['rrd_dir'] . "/".$device['hostname']."/" . safename("sensor-humidity-apc-memSensorsHumidity.$index.rrd");
    if (is_file($old_rrd)) { rename($old_rrd,$new_rrd); print_warning("Moved RRD"); }

    discover_sensor($valid['sensor'], 'humidity', $device, $oid, "memSensorsHumidity.$index", 'apc', $descr, 1, $value);
  }
}

#### INROW CHILLER ###################################################################################

echo(" ");

$cache['apc'] = array();

foreach (array("airIRRCGroupStatus", "airIRRCGroupSetpoints") as $table)
{
  echo("$table ");
  $cache['apc'] = snmpwalk_cache_multi_oid($device, $table, $cache['apc'], "PowerNet-MIB", mib_dirs('apc'));
}

foreach ($cache['apc'] as $index => $entry)
{
  if (count($cache['apc']) != 1) { $unitDescr = "Unit " . ($index+1) . " "; } else { $unitDescr = ""; }

  $descr = "Group Min Rack Inlet Temperature";
  $oid   = ".1.3.6.1.4.1.318.1.1.13.3.2.1.1.8.$index";
  $value = $entry['airIRRCGroupStatusMinRackInletTempMetric'];

  discover_sensor($valid['sensor'], 'temperature', $device, $oid, "airIRRCGroupStatusMinRackInletTempMetric.$index", 'apc', $descr, 0.1, $value / 10);

  $descr = "Group Max Rack Inlet Temperature";
  $oid   = ".1.3.6.1.4.1.318.1.1.13.3.2.1.1.6.$index";
  $value = $entry['airIRRCGroupStatusMaxRackInletTempMetric'];

  discover_sensor($valid['sensor'], 'temperature', $device, $oid, "airIRRCGroupStatusMaxRackInletTempMetric.$index", 'apc', $descr, 0.1, $value / 10);

  $descr = "Group Cooling Setpoint";
  $oid   = ".1.3.6.1.4.1.318.1.1.13.3.2.1.2.2.$index";
  $value = $entry['airIRRCGroupSetpointsCoolMetric'];

  discover_sensor($valid['sensor'], 'temperature', $device, $oid, "airIRRCGroupSetpointsCoolMetric.$index", 'apc', $descr, 0.1, $value / 10);

  $descr = "Group Supply Setpoint";
  $oid   = ".1.3.6.1.4.1.318.1.1.13.3.2.1.2.4.$index";
  $value = $entry['airIRRCGroupSetpointsSupplyAirMetric'];

  discover_sensor($valid['sensor'], 'temperature', $device, $oid, "airIRRCGroupSetpointsSupplyAirMetric.$index", 'apc', $descr, 0.1, $value / 10);

  $descr = "Group Air Flow";
  $oid   = ".1.3.6.1.4.1.318.1.1.13.3.2.1.1.3.$index";
  $value = $entry['airIRRCGroupStatusAirFlowUS'];

  discover_sensor($valid['sensor'], 'airflow', $device, $oid, "airIRRCGroupStatusAirFlowUS.$index", 'apc', $descr, 1, $value);

  $descr = "Group Cooling Output";
  $oid   = ".1.3.6.1.4.1.318.1.1.13.3.2.1.1.1.$index";
  $value = $entry['airIRRCGroupStatusCoolOutput'];

  discover_sensor($valid['sensor'], 'power', $device, $oid, "airIRRCGroupStatusCoolOutput.$index", 'apc', $descr, 100, $value * 100);

  $descr = "Group Cooling Demand";
  $oid   = ".1.3.6.1.4.1.318.1.1.13.3.2.1.1.2.$index";
  $value = $entry['airIRRCGroupStatusCoolDemand'];

  discover_sensor($valid['sensor'], 'power', $device, $oid, "airIRRCGroupStatusCoolDemand.$index", 'apc', $descr, 100, $value * 100);
}

echo(" ");

$cache['apc'] = array();

foreach (array("airIRRCUnitStatus") as $table)
{
  echo("$table ");
  $cache['apc'] = snmpwalk_cache_multi_oid($device, $table, $cache['apc'], "PowerNet-MIB", mib_dirs('apc'));
}

foreach ($cache['apc'] as $index => $entry)
{
  if (count($cache['apc']) != 1) { $unitDescr = "Unit " . ($index+1) . " "; } else { $unitDescr = ""; }

  $descr = $unitDescr . "Rack Inlet";
  $oid   = ".1.3.6.1.4.1.318.1.1.13.3.2.2.2.7.$index";
  $value = $entry['airIRRCUnitStatusRackInletTempMetric'];
  $limits = array('limit_high' => $entry['airIRRCUnitThresholdsRackInletHighTempMetric'] / 10);

  discover_sensor($valid['sensor'], 'temperature', $device, $oid, "airIRRCUnitStatusRackInletTempMetric.$index", 'apc', $descr, 0.1, $value / 10, $limits);

  ## Rename code for older revisions
  $old_rrd = $config['rrd_dir'] . "/".$device['hostname']."/" . safename("sensor-temperature-Ra-" . $index . ".rrd");
  $new_rrd = $config['rrd_dir'] . "/".$device['hostname']."/" . safename("sensor-temperature-apc-airIRRCUnitStatusRackInletTempMetric." . $index . ".rrd");
  if (is_file($old_rrd)) { rename($old_rrd,$new_rrd); echo("Moved RRD "); }

  $descr = $unitDescr . "Supply Air";
  $oid   = ".1.3.6.1.4.1.318.1.1.13.3.2.2.2.9.$index";
  $value = $entry['airIRRCUnitStatusSupplyAirTempMetric'];
  $limits = array('limit_high' => $entry['airIRRCUnitThresholdsSupplyAirHighTempMetric'] / 10);

  discover_sensor($valid['sensor'], 'temperature', $device, $oid, "airIRRCUnitStatusSupplyAirTempMetric.$index", 'apc', $descr, 0.1, $value / 10, $limits);

  ## Rename code for older revisions
  $old_rrd = $config['rrd_dir'] . "/".$device['hostname']."/" . safename("sensor-temperature-Su-" . $index . ".rrd");
  $new_rrd = $config['rrd_dir'] . "/".$device['hostname']."/" . safename("sensor-temperature-apc-airIRRCUnitStatusSupplyAirTempMetric." . $index . ".rrd");
  if (is_file($old_rrd)) { rename($old_rrd,$new_rrd); echo("Moved RRD "); }

  $descr = $unitDescr . "Return Air";
  $oid   = ".1.3.6.1.4.1.318.1.1.13.3.2.2.2.11.$index";
  $value = $entry['airIRRCUnitStatusReturnAirTempMetric'];
  $limits = array('limit_high' => $entry['airIRRCUnitThresholdsReturnAirHighTempMetric'] / 10);

  discover_sensor($valid['sensor'], 'temperature', $device, $oid, "airIRRCUnitStatusReturnAirTempMetric.$index", 'apc', $descr, 0.1, $value / 10, $limits);

  ## Rename code for older revisions
  $old_rrd = $config['rrd_dir'] . "/".$device['hostname']."/" . safename("sensor-temperature-Re-" . $index . ".rrd");
  $new_rrd = $config['rrd_dir'] . "/".$device['hostname']."/" . safename("sensor-temperature-apc-airIRRCUnitStatusReturnAirTempMetric." . $index . ".rrd");
  if (is_file($old_rrd)) { rename($old_rrd,$new_rrd); echo("Moved RRD "); }

  $descr = $unitDescr . "Entering Fluid";
  $oid   = ".1.3.6.1.4.1.318.1.1.13.3.2.2.2.24.$index";
  $value = $entry['airIRRCUnitStatusEnteringFluidTemperatureMetric'];
  $limit = $entry['airIRRCUnitThresholdsEnteringFluidHighTempMetric'];
  $limits = array('limit_high' => $entry['airIRRCUnitThresholdsEnteringFluidHighTempMetric'] / 10);

  discover_sensor($valid['sensor'], 'temperature', $device, $oid, "airIRRCUnitStatusEnteringFluidTemperatureMetric.$index", 'apc', $descr, 0.1, $value / 10, $limits);

  ## Rename code for older revisions
  $old_rrd = $config['rrd_dir'] . "/".$device['hostname']."/" . safename("sensor-temperature-En-" . $index . ".rrd");
  $new_rrd = $config['rrd_dir'] . "/".$device['hostname']."/" . safename("sensor-temperature-apc-airIRRCUnitStatusEnteringFluidTemperatureMetric." . $index . ".rrd");
  if (is_file($old_rrd)) { rename($old_rrd,$new_rrd); echo("Moved RRD "); }

  $descr = $unitDescr . "Leaving Fluid";
  $oid   = ".1.3.6.1.4.1.318.1.1.13.3.2.2.2.26.$index";
  $value = $entry['airIRRCUnitStatusLeavingFluidTemperatureMetric'];

  discover_sensor($valid['sensor'], 'temperature', $device, $oid, "airIRRCUnitStatusLeavingFluidTemperatureMetric.$index", 'apc', $descr, 0.1, $value / 10);

  ## Rename code for older revisions
  $old_rrd = $config['rrd_dir'] . "/".$device['hostname']."/" . safename("sensor-temperature-Le-" . $index . ".rrd");
  $new_rrd = $config['rrd_dir'] . "/".$device['hostname']."/" . safename("sensor-temperature-apc-airIRRCUnitStatusLeavingFluidTemperatureMetric." . $index . ".rrd");
  if (is_file($old_rrd)) { rename($old_rrd,$new_rrd); echo("Moved RRD "); }

  $descr = $unitDescr . "Air Flow";
  $oid   = ".1.3.6.1.4.1.318.1.1.13.3.2.2.2.4.$index";
  $value = $entry['airIRRCUnitStatusAirFlowUS'];

  discover_sensor($valid['sensor'], 'airflow', $device, $oid, "airIRRCUnitStatusAirFlowUS.$index", 'apc', $descr, 1, $value);

  $descr = $unitDescr . "Cooling Output";
  $oid   = ".1.3.6.1.4.1.318.1.1.13.3.2.2.2.2.$index";
  $value = $entry['airIRRCUnitStatusCoolOutput'];

  discover_sensor($valid['sensor'], 'power', $device, $oid, "airIRRCUnitStatusCoolOutput.$index", 'apc', $descr, 100, $value * 100);

  $descr = $unitDescr . "Cooling Demand";
  $oid   = ".1.3.6.1.4.1.318.1.1.13.3.2.2.2.3.$index";
  $value = $entry['airIRRCUnitStatusCoolDemand'];

  discover_sensor($valid['sensor'], 'power', $device, $oid, "airIRRCUnitStatusCoolDemand.$index", 'apc', $descr, 100, $value * 100);

  $descr = $unitDescr . " Air Filter Pressure";
  $oid   = ".1.3.6.1.4.1.318.1.1.13.3.2.2.2.15.$index";
  $value = $entry['airIRRCUnitStatusFilterDPMetric'];

  discover_sensor($valid['sensor'], 'pressure', $device, $oid, "airIRRCUnitStatusFilterDPMetric.$index", 'apc', $descr, 1, $value);

  $descr = $unitDescr . " Containment Pressure";
  $oid   = ".1.3.6.1.4.1.318.1.1.13.3.2.2.2.13.$index";
  $value = $entry['airIRRCUnitStatusContainmtDPMetric'];

  discover_sensor($valid['sensor'], 'pressure', $device, $oid, "airIRRCUnitStatusContainmtDPMetric.$index", 'apc', $descr, 1, $value);
}

#### Legacy mUpsEnvironment Sensors (AP9312TH) #######################################################

echo(" ");

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
    $value = state_string_to_numeric('powernet-mupscontact-state',$entry['currentStatus']);

    discover_sensor($valid['sensor'], 'state', $device, $oid, "currentStatus.$index", 'powernet-mupscontact-state', $descr, NULL, $value, array('entPhysicalClass' => 'other'));
  }
}

// EOF
