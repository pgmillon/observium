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

if (preg_match('/Dell Networking (?<hardware>N\d\w+), (?<version>[\d\.\-]+)/', $poll_device['sysDescr'], $matches))
{
  // Dell Networking N1524, 6.2.7.2, Linux 3.6.5
  // Dell Networking N4064, 6.1.2.4, Linux 2.6.32.9
  // Dell Networking N4064F, 6.1.2.4, Linux 2.6.32.9
  // Dell Networking N3048, 6.2.0.5, Linux 3.6.5-1289203e

  $hardware = $matches['hardware'];
  $version  = $matches['version'];
} else {
  // FIXME. Use snmp here, but in most cases same detected by sysDescr
}

unset($matches);

// EOF
