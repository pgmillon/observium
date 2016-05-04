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

if (!empty($agent_data['app']['bind']['global']))
{
  $app_id = discover_app($device, 'bind');

  // Prepare data arrays
  // -------------------
  $rrtypes = array('SOA', 'ANY', 'A', 'AAAA', 'NS', 'MX', 'CNAME', 'DNAME', 'TXT', 'SPF', 'SRV', 'SSHFP', 'TLSA', 'IPSECKEY', 'PTR', 'DNSKEY', 'RRSIG', 'NSEC', 'NSEC3', 'NSEC3PARAM', 'DS', 'DLV', 'IXFR', 'AXFR');

  // Requests incoming
  $req_in = array(
    'QUERY' => 0,
    'STATUS' => 0,
    'NOTIFY' => 0,
    'UPDATE' => 0,
  );

  // Query incoming
  $query_in = array();
  foreach ($rrtypes as $rrtype)
  {
    $query_in[$rrtype] = 0;
  }

  // Query outgoing
  $query_out = array();

  // Name server statistics
  $ns_stats_field_mapping = array(
    "IPv4 requests received" => 'Requestv4',
    "IPv6 requests received" => 'Requestv6',
    "requests with EDNS(0) received" => 'ReqEdns0',
    "requests with unsupported EDNS version received" => 'ReqBadEDNSVer',
    "requests with TSIG received" => 'ReqTSIG',
    "requests with SIG(0) received" => 'ReqSIG0',
    "requests with invalid signature" => 'ReqBadSIG',
    "TCP requests received" => 'ReqTCP',
    "auth queries rejected" => 'AuthQryRej',
    "recursive queries rejected" => 'RecQryRej',
    "transfer requests rejected" => 'XfrRej',
    "update requests rejected" => 'UpdateRej',
    "responses sent" => 'Response',
    "truncated responses sent" => 'TruncatedResp',
    "responses with EDNS(0) sent" => 'RespEDNS0',
    "responses with TSIG sent" => 'RespTSIG',
    "responses with SIG(0) sent" => 'RespSIG0',
    "queries resulted in successful answer" => 'QrySuccess',
    "queries resulted in authoritative answer" => 'QryAuthAns',
    "queries resulted in non authoritative answer" => 'QryNoauthAns',
    "queries resulted in referral answer" => 'QryReferral',
    "queries resulted in nxrrset" => 'QryNxrrset',
    "queries resulted in SERVFAIL" => 'QrySERVFAIL',
    "queries resulted in FORMERR" => 'QryFORMERR',
    "queries resulted in NXDOMAIN" => 'QryNXDOMAIN',
    "queries caused recursion" => 'QryRecursion',
    "duplicate queries received" => 'QryDuplicate',
    "queries dropped" => 'QryDropped',
    "other query failures" => 'QryFailure',
    "requested transfers completed" => 'XfrReqDone',
    "update requests forwarded" => 'UpdateReqFwd',
    "update responses forwarded" => 'UpdateRespFwd',
    "update forward failed" => 'UpdateFwdFail',
    "updates completed" => 'UpdateDone',
    "updates failed" => 'UpdateFail',
    "updates rejected due to prerequisite failure" => 'UpdateBadPrereq',
    "response policy zone rewrites" => 'RPZRewrites',
  );

  $ns_stats_fields = array_values($ns_stats_field_mapping);
  array_sort($ns_stats_fields);

  $ns_stats = array();
  foreach ($ns_stats_fields as $field)
  {
    $ns_stats[$field] = 0;
  }

  // Zone maintenance
  $zone_maint_field_mapping = array(
    "IPv4 notifies sent" => 'NotifyOutv4',
    "IPv6 notifies sent" => 'NotifyOutv6',
    "IPv4 notifies received" => 'NotifyInv4',
    "IPv6 notifies received" => 'NotifyInv6',
    "notifies rejected" => 'NotifyRej',
    "IPv4 SOA queries sent" => 'SOAOutv4',
    "IPv6 SOA queries sent" => 'SOAOutv6',
    "IPv4 AXFR requested" => 'AXFRReqv4',
    "IPv6 AXFR requested" => 'AXFRReqv6',
    "IPv4 IXFR requested" => 'IXFRReqv4',
    "IPv6 IXFR requested" => 'IXFRReqv6',
    "transfer requests succeeded" => 'XfrSuccess',
    "transfer requests failed" => 'XfrFail',
  );

  $zone_maint_fields = array_values($zone_maint_field_mapping);
  array_sort($zone_maint_fields);

  $zone_maint = array();
  foreach ($zone_maint_fields as $field)
  {
    $zone_maint[$field] = 0;
  }

  // Resolver
  $resolver_field_mapping = array(
    "IPv4 queries sent" => 'Queryv4',
    "IPv6 queries sent" => 'Queryv6',
    "IPv4 responses received" => 'Responsev4',
    "IPv6 responses received" => 'Responsev6',
    "NXDOMAIN received" => 'NXDOMAIN',
    "SERVFAIL received" => 'SERVFAIL',
    "FORMERR received" => 'FORMERR',
    "other errors received" => 'OtherError',
    "EDNS(0) query failures" => 'EDNS0Fail',
    "mismatch responses received" => 'Mismatch',
    "truncated responses received" => 'Truncated',
    "lame delegations received" => 'Lame',
    "query retries" => 'Retry',
    "queries aborted due to quota" => 'QueryAbort',
    "failures in opening query sockets" => 'QuerySockFail',
    "query timeouts" => 'QueryTimeout',
    "IPv4 NS address fetches" => 'GlueFetchv4',
    "IPv6 NS address fetches" => 'GlueFetchv6',
    "IPv4 NS address fetch failed" => 'GlueFetchv4Fail',
    "IPv6 NS address fetch failed" => 'GlueFetchv6Fail',
    "DNSSEC validation attempted" => 'ValAttempt',
    "DNSSEC validation succeeded" => 'ValOk',
    "DNSSEC NX validation succeeded" => 'ValNegOk',
    "DNSSEC validation failed" => 'ValFail',
    "queries with RTT < 10ms" => 'QryRTT10',
    "queries with RTT 10-100ms" => 'QryRTT100',
    "queries with RTT 100-500ms" => 'QryRTT500',
    "queries with RTT 500-800ms" => 'QryRTT800',
    "queries with RTT 800-1600ms" => 'QryRTT1600',
    "queries with RTT > 1600ms" => 'QryRTT1600plus',
  );

  $resolver_fields = array_values($resolver_field_mapping);
  array_sort($resolver_fields);

  $resolver = array();

  // Cache
  $cache = array();

  // Store the data in arrays
  // ------------------------
  $lines = explode("\n", $agent_data['app']['bind']['global']);
  foreach ($lines as $line) {
    // Line format is "key:value"
    list ($key, $value) = explode(':', $line);

    // Keys consist of "section,subkey"
    list ($section, $subkey) = explode(',', $key, 2);

    // The subkey depends on the section
    if ($section == 'req-in')
    {
      // Subkey is the opcode
      $req_in[$subkey] = (int) $value;
    }
    elseif ($section == 'query-in')
    {
      // Subkey is the RRType
      $query_in[$subkey] = (int) $value;
    }
    elseif ($section == 'ns-stats')
    {
      // Subkey is description
      if (isset($ns_stats_field_mapping[$subkey]))
      {
        $subkey = $ns_stats_field_mapping[$subkey];
      }
      $ns_stats[$subkey] = (int) $value;
    }
    elseif ($section == 'zone-maint')
    {
      // Subkey is description
      if (isset($zone_maint_field_mapping[$subkey]))
      {
        $subkey = $zone_maint_field_mapping[$subkey];
      }
      $zone_maint[$subkey] = (int) $value;
    }
  }

  // Done with the global stuff
  unset($agent_data['app']['bind']['global']);

  // The rest is views
  foreach ($agent_data['app']['bind'] as $view => $view_data)
  {
    $lines = explode("\n", $agent_data['app']['bind'][$view]);
    foreach ($lines as $line) {
      // Line format is "key:value"
      list ($key, $value) = explode(':', $line);

      // Keys consist of "section,subkey"
      list ($section, $subkey) = explode(',', $key, 2);

      // The subkey depends on the section
      if ($section == 'query-out')
      {
        // Create the view if it doesn't exist yet
        if (!isset($query_out[$view]))
        {
          foreach ($rrtypes as $rrtype)
          {
            $query_out[$view][$rrtype] = 0;
          }
        }

        // Subkey is the RRType
        $query_out[$view][$subkey] = (int) $value;
      }
      elseif ($section == 'resolver')
      {
        // Create the view if it doesn't exist yet
        if (!isset($resolver[$view]))
        {
          foreach ($resolver_fields as $field)
          {
            $resolver[$view][$field] = 0;
          }
        }

        // Subkey is the description
        if (isset($resolver_field_mapping[$subkey]))
        {
          $subkey = $resolver_field_mapping[$subkey];
        }
        $resolver[$view][$subkey] = (int) $value;
      }
      elseif ($section == 'cache')
      {
        // Create the view if it doesn't exist yet
        if (!isset($cache[$view]))
        {
          foreach ($rrtypes as $rrtype)
          {
            // Create fields for both positive and negative cache entries
            $cache[$view][$field] = 0;
            $cache[$view]['!'.$field] = 0;
          }
        }
        // Subkey is the RRType
        $cache[$view][$subkey] = (int) $value;
      }
    }
  }

  // Use the data from the arrays to build RRDs
  // ------------------------------------------

  // rrdcreate list of rrtypes
  $rrdcreate_rrtypes = "";
  foreach ($rrtypes as $rrtype)
  {
    $rrdcreate_rrtypes .= " DS:$rrtype:DERIVE:600:0:7500000";
  }

  // req-in
  $rrd_filename = "app-bind-$app_id-req-in.rrd";

  rrdtool_create($device, $rrd_filename, " \
        DS:query:DERIVE:600:0:7500000 \
        DS:status:DERIVE:600:0:7500000 \
        DS:notify:DERIVE:600:0:7500000 \
        DS:update:DERIVE:600:0:7500000 ");

  rrdtool_update($device, $rrd_filename, 'N:'.$req_in['QUERY'].':'.$req_in['STATUS'].':'.$req_in['NOTIFY'].':'.$req_in['UPDATE']);

  // query-in
  $rrd_filename = "app-bind-$app_id-query-in.rrd";

  rrdtool_create($device, $rrd_filename, " $rrdcreate_rrtypes ");

  $rrd_data = "";
  foreach ($rrtypes as $rrtype)
  {
    $rrd_data .= ":".$query_in[$rrtype];
  }
  rrdtool_update($device, $rrd_filename,  "N".$rrd_data);

  // ns-stats
  $rrd_filename = "app-bind-$app_id-ns-stats.rrd";

  // rrdcreate list of fields
  $rrdcreate_ns_stats = "";
  foreach ($ns_stats_fields as $field)
  {
    $rrdcreate_ns_stats .= " DS:$field:DERIVE:600:0:7500000";
  }

  rrdtool_create($device, $rrd_filename, " $rrdcreate_ns_stats ");

  $rrd_data = "";
  foreach ($ns_stats_fields as $field)
  {
    $rrd_data .= ":".$ns_stats[$field];
  }
  rrdtool_update($device, $rrd_filename,  "N".$rrd_data);

  // zone-maint
  $rrd_filename = "app-bind-$app_id-zone-maint.rrd";

  // rrdcreate list of fields
  $rrdcreate_zone_maint = "";
  foreach ($zone_maint_fields as $field)
  {
    $rrdcreate_zone_maint .= " DS:$field:DERIVE:600:0:7500000";
  }
  rrdtool_create($device, $rrd_filename, " $rrdcreate_zone_maint ");

  $rrd_data = "";
  foreach ($zone_maint_fields as $field)
  {
    $rrd_data .= ":".$zone_maint[$field];
  }
  rrdtool_update($device, $rrd_filename,  "N".$rrd_data);

  // query-out
  foreach ($query_out as $view => $view_data)
  {
    $rrd_filename = "app-bind-$app_id-query-out-".$view.".rrd";

    rrdtool_create($device, $rrd_filename, " $rrdcreate_rrtypes ");

    $rrd_data = "";
    foreach ($rrtypes as $rrtype)
    {
      $rrd_data .= ":".$view_data[$rrtype];
    }
    rrdtool_update($device, $rrd_filename,  "N".$rrd_data);
  }

  // resolver
  foreach ($resolver as $view => $view_data)
  {
    $rrd_filename = "app-bind-$app_id-resolver-$view.rrd";

    // rrdcreate list of fields
    $rrdcreate_resolver = "";
    foreach ($resolver_fields as $field)
    {
      $rrdcreate_resolver .= " DS:$field:DERIVE:600:0:7500000";
    }
    rrdtool_create($device, $rrd_filename, " $rrdcreate_resolver ");

    $rrd_data = "";
    foreach ($resolver_fields as $field)
    {
      $rrd_data .= ":".$view_data[$field];
    }
    rrdtool_update($device, $rrd_filename,  "N".$rrd_data);
  }

  // cache
  foreach ($cache as $view => $view_data)
  {
    $rrd_filename = "app-bind-$app_id-cache-$view.rrd";

    $rrdcreate_cache = "";
    foreach ($rrtypes as $rrtype)
    {
      $rrdcreate_cache .= " DS:$rrtype:GAUGE:600:0:1000000";
      $rrdcreate_cache .= " DS:NEG_$rrtype:GAUGE:600:0:1000000";
    }
    rrdtool_create($device, $rrd_filename, " $rrdcreate_cache ");

    $rrd_data = "";
    foreach ($rrtypes as $rrtype)
    {
      $rrd_data .= ":".$view_data[$rrtype];
      $rrd_data .= ":".$view_data['!'.$rrtype];
    }
    rrdtool_update($device, $rrd_filename,  "N".$rrd_data);
  }
}

// EOF
