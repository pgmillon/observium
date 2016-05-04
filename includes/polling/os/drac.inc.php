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

// DELL-RAC-MIB::drsFirmwareVersion.0 = STRING: "1.23.23"
// DELL-RAC-MIB::drsProductShortName.0 = STRING: "iDRAC7"
// DELL-RAC-MIB::drsSystemServiceTag.0 = STRING: "CGJ2H5J"

$version  = trim(snmp_get($device, "drsFirmwareVersion.0",  "-OQv", "DELL-RAC-MIB"),'"');
$hardware = trim(snmp_get($device, "drsProductShortName.0", "-OQv", "DELL-RAC-MIB"),'"');
$serial   = trim(snmp_get($device, "drsSystemServiceTag.0", "-OQv", "DELL-RAC-MIB"),'"');

// DELL-RAC-MIB::drsProductURL.0 = STRING: "https://192.168.2.1:443"
$ra_url_http = snmp_get($device, "drsProductURL.0", "-Oqv", "DELL-RAC-MIB", mib_dirs('dell'));

if ($ra_url_http != '')
{
  set_dev_attrib($device, 'ra_url_http', $ra_url_http);
} else {
  // Not found in DELL-RAC-MIB, try getting from IDRAC-MIB-SMIv2 instead

  $ra_url_http = snmp_get($device, "racURL.0", "-Oqv", "IDRAC-MIB-SMIv2", mib_dirs('dell'));
  if ($ra_url_http != '')
  {
    set_dev_attrib($device, 'ra_url_http', $ra_url_http);
  } else {
    del_dev_attrib($device, 'ra_url_http');
  }
}
    
// EOF
