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

echo("JUNIPER-SRX5000-SPU-MONITORING-MIB ");

$srx_spu_array = array();

$srx_spu_array = snmpwalk_cache_multi_oid($device, "jnxJsSPUMonitoringNodeDescr", $srx_spu_array, "JUNIPER-SRX5000-SPU-MONITORING-MIB");
$srx_spu_array = snmpwalk_cache_multi_oid($device, "jnxJsSPUMonitoringFPCIndex", $srx_spu_array, "JUNIPER-SRX5000-SPU-MONITORING-MIB");
$srx_spu_array = snmpwalk_cache_multi_oid($device, "jnxJsSPUMonitoringCPUUsage", $srx_spu_array, "JUNIPER-SRX5000-SPU-MONITORING-MIB");

if (OBS_DEBUG > 1) { print_vars($srx_spu_array); }

foreach ($srx_spu_array as $index => $entry)
{
  $usage_oid = ".1.3.6.1.4.1.2636.3.39.1.12.1.1.1.4." . $index; // node0 FPC: SRX3k SPC
  $descr = ($entry['jnxJsSPUMonitoringNodeDescr'] == 'single' ? '' : $entry['jnxJsSPUMonitoringNodeDescr'] . ' ') . 'SPC slot ' .  $entry['jnxJsSPUMonitoringFPCIndex'];
  $usage = $entry['jnxJsSPUMonitoringCPUUsage'];

  discover_processor($valid['processor'], $device, $usage_oid, $index, "junos", $descr, 1, $usage, NULL, NULL);
}

unset ($srx_spu_array);

// EOF
