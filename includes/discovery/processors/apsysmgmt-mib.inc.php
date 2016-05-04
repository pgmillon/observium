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

// Hardcoded discovery of cpu usage on Acme Packet

echo("APSYSMGMT-MIB ");

$descr = "Processor";
$usage = snmp_get($device, "apSysCPUUtil.0", "-Ovq", "APSYSMGMT-MIB", mib_dirs('acme'));

if (is_numeric($usage))
{
  discover_processor($valid['processor'], $device, ".1.3.6.1.4.1.9148.3.2.1.1.1.0", "0", "acme", $descr, "1", $usage, NULL, NULL);
}

// EOF
