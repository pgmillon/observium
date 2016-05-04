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

if (preg_match('/^RoomAlert(\d+\w)/', $poll_device['sysDescr'], $regexp_result))
{
  $hardware = "RoomAlert " . $regexp_result[1];
}

// EOF
