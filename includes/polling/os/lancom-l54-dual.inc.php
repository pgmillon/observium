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

// LANCOM-L54-dual-MIB::firVerMod.ifc = STRING: "LANCOM L-54 dual Wireless"
// LANCOM-L54-dual-MIB::firVerVer.ifc = STRING: "8.62.0103RU7 / 24.01.2013"
// LANCOM-L54-dual-MIB::firVerSer.ifc = STRING: "104671800120"

echo("LANCOM");

$data = snmp_get_multi($device, 'firVerMod.ifc firVerVer.ifc firVerSer.ifc', '-OQUs', 'LANCOM-L54-dual-MIB', mib_dirs('lancom'));

print_r($data);

$hardware = $data['ifc']['firVerMod'];
list($version, $features) = explode(" / ", $data['ifc']['firVerVer']);
$serial  = $data['ifc']['firVerSer'];

// EOF
