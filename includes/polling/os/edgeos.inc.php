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

if (preg_match("/^EdgeOS/", $poll_device['sysDescr']))
{
  $version = $poll_device['sysDescr'];
  $version = preg_replace("/^EdgeOS/", "", $version);
}

// EOF
