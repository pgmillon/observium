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

// Note, device attrib 'eqlgrpmemid' sets in equallogic 'os' module.
$eqlgrpmemid = get_dev_attrib($device, 'eqlgrpmemid');

if (is_numeric($eqlgrpmemid))
{
  $mib = 'EQLDISK-MIB';
  echo(" $mib ");

  // EQLDISK-MIB::eqlDiskModelNumber.1.1049142137.1 = STRING: ST3450857SS
  // EQLDISK-MIB::eqlDiskModelNumber.1.1049142137.2 = STRING: ST3450857SS
  // EQLDISK-MIB::eqlDiskStatus.1.1049142137.1 = INTEGER: on-line(1)
  // EQLDISK-MIB::eqlDiskStatus.1.1049142137.2 = INTEGER: on-line(1)
  // EQLDISK-MIB::eqlDiskId.1.1049142137.1 = INTEGER: 0
  // EQLDISK-MIB::eqlDiskId.1.1049142137.2 = INTEGER: 1
  $cache['equallogic']['eqlDiskTable'] = snmpwalk_cache_multi_oid($device, 'eqlDiskTable', array(), $mib, mib_dirs('equallogic'));

  foreach ($cache['equallogic']['eqlDiskTable'] as $index => $entry)
  {
    if (strstr($index, $eqlgrpmemid))
    {
      $descr = 'Disk '.$entry['eqlDiskId'] . ': ' . trim($entry['eqlDiskModelNumber']);

      $oid   = '.1.3.6.1.4.1.12740.3.1.1.1.8.'.$index;
      $value = $entry['eqlDiskStatus'];

      if ($value !== '')
      {
        discover_sensor($valid['sensor'], 'state', $device, $oid, 'eqlDiskStatus.'.$index, 'eql-disk-state', $descr, NULL, $value, array('entPhysicalClass' => 'storage'));
      }
    }
  }
}

// EOF
