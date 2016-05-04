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

echo(" DELL-RAC-MIB ");

// Hardcoded since that's how DELL's MIB is written, and, well, why not!

//DELL-RAC-MIB::drsGlobalSystemStatus.0 = INTEGER: ok(3) // .1.3.6.1.4.1.674.10892.2.2.1.0 = INTEGER: ok(3)

//DELL-RAC-MIB::drsGlobalCurrStatus.0 = INTEGER: ok(3)
//DELL-RAC-MIB::drsIOMCurrStatus.0 = INTEGER: ok(3)
//DELL-RAC-MIB::drsKVMCurrStatus.0 = INTEGER: ok(3)
//DELL-RAC-MIB::drsRedCurrStatus.0 = INTEGER: ok(3)
//DELL-RAC-MIB::drsPowerCurrStatus.0 = INTEGER: ok(3)
//DELL-RAC-MIB::drsFanCurrStatus.0 = INTEGER: ok(3)
//DELL-RAC-MIB::drsBladeCurrStatus.0 = INTEGER: ok(3)
//DELL-RAC-MIB::drsTempCurrStatus.0 = INTEGER: ok(3)
//DELL-RAC-MIB::drsCMCCurrStatus.0 = INTEGER: ok(3)

$drac = array(array('oid' => ".1.3.6.1.4.1.674.10892.2.3.1.1.0", 'descr' => "Global System Health"),
              array('oid' => ".1.3.6.1.4.1.674.10892.2.3.1.2.0", 'descr' => "IOM Health"),
              array('oid' => ".1.3.6.1.4.1.674.10892.2.3.1.3.0", 'descr' => "iKVM Health"),
              array('oid' => ".1.3.6.1.4.1.674.10892.2.3.1.4.0", 'descr' => "Redundancy Health"),
              array('oid' => ".1.3.6.1.4.1.674.10892.2.3.1.5.0", 'descr' => "Power Health"),
              array('oid' => ".1.3.6.1.4.1.674.10892.2.3.1.6.0", 'descr' => "Fan Health"),
              array('oid' => ".1.3.6.1.4.1.674.10892.2.3.1.7.0", 'descr' => "Blade Health"),
              array('oid' => ".1.3.6.1.4.1.674.10892.2.3.1.8.0", 'descr' => "Temperature Health"),
              array('oid' => ".1.3.6.1.4.1.674.10892.2.3.1.9.0", 'descr' => "CMC Health"),
);

foreach ($drac as $index => $entry)
{
  $value  = snmp_get($device, $entry['oid'], "-Oqv");
  $index  = str_replace(".1.3.6.1.4.1.674.10892.", "", $entry['oid']);

  if ($value)
  {
      discover_status($device, $entry['oid'], $index, 'dell-rac-mib', $entry['descr'], NULL, array('entPhysicalClass' => 'chassis'));
  }
}

// EOF
