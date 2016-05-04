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

/**

 G6-FACTORY-MIB::factoryArticleNumber.0; Value (OctetString): MS440210M-G6+
 G6-FACTORY-MIB::factorySerialNumber.0; Value (OctetString): <removed>
 G6-SYSTEM-MIB::firmwareRunningVersion.0; Value (OctetString): 10.5.4a
 G6-FACTORY-MIB::factoryWebDescription.0; Value (OctetString): Micro Switch 6xGBE, Mgmt, MicroSD card, internal memory, Vert., 4xRJ-45, EEE,  Up: ST MM 850nm, Down: RJ-45, Pwr: AC, 10W
*/
$data   = snmp_get_multi($device, "factoryArticleNumber.0 factorySerialNumber.0 firmwareRunningVersion.0","-OQUs", "+G6-FACTORY-MIB:G6-SYSTEM-MIB", mib_dirs("microsens-g6"));
$data   = $data[0];

$serial   = $data['factorySerialNumber'];
$hardware = $data['factoryArticleNumber'];
$version  = $data['firmwareRunningVersion'];
$features = '';
// EOF
