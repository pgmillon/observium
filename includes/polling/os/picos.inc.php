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

if (preg_match('/Pica8 .+Software version ([\d\.]+).+Hardware model ([\w\-]+)/s', $poll_device['sysDescr'], $matches))
{
  $version  = $matches[1];
  $hardware = $matches[2];
}

// EOF
