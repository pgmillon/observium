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

// Vyatta
// Vyatta unknown-version
// Vyatta VC6.2-2011.02.09
// Vyatta Vyatta Core 6.0 Beta 2010.02.19
if (preg_match('/Vyatta (?:[a-z ]+)(?<version>[\d\.]+)/i', $poll_device['sysDescr'], $matches))
{
  $version = $matches['version'];
}

// EOF
