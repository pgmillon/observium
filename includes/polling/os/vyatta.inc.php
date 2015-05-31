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

list($features, $version) = explode("-", trim(str_replace("Vyatta", "", snmp_get($device, "SNMPv2-MIB::sysDescr.0", "-Oqv", "SNMPv2-MIB"))), 2);

// EOF
