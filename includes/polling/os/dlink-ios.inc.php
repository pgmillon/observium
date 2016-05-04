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

//D-Link Internetwork Operating System Software 602LB Series Software, Version 1.0.7D (BASE), RELEASE SOFTWARE Copyright (c) 2002 D-Link Corporation. Compiled: 2007-01-29 18:32:26 by system, Image text-base: 0x10000 ROM: System Bootstrap, Version 0.8.2 Serial num:DP6D176000120, ID num:206308 System image file is "Router.bin" DI-602LB (RISC) 32768K bytes of memory,3584K bytes of flash
//D-Link £¨India£© Limited Internetwork Operating System Software 1705 Series Software, Version 1.0.7F (BASE), RELEASE SOFTWARE Copyright (c) 2007 by D-Link £¨India£© Limited Compiled: 2008-01-31 14:25:20 by system, Image text-base: 0x10000 ROM: System Bootstrap, Version 0.8.2 Serial num:000H682000022, ID num:009675 System image file is "Router.bin" DI-1705 (RISC) 32768K bytes of memory,3584K bytes of flash
//D-Link Internetwork Operating System Software 602MB+ Series Software, Version 5.0.0D (BASE), RELEASE SOFTWARE Copyright (c) 2007 D-Link Corporation. Compiled: 2008-01-31 14:25:02 by system, Image text-base: 0x10000 ROM: System Bootstrap, Version 0.4.5 Serial num:DP6E193000097, ID num:201077 System image file is "DI3605-5.0.0D.bin" DI-602MB+ (RISC) 131072K bytes of memory,16384K bytes of flash
if (preg_match('/(?<hardware2>[\w\d\.\+\-]+) Series Software, Version (?<version>[\w\d\.\+\-]+) .+?Version (?<version2>[\w\d\.\+\-]+)\sSerial num:(?<serial>[\d\w]+), .+System image file is "[^"]+"\s(?<hardware>[\w\d\.\+\-]+)/s', $poll_device['sysDescr'], $matches))
{
  $hardware = $matches['hardware'];
  $version  = $matches['version'];
  $serial   = $matches['serial'];
}

// EOF
