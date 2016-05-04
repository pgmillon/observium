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

$fnSysVersion = snmp_get($device, ".1.3.6.1.4.1.388.11.2.2.1.3.2.0", "-Ovq");
$serial       = trim(snmp_get($device, ".1.3.6.1.4.1.388.11.2.2.1.1.0", "-Ovq"),'"');
$version      = trim(snmp_get($device, ".1.3.6.1.4.1.388.11.2.2.1.3.2.0", "-Ovq"),'"');

// preg_match("/HW=(^\s]+)/",$sysDescr,$hardwarematches);
preg_match("/\s+[^\s]+/",$poll_device['sysDescr'],$hardwarematches);
$hardware     = $hardwarematches[0];

// EOF
