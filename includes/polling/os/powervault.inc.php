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

// Firmware level (for TL2000/TL4000)
$version = snmp_get($device, "libraryFwLevel.1", "-OQv", "DELL-TL4000-MIB", mib_dirs('dell'));

// Remote access URL for TL2000/TL4000
$ra_url_http = snmp_get($device, "TL4000IdURL.0", "-Oqv", "DELL-TL4000-MIB", mib_dirs('dell'));

if ($ra_url_http != '')
{
  set_dev_attrib($device, 'ra_url_http', $ra_url_http);
} else {
  del_dev_attrib($device, 'ra_url_http');
}

// EOF
