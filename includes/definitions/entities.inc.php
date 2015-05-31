<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage definitions
 * @copyright  (C) 2006-2014 Adam Armstrong
 *
 */

/////////////////////////////////////////////////////////
//  NO CHANGES TO THIS FILE, IT IS NOT USER-EDITABLE   //
/////////////////////////////////////////////////////////
//               YES, THAT MEANS YOU                   //
/////////////////////////////////////////////////////////

// Definitions related to various entities known by Observium

$config['entities']['device']['id_field']             = 'device_id';
$config['entities']['device']['name_field']           = "hostname";
$config['entities']['device']['table']                = "devices";
$config['entities']['device']['icon']                 = "oicon-servers";
#$config['entities']['device']['graph']                = array('type' => 'mempool_usage', 'id' => '@mempool_id'); // Graph should be done per metric

$config['entities']['mempool']['id_field']            = 'mempool_id';
$config['entities']['mempool']['name_field']          = "mempool_descr";
$config['entities']['mempool']['table']               = "mempools";
$config['entities']['mempool']['icon']                = "oicon-memory";
$config['entities']['mempool']['graph']               = array('type' => 'mempool_usage', 'id' => '@mempool_id');

$config['entities']['storage']['id_field']            = 'storage_id';
$config['entities']['storage']['name_field']          = "storage_descr";
$config['entities']['storage']['table']               = "storage";
$config['entities']['storage']['icon']                = "oicon-drive";
$config['entities']['storage']['graph']               = array('type' => 'storage_usage', 'id' => '@storage_id');

$config['entities']['processor']['id_field']          = 'processor_id';
$config['entities']['processor']['name_field']        = "processor_descr";
$config['entities']['processor']['table']             = "processors";
$config['entities']['processor']['icon']              = "oicon-processor";
$config['entities']['processor']['graph']             = array('type' => 'processor_usage', 'id' => '@processor_id');

// Sensors are in sensors.inc.php

$config['entities']['bgp_peer']['id_field']           = "bgpPeer_id";
$config['entities']['bgp_peer']['name_field']         = "bgpPeerRemoteAddr";
$config['entities']['bgp_peer']['table']              = "bgpPeers";
$config['entities']['bgp_peer']['icon']               = "oicon-chain";
$config['entities']['bgp_peer']['graph']              = array('type' => 'bgp_updates', 'id' => '@bgpPeer_id');

$config['entities']['netscaler_vsvr']['id_field']     = "vsvr_id";
$config['entities']['netscaler_vsvr']['name_field']   = "vsvr_label";
$config['entities']['netscaler_vsvr']['table']        = "netscaler_vservers";
$config['entities']['netscaler_vsvr']['ignore_field'] = "vsvr_ignore";
$config['entities']['netscaler_vsvr']['icon']         = "oicon-server";
$config['entities']['netscaler_vsvr']['graph']        = array('type' => 'netscalervsvr_bits', 'id' => '@vsvr_id');

$config['entities']['netscaler_svc']['id_field']      = "svc_id";
$config['entities']['netscaler_svc']['name_field']    = "svc_label";
$config['entities']['netscaler_svc']['table']         = "netscaler_services";
$config['entities']['netscaler_svc']['ignore_field']  = "svc_ignore";
$config['entities']['netscaler_svc']['icon']          = "oicon-service-bell";
$config['entities']['netscaler_svc']['graph']         = array('type' => 'netscalersvc_bits', 'id' => '@svc_id');

$config['entities']['toner']['id_field']              = "toner_id";
$config['entities']['toner']['name_field']            = "toner_descr";
$config['entities']['toner']['table']                 = "toner";
$config['entities']['toner']['icon']                  = "oicon-contrast";
$config['entities']['toner']['graph']                 = array('type' => 'toner_usage', 'id' => '@toner_id');

$config['entities']['port']['id_field']               = "port_id";
$config['entities']['port']['name_field']             = "label";
$config['entities']['port']['descr_field']            = "ifAlias";
$config['entities']['port']['table']                  = "ports";
$config['entities']['port']['ignore_field']           = "ignore";
$config['entities']['port']['disable_field']          = "disable";
$config['entities']['port']['deleted_field']          = "deleted";
$config['entities']['port']['icon']                   = "oicon-network-ethernet";
$config['entities']['port']['graph']                  = array('type' => 'port_bits', 'id' => '@port_id');

$config['default']['icon']                            = "oicon-circle-metal";

// ksort($config['entities']);

// EOF
