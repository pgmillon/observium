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

$mib = 'HOST-RESOURCES-MIB';

if (!is_array($cache_storage['host-resources-mib']))
{
  $cache_storage['host-resources-mib'] = snmpwalk_cache_oid($device, "hrStorageEntry", NULL, "HOST-RESOURCES-MIB:HOST-RESOURCES-TYPES", mib_dirs());
} else {
  print_debug("Cached!");
}

$index = $mempool['mempool_index'];
$entry = $cache_storage['host-resources-mib'][$index];

$mempool['mempool_precision'] = $entry['hrStorageAllocationUnits'];
$mempool['used']              = (int)$entry['hrStorageUsed']; // if hrStorageUsed not set, use 0
$mempool['total']             = $entry['hrStorageSize'];

// EOF
