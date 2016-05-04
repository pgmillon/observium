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

if (preg_match('/^Expert Power Control( NET 8x)? ([0-9\/]+)/', $device['sysDescr'], $matches))
{
  $hardware = "Expert Power Control " . $matches[2];
} elseif (preg_match('/^Expert Power Control NET (.*)/', $device['sysDescr'], $matches))
{
  $hardware = "Expert Power Control " . $matches[1];
} elseif (preg_match('/^Expert Power Control (.*)/', $device['sysDescr'], $matches))
{
  $hardware = "Expert Power Control " . $matches[1];
}

// EOF
