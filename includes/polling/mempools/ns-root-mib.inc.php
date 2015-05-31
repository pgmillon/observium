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

$mib = 'NS-ROOT-MIB';

$mempool['total'] = snmp_get($device, "memSizeMB.0",   "-OvQ", $mib, mib_dirs('citrix'));
$mempool['perc']  = snmp_get($device, "resMemUsage.0", "-OvQ", $mib, mib_dirs('citrix'));

// EOF
