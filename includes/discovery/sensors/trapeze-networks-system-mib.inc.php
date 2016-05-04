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

// Supply
echo(" TRAPEZE-NETWORKS-SYSTEM-MIB ");

$sensor_state_type = "trapeze-state";
$oids = snmpwalk_cache_oid($device, 'trpzSysPowerSupplyEntry', array(), 'TRAPEZE-NETWORKS-SYSTEM-MIB', mib_dirs('trapeze'));

foreach ($oids as $index => $entry)
{
  if (isset($entry['trpzSysPowerSupplyStatus']))
  {
    $oid  = '.1.3.6.1.4.1.14525.4.8.1.1.13.1.2.1.2.'.$index;
    //echo($index);
    //Not numerical values, only states
    $value = $entry['trpzSysPowerSupplyStatus'];
    discover_sensor($valid['sensor'], 'state', $device, $oid, $index, $sensor_state_type, $entry['trpzSysPowerSupplyDescr'], NULL, $value, array('entPhysicalClass' => 'powerSupply'));
  }
}

// EOF
