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

if (preg_match('/(?:Digi )?(?<hardware>Connect.+?)(?:\(.+?\))?,? Version (?<version>\S+)/', $poll_device['sysDescr'], $matches))
{
  //Connect WAN 3G (MEI serial, Watchport sensor) Version 82002592_B1 05/07/2011
  //Connect WAN 3G (RS232 serial) Version 82001532_F3 03/16/2010
  //Connect WAN 3G (RS232 serial) Version ubuntu_tj 10/22/2012 16:32:14 PDT
  //Connect WAN 3G IA Verizon Version 82001912_C2_VZW_ESN_FIX 03/09/2010
  //Connect WAN 3G IA Version 82001912_G1 01/31/2012
  //Connect WAN 3G L (RS232 serial) Version 82002420_F1 08/23/2012
  //Connect WAN 3G Verizon Version 82001532_D1 12/12/2008
  //Connect WAN VPN GSM-R Version 82001662_G 03/22/2011
  //ConnectPort TS1 W RJ Version 82001772_B1 02/13/2008
  //ConnectPort X4 NEMA Version 82001536_N 08/30/2013
  //Digi Connect Device, Version Unknown
  //Digi Connect ME Version 82000856_F6 07/21/2006
  //Digi Connect N2S-170 Version 82001120_J1 04/21/2008
  //Digi Connect SP RS232 Version 82000908_H1 12/12/2006
  //Digi Connect WAN VPN Edge Version 82001253_G1 09/17/2008

  $hardware = $matches['hardware'];
  $version  = $matches['version'];
}

// EOF
