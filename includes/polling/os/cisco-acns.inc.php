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

$serial = trim(snmp_get($device, 'ceAssetSerialNumber.1', '-OQv', 'CISCO-ENTITY-ASSET-MIB', mib_dirs('cisco')),'"');
$version = trim(snmp_get($device, 'ceAssetSoftwareRevision.1', '-OQv', 'CISCO-ENTITY-ASSET-MIB', mib_dirs('cisco')),'"');
$hardware = trim(snmp_get($device, 'entPhysicalDescr.1', '-OQv', 'ENTITY-MIB', mib_dirs('cisco')),'"');

// EOF
