<?php

// EMBEDDED-NGX-MIB

if (!is_array($cache_storage['embedded-ngx-mib']))
{
  $cache_storage['embedded-ngx-mib'] = snmpwalk_cache_oid($device, "swStorage", NULL, "EMBEDDED-NGX-MIB", mib_dirs("checkpoint"));
  if ($debug && count($cache_storage['embedded-ngx-mib'])) { print_vars($cache_storage['embedded-ngx-mib']); }
}

$entry = $cache_storage['embedded-ngx-mib'][$storage['storage_index']];

$storage['units'] = 1024;
$storage['size']  = $entry['swStorageConfigTotal'] * $storage['units'];
$storage['free']  = $entry['swStorageConfigFree'] * $storage['units'];

$storage['used']  = $storage['size'] - $storage['free'];

// EOF
