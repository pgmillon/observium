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

# APNL-MODULAR-PDU-MIB::apnlModules.pdu.pduSoftwareVersion.0 = STRING: V2.23
$version = trim(snmp_get($device, "apnlModules.pdu.pduSoftwareVersion.0", "-OQv", "APNL-MODULAR-PDU-MIB"),'" ');
$serial = trim(snmp_get($device, "apnlModules.pdu.pduSerialNumber.0", "-OQv", "APNL-MODULAR-PDU-MIB"),'" ');
$hardware = "OEC ".$poll_device['sysDescr'];

// EOF
