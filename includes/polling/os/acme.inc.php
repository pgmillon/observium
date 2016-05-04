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

if (preg_match('/ (S|E|M|D|L)CX?\d+.*/', $poll_device['sysDescr'], $regexp_result))
{
  $version = trim($regexp_result[0]);
}

$hardware = $entPhysical['entPhysicalDescr'];
if (preg_match('/ (\D+\d+)/', $hardware, $regexp_result))
{
  $hardware = trim($regexp_result[1]);
}

$serial = $entPhysical['entPhysicalSerialNum'];

// EOF
