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

// Cisco Small Business

# CISCOSB-rndMng::rlCpuUtilEnable.0 = INTEGER: true(1)
# CISCOSB-rndMng::rlCpuUtilDuringLast5Minutes.0 = INTEGER: 4

echo("CISCOSB-rndMng ");

$data  = snmp_get_multi($device, 'rlCpuUtilEnable.0 rlCpuUtilDuringLast5Minutes.0', "-OQUs", "CISCOSB-rndMng", mib_dirs(array('ciscosb')));
$descr = "CPU";
$index = 0;
$oid   = ".1.3.6.1.4.1.9.6.1.101.1.9.$index";
$usage = $data[0]['rlCpuUtilDuringLast5Minutes'];

if ($data[0]['rlCpuUtilEnable'] == 'true')
{
  discover_processor($valid['processor'], $device, $oid, $index, "ciscosb", $descr, "1", $usage, NULL, NULL);
}

// EOF
