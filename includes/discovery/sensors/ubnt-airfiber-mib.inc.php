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

echo(" UBNT-AirFIBER-MIB ");

// Getting Radios

$table = snmpwalk_cache_oid($device, "radio0TempC", array(), "UBNT-AirFIBER-MIB", mib_dirs('ubiquiti'));
$table = snmpwalk_cache_oid($device, "radio1TempC", $table, "UBNT-AirFIBER-MIB", mib_dirs('ubiquiti'));

// Goes through the SNMP radio data
foreach ($table as $index => $entry)
{

  $options = array();

  if (is_numeric($entry['radio0TempC']))
  {
      discover_sensor($valid['sensor'], 'temperature', $device, '.1.3.6.1.4.1.41112.1.3.2.1.8.'.$index, $index, 'UBNT-AirFIBER-MIB::radio0TempC', "Radio 0", '1', $entry['radio0TempC'], $options);
  }

  if (is_numeric($entry['radio1TempC']))
  {
      discover_sensor($valid['sensor'], 'temperature', $device, '.1.3.6.1.4.1.41112.1.3.2.1.10.'.$index, $index, 'UBNT-AirFIBER-MIB::radio1TempC', "Radio 1", '1', $entry['radio0TempC'], $options);
  }

}

// EOF
