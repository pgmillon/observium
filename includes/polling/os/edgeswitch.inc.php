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

// Note: MIBs for this are troublesome, since they are modified Broadcom reference MIBs and overlap with Dell, Netgear and others.

/*
.1.3.6.1.4.1.4413.1.1.1.1.1.1.0 = STRING: "EdgeSwitch 24-Port 250W, 1.1.2.4767216, Linux 3.6.5-f4a26ed5"
.1.3.6.1.4.1.4413.1.1.1.1.1.2.0 = STRING: "EdgeSwitch 24-Port 250W"
.1.3.6.1.4.1.4413.1.1.1.1.1.3.0 = STRING: "ES-24-250W"
.1.3.6.1.4.1.4413.1.1.1.1.1.4.0 = STRING: "44D9E70524D9"
.1.3.6.1.4.1.4413.1.1.1.1.1.6.0 = STRING: "A"
.1.3.6.1.4.1.4413.1.1.1.1.1.7.0 = STRING: "BCM53344"
.1.3.6.1.4.1.4413.1.1.1.1.1.8.0 = STRING: "0xbc00"
.1.3.6.1.4.1.4413.1.1.1.1.1.9.0 = Hex-STRING: 44 D9 E7 05 24 D9
.1.3.6.1.4.1.4413.1.1.1.1.1.10.0 = STRING: "Linux 3.6.5-f4a26ed5"
.1.3.6.1.4.1.4413.1.1.1.1.1.11.0 = STRING: "BCM53344_A0"
.1.3.6.1.4.1.4413.1.1.1.1.1.12.0 = STRING: " QOS"
.1.3.6.1.4.1.4413.1.1.1.1.1.13.0 = STRING: "1.1.2.4767216"
.1.3.6.1.4.1.4413.1.1.1.1.2.1.0 = INTEGER: 9586
.1.3.6.1.4.1.4413.1.1.1.1.2.3.0 = INTEGER: 8254
*/

$version  = trim(snmp_get($device, ".1.3.6.1.4.1.4413.1.1.1.1.1.13.0", "-OQv"), '"');
$hardware = trim(snmp_get($device, ".1.3.6.1.4.1.4413.1.1.1.1.1.2.0", "-OQv"), '"');
$model    = trim(snmp_get($device, ".1.3.6.1.4.1.4413.1.1.1.1.1.3.0", "-OQv"), '"');
$serial   = trim(snmp_get($device, ".1.3.6.1.4.1.4413.1.1.1.1.1.4.0", "-OQv"), '"');
$features = trim(snmp_get($device, ".1.3.6.1.4.1.4413.1.1.1.1.1.10.0", "-OQv"), '"');

// EOF
