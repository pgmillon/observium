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

// Expert PDU Basic 8111
// Expert PDU energy 8182
// Expert PDU 8310

if (preg_match('/^Expert PDU (.*)/', $device['sysDescr'], $matches))
{
  $hardware = "Expert PDU " . ucfirst($matches[1]);
}

// EOF
