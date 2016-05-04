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

// SNMPv2-MIB::sysDescr.0 = STRING: FB6202 Mercury (V1.34.000 2014-10-24T14:10:58)

if (preg_match('/^([\w]+) ([\w]+) \((.+)\ (.+)T(.+)\)/', $poll_device['sysDescr'], $matches))
{
  $hardware = $matches[1];
  $version  = $matches[3];
  $features = $matches[4];
}

// EOF
