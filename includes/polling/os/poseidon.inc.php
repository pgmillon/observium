<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage poller
 * @copyright  (C) 2006-2014 Adam Armstrong
 *
 */

// sysDescr.0 = STRING: "Poseidon 2250 SNMP Supervisor v1.0.9"
// sysDescr.0 = STRING: "Poseidon2 4002 SNMP Supervisor v1.2.0"
preg_match('/(Poseidon2*\ \d+)\ SNMP\ Supervisor\ v([\d\.]+)/', $poll_device['sysDescr'], $hardware);
list(, $hardware, $version) = $hardware;

// EOF
