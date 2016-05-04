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

$manufacturer = trim(snmp_get($device, "upsIdentManufacturer.0", "-OQv", "UPS-MIB"),'"');
if ($GLOBALS['snmp_status'])
{
  $version  = trim(snmp_get($device, "upsIdentUPSSoftwareVersion.0", "-OQv", "UPS-MIB"),'"');
  $model    = trim(snmp_get($device, "upsIdentModel.0", "-OQv", "UPS-MIB"),'"');
  $hardware = $manufacturer . ' ' . $model;

  // Clean up
  $hardware = str_replace("Liebert Corporation Liebert", "Liebert", $hardware);
}

// EOF
