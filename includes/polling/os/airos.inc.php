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

// FIXME. airos != unifi. required device for tests
$data = snmpwalk_cache_oid($device, "dot11manufacturerProductName", array(), "IEEE802dot11-MIB", mib_dirs());
if ($data)
{
  $data = snmpwalk_cache_oid($device, "dot11manufacturerProductVersion", $data, "IEEE802dot11-MIB", mib_dirs());

  $data = current($data);
  $hardware = $data['dot11manufacturerProductName'];
  list(,$version) = preg_split('/\.v/', $data['dot11manufacturerProductVersion']);
}

// EOF
