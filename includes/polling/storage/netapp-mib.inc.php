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

// NETAPP-MIB
echo(' NETAPP-MIB: ');

$index = $storage['storage_index'];

if ($storage['storage_hc'])
{
  $netapp_oids = array('size' => 'df64TotalKBytes', 'used' => 'df64UsedKBytes', 'free' => 'df64AvailKBytes');

  if (!is_array($cache_storage['netapp-mib-hc']))
  {
    foreach (array('size', 'used', 'free') as $param)
    {
      $oid = $netapp_oids[$param];
      $cache_storage['netapp-mib-hc'] = snmpwalk_cache_multi_oid($device, $oid, $cache_storage['netapp-mib-hc'], 'NETAPP-MIB', mib_dirs('netapp'));
    }
    if (OBS_DEBUG && count($cache_storage['netapp-mib-hc'])) { print_vars($cache_storage['netapp-mib-hc']); }
  }
  $entry = $cache_storage['netapp-mib-hc'][$index];
} else {
  $netapp_oids = array('size' => 'dfKBytesTotal',   'used' => 'dfKBytesUsed',   'free' => 'dfKBytesAvail');

  if (!is_array($cache_storage['netapp-mib']))
  {
    foreach (array('size', 'used', 'free') as $param)
    {
      $oid = $netapp_oids[$param];
      $cache_storage['netapp-mib'] = snmpwalk_cache_multi_oid($device, $oid, $cache_storage['netapp-mib'], 'NETAPP-MIB', mib_dirs('netapp'));
    }
    if (OBS_DEBUG && count($cache_storage['netapp-mib'])) { print_vars($cache_storage['netapp-mib']); }
  }
  $entry = $cache_storage['netapp-mib-hc'][$index];
}

$storage['units'] = 1024; // Hardcode units.
foreach (array('size', 'used', 'free') as $param)
{
  $oid = $netapp_oids[$param];
  $storage[$param] = $entry[$oid] * $storage['units'];
}

unset($index, $entry, $param, $netapp_oids);

// EOF
