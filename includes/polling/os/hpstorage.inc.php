<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage poller
 * @author     Adam Armstrong <adama@observium.org>
 * @copyright  (C) 2006-2013 Adam Armstrong, (C) 2013-2016 Observium Limited
 *
 */

$hardware = $poll_device['sysDescr'];
$serial   = snmp_get($device, 'cpqSiSysSerialNum.0', '-OQv', 'CPQSINFO-MIB', mib_dirs('hp'));

// EOF
