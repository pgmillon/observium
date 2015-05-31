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

if (preg_match('/Pica8 .+Software version ([\d\.]+).+Hardware model ([\w\-]+)/s', $poll_device['sysDescr'], $matches))
{
  $version  = $matches[1];
  $hardware = $matches[2];
}

// EOF
