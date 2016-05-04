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

print_vars(snmpwalk_cache_oid ($device, "system", array()));

print_vars(snmp_cache_oid ("system", $device, array()));

// EOF
