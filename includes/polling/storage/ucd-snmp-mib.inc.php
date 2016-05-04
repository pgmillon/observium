<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage poller
 * @copyright  (C) 2006-2013 Adam Armstrong, (C) 2013-2016 Observium Limited
 *
 */

// UCD-SNMP-MIB
echo(' UCD-SNMP-MIB: ');

$ucd_oids = array();
if ($storage['storage_hc'])
{
  if ($cache_storage['ucd-snmp-mib-walked']['storage_hc'] !== TRUE) // Hack for real caching
  {
    $ucd_oids = array('dskTotalHigh', 'dskTotalLow', 'dskUsedHigh', 'dskUsedLow', 'dskAvailHigh', 'dskAvailLow');
    $cache_storage['ucd-snmp-mib-walked']['storage_hc'] = TRUE;
  }
} else {
  if ($cache_storage['ucd-snmp-mib-walked']['storage'] !== TRUE) // Hack for real caching
  {
    $ucd_oids = array('dskTotal', 'dskUsed', 'dskAvail');
    $cache_storage['ucd-snmp-mib-walked']['storage'] = TRUE;
  }
}

$index = $storage['storage_index'];
$storage['units'] = 1024; // Hardcode units.

foreach ($ucd_oids as $oid)
{
  $cache_storage['ucd-snmp-mib'] = snmpwalk_cache_multi_oid($device, $oid, $cache_storage['ucd-snmp-mib'], 'UCD-SNMP-MIB', mib_dirs());
}

foreach (array('size' => 'dskTotal', 'used' => 'dskUsed', 'free' => 'dskAvail') as $param => $oid)
{
  if ($storage['storage_hc'])
  {
    $storage[$param]  = $cache_storage['ucd-snmp-mib'][$index][$oid.'High'] * 4294967296 + $cache_storage['ucd-snmp-mib'][$index][$oid.'Low'];
    $storage[$param] *= $storage['units'];
  } else {
    $storage[$param]  = $cache_storage['ucd-snmp-mib'][$index][$oid] * $storage['units'];
  }
}

if ($storage['storage_hc'] && ($storage['size'] - $storage['used'] - $storage['free']) > 100) { $storage['used'] = $storage['size'] - $storage['free']; } // F.u. BSNMPd

// EOF
