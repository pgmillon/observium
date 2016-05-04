<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage discovery
 * @copyright  (C) 2006-2013 Adam Armstrong, (C) 2013-2016 Observium Limited
 *
 */

if (!$os && ($sysObjectId == '.1.3.6.1.4.1.16972' || strpos($sysObjectId, '.1.3.6.1.4.1.1.2.3.4.5') === 0)) // Too many vendors/devices
{
  if (preg_match('/^\d+\.\d+\.\d+(?: \d\.\d+ v[\w\.]+)? Build \d+ Rel\.\d+\w?/', $sysDescr))
  {
    //1.1.1 Build 140815 Rel.40202
    //1.0.1 Build 131031 Rel.37305
    //0.9.1 0.1 v0041.0 Build 140905 Rel.30877n
    //0.7.0 0.18 v0007.0 Build 130114 Rel.62291n
    //0.6.0 2.13 v000c.0 Build 140919 Rel.52310n
    $os = 'tplink-adsl';
  }
  else if (preg_match('/^TD-[a-z]?\d{4}\w*/i', $sysDescr))
  {
    //TD-8817
    //TD-W8901G
    //TD-W8951ND
    //TD-8816 1.0
    //TD-W8901G 3.0
    $os = 'tplink-adsl';
  }
  else if (preg_match('/^\d{6}_\d{4}-[\d\.]+\w\.\d+\.wp\d\.\w+\.\w+/', $sysDescr))
  {
    //110118_1917-4.02L.03.wp1.A2pB025k.d21j2
    //100826_0217-4.02L.03.wp1.A2pB025k.d21j2
    //100702_1223-4.02L.03.wp1.A2pB025k.d21j2
    $os = 'innacomm';
  }
  //DSL_2500E  - see dlink-generic os definition
  //GE_1.07    - see dlink-generic os definition
  //ZXV10 W300 - see zxv10 os definition

  // FIXME, unknown sysDescr:
  //ADSL SoHo Router
  //ADSL Modem/Route
  //.. too many, really
  //SmartAX
  //Sterlite Router
  //Sweex MO300
  //Wireless ADSL Mo
  //Wireless-N 150M
}

// EOF
