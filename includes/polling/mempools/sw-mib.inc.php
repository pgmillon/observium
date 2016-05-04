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

$mib = 'SW-MIB';

$mempool['total'] = 2147483648;
$mempool['perc']  = snmp_get($device, "swMemUsage.0", "-Ovq", $mib, mib_dirs('brocade'));

// EOF
