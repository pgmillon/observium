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

if (!empty($wmi['exchange']['services']))
{
  /* TODO:
      - Perform more testing with Exchange 2003, 2007, 2013
      - Review CAS counters
      - Review Information Store counters
      - Review Transport Role counters
      - Add Unified Messaging counters
  */
  echo(" Exchange:\n   ");

  // Exchange Client Access - Active Sync

  $wql = "SELECT * FROM Win32_PerfFormattedData_MSExchangeActiveSync_MSExchangeActiveSync";
  $wmi['exchange']['cas']['activesync'] = wmi_parse(wmi_query($wql, $override), TRUE);

  if ($wmi['exchange']['cas']['activesync'])
  {
    $app_found['exchange'] = TRUE;
    echo("Active Sync; ");

    $rrd_filename = "wmi-app-exchange-as.rrd";

    rrdtool_create($device, $rrd_filename,
        "DS:synccommandspending:GAUGE:600:0:125000000000 ".
        "DS:pingcommandspending:GAUGE:600:0:125000000000 ".
        "DS:currentrequests:GAUGE:600:0:125000000000 "
      );

    rrdtool_update($device, $rrd_filename, "N:".
      $wmi['exchange']['cas']['activesync']['SyncCommandsPending'].":".
      $wmi['exchange']['cas']['activesync']['PingCommandsPending'].":".
      $wmi['exchange']['cas']['activesync']['CurrentRequests']
    );

    unset($wmi['exchange']['cas']['activesync'], $rrd_filename);
  }

  // Exchange Client Access - Autodiscover

  $wql = "SELECT * FROM Win32_PerfFormattedData_MSExchangeAutodiscover_MSExchangeAutodiscover";
  $wmi['exchange']['cas']['autodiscover'] = wmi_parse(wmi_query($wql, $override), TRUE);

  if ($wmi['exchange']['cas']['autodiscover'])
  {
    $app_found['exchange'] = TRUE;
    echo("Auto Discover; ");
    $rrd_filename = "wmi-app-exchange-auto.rrd";

    rrdtool_create($device, $rrd_filename,
        "DS:totalrequests:COUNTER:600:0:125000000000 ".
        "DS:errorresponses:COUNTER:600:0:125000000000 "
      );

    rrdtool_update($device, $rrd_filename, "N:".
      $wmi['exchange']['cas']['autodiscover']['TotalRequests'].":".
      $wmi['exchange']['cas']['autodiscover']['ErrorResponses']
    );

    unset($wmi['exchange']['cas']['autodiscover'], $rrd_filename);
  }

  // Exchange Client Access - Offline Address Book

  $wql = "SELECT * FROM Win32_PerfFormattedData_MSExchangeFDSOAB_MSExchangeFDSOAB WHERE Name='_total'";
  $wmi['exchange']['cas']['oab'] = wmi_parse(wmi_query($wql, $override), TRUE);

  if ($wmi['exchange']['cas']['oab'])
  {
    $app_found['exchange'] = TRUE;
    echo("OAB; ");

    $rrd_filename = "wmi-app-exchange-oab.rrd";

    rrdtool_create($device, $rrd_filename,
        "DS:dltasksqueued:GAUGE:600:0:125000000000 ".
        "DS:dltaskscompleted:GAUGE:600:0:125000000000 "
      );

    rrdtool_update($device, $rrd_filename, "N:".
      $wmi['exchange']['cas']['oab']['DownloadTaskQueued'].":".
      $wmi['exchange']['cas']['oab']['DownloadTasksCompleted']
    );

    unset($wmi['exchange']['cas']['oab'], $rrd_filename);
  }

  // Exchange Client Access - Outlook Web App

  $wql = "SELECT * FROM Win32_PerfFormattedData_MSExchangeOWA_MSExchangeOWA";
  $wmi['exchange']['cas']['owa'] = wmi_parse(wmi_query($wql, $override), TRUE);

  if ($wmi['exchange']['cas']['owa'])
  {
    $app_found['exchange'] = TRUE;
    echo("OWA; ");

    $rrd_filename = "wmi-app-exchange-owa.rrd";

    rrdtool_create($device, $rrd_filename,
        "DS:currentuniqueusers:GAUGE:600:0:125000000000 ".
        "DS:avgresponsetime:GAUGE:600:0:125000000000 ".
        "DS:avgsearchtime:GAUGE:600:0:125000000000 ",
        "RRA:LAST:0.5:1:2016  RRA:LAST:0.5:6:2976  RRA:LAST:0.5:24:1440  RRA:LAST:0.5:288:1440 " . $GLOBALS['config']['rrd']['rra']
      );

    rrdtool_update($device, $rrd_filename, "N:".
      $wmi['exchange']['cas']['owa']['CurrentUniqueUsers'].":".
      $wmi['exchange']['cas']['owa']['AverageResponseTime'].":".
      $wmi['exchange']['cas']['owa']['AverageSearchTime']
    );

    unset($wmi['exchange']['cas']['owa'], $rrd_filename);
  }

  // Exchange Hub Transport - Queues

  $wql = "SELECT * FROM Win32_PerfFormattedData_MSExchangeTransportQueues_MSExchangeTransportQueues WHERE Name='_total'";
  $wmi['exchange']['transport']['queues'] = wmi_parse(wmi_query($wql, $override), TRUE);

  if ($wmi['exchange']['transport']['queues'])
  {
    $app_found['exchange'] = TRUE;
    echo("Transport Queues; ");

    $rrd_filename = "wmi-app-exchange-tqs.rrd";

    rrdtool_create($device, $rrd_filename,
        "DS:aggregatequeue:GAUGE:600:0:125000000000 ".
        "DS:deliveryqpersec:GAUGE:600:0:125000000000 ".
        "DS:mbdeliverqueue:GAUGE:600:0:125000000000 ".
        "DS:submissionqueue:GAUGE:600:0:125000000000 "
      );

    rrdtool_update($device, $rrd_filename, "N:".
      $wmi['exchange']['transport']['queues']['AggregateDeliveryQueueLengthAllQueues'].":".
      $wmi['exchange']['transport']['queues']['ItemsQueuedforDeliveryPerSecond'].":".
      $wmi['exchange']['transport']['queues']['ActiveMailboxDeliveryQueueLength'].":".
      $wmi['exchange']['transport']['queues']['SubmissionQueueLength']
    );

    unset($wmi['exchange']['transport']['queues'], $rrd_filename);
  }

  // Exchange Hub Transport - SMTP SEND

  $wql = "SELECT * FROM Win32_PerfFormattedData_MSExchangeTransportSmtpSend_MSExchangeTransportSmtpSend WHERE Name='_total'";
  $wmi['exchange']['transport']['smtp'] = wmi_parse(wmi_query($wql, $override), TRUE);

  if ($wmi['exchange']['transport']['smtp'])
  {
    $app_found['exchange'] = TRUE;
    echo("SMTP; ");

    $rrd_filename = "wmi-app-exchange-smtp.rrd";

    rrdtool_create($device, $rrd_filename,
        "DS:currentconnections:GAUGE:600:0:125000000000 ".
        "DS:msgsentpersec:GAUGE:600:0:125000000000 "
      );

    rrdtool_update($device, $rrd_filename, "N:".
      $wmi['exchange']['transport']['smtp']['ConnectionsCurrent'].":".
      $wmi['exchange']['transport']['smtp']['MessagesSentPersec']
    );

    unset($wmi['exchange']['transport']['queues'], $rrd_filename);
  }

  // Exchange Information Store

  $wql = "SELECT * FROM Win32_PerfFormattedData_MSExchangeIS_MSExchangeIS";
  $wmi['exchange']['mailbox']['is'] = wmi_parse(wmi_query($wql, $override), TRUE);

  if ($wmi['exchange']['mailbox']['is'])
  {
    $app_found['exchange'] = TRUE;
    echo("IS; ");

    $rrd_filename = "wmi-app-exchange-is.rrd";

    rrdtool_create($device, $rrd_filename,
        "DS:activeconcount:GAUGE:600:0:125000000000 ".
        "DS:usercount:GAUGE:600:0:125000000000 ".
        "DS:rpcrequests:GAUGE:600:0:125000000000 ".
        "DS:rpcavglatency:GAUGE:600:0:125000000000 ".
        "DS:clientrpcfailbusy:GAUGE:600:0:125000000000 "
      );

    rrdtool_update($device, $rrd_filename, "N:".
      $wmi['exchange']['mailbox']['is']['ActiveConnectionCount'].":".
      $wmi['exchange']['mailbox']['is']['UserCount'].":".
      $wmi['exchange']['mailbox']['is']['RPCRequests'].":".
      $wmi['exchange']['mailbox']['is']['RPCAveragedLatency'].":".
      $wmi['exchange']['mailbox']['is']['ClientRPCsFailedServerTooBusy']
    );

    unset($wmi['exchange']['mailbox']['is'], $rrd_filename);
  }

  // Exchange Information Store - Mailbox Data

  $wql = "SELECT * FROM Win32_PerfFormattedData_MSExchangeIS_MSExchangeISMailbox WHERE Name='_total'";
  $wmi['exchange']['mailbox']['mailbox'] = wmi_parse(wmi_query($wql, $override), TRUE);

  if ($wmi['exchange']['mailbox']['mailbox'])
  {
    $app_found['exchange'] = TRUE;
    echo("Mailbox; ");

    $rrd_filename = "wmi-app-exchange-mailbox.rrd";

    rrdtool_create($device, $rrd_filename,
        "DS:rpcavglatency:GAUGE:600:0:125000000000 ".
        "DS:msgqueued:GAUGE:600:0:125000000000 ".
        "DS:msgsentsec:GAUGE:600:0:125000000000 ".
        "DS:msgdeliversec:GAUGE:600:0:125000000000 ".
        "DS:msgsubmitsec:GAUGE:600:0:125000000000 "
      );

    rrdtool_update($device, $rrd_filename, "N:".
      $wmi['exchange']['mailbox']['mailbox']['RPCAverageLatency'].":".
      $wmi['exchange']['mailbox']['mailbox']['MessagesQueuedForSubmission'].":".
      $wmi['exchange']['mailbox']['mailbox']['MessagesSentPersec'].":".
      $wmi['exchange']['mailbox']['mailbox']['MessagesDeliveredPersec'].":".
      $wmi['exchange']['mailbox']['mailbox']['MessagesSubmittedPersec']
    );
  }

  echo("\n");
}

if ($app_found['exchange'] == TRUE)
{
  $app['type'] = "exchange";
  $app['name'] = "Exchange";
  wmi_dbAppInsert($device['device_id'], $app); // FIXME discover_app ?
  unset($app);
}

unset ($wmi['exchange']);

// EOF
