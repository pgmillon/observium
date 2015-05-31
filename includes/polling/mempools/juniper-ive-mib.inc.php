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

$mib = 'JUNIPER-IVE-MIB';

$mempool['perc'] = snmp_get($device, "iveMemoryUtil.0", "-OvQ", $mib, mib_dirs('juniper'));

// EOF
