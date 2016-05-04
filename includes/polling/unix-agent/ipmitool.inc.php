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

global $agent_sensors;

if ($agent_data['ipmitool']['sensor'] != '|')
{
  echo "IPMI: ";

  // Parse function returns array, don't overwrite this array, other agent modules could also have filled this out, so merge!
  // I'm not sure about having this code here. We should pass this array to a function that puts it in the right place and merges.
  $agent_sensors = array_merge_recursive($agent_sensors,parse_ipmitool_sensor($device, $agent_data['ipmitool']['sensor'], 'agent'));
}

// EOF
