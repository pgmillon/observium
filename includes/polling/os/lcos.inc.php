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

//LCOS-MIB::lcsFirmwareVersionTableEntryIfc.eIfc = INTEGER: eIfc(1)
//LCOS-MIB::lcsFirmwareVersionTableEntryModule.eIfc = STRING: LANCOM L-321agn Wireless
//LCOS-MIB::lcsFirmwareVersionTableEntryVersion.eIfc = STRING: 8.82.0100RU1 / 28.08.2013
//LCOS-MIB::lcsFirmwareVersionTableEntrySerialNumber.eIfc = STRING: 4003xxxxxxxxxxxx

$data = snmp_get_multi($device, 'lcsFirmwareVersionTableEntryModule.eIfc lcsFirmwareVersionTableEntryVersion.eIfc lcsFirmwareVersionTableEntrySerialNumber.eIfc', '-OQUs', 'LCOS-MIB', mib_dirs('lancom'));

print_r($data);

$hardware = $data['eIfc']['lcsFirmwareVersionTableEntryModule'];
list($version, $features) = explode(" / ", $data['eIfc']['lcsFirmwareVersionTableEntryVersion']);
$serial  = $data['eIfc']['lcsFirmwareVersionTableEntrySerialNumber'];

// EOF
