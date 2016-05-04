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

// Vyatta VyOS 1.1.3
// VyOS 1.2.0
if (preg_match('/VyOS (?<version>[\d\.]+)/', $poll_device['sysDescr'], $matches))
{
  $version = $matches['version'];
}

// EOF
