<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage poller
 * @copyright  (C) 2006-2013 Adam Armstrong, 2013-2016 Observium Limited
 *
 */

$mempool['total'] = snmp_get($device, ".1.3.6.1.4.1.8744.5.21.1.1.9.0", "-Ovq") / 1024;
$mempool['free']  = snmp_get($device, ".1.3.6.1.4.1.8744.5.21.1.1.10.0", "-Ovq") / 1024;
$mempool['used']  = $mempool['total'] - $mempool['free'];

// EOF
