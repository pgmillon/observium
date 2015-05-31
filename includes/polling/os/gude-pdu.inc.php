<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage poller
 * @copyright  (C) 2006-2014 Adam Armstrong
 *
 */

// Expert PDU Basic 8111
// Expert PDU energy 8182
// Expert PDU 8310

if (preg_match('/^Expert PDU (.*)/', $device['sysDescr'], $matches))
{
  $hardware = "Expert PDU " . ucfirst($matches[1]);
}

// EOF
