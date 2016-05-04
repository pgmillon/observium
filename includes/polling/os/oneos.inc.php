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

$type = 'voip';
if (preg_match('/^ONEOS\d+-(?<platform>[a-z]+)(?:_(?<features>\w+))?-V(?<version>[\w\.]+)/i', $poll_device['sysDescr'], $matches))
{
  //ONEOS1-ADVIP-V3.5R4E30
  //ONEOS1-ADVIP-V4.2R5E15_A22_Q12
  //ONEOS10-ADVIP-V4.3R6E20
  //ONEOS10-ADVIP_11N-V5.2R1C13
  //ONEOS11-ADVIP-V4.2R5E2_FT12_C
  //ONEOS6-ADVIP-V4.2R5E9
  //ONEOS6-ADVIP-V4.2R5E9_A07
  //ONEOS6-ADVIP_11N-V5.2R1C7_HG2
  //ONEOS92-MULTI_FT-V5.2R1C9_FT9
  //ONEOS1-VOIP-V3.5R4E30
  //ONEOS1-VOIP_H323-V3.7R11E15_V30I
  //ONEOS1-VOIP_SIP-V3.7R11E15_V28G
  //ONEOS10-VOIP_SIP-V4.3R6E21_HB1_T2
  //ONEOS10-VOIP_SIP_11N-V4.3R4E40_CP3
  //ONEOS4-VOIP_H323_FT-V3.7R11E20_FT10
  //ONEOS5-VOIP_H323-V4.3R4E18
  //ONEOS9-VOIP_SIP-V5.1R5E18_HA1
  //ONEOS90-VOIP_SIP_11N_FT-V4.3R4E34_FT8

  if ($matches['platform'] == 'ADVIP' || $matches['platform'] == 'MULTI')
  {
    $type = 'network';
  }
  $features = $matches['features'];
  $version  = $matches['version'];
}

$data = snmp_get_multi($device, 'oacSysIMSysMainIdentifier.0 oacExpIMSysHwcSerialNumber.0', '-OQUs', 'ONEACCESS-SYS-MIB');
if (isset($data[0]))
{
  //var_dump($data[0]);
  $hardware = str_replace('oac', '', $data[0]['oacSysIMSysMainIdentifier']);
  $serial   = $data[0]['oacExpIMSysHwcSerialNumber'];
}

unset($data);

// EOF
