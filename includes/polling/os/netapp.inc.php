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

// NETAPP-MIB::productType.0 pciBased
// NETAPP-MIB::productVersion.0 NetApp Release 7.3.6P2: Wed Sep 14 01:39:26 PDT 2011
// NETAPP-MIB::productId.0 0101206979
// NETAPP-MIB::productVendor.0 netapp
// NETAPP-MIB::productModel.0 FAS3020
// NETAPP-MIB::productFirmwareVersion.0 CFE 3.1.0
// NETAPP-MIB::productGuiUrl.0 https://:443/na_admin
// NETAPP-MIB::productApiUrl.0 https://:443/servlets/netapp.servlets.admin.XMLrequest_filer
// NETAPP-MIB::productSerialNum.0 XXXXXXXXXXX
// NETAPP-MIB::productPartnerSerialNum.0 not applicable
// NETAPP-MIB::productCPUArch.0 x86
// NETAPP-MIB::productTrapData.0 Trap variable currently unused.
// NETAPP-MIB::productMachineType.0 FAS3020

$mib = 'NETAPP-MIB';

if (preg_match('/^NetApp Release ([\w\.]+)(?: [\w\-]+)?:/', $poll_device['sysDescr'], $matches))
{
  $version = $matches[1];
}

$hardware = snmp_get($device, 'productModel.0', '-Osqv', $mib, mib_dirs('netapp'));
$serial   = snmp_get($device, 'productSerialNum.0', '-Osqv', $mib, mib_dirs('netapp'));
$firmware = snmp_get($device, 'productFirmwareVersion.0', '-Osqv', $mib, mib_dirs('netapp'));
$features = snmp_get($device, 'productCPUArch.0', '-Osqv', $mib, mib_dirs('netapp'));

// FIXME --- remove this stuff soon

if(is_file($host_rrd . '/netapp_stats.rrd'))
{

/// FIXME. Move to graphs module.
// 64-bit counters. We don't support the legacy 32-bit counters and their high-low maths.
//
// misc64NfsOps.0 = 22970088164
// misc64CifsOps.0 = 106806017
// misc64HttpOps.0 = 0
// misc64NetRcvdBytes.0 = 136780925422179
// misc64NetSentBytes.0 = 187136027544040
// misc64DiskReadBytes.0 = 449307535990784
// misc64DiskWriteBytes.0 = 247258801713152
// misc64TapeReadBytes.0 = 0
// misc64TapeWriteBytes.0 = 0

$rrd_filename   = $host_rrd . '/netapp_stats.rrd';

$rrd_create = '  \
     DS:iscsi_ops:COUNTER:600:0:10000000000 \
     DS:fcp_ops:COUNTER:600:0:10000000000 \
     DS:nfs_ops:COUNTER:600:0:10000000000 \
     DS:cifs_ops:COUNTER:600:0:10000000000 \
     DS:http_ops:COUNTER:600:0:10000000000 \
     DS:net_rx:COUNTER:600:0:10000000000 \
     DS:net_tx:COUNTER:600:0:10000000000 \
     DS:disk_rd:COUNTER:600:0:10000000000 \
     DS:disk_wr:COUNTER:600:0:10000000000 \
     DS:tape_rd:COUNTER:600:0:10000000000 \
     DS:tape_wr:COUNTER:600:0:10000000000 ';

$snmpdata = snmp_get_multi($device, 'iscsi64Ops.0 fcp64Ops.0 misc64NfsOps.0 misc64CifsOps.0 misc64HttpOps.0 misc64NetRcvdBytes.0 misc64NetSentBytes.0 misc64DiskReadBytes.0 misc64DiskWriteBytes.0 misc64TapeReadBytes.0 misc64TapeWriteBytes.0', '-OQUs', $mib, mib_dirs('netapp'));

rrdtool_create($device, $rrd_filename, $rrd_create);
rrdtool_update($device, $rrd_filename, array($snmpdata[0]['iscsi64Ops'], $snmpdata[0]['fcp64Ops'], $snmpdata[0]['misc64NfsOps'], $snmpdata[0]['misc64CifsOps'], $snmpdata[0]['misc64HttpOps'], $snmpdata[0]['misc64NetRcvdBytes'], $snmpdata[0]['misc64NetSentBytes'], $snmpdata[0]['misc64DiskReadBytes'], $snmpdata[0]['misc64DiskWriteBytes'], $snmpdata[0]['misc64TapeReadBytes'], $snmpdata[0]['misc64TapeWriteBytes']));

$graphs['netapp_ops'] = TRUE;
$graphs['netapp_disk_io'] = TRUE;
$graphs['netapp_net_io'] = TRUE;
$graphs['netapp_tape_io'] = TRUE;

}

