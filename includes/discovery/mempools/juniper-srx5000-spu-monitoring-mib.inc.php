<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage discovery
 * @copyright  (C) 2006-2014 Adam Armstrong
 *
 */

echo(" JUNIPER-SRX5000-SPU-MONITORING-MIB ");

$srx_spu_array = array();

$srx_spu_array = snmpwalk_cache_multi_oid($device, "jnxJsSPUMonitoringNodeDescr", $srx_spu_array, "JUNIPER-SRX5000-SPU-MONITORING-MIB", mib_dirs('junos'));
$srx_spu_array = snmpwalk_cache_multi_oid($device, "jnxJsSPUMonitoringFPCIndex", $srx_spu_array, "JUNIPER-SRX5000-SPU-MONITORING-MIB", mib_dirs('junos'));
$srx_spu_array = snmpwalk_cache_multi_oid($device, "jnxJsSPUMonitoringMemoryUsage", $srx_spu_array, "JUNIPER-SRX5000-SPU-MONITORING-MIB", mib_dirs('junos'));

$srx_spu_array = snmpwalk_cache_multi_oid($device, "jnxJsSPUMonitoringNodeDescr", $srx_spu_array, "JUNIPER-SRX5000-SPU-MONITORING-MIB", mib_dirs('junos'));
foreach ($srx_spu_array as $index => $entry)
{
  if (is_numeric($entry['jnxJsSPUMonitoringMemoryUsage']))
  {
    $descr = ($entry['jnxJsSPUMonitoringNodeDescr'] == 'single' ? '' : $entry['jnxJsSPUMonitoringNodeDescr'] . ' ') . 'SPC slot ' .  $entry['jnxJsSPUMonitoringFPCIndex'];
    $usage = $entry['jnxJsSPUMonitoringMemoryUsage'];
    discover_mempool($valid['mempool'], $device, $index, "JUNIPER-SRX5000-SPU-MONITORING-MIB", $descr, 1, 100, $usage);
  }
}

unset ($srx_spu_array, $index, $descr, $usage);

// EOF
