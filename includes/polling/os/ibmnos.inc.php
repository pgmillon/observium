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

if (preg_match('/^(?:IBM )?(?:Blade Network Technologies|Networking Operating System) (?<hardware>.+)/', $poll_device['sysDescr'], $matches))
{
  $hardware = $matches['hardware'];
}
else if ($entPhysical['entPhysicalName'])
{
  $hardware = $entPhysical['entPhysicalName'];
}
if ($entPhysical['entPhysicalSoftwareRev'])
{
  $version = $entPhysical['entPhysicalSoftwareRev'];
}
if ($entPhysical['entPhysicalSerialNum'])
{
  $serial = $entPhysical['entPhysicalSerialNum'];
}

// EOF
