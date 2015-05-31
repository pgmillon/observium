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

// ExtendAir r5000
// ExtendAir rc13005
// ExtendAir G2 rc11020
// EX-5i
//$hardware = $poll_device['sysDescr'];

$data = snmpwalk_cache_multi_oid($device, 'radioInfo', array(), 'ExaltComProducts', mib_dirs('exalt'));
$hardware      = $data[0]['modelName'];
list($version) = explode(' ', $data[0]['firmwareVersion']);
$features      = $data[0]['interfaceType'];
$serial        = $data[0]['serialNumber'];
$asset_tag     = $data[0]['partNumber'];

// EOF
