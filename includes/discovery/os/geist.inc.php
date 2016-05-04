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

// First check the sysObjectID, then the sysDescr
if (strstr($sysObjectId, ".1.3.6.1.4.1.21239.2"))
{
  $os = 'geist-pdu'; // Default is PDU

  // Watchdog 1000
  if (strstr($sysDescr, "Watchdog")) { $os = "geist-watchdog"; }
}

// EOF
