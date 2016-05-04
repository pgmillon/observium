<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage webui
 * @copyright  (C) 2006-2013 Adam Armstrong, (C) 2013-2016 Observium Limited
 *
 */

switch ($vars['sort'])
{
  case 'traffic':
    $ports = array_sort($ports, 'ifOctets_rate', 'SORT_DESC');
    break;
  case 'traffic_in':
    $ports = array_sort($ports, 'ifInOctets_rate', 'SORT_DESC');
    break;
  case 'traffic_out':
    $ports = array_sort($ports, 'ifOutOctets_rate', 'SORT_DESC');
    break;
  case 'traffic_perc_in':
    $ports = array_sort($ports, 'ifInOctets_perc', 'SORT_DESC');
    break;
  case 'traffic_perc_out':
    $ports = array_sort($ports, 'ifOutOctets_perc', 'SORT_DESC');
    break;
  case 'traffic_perc':
    $ports = array_sort($ports, 'ifOctets_perc', 'SORT_DESC');
    break;
  case 'packets':
    $ports = array_sort($ports, 'ifUcastPkts_rate', 'SORT_DESC');
    break;
  case 'packets_in':
    $ports = array_sort($ports, 'ifInUcastPkts_rate', 'SORT_DESC');
    break;
  case 'packets_out':
    $ports = array_sort($ports, 'ifOutUcastPkts_rate', 'SORT_DESC');
    break;
  case 'errors':
    $ports = array_sort($ports, 'ifErrors_rate', 'SORT_DESC');
    break;
  case 'speed':
    $ports = array_sort($ports, 'ifSpeed', 'SORT_DESC');
    break;
  case 'port':
    $ports = array_sort($ports, 'ifDescr', 'SORT_ASC');
    break;
  case 'media':
    $ports = array_sort($ports, 'ifType', 'SORT_ASC');
    break;
  case 'descr':
    $ports = array_sort($ports, 'ifAlias', 'SORT_ASC');
    break;
  case 'mac':
    $ports = array_sort($ports, 'ifPhysAddress', 'SORT_DESC');
    break;
  case 'device':
    $ports = array_sort($ports, 'hostname', 'SORT_ASC');
    break;
  default:
    $ports = array_sort($ports, 'ifIndex', 'SORT_ASC');
}

// EOF
