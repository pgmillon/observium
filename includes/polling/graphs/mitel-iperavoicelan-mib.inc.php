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
MITEL-IperaVoiceLAN-MIB::mitelIpera3000IPUsrLicPurchased.0 = INTEGER: 24
MITEL-IperaVoiceLAN-MIB::mitelIpera3000IPUsrLicUsed.0 = INTEGER: 17
MITEL-IperaVoiceLAN-MIB::mitelIpera3000IPDevLicPurchased.0 = INTEGER: 0
MITEL-IperaVoiceLAN-MIB::mitelIpera3000IPDevLicUsed.0 = INTEGER: 16
*/

$table_defs['MITEL-IperaVoiceLAN-MIB']['mitelIpera3000SysCapDisplay'] = array(
  'table'         => 'mitelIpera3000SysCapDisplay',
  'numeric'       => '.1.3.6.1.4.1.1027.4.1.1.2.1.2',
  'mib'           => 'MITEL-IperaVoiceLAN-MIB',
  'mib_dir'       => 'mitel',
  'descr'         => 'System capacity information',
  'graphs'        => array('mitelIpera-UsrLic', 'mitelIpera-DevLic'),
  'ds_rename'     => array('mitelIpera3000IP' => ''),
  'oids'          => array(
    'mitelIpera3000IPUsrLicPurchased' =>  array('numeric' => '1',  'descr' => 'The number of the user license purchased',   'ds_type' => 'GAUGE', 'ds_min' => '0'),
    'mitelIpera3000IPUsrLicUsed'      =>  array('numeric' => '2',  'descr' => 'The number of the user license used',        'ds_type' => 'GAUGE', 'ds_min' => '0'),
    'mitelIpera3000IPDevLicPurchased' =>  array('numeric' => '3',  'descr' => 'The number of the device license purchased', 'ds_type' => 'GAUGE', 'ds_min' => '0'),
    'mitelIpera3000IPDevLicUsed'      =>  array('numeric' => '4',  'descr' => 'The number of the device license used',      'ds_type' => 'GAUGE', 'ds_min' => '0')
  )
);

// EOF
