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

$mib = 'COMPELLENT-MIB';

$version  = snmp_get($device, 'productIDVersion.0', '-OQv', $mib);
$hardware = 'Compellent '.trim(snmp_get($device, 'scEnclModel.1', '-OQv', $mib), 'EN-');;
//$serial   = snmp_get($device, 'productIDSerialNumber.0', '-OQv', $mib);

// EOF
