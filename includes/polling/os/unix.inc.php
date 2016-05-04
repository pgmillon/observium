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

switch ($device['os'])
{
  case 'linux':
  case 'endian':
  case 'openwrt':
  case 'ddwrt':
    list(,,$version) = explode (" ", $poll_device['sysDescr']);

    $kernel = $version;

    // Use agent DMI data if available
    if (isset($agent_data['dmi']))
    {
      if ($agent_data['dmi']['system-product-name'])
      {
        $hw = ($agent_data['dmi']['system-manufacturer'] ? $agent_data['dmi']['system-manufacturer'] . ' ' : '') . $agent_data['dmi']['system-product-name'];

        // Clean up "Dell Computer Corporation" and "Intel Corporation"
        $hw = str_replace(" Computer Corporation", "", $hw);
        $hw = str_replace(" Corporation", "", $hw);
      }

      // If these exclude lists grow any further we should move them to definitions...
      if (isset($agent_data['dmi']['system-serial-number'])
        && $agent_data['dmi']['system-serial-number'] != '............'
        && $agent_data['dmi']['system-serial-number'] != 'Not Specified'
        && $agent_data['dmi']['system-serial-number'] != '0123456789')
      {
        $serial = $agent_data['dmi']['system-serial-number'];
      }

      if (isset($agent_data['dmi']['chassis-asset-tag'])
        && $agent_data['dmi']['chassis-asset-tag'] != '....................'
        && strcasecmp($agent_data['dmi']['chassis-asset-tag'], 'To be filled by O.E.M.') != 0
        && $agent_data['dmi']['chassis-asset-tag'] != 'No Asset Tag')
      {
        $asset_tag = $agent_data['dmi']['chassis-asset-tag'];
      }
      elseif (isset($agent_data['dmi']['baseboard-asset-tag'])
        && $agent_data['dmi']['baseboard-asset-tag'] != '....................'
        && strcasecmp($agent_data['dmi']['baseboard-asset-tag'], 'To be filled by O.E.M.') != 0
        && $agent_data['dmi']['baseboard-asset-tag'] != 'Tag 12345')
      {
        $asset_tag = $agent_data['dmi']['baseboard-asset-tag'];
      }
    }

    // Use agent virt-what data if available
    if (isset($agent_data['virt']['what']))
    {
      if (isset($config['virt-what'][$agent_data['virt']['what']]))
      {
        // We cycle through every line here, the previous one is overwritten.
        // This is OK, as virt-what prints general-to-specific order and we want most specific.
        foreach (explode("\n", $agent_data['virt']['what']) as $virtwhat)
        {
          $hw = $config['virt-what'][$virtwhat];
        }
      }
    }

    if (is_array($entPhysical) && !$hw)
    {
      $hw = $entPhysical['entPhysicalDescr'];
      if (!empty($entPhysical['entPhysicalSerialNum']))
      {
        $serial = $entPhysical['entPhysicalSerialNum'];
      }
    }

    if (!$hw)
    {
      // Detect Dell hardware via OpenManage SNMP
      $hw = trim(snmp_get($device, "chassisModelName.1", "-Oqv", "MIB-Dell-10892", mib_dirs('dell')),'" ');

      if ($hw)
      {
        $hw        = "Dell " . $hw;
        $serial    = trim(snmp_get($device, "chassisServiceTagName.1", "-Oqv", "MIB-Dell-10892", mib_dirs('dell')),'" ');
        $asset_tag = trim(snmp_get($device, "chassisAssetTagName.1", "-Oqv", "MIB-Dell-10892", mib_dirs('dell')),'" ');
      }
    }

    if (!$hw)
    {
      // Detect HP hardware via hp-snmp-agents
      $hw = trim(snmp_get($device, "cpqSiProductName.0", "-Oqv", "CPQSINFO-MIB", mib_dirs('hp')),'" ');

      if ($hw)
      {
        $hw        = "HP " . $hw;
        $serial    = trim(snmp_get($device, "cpqSiSysSerialNum.0", "-Oqv", "CPQSINFO-MIB", mib_dirs('hp')),'" ');
        $asset_tag = trim(snmp_get($device, "cpqSiAssetTag.0", "-Oqv", "CPQSINFO-MIB", mib_dirs('hp')),'" ');
      }
    }

    $hardware = rewrite_unix_hardware($poll_device['sysDescr'], $hw);
    break;

  case 'aix':
    list($hardware,,$os_detail,) = explode("\n", $poll_device['sysDescr']);
    if (preg_match('/: 0*(\d+\.)0*(\d+\.)0*(\d+\.)(\d+)/', $os_detail, $matches))
    {
      // Base Operating System Runtime AIX version: 05.03.0012.0001
      $version = $matches[1] . $matches[2] . $matches[3] . $matches[4];
    }

    $hardware_model = snmp_get($device, "aixSeMachineType.0", "-Oqv", "IBM-AIX-MIB");
    if ($hardware_model)
    {
      $hardware_model = trim(str_replace("\"", "", $hardware_model));
      list(,$hardware_model) = explode(",", $hardware_model);

      $serial = snmp_get($device, "aixSeSerialNumber.0", "-Oqv", "IBM-AIX-MIB");
      $serial = trim(str_replace("\"", "", $serial));
      list(,$serial) = explode(",", $serial);

      $hardware .= " ($hardware_model)";
    }
    break;

  case 'freebsd':
    preg_match('/FreeBSD ([\d\.]+-[\w\d-]+)/i', $poll_device['sysDescr'], $matches);
    $kernel = $matches[1];
    $hardware = rewrite_unix_hardware($poll_device['sysDescr']);
    break;

  case 'dragonfly':
    list(,,$version,,,$features) = explode (" ", $poll_device['sysDescr']);
    $hardware = rewrite_unix_hardware($poll_device['sysDescr']);
    break;

  case 'netbsd':
    list(,,$version,,,$features) = explode (" ", $poll_device['sysDescr']);
    $features = str_replace("(", "", $features);
    $features = str_replace(")", "", $features);
    $hardware = rewrite_unix_hardware($poll_device['sysDescr']);
    break;

  case 'openbsd':
  case 'solaris':
  case 'opensolaris':
    list(,,$version,$features) = explode (" ", $poll_device['sysDescr']);
    $features = str_replace("(", "", $features);
    $features = str_replace(")", "", $features);
    $hardware = rewrite_unix_hardware($poll_device['sysDescr']);
    break;

  case 'darwin':
    list(,,$version) = explode (" ", $poll_device['sysDescr']);
    $hardware = rewrite_unix_hardware($poll_device['sysDescr']);
    break;

  case 'monowall':
  case 'pfsense':
    list(,,$version,,, $kernel) = explode(" ", $poll_device['sysDescr']);
    $distro = $device['os'];
    $hardware = rewrite_unix_hardware($poll_device['sysDescr']);
    break;

  case 'freenas':
  case 'nas4free':
    preg_match('/Software: FreeBSD ([\d\.]+-[\w\d-]+)/i', $poll_device['sysDescr'], $matches);
    $version = $matches[1];
    $hardware = rewrite_unix_hardware($poll_device['sysDescr']);
    break;

  case 'qnap':
    $hardware = $entPhysical['entPhysicalName'];
    $version  = $entPhysical['entPhysicalFirmwareRev'];
    $serial   = $entPhysical['entPhysicalSerialNum'];
    break;

  case 'ipso':
    // IPSO Bastion-1 6.2-GA039 releng 1 04.14.2010-225515 i386
    // IP530 rev 00, IPSO ruby.infinity-insurance.com 3.9-BUILD035 releng 1515 05.24.2005-013334 i386
    if (preg_match('/IPSO [^ ]+ ([^ ]+) /', $poll_device['sysDescr'], $matches))
    {
      $version = $matches[1];
    }

    $data = snmp_get_multi($device, 'ipsoChassisMBType.0 ipsoChassisMBRevNumber.0 ipsoChassisSerialNumber.0', '-OQUs', 'NOKIA-IPSO-SYSTEM-MIB', mib_dirs('checkpoint'));
    if (isset($data[0]))
    {
      $hw = $data[0]['ipsoChassisMBType'] . ' rev ' . $data[0]['ipsoChassisMBRevNumber'];
      $serial = $data[0]['ipsoChassisSerialNumber'];
    }
    $hardware = rewrite_unix_hardware($poll_device['sysDescr'], $hw);
    break;

  case 'sofaware':
    // EMBEDDED-NGX-MIB::swHardwareVersion.0 = "1.3T ADSL-A"
    // EMBEDDED-NGX-MIB::swHardwareType.0 = "SBox-200-B"
    // EMBEDDED-NGX-MIB::swLicenseProductName.0 = "Safe@Office 500, 25 nodes"
    // EMBEDDED-NGX-MIB::swFirmwareRunning.0 = "8.2.26x"
    $data = snmp_get_multi($device, 'swHardwareVersion.0 swHardwareType.0 swLicenseProductName.0 swFirmwareRunning.0', '-OQUs', 'EMBEDDED-NGX-MIB', mib_dirs('checkpoint'));
    if (isset($data[0]))
    {
      list($hw) = explode(',', trim($data[0]['swLicenseProductName'], '" '));
      $hardware = $hw . ' ' . trim($data[0]['swHardwareType'], '" ') . ' ' . trim($data[0]['swHardwareVersion'], '" ');
      $version  = trim($data[0]['swFirmwareRunning'], '" ');
    }
    break;
}

