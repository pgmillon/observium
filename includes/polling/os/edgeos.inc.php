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

if (preg_match("/^EdgeOS/", $poll_device['sysDescr']))
{
  $version = $poll_device['sysDescr'];
  $version = preg_replace("/^EdgeOS/", "", $version);
}

// EOF
