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

$mib = 'APSYSMGMT-MIB';

$mempool['perc'] = snmp_get($device, "apSysMemoryUtil.0", "-OvQ", $mib, mib_dirs('acme'));

// EOF
