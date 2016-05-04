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

include_once($GLOBALS['config']['install_dir'] . "/includes/wmi.inc.php");

if ($device['os'] == "windows")
{
  $wmi = array();

  echo("WMI Poller:\n");

  $wmi_attribs = array();
  //foreach (dbFetchRows("SELECT * FROM devices_attribs WHERE `device_id` = ? AND `attrib_type` LIKE 'wmi%'", array($device['device_id'])) as $entry)
  foreach (get_entity_attribs('device', $device['device_id']) as $attrib => $entry)
  {
    if (strpos($attrib, 'wmi_') === 0)
    {
      $wmi_attribs[$attrib] = $entry;
    }
  }

  foreach ($GLOBALS['config']['wmi']['modules'] as $module => $module_status)
  {
    if (!array_key_exists("wmi_poll_".$module, $wmi_attribs))
    {
      $wmi_attribs['wmi_poll_'.$module] = $module_status;
    }
  }

  if ($wmi_attribs['wmi_override'])
  {
    $override = array(
      "hostname" => $wmi_attribs['wmi_hostname'],
      "domain"   => $wmi_attribs['wmi_domain'],
      "username" => $wmi_attribs['wmi_username'],
      "password" => $wmi_attribs['wmi_password']
    );
  }

// Computer Name - This is set for WMI classes that need a non-FQDN hostname

  $wql = "SELECT Name FROM Win32_ComputerSystem";
  $wmi['computer_name'] = wmi_parse(wmi_query($wql, $override), TRUE, "Name");

// Operating System - Updates device info to exact OS version installed

  if ($wmi_attribs['wmi_poll_os'])
  {
    $wql = "SELECT * FROM Win32_OperatingSystem";
    $wmi['os'] = wmi_parse(wmi_query($wql, $override), TRUE);

    if ($wmi['os'])
    {
      include($GLOBALS['config']['install_dir'] . "/includes/polling/os/wmi.inc.php");
    }
  }

// Processors - Fixes "Unknown Processor Type" and "Intel" values

  if ($wmi_attribs['wmi_poll_processors'])
  {
    $wql = "SELECT * FROM Win32_Processor";
    $wmi['processors'] = wmi_parse(wmi_query($wql, $override));

    if ($wmi['processors'])
    {
      include($GLOBALS['config']['install_dir'] . "/includes/polling/processors/wmi.inc.php");
    }
  }

// Logical Disks

  if ($wmi_attribs['wmi_poll_storage'])
  {
    $wql = "SELECT * FROM Win32_LogicalDisk WHERE Description='Local Fixed Disk'";
    $wmi['disk']['logical'] = wmi_parse(wmi_query($wql, $override));

    if ($wmi['disk']['logical'])
    {
      include($GLOBALS['config']['install_dir'] . "/includes/polling/storage/wmi.inc.php");
    }
  }

// Microsoft Exchange

  if ($wmi_attribs['wmi_poll_exchange'])
  {
    $wql = "SELECT Name FROM Win32_Service WHERE Name LIKE '%MSExchange%'";
    $wmi['exchange']['services'] = wmi_parse(wmi_query($wql, $override), TRUE);

    if ($wmi['exchange']['services'])
    {
      include($GLOBALS['config']['install_dir'] . "/includes/polling/applications/exchange.inc.php");
    }
  }

// Microsoft SQL Server

  if ($wmi_attribs['wmi_poll_mssql'])
  {
    $wql = "SELECT Name, ProcessId FROM Win32_Service WHERE Name LIKE '%MSSQL$%' OR Name = 'MSSQLSERVER'";
    $wmi['mssql']['services'] = wmi_parse(wmi_query($wql, $override));

    if ($wmi['mssql']['services'])
    {
      include($GLOBALS['config']['install_dir'] . "/includes/polling/applications/mssql.inc.php");
    }
  }

  unset($wmi);
}

// EOF
