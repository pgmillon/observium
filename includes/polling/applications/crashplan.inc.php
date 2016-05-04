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

if (!empty($agent_data['app']['crashplan']))
{
  $app_id = discover_app($device, 'crashplan');

  $crashplan_data = json_decode($agent_data['app']['crashplan']['server'], TRUE);

  if (is_array($crashplan_data['data']['servers']))
  {
    # [serverName] => crashplan.luciad.com
    # [totalBytes] => 16995951050752
    # [usedBytes] => 16322661449728
    # [usedPercentage] => 96
    # [freeBytes] => 673289601024
    # [freePercentage] => 4
    # [coldBytes] => 3762904182328
    # [coldPercentageOfUsed] => 23
    # [coldPercentageOfTotal] => 22
    # [archiveBytes] => 11678769817966
    # [selectedBytes] => 19313807393642
    # [remainingBytes] => 379281681813
    # [inboundBandwidth] => 53
    # [outboundBandwidth] => 67
    # [orgCount] => 1
    # [userCount] => 83
    # [computerCount] => 97
    # [onlineComputerCount] => 27
    # [backupSessionCount] => 0

    foreach ($crashplan_data['data']['servers'] as $crashplan_server)
    {
      $crashplan_servers[] = $crashplan_server['serverName'];

      $rrd_filename = "app-crashplan-".$crashplan_server['serverName'].".rrd";

      unset($rrd_values);

      foreach (array('totalBytes', 'usedBytes', 'usedPercentage', 'freeBytes', 'freePercentage', 'coldBytes', 'coldPercentageOfUsed',
        'coldPercentageOfTotal', 'archiveBytes', 'selectedBytes', 'remainingBytes', 'inboundBandwidth', 'outboundBandwidth', 'orgCount',
        'userCount', 'computerCount', 'onlineComputerCount', 'backupSessionCount') as $key)
      {
        $rrd_values[] = (is_numeric($crashplan_server[$key]) ? $crashplan_server[$key] : "U");
      }

      rrdtool_create($device, $rrd_filename, " \
          DS:totalBytes:GAUGE:600:0:125000000000 \
          DS:usedBytes:GAUGE:600:0:125000000000 \
          DS:usedPercentage:GAUGE:600:0:100 \
          DS:freeBytes:GAUGE:600:0:125000000000 \
          DS:freePercentage:GAUGE:600:0:100 \
          DS:coldBytes:GAUGE:600:0:125000000000 \
          DS:coldPctOfUsed:GAUGE:600:0:100 \
          DS:coldPctOfTotal:GAUGE:600:0:100 \
          DS:archiveBytes:GAUGE:600:0:125000000000 \
          DS:selectedBytes:GAUGE:600:0:125000000000 \
          DS:remainingBytes:GAUGE:600:0:125000000000 \
          DS:inboundBandwidth:GAUGE:600:0:125000000000 \
          DS:outboundBandwidth:GAUGE:600:0:125000000000 \
          DS:orgCount:GAUGE:600:0:125000000000 \
          DS:userCount:GAUGE:600:0:125000000000 \
          DS:computerCount:GAUGE:600:0:125000000000 \
          DS:onlineComputerCount:GAUGE:600:0:125000000000 \
          DS:backupSessionCount:GAUGE:600:0:125000000000 ");

      rrdtool_update($device, $rrd_filename, "N:" . implode(':', $rrd_values));
    }

    # Set list of servers as device attribute so we can use it in the web interface
    set_dev_attrib($device, 'crashplan_servers', json_encode($crashplan_servers));
  }
}

// EOF
