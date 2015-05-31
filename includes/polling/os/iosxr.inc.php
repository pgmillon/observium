<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage poller
 * @copyright  (C) 2006-2014 Adam Armstrong
 *
 */

if (preg_match('/^Cisco IOS XR Software \(Cisco ([^\)]+)\),\s+Version ([^\[]+)\[([^\]]+)\]/', $poll_device['sysDescr'], $regexp_result))
{
  $hardware = $regexp_result[1];
  $features = $regexp_result[3];
  $version = $regexp_result[2];
}
else
{
  # It is not an IOS-XR ... What should we do ?
}

// EOF
