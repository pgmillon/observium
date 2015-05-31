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

$mib = 'ASYNCOS-MAIL-MIB';

$mempool['perc'] = snmp_get($device, "perCentMemoryUtilization.0", "-OvQ", $mib, mib_dirs('cisco'));

// EOF
