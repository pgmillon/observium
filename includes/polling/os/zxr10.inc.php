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

if(preg_match('/^ZXR10 ROS Version V4/', $poll_device['sysDescr'])) {
 // ROS4

 $explode = explode(',', $poll_device['sysDescr']);

 $patch_version = preg_replace('/ Copyright(.*)/', '', $explode[1]);
 $version = $explode[0] . $patch_version;

 preg_match('/Version V(\S+) (.+) Software,/', $poll_device['sysDescr'], $matches);

 $hardware = $matches[2];
}
elseif(preg_match('/^ZTE ZXR10/', $poll_device['sysDescr'])) {
 // ROS5, 89E switch
 preg_match('/^ZTE ZXR10 (.+) Software, 8900&8900E Version: (.+),/', $poll_device['sysDescr'], $matches);

 $hardware = "ZXR10 " . $matches[1];
 $version = $matches[2];
}
else {
 // ROS5
 preg_match('/^ZXR10 (.+), ZTE ZXR10 Software Version: (.+)/', $poll_device['sysDescr'], $matches);

 $hardware = "ZXR10 " . $matches[1];
 $version = $matches[2];
}

// EOF
