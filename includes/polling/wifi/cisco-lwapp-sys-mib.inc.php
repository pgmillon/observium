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

//CISCO-LWAPP-SYS-MIB::clsSysMaxClients.0 = Gauge32: 7000
//CISCO-LWAPP-SYS-MIB::clsMaxClientsCount.0 = Gauge32: 53
//CISCO-LWAPP-SYS-MIB::clsSysApConnectCount.0 = Gauge32: 22
$mib = 'CISCO-LWAPP-SYS-MIB';
echo(" $mib ");

//$wificlients1 = snmp_get($device, "clsSysApConnectCount.0", "-OUqnv", $mib); // This is AP count
$wificlients1 = snmp_get($device, "clsMaxClientsCount.0", "-OUqnv", $mib);

if (!is_numeric($wificlients1))
{
  unset($wificlients1);
}

// EOF
