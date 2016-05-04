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

if (preg_match('/^([\w ]+) - (Software Version) (.+)/', $poll_device['sysDescr'], $matches))
{
  $hardware = $matches[1];
  $version  = $matches[3];
}

// EOF
