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

$mib = 'COMPELLENT-MIB';

$cml['temp'] = snmpwalk_cache_oid($device, 'scEnclTempTable', array(), $mib);
$cml['fan'] = snmpwalk_cache_oid($device, 'scEnclFanTable', array(), $mib);
$cml['ctrl'] = snmpwalk_cache_oid($device, 'scCtlrEntry', array(), $mib);
$cml['disk'] = snmpwalk_cache_oid($device, 'scDiskEntry', array(), $mib);
$cml['cache'] = snmpwalk_cache_oid($device, 'ScCacheEntry', array(), $mib);

foreach ($cml['temp'] as $index => $entry)
{
  $oid   = '.1.3.6.1.4.1.16139.2.23.1.5.'.$index;
  $descr = $entry['scEnclTempLocation'];
  $value = $entry['scEnclTempCurrentC'];

  if (is_numeric($value))
  {
    discover_sensor($valid['sensor'], 'temperature', $device, $oid, $index, 'compellent', $descr, 1, $value);
  }
}

foreach ($cml['fan'] as $index => $entry)
{
  $oid   = '.1.3.6.1.4.1.16139.2.20.1.3.'.$index;
  $descr = $entry['scEnclFanLocation'];
  $value = $entry['scEnclFanStatus'];

  if ($value)
  {
    discover_status($device, $oid, 'scEnclFanStatus.'.$index, 'compellent-mib-state', $descr, $value, array('entPhysicalClass' => 'fan'));
  }
}

foreach ($cml['ctrl'] as $index => $entry)
{
  $oid   = '.1.3.6.1.4.1.16139.2.13.1.3.'.$index;
  $descr = 'Controller '.$entry['scCtlrNbr'].' ('.$entry['scCtlrName'].')';
  $value = $entry['scCtlrStatus'];

  if ($value)
  {
    discover_status($device, $oid, 'scCtlrStatus.'.$index, 'compellent-mib-state', $descr, $value, array('entPhysicalClass' => 'other'));
  }
}

foreach ($cml['disk'] as $index => $entry)
{
  $oid   = '.1.3.6.1.4.1.16139.2.14.1.3.'.$index;
  $descr = 'Disk '.$entry['scDiskNamePosition'];
  $value = $entry['scDiskStatus'];

  if ($value)
  {
    discover_status($device, $oid, 'scDiskStatus.'.$index, 'compellent-mib-state', $descr, $value, array('entPhysicalClass' => 'disk'));
  }
}

foreach ($cml['cache'] as $index => $entry)
{
  $oid   = '.1.3.6.1.4.1.16139.2.28.1.5.'.$index;
  $descr = 'Controller '.$entry['scCacheNbr'].' Cache';
  $value = $entry['scCacheBatStat'];

  if ($value)
  {
    discover_status($device, $oid, 'scCacheBatStat.'.$index, 'compellent-mib-cache-state', $descr, $value, array('entPhysicalClass' => 'other'));
  }
}

unset($cml['temp'], $cml['fan'], $cml['ctrl'], $cml['disk'], $cml['cache'], $oid, $descr, $value);

// EOF
