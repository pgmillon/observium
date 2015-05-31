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

#SNMPv2-SMI::enterprises.10704.1.2 = STRING: "GWAY-5.2.6-021"

$version = trim(snmp_get($device, ".1.3.6.1.4.1.10704.1.2", "-OQv", "", ""),'" ');

// EOF
