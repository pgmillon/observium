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

echo(" GUDEADS-EPC2X6-MIB ");

$oid   = ".1.3.6.1.4.1.28507.6.1.2.2.1.3";
$descr = "Temperature";
$value = snmp_get($device, "epc2x6Temperature.0","-Oqv", "GUDEADS-EPC2X6-MIB", mib_dirs('gude'));

if ($value != '' && $value != 99990)
{
  discover_sensor($valid['sensor'], 'temperature', $device, $oid, 'epc2x6Temperature.0', 'epc2x6', $descr, 1, $value);
}

// EOF