// Has 'distro' script data already been returned via the agent?
if (isset($agent_data['distro']) && isset($agent_data['distro']['SCRIPTVER']))
{
  $distro     = $agent_data['distro']['DISTRO'];
  $distro_ver = $agent_data['distro']['DISTROVER'];
  $kernel     = $agent_data['distro']['KERNEL'];
  $arch       = $agent_data['distro']['ARCH'];
} else {

  // Distro "extend" support
  $os_data = trim(snmp_get($device, ".1.3.6.1.4.1.2021.7890.1.3.1.1.6.100.105.115.116.114.111", "-Oqv", "UCD-SNMP-MIB", mib_dirs()),'" ');

  if (!$os_data) // No "extend" support, try "exec" support
  {
    $os_data = trim(snmp_get($device, ".1.3.6.1.4.1.2021.7890.1.101.1", "-Oqv", "UCD-SNMP-MIB", mib_dirs()),'" ');
  }

  // Disregard data if we're just getting an error.
  if (!$os_data || strpos($os_data, "/usr/bin/distro") !== FALSE)
  {
    unset($os_data);
  }
  else if (strpos($os_data, '|'))
  {
    // "Linux|3.2.0-4-amd64|amd64|Debian|7.5"
    list($osname,$kernel,$arch,$distro,$distro_ver) = explode('|', $os_data, 5);
  } else {
    // Old distro, not supported now: "Ubuntu 12.04"
    list($distro, $distro_ver) = explode(" ", $os_data);
  }
}

if (!$features && isset($distro))
{
  $features = "$distro $distro_ver";
}

unset($hw, $data);

// EOF
