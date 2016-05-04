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

// SNMPv2-MIB::sysDescr.0 = STRING: Peakflow SP 6.0 (build DIXB-B)  System Board Model: SE7520JR23S Serial Number: AZLR6340402

if (preg_match('/(?<hw1>[\w ]+?) +(?<version>[\d\.]+) +\(build .+?\) +System Board Model: +(?<hw2>\w+) +Serial Number: +(?<serial>\w+)/', $poll_device['sysDescr'], $matches))
{
  $hardware = $matches['hw1'].' ('.$matches['hw2'].')';
  $version  = $matches['version'];
  $serial   = $matches['serial'];
}

unset($matches);

// EOF
