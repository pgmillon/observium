<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage discovery
 * @copyright  (C) 2006-2014 Adam Armstrong
 *
 */

// NOTE. Here only walking, because needed additional checks by HOST-RESOURCES-MIB (see host-resources-mib.inc.php in current directory)

$mib = 'UCD-SNMP-MIB';
$cache_storage['ucd-snmp-mib'] = snmpwalk_cache_oid($device, 'dskEntry', array(), $mib, mib_dirs());

// EOF
