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

if (preg_match('/^RoomAlert(\d+\w)/', $poll_device['sysDescr'], $regexp_result))
{
  $hardware = "RoomAlert " . $regexp_result[1];
}

// EOF