if(is_file($host_rrd . '/netapp_cp.rrd'))
{

// Checkpoint Ops - use a separate RRD file
//
// NETAPP-MIB::cpTime.0 = Timeticks: (746551966) 86 days, 9:45:19.66
// NETAPP-MIB::cpFromTimerOps.0 = Counter32: 2997924
// NETAPP-MIB::cpFromSnapshotOps.0 = Counter32: 8341
// NETAPP-MIB::cpFromLowWaterOps.0 = Counter32: 0
// NETAPP-MIB::cpFromHighWaterOps.0 = Counter32: 1341309
// NETAPP-MIB::cpFromLogFullOps.0 = Counter32: 279
// NETAPP-MIB::cpFromCpOps.0 = Counter32: 156692
// NETAPP-MIB::cpTotalOps.0 = Counter32: 4780961
// NETAPP-MIB::cpFromFlushOps.0 = Counter32: 0
// NETAPP-MIB::cpFromSyncOps.0 = Counter32: 272605
// NETAPP-MIB::cpFromLowVbufOps.0 = Counter32: 0
// NETAPP-MIB::cpFromCpDeferredOps.0 = Counter32: 952
// NETAPP-MIB::cpFromLowDatavecsOps.0 = Counter32: 0

$rrd_filename = $host_rrd . '/netapp-cp.rrd';

$rrd_create = '\
  DS:time:COUNTER:600:0:10000000000 \
  DS:timer:COUNTER:600:0:10000000000 \
  DS:snapshot:COUNTER:600:0:10000000000 \
  DS:low_water:COUNTER:600:0:10000000000 \
  DS:high_water:COUNTER:600:0:10000000000 \
  DS:log_full:COUNTER:600:0:10000000000 \
  DS:cp:COUNTER:600:0:10000000000 \
  DS:flush:COUNTER:600:0:10000000000 \
  DS:sync:COUNTER:600:0:10000000000 \
  DS:low_vbuf:COUNTER:600:0:10000000000 \
  DS:cp_deferred:COUNTER:600:0:10000000000 \
  DS:low_datavecs:COUNTER:600:0:10000000000 ';

$snmpdata = snmp_get_multi($device, 'cpTime.0 cpFromTimerOps.0 cpFromSnapshotOps.0 cpFromLowWaterOps.0 cpFromHighWaterOps.0 cpFromLogFullOps.0 cpFromCpOps.0 cpFromFlushOps.0 cpFromSyncOps.0 cpFromLowVbufOps.0 cpFromCpDeferredOps.0 cpFromLowDatavecsOps.0', '-OQUs', $mib, mib_dirs('netapp'));

rrdtool_create($device, $rrd_filename, $rrd_create);
rrdtool_update($device, $rrd_filename, array($snmpdata[0]['cpTime'], $snmpdata[0]['cpFromTimerOps'], $snmpdata[0]['cpFromSnapshotOps'], $snmpdata[0]['cpFromLowWaterOps'], $snmpdata[0]['cpFromHighWaterOps'], $snmpdata[0]['cpFromLogFullOps'], $snmpdata[0]['cpFromCpOps'], $snmpdata[0]['cpFromFlushOps'], $snmpdata[0]['cpFromSyncOps'], $snmpdata[0]['cpFromLowVbufOps'], $snmpdata[0]['cpFromCpDeferredOps'], $snmpdata[0]['cpFromLowDatavecsOps']));

$graphs['netapp_cp_ops'] = TRUE;

}

unset($snmpdata, $rrd_filename, $rrd_create);

// EOF
