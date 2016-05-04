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

// Vyatta
// Vyatta unknown-version
// Vyatta VC6.2-2011.02.09
// Vyatta Vyatta Core 6.0 Beta 2010.02.19
if (preg_match('/Vyatta (?:[a-z ]+)(?<version>[\d\.]+)/i', $poll_device['sysDescr'], $matches))
{
  $version = $matches['version'];
}

// EOF
