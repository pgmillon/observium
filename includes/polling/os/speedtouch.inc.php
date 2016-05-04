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

$serial = "";
#list(,$hardware,) = explode(" ", $hardware);
$hardware = $poll_device['sysDescr'];

$features = "";

// Filthy hack to get software version. may not work on anything but 585v7 :)
//shell_exec($config['snmpget'] . " -M ".$config['mib_dir'] . ' -Ovq '. snmp_gen_auth($device) .' '.$device['hostname'].' ifDescr.101');
$loop = snmp_get($device, 'ifDescr.101');

if ($loop)
{
  preg_match('@([0-9]+\.[0-9]+\.[0-9]+\.[0-9]+)@i',
    $loop, $matches);
    $version = $matches[1];
}

// EOF
