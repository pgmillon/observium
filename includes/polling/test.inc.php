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

print_vars(snmpwalk_cache_oid ($device, "system", array()));

print_vars(snmp_cache_oid ("system", $device, array()));

// EOF
