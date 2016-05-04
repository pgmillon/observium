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

echo(" OG-STATUS-MIB ");

$value = snmp_get($device, "ogEmdStatusTemp.1", "-Oqv", "OG-STATUS-MIB", mib_dirs('opengear'));

if (is_numeric($value) && $value > 0)
{
  $descr = "Internal environmental sensor";
  $oid = ".1.3.6.1.4.1.25049.16.4.1.3.1";
  discover_sensor($valid['sensor'], 'temperature', $device, $oid, "ogEmdStatusTemp", 'og-status-mib', $descr, 1, $value);
}

$value = snmp_get($device, "ogEmdStatusHumidity.1", "-Oqv", "OG-STATUS-MIB", mib_dirs('opengear'));

if (is_numeric($value) && $value > 0)
{
  $descr = "Internal environmental sensor";
  $oid = ".1.3.6.1.4.1.25049.16.4.1.4.1";
  discover_sensor($valid['sensor'], 'humidity', $device, $oid, "ogEmdStatusHumidity", 'og-status-mib', $descr, 1, $value);
}

// EOF
