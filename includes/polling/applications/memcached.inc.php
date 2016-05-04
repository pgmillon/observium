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

if (!empty($agent_data['app']['memcached']))
{
  foreach ($agent_data['app']['memcached'] as $memcached_host => $memcached_data)
  {
    $app_id = discover_app($device, 'memcached', $memcached_host);

    // Only run if we have a valid host with a : separating host:port
    if (strpos($memcached_host, ":"))
    {

      echo(" memcached(".$memcached_host.") ");

      // These are the keys we expect. If we fall back to the old value-only
      // data (instead of the new key:value data) we expect them exactly in
      // this order.
      $keys = array('accepting_conns', 'auth_cmds', 'auth_errors', 'bytes',
                    'bytes_read', 'bytes_written', 'cas_badval', 'cas_hits',
                    'cas_misses', 'cmd_flush', 'cmd_get', 'cmd_set',
                    'conn_yields', 'connection_structures',
                    'curr_connections', 'curr_items', 'decr_hits',
                    'decr_misses', 'delete_hits', 'delete_misses',
                    'evictions', 'get_hits', 'get_misses', 'incr_hits',
                    'incr_misses', 'limit_maxbytes', 'listen_disabled_num',
                    'pid', 'pointer_size', 'rusage_system', 'rusage_user',
                    'threads', 'time', 'total_connections', 'total_items',
                    'uptime', 'version');

      // Initialise the expected values
      $values = array();
      foreach ($keys as $key)
      {
        $values[$key] = '0';
      }

      // Parse the data, first try key:value format
      $lines = explode("\n", $memcached_data);
      $fallback_to_values_only = False;
      foreach ($lines as $line) {
        // Fall back to values only if we don't see a : separator
        if (!strstr($line, ':'))
        {
          $fallback_to_values_only = True;
          break;
        }

        // Parse key:value line
        list($key, $value) = explode(':', $line, 2);
        $values[$key] = $value;
      }

      if ($fallback_to_values_only)
      {
        // See if we got the expected data
        if (count($keys) != count($lines))
        {
          // Skip this one, we don't know how to handle this data
          echo("<- [skipped, incompatible data received] ");
          continue;
        }

        // Combine keys and values
        echo("<- [old data format received, please upgrade agent] ");
        $values = array_combine($keys, $lines);
      }

      $rrd_filename = "app-memcached-".$memcached_host.".rrd";

      rrdtool_create($device, $rrd_filename, " \
        DS:uptime:GAUGE:600:0:125000000000 \
        DS:threads:GAUGE:600:0:125000000000 \
        DS:rusage_user:GAUGE:600:0:125000000000 \
        DS:rusage_system:GAUGE:600:0:125000000000 \
        DS:curr_items:GAUGE:600:0:125000000000 \
        DS:total_items:DERIVE:600:0:125000000000 \
        DS:limit_maxbytes:GAUGE:600:0:U \
        DS:curr_connections:GAUGE:600:0:125000000000 \
        DS:total_connections:DERIVE:600:0:125000000000 \
        DS:conn_structures:GAUGE:600:0:125000000000 \
        DS:bytes:GAUGE:600:0:U \
        DS:cmd_get:DERIVE:600:0:125000000000 \
        DS:cmd_set:DERIVE:600:0:125000000000 \
        DS:cmd_flush:DERIVE:600:0:12500000000 \
        DS:get_hits:DERIVE:600:0:125000000000 \
        DS:get_misses:DERIVE:600:0:125000000000 \
        DS:evictions:DERIVE:600:0:125000000000 \
        DS:bytes_read:DERIVE:600:0:125000000000 \
        DS:bytes_written:DERIVE:600:0:125000000000 \
        ");

      // Construct the data
      $rrd_data = "";
      $rrd_keys = array('uptime', 'threads', 'rusage_user', 'rusage_system',
                        'curr_items', 'total_items', 'limit_maxbytes',
                        'curr_connections', 'total_connections',
                        'connection_structures', 'bytes', 'cmd_get',
                        'cmd_set', 'cmd_flush', 'get_hits', 'get_misses',
                        'evictions', 'bytes_read', 'bytes_written');

      foreach ($rrd_keys as $key)
      {
        $rrd_data .= ":".$values[$key];
      }

      rrdtool_update($device, $rrd_filename, "N".$rrd_data);
    }

  }

}

// EOF
