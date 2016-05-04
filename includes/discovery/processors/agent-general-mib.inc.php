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

// Hardcoded discovery of cpu usage on D-Link devices.
//
// AGENT-GENERAL-MIB::agentCPUutilizationIn5min.0 = INTEGER: 25

echo("AGENT-GENERAL-MIB ");

$descr = "Processor";
$usage = snmp_get($device, "agentCPUutilizationIn5min.0", "-Ovq", "AGENT-GENERAL-MIB", mib_dirs('d-link'));

if (is_numeric($usage))
{
  discover_processor($valid['processor'], $device, ".1.3.6.1.4.1.171.12.1.1.6.3.0", "0", "dlink-fixed", $descr, "1", $usage, NULL, NULL);
}

// EOF
