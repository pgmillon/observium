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

if (!empty($agent_data['app']['varnish']))
{
  $app_id = discover_app($device, 'varnish');

  $data = $agent_data['app']['varnish'];
  $rrd_filename = "app-varnish-$app_id.rrd";

  // Varnish specific output from agent
  list ($backend_req, $backend_unhealthy, $backend_busy, $backend_fail, $backend_reuse, $backend_toolate, $backend_recycle, $backend_retry, $cache_hitpass, $cache_hit, $cache_miss, $lru_nuked, $lru_moved) = explode(";", $data);

  rrdtool_create($device, $rrd_filename, " \
      DS:backend_req:COUNTER:600:0:125000000000 \
      DS:backend_unhealthy:COUNTER:600:0:125000000000 \
      DS:backend_busy:COUNTER:600:0:125000000000 \
      DS:backend_fail:COUNTER:600:0:125000000000 \
      DS:backend_reuse:COUNTER:600:0:125000000000 \
      DS:backend_toolate:COUNTER:600:0:125000000000 \
      DS:backend_recycle:COUNTER:600:0:125000000000 \
      DS:backend_retry:COUNTER:600:0:125000000000 \
      DS:cache_hitpass:COUNTER:600:0:125000000000 \
      DS:cache_hit:COUNTER:600:0:125000000000 \
      DS:cache_miss:COUNTER:600:0:125000000000 \
      DS:lru_nuked:COUNTER:600:0:125000000000 \
      DS:lru_moved:COUNTER:600:0:125000000000 ");

  $rrd = sprintf("N:%d:%d:%d:%d:%d:%d:%d:%d:%d:%d:%d:%d:%d", $backend_req, $backend_unhealthy, $backend_busy, $backend_fail, $backend_reuse, $backend_toolate, $backend_recycle, $backend_retry, $cache_hitpass, $cache_hit, $cache_miss, $lru_nuked, $lru_moved);

  rrdtool_update($device, $rrd_filename, $rrd);

  unset($data, $rrd_filename, $rrd);
}

// EOF
