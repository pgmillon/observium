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

// NOTE: The DELL-TL2000-MIB is completely identical (same OIDs), just all "4000" have been replaced by "2000". TL-2000 works with this file.

echo(" DELL-TL4000-MIB ");

// DELL-TL4000-MIB::TL4000StatusGlobalStatus.0 = INTEGER: ok(3)

$oids = snmpwalk_cache_oid($device, "TL4000Status", array(), "DELL-TL4000-MIB", mib_dirs('dell'));

foreach ($oids as $index => $entry)
{
  $descr   = "Tape Library Status";
  $oid     = ".1.3.6.1.4.1.674.10893.2.102.2.1.$index";
  $value   = $entry['TL4000StatusGlobalStatus'];

  if ($value != '')
  {
    discover_sensor($valid['sensor'], 'state', $device, $oid, "TL4000StatusGlobalStatus.$index", 'dell-tl4000-status-state', $descr, 1, $value);
  }

}

/*
$oids = snmpwalk_cache_oid($device, "libraryTable", array(), "DELL-TL4000-MIB", mib_dirs('dell'));

// DELL-TL4000-MIB::libraryDoorState.1 = INTEGER: closed(3)
// DELL-TL4000-MIB::libraryImpExpState.1 = INTEGER: closed(3)

foreach ($oids as $index => $entry)
{
}
*/

// EOF
