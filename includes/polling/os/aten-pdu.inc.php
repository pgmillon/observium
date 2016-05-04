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

//ATEN-PE-CFG::modelName.0 = STRING: "PE8108G"
//ATEN-PE-CFG::deviceFWversion.0 = STRING: "1.5.148"
$hardware = snmp_get($device, "modelName.0",       "-Oqv", "ATEN-PE-CFG");
$version  = snmp_get($device, "deviceFWversion.0", "-Oqv", "ATEN-PE-CFG");

// EOF
