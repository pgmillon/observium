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

// SNMPv2-MIB::sysDescr.0 Blue Coat AV810 Series, ProxyAV Version: 3.5.2.1, Release id: 145195

  $sysdescr = snmp_get($device, "sysDescr.0", "-Osqv", "SNMPv2-MIB");

  $tmp = explode(",", $sysdescr);

  $hardware = trim($tmp[0]);
  $version = trim($tmp[1]);
  $features = trim($tmp[2]);

  // This should probably move somewhere better
  echo("BLUECOAT-HOST-RESOURCES-MIB");

  $serial = trim(snmp_get($device, "bchrSerial.0", "-OQv", "BLUECOAT-HOST-RESOURCES-MIB", mib_dirs('bluecoat')),'" ');

// EOF
