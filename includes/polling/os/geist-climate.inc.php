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

$version  = snmp_get($device, "productVersion.0", "-Ovq", "GEIST-V4-MIB", mib_dirs('geist'));
$hardware = "Geist " . snmp_get($device, "productTitle.0", "-Ovq", "GEIST-V4-MIB", mib_dirs('geist'));

$ra_url_http = snmp_get($device, "productUrl.0", "-Ovq", "GEIST-V4-MIB", mib_dirs('geist'));

// Can be either STRING or IpAddress, check for leading http://
if (substr($ra_url_http,0,7) != 'http://') { $ra_url_http = "http://$ra_url_http"; }

if ($ra_url_http != '')
{
  set_dev_attrib($device, 'ra_url_http', $ra_url_http);
} else {
  del_dev_attrib($device, 'ra_url_http');
}

// EOF
