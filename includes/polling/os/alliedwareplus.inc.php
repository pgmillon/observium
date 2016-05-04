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

if (preg_match('/(?<hardware>AW\+) v(?<version>[\d\.\-]+)/', $poll_device['sysDescr'], $matches))
{
  // Allied Telesis router/switch, AW+ v5.3.4-0.2
  // Allied Telesis router/switch, AW+ v5.2.2-0.11
  $hardware = $matches['hardware'];
  $version  = $matches['version'];
} else {
  $hardware = 'AW+';
  $version  = snmp_get($device, "currSoftVersion.0", "-OsvQU", "AT-SETUP-MIB");
}

// EOF
