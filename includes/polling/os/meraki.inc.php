<?php

if (preg_match('/^Meraki ([A-Z\-_0-9]+) (.*)/', $poll_device['sysDescr'], $regexp_result))
{
  $hardware = $regexp_result[1];
  $platform = $regexp_result[2];
}

if (($device['type'] == 'network' || $device['type'] == '') && strpos($platform, 'AP'))
{
  // Set type to wireless for APs
  $update_array['type'] = 'wireless';
  log_event("type -> wireless", $device, 'system');
}

// EOF

