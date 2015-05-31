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

preg_match('/Blade Network Technologies (.*)$/', $poll_device['sysDescr'], $store);

if (isset($store[1]))
{
  $hardware = $store[1];
}

// EOF
