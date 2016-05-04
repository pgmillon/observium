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

$mib = 'EMBEDDED-NGX-MIB';
echo(" $mib ");

# lookup for storage data
$entry = snmpwalk_cache_oid($device, 'swStorage', NULL, $mib, mib_dirs('checkpoint'));

if (is_array($entry))
{
    $index  = 0;
    $descr  = "Config Storage";
    $free   = $entry[$index]['swStorageConfigFree']  * 1024;
    $total  = $entry[$index]['swStorageConfigTotal'] * 1024;
    $used   = $total - $free;
    discover_storage($valid['storage'], $device, $index, 'StorageConfig', $mib, $descr, 1024, $total, $used, 0);
}

unset ($entry, $index, $descr, $total, $used, $free);

// EOF
