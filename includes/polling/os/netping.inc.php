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

if (preg_match('/(?<hw1>UniPing|NetPing) (?<hw2>.*?), FW v(?<version>\d[\w\-\.]+)/', $poll_device['sysDescr'], $matches))
{
  // UniPing Server Solution, FW v50.11.7.A-10
  // UniPing Server Solution v3/SMS, FW v70.5.2.E-1
  // UniPing v3, FW v60.3.6.A-1
  // NetPing 8/PWRv3/SMS, FW v48.4.5.A-1
  $version  = $matches['version'];
  $hardware = $matches['hw1'] . ' ' . $matches['hw2'];
}

// EOF