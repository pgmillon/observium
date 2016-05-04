<?php
/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage discovery
 * @copyright  (C) 2006-2013 Adam Armstrong, (C) 2013-2016 Observium Limited
 *
 */


echo " ATTO6500N-MIB ";

$atto_fc_ports = snmpwalk_cache_oid($device, "fcPortPortNumber", array(), "ATTO6500N-MIB");
foreach($atto_fc_ports as $port){
	$index = $port['fcPortPortNumber'];
	$sensorName = "FiberChannel Port ".$index;	
	$oid = ".1.3.6.1.4.1.4547.2.3.3.1.1.3.".$index;
	discover_status($device, $index, "fcPortOperationalState.".$index, "atto6500n-mib-fcPort", $sensorName, NULL, array('entPhysicalClass' => 'port'));
}

$atto_sas_ports = snmpwalk_cache_oid($device, "sasPortPortNumber", array(), "ATTO6500N-MIB");
foreach($atto_sas_ports as $port){
	$index = $port['sasPortPortNumber'];
	$sensorName = "SAS Port ".$index;	
	$oid = ".1.3.6.1.4.1.4547.2.3.3.3.1.2.".$index;
	discover_status($device, $index, "sasPortOperationalState.".$index, "atto6500n-mib-sasPort", $sensorName, NULL, array('entPhysicalClass' => 'port'));
}

unset($atto_fc_ports, $atto_sas_ports);
