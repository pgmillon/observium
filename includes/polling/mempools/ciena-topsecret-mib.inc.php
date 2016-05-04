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

$mib = 'CIENA-TOPSECRET-MIB';

$mempool['used']  = snmp_get($device, ".1.3.6.1.4.1.6141.2.60.12.1.9.1.1.4.2", "-OvQU", mib_dirs());
$mempool['total'] = snmp_get($device, ".1.3.6.1.4.1.6141.2.60.12.1.9.1.1.2.2", "-OvQU", mib_dirs());

// EOF
