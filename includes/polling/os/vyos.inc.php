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

// Vyatta VyOS 1.1.3
// VyOS 1.2.0
if (preg_match('/VyOS (?<version>[\d\.]+)/', $poll_device['sysDescr'], $matches))
{
  $version = $matches['version'];
}

// EOF
