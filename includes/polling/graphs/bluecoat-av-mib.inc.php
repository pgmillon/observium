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

// BLUECOAT-AV-MIB  blueCoatAvMibObjects
//
// BLUECOAT-AV-MIB::avFilesScanned.0 = Wrong Type (should be Counter32): Counter64: 612755298
// BLUECOAT-AV-MIB::avVirusesDetected.0 = Counter32: 657
// BLUECOAT-AV-MIB::avPatternVersion.0 = STRING: 140107.171700.7246432
// BLUECOAT-AV-MIB::avPatternDateTime.0 = STRING: 2014-1-7,17:17:0.0
// BLUECOAT-AV-MIB::avEngineVersion.0 = STRING: 8.1.8.79
// BLUECOAT-AV-MIB::avVendorName.0 = STRING: Kaspersky Labs
// BLUECOAT-AV-MIB::avLicenseDaysRemaining.0 = INTEGER: 173
// BLUECOAT-AV-MIB::avPublishedFirmwareVersion.0 = STRING: 3.5.1.3
// BLUECOAT-AV-MIB::avInstalledFirmwareVersion.0 = STRING: 3.5.1.3
// BLUECOAT-AV-MIB::avSlowICAPConnections.0 = Gauge32: 1
// BLUECOAT-AV-MIB::avICAPFilesScanned.0 = Counter64: 612755309
// BLUECOAT-AV-MIB::avICAPVirusesDetected.0 = Counter32: 657
// BLUECOAT-AV-MIB::avSecureICAPFilesScanned.0 = Counter64: 0
// BLUECOAT-AV-MIB::avSecureICAPVirusesDetected.0 = Counter32: 0

$table_defs['BLUECOAT-AV-MIB']['blueCoatAvMibObjects'] = array(
  'file'          => 'proxyav.rrd',
  'call_function' => 'snmp_get',
  'mib'           => 'BLUECOAT-AV-MIB',
  'mib_dir'       => 'bluecoat',
  'table'         => 'blueCoatAvMibObjects',
  'ds_rename'     => array(
     'av' => '',
  ),
  'graphs'        => array('files_scanned', 'virus_detected', 'slow_icap', 'icap_scanned', 'icap_virus', 'sicap_scanned', 'sicap_virus'),
  'oids'          => array(
     'avFilesScanned'              => array('descr' => 'Files Scanned', 'ds_type' => 'COUNTER', 'ds_min' => '0'),
     'avVirusesDetected'           => array('descr' => 'Viruses Detected', 'ds_type' => 'COUNTER', 'ds_min' => '0'),
     'avSlowICAPConnections'       => array('descr' => 'Slow ICAP Connections', 'ds_type' => 'GAUGE', 'ds_min' => '0'),
     'avICAPFilesScanned'          => array('descr' => 'ICAP Files Scanned', 'ds_type' => 'COUNTER', 'ds_min' => '0'),
     'avICAPVirusesDetected'       => array('descr' => 'ICAP Viruses Detected', 'ds_type' => 'COUNTER', 'ds_min' => '0'),
     'avSecureICAPFilesScanned'    => array('descr' => 'Secure ICAP Files Scanned', 'ds_type' => 'COUNTER', 'ds_min' => '0'),
     'avSecureICAPVirusesDetected' => array('descr' => 'Secure ICAP Viruses Detected', 'ds_type' => 'COUNTER', 'ds_min' => '0'),
  )
);

// EOF
