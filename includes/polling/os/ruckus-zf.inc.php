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

/**

 RUCKUS-HWINFO-MIB::ruckusHwInfoModelNumber.0 = STRING: ZF7982
 RUCKUS-HWINFO-MIB::ruckusHwInfoSerialNumber.0 = STRING: <removed>
 RUCKUS-HWINFO-MIB::ruckusHwInfoCustomerID.0 = STRING: <removed>
 RUCKUS-HWINFO-MIB::ruckusHwInfoHWMajorRevision.0 = Gauge32: 2818
 RUCKUS-HWINFO-MIB::ruckusHwInfoHWMinorRevision.0 = Gauge32: 911
 RUCKUS-SWINFO-MIB::ruckusSwRevIndex.1 = INTEGER: 1
 RUCKUS-SWINFO-MIB::ruckusSwRevName.1 = STRING: not yet available
 RUCKUS-SWINFO-MIB::ruckusSwRevision.1 = STRING: 9.6.1.0.15
 RUCKUS-SWINFO-MIB::ruckusSwRevSize.1 = Gauge32: 0
 RUCKUS-SWINFO-MIB::ruckusSwRevStatus.1 = INTEGER: active(2)

 */

$data = snmp_get_multi($device, "ruckusHwInfoModelNumber.0 ruckusHwInfoSerialNumber.0 ruckusHwInfoCustomerID.0 ruckusHwInfoHWMajorRevision.0 ruckusHwInfoHWMinorRevision.0", "-OQUs", "RUCKUS-HWINFO-MIB", mib_dirs("ruckus"));
$data = $data[0];

$serial       = $data['ruckusHwInfoSerialNumber'];
$hardware     = $data['ruckusHwInfoModelNumber'];
$hw_ver       = $data['ruckusHwInfoHWMajorRevision'] .'.'. $data['ruckusHwInfoHWMinorRevision'];

// Currently unused information
// $customer_id  = $data['ruckusHwInfoSerialNumber'];
// $hw_major_ver = $data['ruckusHwInfoHWMajorRevision'];
// $hw_minor_ver = $data['ruckusHwInfoHWMinorRevision'];

unset ($data);

$data = snmpwalk_cache_oid($device, 'ruckusSwRevTable', array(), 'RUCKUS-SWINFO-MIB', mib_dirs("ruckus"));

print_r($data);

foreach ($data as $sw_rev)
{
  if ($sw_rev['ruckusSwRevStatus'] = "active")
  {
    $version = $sw_rev['ruckusSwRevision'];
  }
}