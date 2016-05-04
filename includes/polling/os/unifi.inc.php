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

// IEEE802dot11-MIB::dot11manufacturerProductName.5 = STRING: UAP-LR
// IEEE802dot11-MIB::dot11manufacturerProductVersion.5 = STRING: BZ.ar7240.v3.1.9.2442.131217.1549

$data = snmpwalk_cache_oid($device, "dot11manufacturerProductName", array(), "IEEE802dot11-MIB", mib_dirs());
if ($data)
{
  $data = snmpwalk_cache_oid($device, "dot11manufacturerProductVersion", $data, "IEEE802dot11-MIB", mib_dirs());

  $data = current($data);
  $hardware = "Unifi " . $data['dot11manufacturerProductName'];
  list(,$version) = preg_split('/\.v/', $data['dot11manufacturerProductVersion']);
}

// EOF
