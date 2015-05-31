<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage webui
 * @copyright  (C) 2006-2014 Adam Armstrong
 *
 */

// Definitions related to the applications system

// Application graph definitions

$config['app']['apache']['top']            = array('bits', 'hits', 'scoreboard', 'cpu');
$config['app']['nginx']['top']             = array('connections', 'req');
$config['app']['bind']['top']              = array('req_in', 'answers', 'resolv_errors', 'resolv_rtt');
$config['app']['powerdns']['top']          = array('recursing', 'queries', 'querycache', 'latency');
$config['app']['powerdns-recursor']['top'] = array('queries', 'timeouts', 'cache', 'latency');
$config['app']['unbound']['top']           = array('queries', 'queue', 'memory', 'qtype');
$config['app']['mysql']['top']             = array('network_traffic', 'connections', 'command_counters', 'select_types');
$config['app']['postgresql']['top']        = array('xact', 'blks', 'tuples', 'tuples_query');
$config['app']['memcached']['top']         = array('bits', 'commands', 'data', 'items');
$config['app']['drbd']['top']              = array('disk_bits', 'network_bits', 'queue', 'unsynced');
$config['app']['ntpd']['top']              = array('stats', 'freq', 'stratum', 'bits');
$config['app']['nfs']['top']               = array('nfs2','nfs3', 'nfs4');
$config['app']['shoutcast']['top']         = array('multi_stats', 'multi_bits');
$config['app']['freeradius']['top']        = array('authentication','accounting');
$config['app']['exim-mailqueue']['top']    = array('total');
$config['app']['zimbra']['top']            = array('threads','mtaqueue','fdcount');
$config['app']['crashplan']['top']         = array('bits', 'sessions', 'archivesize', 'disk');
$config['app']['asterisk']['top']          = array('activecall', 'peers');
$config['app']['mssql']['top']             = array('stats', 'cpu_usage', 'memory_usage');

// EOF
