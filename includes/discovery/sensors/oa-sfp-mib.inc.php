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

echo(" OA-SFP-MIB ");

$oids = snmpwalk_cache_oid($device, "oaSfpDiagnosticTemperature", array(), "OA-SFP-MIB", mib_dirs('mrv'));
$oids = snmpwalk_cache_oid($device, "oaSfpDiagnosticVcc",           $oids, "OA-SFP-MIB", mib_dirs('mrv'));
$oids = snmpwalk_cache_oid($device, "oaSfpDiagnosticTxBias",        $oids, "OA-SFP-MIB", mib_dirs('mrv'));
$oids = snmpwalk_cache_oid($device, "oaSfpDiagnosticTxPower",       $oids, "OA-SFP-MIB", mib_dirs('mrv'));
$oids = snmpwalk_cache_oid($device, "oaSfpDiagnosticRxPower",       $oids, "OA-SFP-MIB", mib_dirs('mrv'));

if (OBS_DEBUG > 1) { print_vars($oids); }

foreach ($oids as $index => $entry)
{
  list ($mrvslot, $mrvport) = explode('.', $index);
  $xdescr = "Slot $mrvslot port $mrvport";
  unset($mrvslot, $mrvport);

  if ($entry['oaSfpDiagnosticTemperature'] != 'empty')
  {
    $descr = $xdescr. " DOM Temperature";
    $scale = 0.1;
    $oid   = ".1.3.6.1.4.1.6926.1.18.1.1.3.1.3.$index";
    $value = intval($entry['oaSfpDiagnosticTemperature']);
    if ($value <> 0)
    {
      discover_sensor($valid['sensor'], 'temperature', $device, $oid, $index, 'lambdadriver-dom-temp', $descr, $scale, $value);
    }
  }

  if ($entry['oaSfpDiagnosticVcc'] != 'empty')
  {
    $descr = $xdescr . " SFP supply voltage";
    $scale = 0.0001;
    $oid   = ".1.3.6.1.4.1.6926.1.18.1.1.3.1.4.$index";
    $value = intval($entry['oaSfpDiagnosticVcc']);
    if ($value <> 0)
    {
      discover_sensor($valid['sensor'], 'voltage', $device, $oid, $index, 'lambdadriver-dom-voltage', $descr, $scale, $value);
    }
  }

  if ($entry['oaSfpDiagnosticTxBias'] != 'empty')
  {
    $descr = $xdescr . " DOM Current";
    $scale = si_to_scale('micro');
    $oid   = ".1.3.6.1.4.1.6926.1.18.1.1.3.1.5.$index";
    $value = intval($entry['oaSfpDiagnosticTxBias']);
    if ($value <> 0)
    {
      discover_sensor($valid['sensor'], 'current', $device, $oid, $index, 'lambdadriver-dom-current', $descr, $scale, $value);
    }
  }

  if ($entry['oaSfpDiagnosticRxPower'] != 'empty')
  {
    $descr = $xdescr . " RX";
    $scale = 0.01;
    $oid   = ".1.3.6.1.4.1.6926.1.18.1.1.3.1.7.$index";
    $value = intval($entry['oaSfpDiagnosticRxPower']);
    if ($value <> -5000)
    {
      discover_sensor($valid['sensor'], 'dbm', $device, $oid, $index, 'lambdadriver-dom-rxpower', $descr, $scale, $value);
    }
  }

  if ($entry['oaSfpDiagnosticTxPower'] != 'empty')
  {
    $descr = $xdescr . " TX";
    $scale = 0.01;
    $oid   = ".1.3.6.1.4.1.6926.1.18.1.1.3.1.6.$index";
    $value = intval($entry['oaSfpDiagnosticRxPower']);
    if ($value <> -5000)
    {
      discover_sensor($valid['sensor'], 'dbm', $device, $oid, $index, 'lambdadriver-dom-txpower', $descr, $scale, $value);
    }
  }
}

// EOF
