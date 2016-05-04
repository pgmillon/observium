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

// HUAWEI-ENTITY-EXTENT-MIB::hwEntitySystemModel.0 = STRING: S5700-52P-PWR-LI-AC
$hardware = snmp_get($device, "hwEntitySystemModel.0", "-Osqv", "HUAWEI-ENTITY-EXTENT-MIB", mib_dirs('huawei'));

// S5700-52P-PWR-LI-AC Huawei Versatile Routing Platform Software VRP (R) software,Version 5.160 (S5700 V200R007C00SPC500) Copyright (C) 2007 Huawei Technologies Co., Ltd.
// Huawei Versatile Routing Platform Software VRP (R) software, Version 8.60 (CE6850 V100R002C00SPC200) Copyright (C) 2012-2013 Huawei Technologies Co., Ltd. HUAWEI CE6850-48S4Q-EI
// Huawei Versatile Routing Platform Software VRP (R) software, Version 5.130 (AR200 V200R003C00) Copyright (C) 2011-2012 HUAWEI TECH CO., LTD Huawei AR201 Router
if (!$hardware && preg_match('/((?:S[0-9]{4,5}|CE[0-9]{4,5}|AR[0-9]{2,4})(?:[0-9A-Z-]*))(?:\s|$)(?!V)/', $poll_device['sysDescr'], $matches))
{
  $hardware = $matches[1];
}

if (preg_match("/Version (.*)/", $poll_device['sysDescr'], $matches))
{
  $version = $matches[1];
}

// EOF
