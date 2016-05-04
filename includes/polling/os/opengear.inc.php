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

// SNMPv2-SMI::enterprises.25049.17.1.1 = STRING: "3.12.1 (Fri Sep 26 16:16:16 EST 2014)"
$tmpver = trim(snmp_get($device, ".1.3.6.1.4.1.25049.17.1.1", "-OQv"),'"');
$verarray = explode(" ", $tmpver);
$version = $verarray[0];

// SNMPv2-SMI::enterprises.25049.17.1.2 = STRING: "55020456371432"
$serial = trim(snmp_get($device, ".1.3.6.1.4.1.25049.17.1.2", "-OQv"),'"');

// EOF
