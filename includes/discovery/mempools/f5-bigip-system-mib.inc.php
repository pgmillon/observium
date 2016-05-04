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

$mib = 'F5-BIGIP-SYSTEM-MIB';
echo("$mib ");

$tmm_memory = snmpwalk_cache_multi_oid($device, "sysTmmStatMemoryUsed", NULL, $mib, mib_dirs('f5'));
$tmm_memory = snmpwalk_cache_multi_oid($device, "sysTmmStatMemoryTotal", $tmm_memory, $mib, mib_dirs('f5'));

foreach ($tmm_memory as $index => $entry)
{
  $total = $entry['sysTmmStatMemoryTotal'];
  if ($total == 0) continue;

  $used  = $entry['sysTmmStatMemoryUsed'];
  $descr = "TMM $index Memory";
  discover_mempool($valid['mempool'], $device, $index, $mib, $descr, 1, $total, $used);
}

unset ($mempool_array, $index, $total, $used);

// EOF
