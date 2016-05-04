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

if (preg_match('/BDCOM(?:\(tm\)|.*Internetwork Operating System Software)? (?<hardware>(?:\w+ )?\S+) (?:Series )?Software, Version (?<version>\S+) (?:.*Serial num:(?<serial>\S+))?/', $poll_device['sysDescr'], $matches))
{
  //BDCOM(tm) S2228-POE Software, Version 2.0.2B Compiled: 2010-7-13 12:1:29 by WANGRENLEI ROM: System Bootstrap,Version 0.3.6,Serial num:2407xxxx
  //BDCOM(tm) 2605 Software, Version 5.0.1C (BASE) Copyright by Shanghai Baud Data Communication CO. LTD. Compiled: 2011-12-26 16:51:48 by SYS_1718, Image text-base: 0x10000 ROM: System Bootstrap, Version 0.4.7,Serial num:RU220xxx System image file is "Router
  //BDCOM Internetwork Operating System Software I-8006 Series Software, Version 5.1.1C (FULL), RELEASE SOFTWARE Copyright (c) 2010 by Shanghai Baud Data Communication CO.LTD Compiled: 2011-04-14 13:42:21 by SYS_1239, Image text-base: 0x108000 ROM: System Boo
  //BDCOM(tm) S3424F Software, Version 2.1.1A Build 13295 Compiled: 2013-6-5 17:37:3 by SYS ROM: System Bootstrap,Version 0.4.4,Serial num:3502xxxx
  //Techroutes-BDCOM Network Pvt. Ltd Internetwork Operating System Software TR 2611 Series Software, Version 1.3.3G (MIDDLE), RELEASE SOFTWARE Copyright (c) 2005 by Techroutes-BDCOM Network Pvt. Ltd Compiled: 2008-11-03 18:10:40 by system, Image text-base: 0
  //BDCOM(tm) 7208 Software, Version 3.0.0P (BASE) Copyright by Shanghai Baud Data Communication CO. LTD. Compiled: 2010-04-27 10:23:38 by system, Image text-base: 0x10000 ROM: System Bootstrap, Version 0.4.2,Serial num:RG000xxx System image file is "Router.b
  $hardware = $matches['hardware'];
  $version  = $matches['version'];
  $serial   = $matches['serial'];
}

// EOF
