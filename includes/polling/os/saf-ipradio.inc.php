<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage definitions
 * @copyright  (C) 2006-2013 Adam Armstrong, (C) 2013-2016 Observium Limited
 *
 */

//SAF-IPRADIO::product.0 = STRING: "CFIP Lumina FODU"
//SAF-IPRADIO::description.0 = STRING: "SAF microwave radio"
//SAF-IPRADIO::radioName.0 = ""
//SAF-IPRADIO::sysDateAndTime.0 = STRING: 2015-7-26,11:58:3.0
//SAF-IPRADIO::sysTemperature.0 = INTEGER: 53
//SAF-IPRADIO::license.0 = STRING: "OK"
//SAF-IPRADIO::licenseMask.0 = INTEGER: -1

//RFC1213-MIB::sysDescr.0 = STRING: "SAF microwave radio;CFIP Lumina FODU v2.64.33;Model:2;HW:15;SN: 3690205xxxxx;PC: I11HJT05HA;IDU PCB: I0BMDB05_R07"
//RFC1213-MIB::sysObjectID.0 = OID: SAF-IPRADIO::ipRadio

$sysDescr_array = explode(";", $poll_device['sysDescr']);
list(,$serial) = explode(" ", $sysDescr_array[4]);
preg_match('/v(?P<version>.+)/', $sysDescr_array['1'], $matches);
$version = $matches['version'];
$hardware = snmp_get($device, 'product.0', '-Osqv', "SAF-IPRADIO", mib_dirs('saf'));

// EOF
