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

// SNMPv2-MIB::sysDescr.0 = STRING: BUFFALO TeraStation TS-XL Ver.1.62 (2013/11/18 14:18:07)
if (preg_match('/^(?:BUFFALO TeraStation\ )([\w-]+) (?:Ver.)([\d.]+)/', $poll_device['sysDescr'], $matches))
{
  $hardware = $matches[1]; // TS-XL
  $version = $matches[2]; // 1.62
}

// EOF
