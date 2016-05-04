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

$mib = 'APSYSMGMT-MIB';

$mempool['perc'] = snmp_get($device, "apSysMemoryUtil.0", "-OvQ", $mib, mib_dirs('acme'));

// EOF
