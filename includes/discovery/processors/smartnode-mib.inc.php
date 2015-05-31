<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage discovery
 * @copyright  (C) 2006-2014 Adam Armstrong
 *
 */

echo(" SMARTNODE-MIB ");

$processors_array = snmpwalk_cache_oid($device, "cpu", array(), "SMARTNODE-MIB", mib_dirs('patton'));

foreach ($processors_array as $index => $entry)
{
  $descr = $entry['cpuDescr'];
  $oid = ".1.3.6.1.4.1.1768.100.70.10.2.1.3.".$index;
  $usage = $entry['cpuWorkload5MinuteAverage'];

  discover_processor($valid['processor'], $device, $oid, $index, "smartnode", $descr, "1", $usage, NULL, NULL);
}

unset ($processors_array);

// EOF
