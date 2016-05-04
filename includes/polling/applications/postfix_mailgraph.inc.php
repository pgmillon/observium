<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage poller
 * @copyright  (C) 2006-2015 Adam Armstrong
 *
 */

if (!empty($agent_data['app']['postfix_mailgraph']))
{
  $postfix_mailgraph = $agent_data['app']['postfix_mailgraph'];

  foreach (explode("\n",$postfix_mailgraph) as $line)
  {
    list($item,$value) = explode(":",$line,2);
    $queue_data[trim($item)] = trim($value);
  }

  $old_rrd_filename = "app-postfix-mailgraph-".$app['app_id'].".rrd";
  $rrd_filename = "app-postfix-mailgraph.rrd";

  rrdtool_create($device, $rrd_filename, " \
                    DS:sent:COUNTER:600:0:1000000 \
                    DS:received:COUNTER:600:0:1000000 \
                    DS:bounced:COUNTER:600:0:1000000 \
                    DS:rejected:COUNTER:600:0:1000000 \
                    DS:virus:COUNTER:600:0:1000000 \
                    DS:spam:COUNTER:600:0:1000000 \
                    DS:greylisted:COUNTER:600:0:1000000 \
                    DS:delayed:COUNTER:600:0:1000000 ");

  unset($rrd_values);

  foreach (array('sent','recv','bounced','rejected','virus', 'spam', 'greylisted', 'delayed') as $key)
  {
    $rrd_values[] = (is_numeric($queue_data[$key]) ? $queue_data[$key] : "U");
  }

  rrdtool_update($device, $rrd_filename, "N:" . implode(':', $rrd_values));
}

// EOF
