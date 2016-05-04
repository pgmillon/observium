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

if (preg_match('/(?:ELTEK )?(?<hardware>.+?)\([\d\.]+\).+?OS:(?<version>.+)/', $poll_device['sysDescr'], $matches))
{
  //Theia(405015.009) Rev: 1.01a, Oct 23 2012 OS:2.5.2
  //WebPower(402414.003) 4.7, May 25 2011 OS:2.5.2
  //ELTEK Webpower(402411.003) Rev4.2, Apr 22 2008 OS:1.99
  //ComPack(405002.009) Rev: 1.05, Jul 6 2011 OS:2.5.2
  //WebPower(402414.003) Rev4.5,Jul 9 2010 OS:2.4 RC2
  $hardware = $matches['hardware'];
  $version  = $matches['version'];
}

$serial = snmp_get($device, ".1.3.6.1.4.1.12148.9.2.1.3.7.0", "-Oqv", "ELTEK-DISTRIBUTED-MIB", mib_dirs('eltek'));

// EOF
