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

// SysDescr.0 = STRING: "D-Link Firewall 2.27.06.10-19064"
if (preg_match('/(D-Link )?Firewall (?<version>[\d\.]+)/i', $poll_device['sysDescr'], $matches))
{
  $version = $matches['version'];
}
$hardware = rewrite_definition_hardware($device, $poll_device['sysObjectID']);

// EOF
