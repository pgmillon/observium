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
    $select .= ',`ifOctets_rate`';
    break;
  case 'traffic_in':
    $select .= ',`ifInOctets_rate`';
    break;
  case 'traffic_out':
    $select .= ',`ifOutOctets_rate`';
    break;
  case 'traffic_perc_in':
    $select .= ',`ifInOctets_perc`';
    break;
  case 'traffic_perc_out':
    $select .= ',`ifOutOctets_perc`';
    break;
  case 'traffic_perc':
    $select .= ', `ifOutOctets_perc`+`ifInOctets_perc` AS `ifOctets_perc`';
    break;
  case 'packets':
    $select .= ',`ifUcastPkts_rate`';
    break;
  case 'packets_in':
    $select .= ',`ifInUcastPkts_rate`';
    break;
  case 'packets_out':
    $select .= ',`ifOutUcastPkts_rate`';
    break;
  case 'errors':
    $select .= ',`ifErrors_rate`';
    break;
  case 'speed':
    $select .= ',`ifSpeed`';
    break;
  case 'port':
    $select .= ',`ifDescr`';
    break;
  case 'media':
    $select .= ',`ifType`';
    break;
  case 'descr':
    $select .= ',`ifAlias`';
    break;
  case 'mac':
    $select .= ',`ifPhysAddress`';
    break;
  case 'device':
    $select .= ',`devices`.`hostname`';
    break;
  default:
    $select .= ',`ifIndex`';
}

// EOF
