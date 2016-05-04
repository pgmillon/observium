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

#sysDescr.0 = STRING: APC Web/SNMP Management Card (MB:v3.9.2 PF:v3.7.4 PN:apc_hw02_aos_374.bin AF1:v3.7.4 AN1:apc_hw02_rpdu_374.bin MN:AP7920 HR:B2 SN: ZA0619025106 MD:05/08/2006)
#sysDescr.0 = STRING: APC Web/SNMP Management Card (MB:v3.8.6 PF:v3.5.7 PN:apc_hw03_aos_357.bin AF1:v3.5.6 AN1:apc_hw03_mem_356.bin MN:AP9340 HR:05 SN: ZA0723023958 MD:06/11/2007)
#sysDescr.0 = STRING: APC Environmental Manager (MB:v3.8.0 PF:v3.0.3 PN:apc_hw03_aos_303.bin AF1:v3.0.4 AN1:apc_hw03_mem_304.bin MN:AP9340 HR:05 SN: IA0711004586 MD:03/16/2007)
#sysDescr.0 = STRING: APC Web/SNMP Management Card (MB:v3.9.2 PF:v3.7.3 PN:apc_hw02_aos_373.bin AF1:v3.7.2 AN1:apc_hw02_sumx_372.bin MN:AP9617 HR:A10 SN: JA0412054242 MD:03/21/2004) (Embedded PowerNet SNMP Agent SW v2.2 compatible)

$apc_pattern = '/^APC .*\(MB:.* PF:(.*) PN:.* AF1:(.*) AN1:.* MN:(.*) HR:(.*) SN:(.*) MD:.*/';
if (preg_match($apc_pattern, $poll_device['sysDescr'], $matches) && stripos($poll_device['sysDescr'], 'Embedded') === FALSE)
{
  $version  = $matches[1];
  $features = 'App ' . $matches[2];
  $hardware = $matches[3] . ' ' . $matches[4];
  $serial   = trim($matches[5]);
} else {
  $apc_oids = array(
    'ups'     => array('serial' => "upsAdvIdentSerialNumber",        'model' => "upsBasicIdentModel",            'hwrev' => "upsAdvIdentFirmwareRevision",       'fwrev' => "upsAdvIdentFirmwareRevision"),      # UPS
    'ats'     => array('serial' => "atsIdentSerialNumber",           'model' => "atsIdentModelNumber",           'hwrev' => "atsIdentHardwareRev",               'fwrev' => "atsIdentFirmwareRev"),              # ATS
    'rPDU'    => array('serial' => "rPDUIdentSerialNumber",          'model' => "rPDUIdentModelNumber",          'hwrev' => "rPDUIdentHardwareRev",              'fwrev' => "rPDUIdentFirmwareRev"),             # PDU
    'rPDU2'   => array('serial' => "rPDU2IdentSerialNumber",         'model' => "rPDU2IdentModelNumber",         'hwrev' => "rPDU2IdentHardwareRev",             'fwrev' => "rPDU2IdentFirmwareRev"),            # PDU
    'sPDU'    => array('serial' => "sPDUIdentSerialNumber",          'model' => "sPDUIdentModelNumber",          'hwrev' => "sPDUIdentHardwareRev",              'fwrev' => "sPDUIdentFirmwareRev"),             # Masterswitch/AP9606
    'ems'     => array('serial' => "emsIdentSerialNumber",           'model' => "emsIdentProductNumber",         'hwrev' => "emsIdentHardwareRev",               'fwrev' => "emsIdentFirmwareRev"),              # NetBotz 200
    'airIRRC' => array('serial' => "airIRRCUnitIdentSerialNumber",   'model' => "airIRRCUnitIdentModelNumber",   'hwrev' => "airIRRCUnitIdentHardwareRevision",  'fwrev' => "airIRRCUnitIdentFirmwareRevision"), # In-Row Chiller
    'airPA'   => array('serial' => "airPASerialNumber",              'model' => "airPAModelNumber",              'hwrev' => "airPAHardwareRevision",             'fwrev' => "airPAFirmwareRevision"),            # A/C
    'xPDU'    => array('serial' => "xPDUIdentSerialNumber",          'model' => "xPDUIdentModelNumber",          'hwrev' => "xPDUIdentHardwareRev",              'fwrev' => "xPDUIdentFirmwareAppRev"),          # PDU
    'xATS'    => array('serial' => "xATSIdentSerialNumber",          'model' => "xATSIdentModelNumber",          'hwrev' => "xATSIdentHardwareRev",              'fwrev' => "xATSIdentFirmwareAppRev"),          # ATS
    'isx'     => array('serial' => "isxModularPduIdentSerialNumber", 'model' => "isxModularPduIdentModelNumber", 'hwrev' => "isxModularPduIdentMonitorCardHardwareRev", 'fwrev' => "isxModularPduIdentMonitorCardFirmwareAppRev"), # Modular PDU
  );

  // These oids are in APC's "experimental" tree, but there is no "real" UPS equivalent for the firmware versions.
  $AOSrev = trim(snmp_get($device, "1.3.6.1.4.1.318.1.4.2.4.1.4.1", "-OQv", "PowerNet-MIB", mib_dirs('apc')),'"');
  if ($AOSrev)
  {
    $APPrev = trim(snmp_get($device, "1.3.6.1.4.1.318.1.4.2.4.1.4.2", "-OQv", "PowerNet-MIB", mib_dirs('apc')),'"');
    $version  = $AOSrev;
    $features = "App $APPrev";
  }

  foreach ($apc_oids as $oid_list)
  {
    $serial = trim(snmp_get($device, $oid_list['serial'] . ".0", "-OQv", "PowerNet-MIB", mib_dirs('apc')),'"');

    if ($serial)
    {
      // If we can find the serial, we'll get the rest of the data too.

      $hardware = trim(trim(snmp_get($device, $oid_list['model'] . ".0", "-OQv", "PowerNet-MIB", mib_dirs('apc')),'"') . ' ' . trim(snmp_get($device, $oid_list['hwrev'] . ".0", "-OQv", "PowerNet-MIB", mib_dirs('apc')),'"'));
      if ($hardware == " ") { unset($hardware); }

      if (!$AOSrev) { $version = trim(snmp_get($device, $oid_list['fwrev'] . ".0", "-OQv", "PowerNet-MIB", mib_dirs('apc')),'"'); }

      break;
    }
  }
}

// EOF
