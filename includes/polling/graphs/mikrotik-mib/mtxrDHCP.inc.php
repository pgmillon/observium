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

$table_defs['MIKROTIK-MIB']['mtxrDHCP'] = array (
  'table'      => 'mtxrDHCP',
  'numeric'    => '.1.3.6.1.4.1.14988.1.1.6',
  'mib'        => 'MIKROTIK-MIB',
  'mib_dir'    => 'mikrotik',
  'file'       => 'mtxrDHCP.rrd',
  'descr'      => 'Mikrotik DHCP Statistics',
  'graphs'     => array('dhcp_leases'),
  'oids'       => array(
    'mtxrDHCPLeaseCount' => array('numeric' => '1',  'descr' => 'Number of DHCP leases', 'ds_min' => '0', 'ds_type' => 'GAUGE'), // Gauge32: 24    
  )
);

// EOF
