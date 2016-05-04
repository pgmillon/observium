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

echo("ADTRAN-AOSCPU ");

$percent = snmp_get($device, "adGenAOS5MinCpuUtil.0", "-OQv", "ADTRAN-AOSCPU");

if (is_numeric($percent))
{
  discover_processor($valid['processor'], $device, ".1.3.6.1.4.1.664.5.53.1.4.4.0", "1", "adtran-aoscpu", "CPU", "1", $percent, NULL, NULL);
}

unset($percent);

// EOF
