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

// EMBEDDED-NGX-MIB

if (!is_array($cache_storage['embedded-ngx-mib']))
{
  $cache_storage['embedded-ngx-mib'] = snmpwalk_cache_oid($device, "swStorage", NULL, "EMBEDDED-NGX-MIB", mib_dirs("checkpoint"));
  if (OBS_DEBUG && count($cache_storage['embedded-ngx-mib'])) { print_vars($cache_storage['embedded-ngx-mib']); }
}

$entry = $cache_storage['embedded-ngx-mib'][$storage['storage_index']];

$storage['units'] = 1024;
$storage['size']  = $entry['swStorageConfigTotal'] * $storage['units'];
$storage['free']  = $entry['swStorageConfigFree'] * $storage['units'];

$storage['used']  = $storage['size'] - $storage['free'];

// EOF
