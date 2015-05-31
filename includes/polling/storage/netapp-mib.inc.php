<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage poller
 * @copyright  (C) 2006-2014 Adam Armstrong
 *
 */

// NETAPP-MIB
echo(' NETAPP-MIB: ');

$index = $storage['storage_index'];

if($storage['storage_hc'])
{
  $netapp_oids = array('size' => 'df64TotalKBytes', 'used' => 'df64UsedKBytes', 'free' => 'df64AvailKBytes');
} else {
  $netapp_oids = array('size' => 'dfKBytesTotal',   'used' => 'dfKBytesUsed',   'free' => 'dfKBytesAvail');
}

$storage['units'] = 1024; // Hardcode units.
foreach (array('size', 'used', 'free') as $param)
{
  $oid = $netapp_oids[$param];
  $cache_storage['netapp-mib'] = snmpwalk_cache_multi_oid($device, $oid, $cache_storage['netapp-mib'], 'NETAPP-MIB', mib_dirs('netapp'));
  $storage[$param] = $cache_storage['netapp-mib'][$index][$oid] * $storage['units'];
}

// EOF
