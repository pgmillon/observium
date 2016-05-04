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

if (preg_match('/^Meraki ([A-Z\-_0-9]+) (.*)/', $poll_device['sysDescr'], $regexp_result))
{
  $hardware = $regexp_result[1];
  $platform = $regexp_result[2];
}

if (($device['type'] == 'network' || $device['type'] == '') && strpos($platform, 'AP'))
{
  // Set type to wireless for APs
  $type = 'wireless';
  //$update_array['type'] = 'wireless';
  //log_event("type -> wireless", $device, 'device');
}

// EOF
