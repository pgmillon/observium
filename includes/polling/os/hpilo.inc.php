<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage poller
 * @copyright  (C) 2006-2015 Adam Armstrong
 *
 */

if (preg_match('/Integrated Lights\-Out (\d+) ([\d\.]+)/', $poll_device['sysDescr'], $matches))
{
  // Integrated Lights-Out 4 2.03 Nov 07 2014
  $hardware = 'iLO '.$matches[1];
  $version  = $matches[2];
}

// EOF
