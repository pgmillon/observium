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

$version = trim(snmp_get($device, "1.3.6.1.4.1.14988.1.1.4.4.0", "-OQv", "", ""),'"');
$features = "Level " . trim(snmp_get($device, "1.3.6.1.4.1.14988.1.1.4.3.0", "-OQv", "", ""),'"');

// Some RouterOS versions return simply "router" as sysDescr. Others (newer?) Return "RouterOS <model number>"
if(strstr($poll_device['sysDescr'], "RouterOS")) { $hardware = substr($poll_device['sysDescr'], 9); }

// EOF
