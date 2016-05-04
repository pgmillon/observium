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

if (preg_match('/(.*) (.*) (.*)/', $poll_device['sysDescr'], $matches))
{
  // Netopia 4652 v8.8r8
  $version  = $matches[3];
  $hardware = 'Netopia ' . $matches[2];
}

// EOF


