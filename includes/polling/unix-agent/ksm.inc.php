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

$ksm = $agent_data['ksm'];
unset($agent_data['ksm']);

foreach (explode("\n",$ksm) as $line)
{
  list($field,$contents) = explode("=",$line,2);
  $agent_data['ksm'][$field] = trim($contents);
}

$rrd_filename = "ksm-pages.rrd";

rrdtool_create($device, $rrd_filename, " \
    DS:pagesShared:GAUGE:600:0:125000000000 \
    DS:pagesSharing:GAUGE:600:0:125000000000 \
    DS:pagesUnshared:GAUGE:600:0:125000000000 ");

rrdtool_update($device, $rrd_filename, "N:" . $agent_data['ksm']['pages_shared'] . ":" . $agent_data['ksm']['pages_sharing'] . ":" . $agent_data['ksm']['pages_unshared']);

$graphs['ksm_pages'] = TRUE;

unset($ksm);

// EOF
