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

if ($entPhysical['entPhysicalDescr'] && $entPhysical['entPhysicalName'] && $entPhysical['entPhysicalSoftwareRev'])
{
  $hardware = $entPhysical['entPhysicalDescr'] . ' ' . $entPhysical['entPhysicalName'];
  if (preg_match('/Version ([0-9\.]+)/', $entPhysical['entPhysicalSoftwareRev'], $matches))
  {
    $version = $matches[1];
  }
  $serial   = $entPhysical['entPhysicalSerialNum'];
  return;
}

$lines = preg_split('/\r\n|\r|\n/', $poll_device['sysDescr']);

if (count($lines) == 2)
{
  if (preg_match('/^(?:HP )?(.*?)(?: \(\w+\))? Switch Software Version ([0-9\.]+), Release ([0-9P]+)/', $lines[0], $matches))
  {
    # HP A5120-48G SI Switch Software Version 5.20, Release 1505P07
    # 1920-24G-PoE+ (370W) Switch Software Version 5.20.99, Release 1105 Copyright(c)2010-2014 Hewlett-Packard Development Company, L.P.
    $hardware = "HP " . $matches[1];
    $version = $matches[2]; // . " " . $matches[3];
  }
  elseif (preg_match('/^H3C Switch (.*) Software Version ([0-9\.]+), Release ([0-9P]+)/', $lines[0], $matches))
  {
    #  H3C Switch S5120-52P-SI Software Version 5.20, Release 1505P01
    $hardware = "H3C " . $matches[1];
    $version = $matches[2]; // . " " . $matches[3];
  }
}
else if (count($lines) == 3)
{
  list(,,,,,,$version,,$release) = explode(" ", $lines[0]);
  $version = rtrim($version, ',') . " " . $release;
  $hardware = $lines[1];
}

$serial = snmp_get($device, "hh3cEntityExtManuSerialNum.1", "-Oqv", "HH3C-ENTITY-EXT-MIB", mib_dirs('hh3c'));

// EOF
