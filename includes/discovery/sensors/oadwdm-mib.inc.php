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

# OADWDM-MIB::oaLdCardTemp.1 = INTEGER: 20
# OADWDM-MIB::oaLdCardTemp.2 = INTEGER: 0
# OADWDM-MIB::oaLdCardType.1 = INTEGER: em2009gm2(35)
# OADWDM-MIB::oaLdCardType.2 = INTEGER: empty(2)

echo(" OADWDM-MIB ");

$oids = snmpwalk_cache_oid($device, "oaLdCardTemp",       array(), "OADWDM-MIB", mib_dirs('mrv'));
$oids = snmpwalk_cache_oid($device, "oaLdCardType",         $oids, "OADWDM-MIB", mib_dirs('mrv'));
$oids = snmpwalk_cache_oid($device, "oaLdDevPSOperStatus",  $oids, "OADWDM-MIB", mib_dirs('mrv'));
$oids = snmpwalk_cache_oid($device, "oaLdDevFANOperStatus", $oids, "OADWDM-MIB", mib_dirs('mrv'));

if (OBS_DEBUG > 1) { print_vars($oids); }

foreach ($oids as $index => $entry)
{
  if ($entry['oaLdCardType'] != 'empty')
  {
    $descr = "Slot $index " . $entry['oaLdCardType'];
    $oid   = ".1.3.6.1.4.1.6926.1.41.3.1.1.26.$index";
    $value = $entry['oaLdCardTemp'];

    if ($value <> 0)
    {
      discover_sensor($valid['sensor'], 'temperature', $device, $oid, $index, 'lambdadriver', $descr, 1, $value);
    }
  }

  if ($entry['oaLdDevPSOperStatus'] != 'empty')
  {
    $descr = "Power Supply $index";
    $oid   = ".1.3.6.1.4.1.6926.1.41.1.10.1.2.1.5.$index";
    $value = $entry['oaLdDevPSOperStatus'];

    discover_sensor($valid['sensor'], 'state', $device, $oid, $index, 'oadwdm-powersupply-state', $descr, NULL, $value, array('entPhysicalClass' => 'powerSupply'));
  }

  if ($entry['oaLdDevFANOperStatus'] != 'empty')
  {
    $descr = "Fan $index";
    $oid   = ".1.3.6.1.4.1.6926.1.41.1.10.3.2.1.5.$index";
    $value = $entry['oaLdDevFANOperStatus'];

    discover_sensor($valid['sensor'], 'state', $device, $oid, $index, 'oadwdm-fan-state', $descr, NULL, $value, array('entPhysicalClass' => 'fan'));
  }
}

// EOF
