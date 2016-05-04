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

if (preg_match('/Mellanox (?<hardware>\w+),MLNX-OS,SWv[^\d]*(?<version>[\d\.\-]+)/', $poll_device['sysDescr'], $matches))
{
  // Mellanox SX6036,MLNX-OS,SWvSX_3.3.5200
  // Mellanox SX1036,MLNX-OS,SWvSX_3.4.0000
  // Mellanox SX1012,MLNX-OS,SWvSX_3.4.0012
  // Mellanox SX1036,MLNX-OS,SWv3.4.1100

  $hardware = $matches['hardware'];
  $version  = $matches['version'];
}
else if (preg_match('/Linux .*? (?<kernel>[\d\.]+-MELLANOXuni-\w+) EFM_(?<arch>[^_]+)_(?<hardware>\w+) EFM_(?<version>[\d\.]+)/', $poll_device['sysDescr'], $matches))
{
  // Linux switch-63014c 2.6.27-MELLANOXuni-m405ex EFM_PPC_M405EX EFM_1.1.3000 #1 2013-07-08 14:29:44 ppc
  // Linux c2-ibsw1 2.6.27-MELLANOXuni-m460ex EFM_PPC_M460EX EFM_1.1.2500 #1 2011-02-22 15:51:54 ppc

  //$hardware = $matches['hardware'];
  $hardware = "IS50XX"; // FIXME. Required devices for tests
  $version  = $matches['version'];
  $kernel   = $matches['kernel'];
  $arch     = $matches['arch'];
} else {
  // FIXME. Use snmp here
}

unset($matches);

// EOF
