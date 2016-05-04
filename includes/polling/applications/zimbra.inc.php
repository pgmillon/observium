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

if (!empty($agent_data['app']['zimbra']))
{
  $app_id = discover_app($device, 'zimbra');

  foreach ($agent_data['app']['zimbra'] as $key => $value)
  {
    # key is "vm", "mysql" etc, value is the csv output
    $zimbra[$key] = parse_csv($value);
  }

  if (is_array($zimbra['mtaqueue']))
  {
    /*
    timestamp, KBytes, requests
    04/23/2013 18:19:30, 0, 0
    */

    $rrd_filename = "app-zimbra-mtaqueue.rrd";
    unset($rrd_values);

    foreach (array('KBytes','requests') as $key)
    {
      $rrd_values[] = (is_numeric($zimbra['mtaqueue'][0][$key]) ? $zimbra['mtaqueue'][0][$key] : "U");
    }

    rrdtool_create($device, $rrd_filename, " \
        DS:kBytes:GAUGE:600:0:125000000000 \
        DS:requests:GAUGE:600:0:125000000000 ");

    rrdtool_update($device, $rrd_filename, "N:" . implode(':', $rrd_values));
  }

  if (is_array($zimbra['fd']))
  {
    /*
    timestamp, fd_count, mailboxd_fd_count
    04/23/2013 18:40:53, 5216, 1451
    */

    $rrd_filename = "app-zimbra-fd.rrd";
    unset($rrd_values);

    foreach (array('fd_count','mailboxd_fd_count') as $key)
    {
      $rrd_values[] = (is_numeric($zimbra['fd'][0][$key]) ? $zimbra['fd'][0][$key] : "U");
    }

    rrdtool_create($device, $rrd_filename, " \
        DS:fdSystem:GAUGE:600:0:125000000000 \
        DS:fdMailboxd:GAUGE:600:0:125000000000 ");

    rrdtool_update($device, $rrd_filename, "N:" . implode(':', $rrd_values));
  }

  if (is_array($zimbra['threads']))
  {
    /*
    timestamp,AnonymousIoService,CloudRoutingReaderThread,GC,ImapSSLServer,ImapServer,LmtpServer,Pop3SSLServer,Pop3Server,ScheduledTask,SocketAcceptor,Thread,Timer,btpool,pool,other,total
    04/23/2013 01:03:10,0,0,0,6,1,3,0,1,2,0,3,2,0,0,72,90
    */

    $rrd_filename = "app-zimbra-threads.rrd";
    unset($rrd_values);

    foreach (array('AnonymousIoService','CloudRoutingReaderThread','GC','ImapSSLServer','ImapServer','LmtpServer','Pop3SSLServer','Pop3Server','ScheduledTask',
      'SocketAcceptor','Thread','Timer','btpool','pool','other','total') as $key)
    {
      $rrd_values[] = (is_numeric($zimbra['threads'][0][$key]) ? $zimbra['threads'][0][$key] : "U");
    }

    rrdtool_create($device, $rrd_filename, " \
        DS:AnonymousIoService:GAUGE:600:0:10000 \
        DS:CloudRoutingReader:GAUGE:600:0:10000 \
        DS:GC:GAUGE:600:0:10000 \
        DS:ImapSSLServer:GAUGE:600:0:10000 \
        DS:ImapServer:GAUGE:600:0:10000 \
        DS:LmtpServer:GAUGE:600:0:10000 \
        DS:Pop3SSLServer:GAUGE:600:0:10000 \
        DS:Pop3Server:GAUGE:600:0:10000 \
        DS:ScheduledTask:GAUGE:600:0:10000 \
        DS:SocketAcceptor:GAUGE:600:0:10000 \
        DS:Thread:GAUGE:600:0:10000 \
        DS:Timer:GAUGE:600:0:10000 \
        DS:btpool:GAUGE:600:0:10000 \
        DS:pool:GAUGE:600:0:10000 \
        DS:other:GAUGE:600:0:10000 \
        DS:total:GAUGE:600:0:10000 ");

    rrdtool_update($device, $rrd_filename, "N:" . implode(':', $rrd_values));
  }

  if (is_array($zimbra['mailboxd']))
  {
    /*
    timestamp,lmtp_rcvd_msgs,lmtp_rcvd_bytes,lmtp_rcvd_rcpt,lmtp_dlvd_msgs,lmtp_dlvd_bytes,db_conn_count,db_conn_ms_avg,ldap_dc_count,ldap_dc_ms_avg,mbox_add_msg_count,mbox_add_msg_ms_avg,mbox_get_count,mbox_get_ms_avg,mbox_cache,mbox_msg_cache,mbox_item_cache,soap_count,soap_ms_avg,imap_count,imap_ms_avg,pop_count,pop_ms_avg,idx_wrt_avg,idx_wrt_opened,idx_wrt_opened_cache_hit,calcache_hit,calcache_mem_hit,calcache_lru_size,idx_bytes_written,idx_bytes_written_avg,idx_bytes_read,idx_bytes_read_avg,bis_read,bis_seek_rate,db_pool_size,innodb_bp_hit_rate,lmtp_conn,lmtp_threads,pop_conn,pop_threads,pop_ssl_conn,pop_ssl_threads,imap_conn,imap_threads,imap_ssl_conn,imap_ssl_threads,http_idle_threads,http_threads,soap_sessions,mbox_cache_size,msg_cache_size,fd_cache_size,fd_cache_hit_rate,acl_cache_hit_rate,account_cache_size,account_cache_hit_rate,cos_cache_size,cos_cache_hit_rate,domain_cache_size,domain_cache_hit_rate,server_cache_size,server_cache_hit_rate,ucservice_cache_size,ucservice_cache_hit_rate,zimlet_cache_size,zimlet_cache_hit_rate,group_cache_size,group_cache_hit_rate,xmpp_cache_size,xmpp_cache_hit_rate,gc_parnew_count,gc_parnew_ms,gc_concurrentmarksweep_count,gc_concurrentmarksweep_ms,gc_minor_count,gc_minor_ms,gc_major_count,gc_major_ms,mpool_code_cache_used,mpool_code_cache_free,mpool_par_eden_space_used,mpool_par_eden_space_free,mpool_par_survivor_space_used,mpool_par_survivor_space_free,mpool_cms_old_gen_used,mpool_cms_old_gen_free,mpool_cms_perm_gen_used,mpool_cms_perm_gen_free,heap_used,heap_free
    04/24/2013 00:23:23,1,6506,1,1,6506,81,0.32098765432098764,1,0.0,1,36.0,84,0.0,100.0,50.0,34.59119496855346,0,0.0,138,10.405797101449275,0,0.0,0.0,0,0,0.0,0.0,0.0,0,0.0,0,0.0,1,0.0,0,1000,0,1,0,1,0,0,1,1,24,3,2,18,2,132,2000,1000,96.99490867673337,99.73302998524733,133,99.78571547607365,1,99.02449324324324,7,99.24445818173449,1,99.99996904482866,0,0.0,28,57.52625437572929,31,67.36491311592478,0,0.0,29250,487518,1382,103802,29250,487518,1382,103802,26258688,480000,83049792,24429248,13369344,0,216226736,186426448,125015776,9201952,312645872,210855696
    */

    print_r($zimbra['mailboxd']);

    foreach (array_keys($zimbra['mailboxd'][0]) as $key)
    {
      for ($line = 0; $line < count($zimbra['mailboxd']); $line++)
      {
        // zmstat writes these CSV files every 30 seconds. The agent passes us the 10 last values, so we have the full 5 minute range.
        // some of the variables should be added up to reach the total, but for most (gauges) we just want the latest value.
        switch ($key)
        {
          case 'lmtp_rcvd_msgs':
          case 'lmtp_rcvd_bytes':
          case 'lmtp_rcvd_rcpt':
          case 'lmtp_dlvd_msgs':
          case 'lmtp_dlvd_bytes':
          case 'mbox_add_msg_count':
          case 'mbox_get_count':
          case 'soap_count':
          case 'imap_count':
          case 'pop_count':
          case 'idx_bytes_written':
          case 'idx_bytes_read':
          case 'bis_read':
          case 'bis_seek_rate':
          case 'gc_parnew_count':
          case 'gc_parnew_ms':
          case 'gc_concurrentmarksweep_count':
          case 'gc_concurrentmarksweep_ms':
            $zimbra['mailboxd-total'][$key] += $zimbra['mailboxd'][$line][$key];
            break;
          default:
            $zimbra['mailboxd-total'][$key] = $zimbra['mailboxd'][$line][$key];
            break;
        }
      }
    }

    $rrd_filename = "app-zimbra-mailboxd.rrd";
    unset($rrd_values);

    foreach (array('lmtp_rcvd_msgs','lmtp_rcvd_bytes','lmtp_rcvd_rcpt','lmtp_dlvd_msgs','lmtp_dlvd_bytes','db_conn_count','db_conn_ms_avg','ldap_dc_count','ldap_dc_ms_avg','mbox_add_msg_count',
      'mbox_add_msg_ms_avg','mbox_get_count','mbox_get_ms_avg','mbox_cache','mbox_msg_cache','mbox_item_cache','soap_count','soap_ms_avg','imap_count','imap_ms_avg','pop_count','pop_ms_avg',
      'idx_wrt_avg','idx_wrt_opened','idx_wrt_opened_cache_hit','calcache_hit','calcache_mem_hit','calcache_lru_size','idx_bytes_written','idx_bytes_written_avg','idx_bytes_read','idx_bytes_read_avg',
      'bis_read','bis_seek_rate','db_pool_size','innodb_bp_hit_rate','lmtp_conn','lmtp_threads','pop_conn','pop_threads','pop_ssl_conn','pop_ssl_threads','imap_conn','imap_threads','imap_ssl_conn',
      'imap_ssl_threads','http_idle_threads','http_threads','soap_sessions','mbox_cache_size','msg_cache_size','fd_cache_size','fd_cache_hit_rate','acl_cache_hit_rate','account_cache_size',
      'account_cache_hit_rate','cos_cache_size','cos_cache_hit_rate','domain_cache_size','domain_cache_hit_rate','server_cache_size','server_cache_hit_rate','ucservice_cache_size',
      'ucservice_cache_hit_rate','zimlet_cache_size','zimlet_cache_hit_rate','group_cache_size','group_cache_hit_rate','xmpp_cache_size','xmpp_cache_hit_rate','gc_parnew_count','gc_parnew_ms',
      'gc_concurrentmarksweep_count','gc_concurrentmarksweep_ms','gc_minor_count','gc_minor_ms','gc_major_count','gc_major_ms','mpool_code_cache_used','mpool_code_cache_free','mpool_par_eden_space_used',
      'mpool_par_eden_space_free','mpool_par_survivor_space_used','mpool_par_survivor_space_free','mpool_cms_old_gen_used','mpool_cms_old_gen_free','mpool_cms_perm_gen_used','mpool_cms_perm_gen_free',
      'heap_used','heap_free') as $key)
    {
      $rrd_values[] = (is_numeric($zimbra['mailboxd-total'][$key]) ? $zimbra['mailboxd-total'][$key] : "U");
    }

    rrdtool_create($device, $rrd_filename, " \
        DS:lmtpRcvdMsgs:DERIVE:600:0:125000000000 \
        DS:lmtpRcvdBytes:DERIVE:600:0:125000000000 \
        DS:lmtpRcvdRcpt:DERIVE:600:0:125000000000 \
        DS:lmtpDlvdMsgs:DERIVE:600:0:125000000000 \
        DS:lmtpDlvdBytes:DERIVE:600:0:125000000000 \
        DS:dbConnCount:GAUGE:600:0:125000000000 \
        DS:dbConnMsAvg:GAUGE:600:0:125000000000 \
        DS:ldapDcCount:GAUGE:600:0:125000000000 \
        DS:ldapDcMsAvg:GAUGE:600:0:125000000000 \
        DS:mboxAddMsgCount:DERIVE:600:0:125000000000 \
        DS:mboxAddMsgMsAvg:GAUGE:600:0:125000000000 \
        DS:mboxGetCount:DERIVE:600:0:125000000000 \
        DS:mboxGetMsAvg:GAUGE:600:0:125000000000 \
        DS:mboxCache:GAUGE:600:0:125000000000 \
        DS:mboxMsgCache:GAUGE:600:0:125000000000 \
        DS:mboxItemCache:GAUGE:600:0:125000000000 \
        DS:soapCount:DERIVE:600:0:125000000000 \
        DS:soapMsAvg:GAUGE:600:0:125000000000 \
        DS:imapCount:DERIVE:600:0:125000000000 \
        DS:imapMsAvg:GAUGE:600:0:125000000000 \
        DS:popCount:DERIVE:600:0:125000000000 \
        DS:popMsAvg:GAUGE:600:0:125000000000 \
        DS:idxWrtAvg:GAUGE:600:0:125000000000 \
        DS:idxWrtOpened:GAUGE:600:0:125000000000 \
        DS:idxWrtOpenedCacheHt:GAUGE:600:0:125000000000 \
        DS:calcacheHit:GAUGE:600:0:125000000000 \
        DS:calcacheMemHit:GAUGE:600:0:125000000000 \
        DS:calcacheLruSize:GAUGE:600:0:125000000000 \
        DS:idxBytesWritten:DERIVE:600:0:125000000000 \
        DS:idxBytesWrittenAvg:GAUGE:600:0:125000000000 \
        DS:idxBytesRead:DERIVE:600:0:125000000000 \
        DS:idxBytesReadAvg:GAUGE:600:0:125000000000 \
        DS:bisRead:DERIVE:600:0:125000000000 \
        DS:bisSeekRate:GAUGE:600:0:125000000000 \
        DS:dbPoolSize:GAUGE:600:0:125000000000 \
        DS:innodbBpHitRate:GAUGE:600:0:125000000000 \
        DS:lmtpConn:GAUGE:600:0:125000000000 \
        DS:lmtpThreads:GAUGE:600:0:125000000000 \
        DS:popConn:GAUGE:600:0:125000000000 \
        DS:popThreads:GAUGE:600:0:125000000000 \
        DS:popSslConn:GAUGE:600:0:125000000000 \
        DS:popSslThreads:GAUGE:600:0:125000000000 \
        DS:imapConn:GAUGE:600:0:125000000000 \
        DS:imapThreads:GAUGE:600:0:125000000000 \
        DS:imapSslConn:GAUGE:600:0:125000000000 \
        DS:imapSslThreads:GAUGE:600:0:125000000000 \
        DS:httpIdleThreads:GAUGE:600:0:125000000000 \
        DS:httpThreads:GAUGE:600:0:125000000000 \
        DS:soapSessions:GAUGE:600:0:125000000000 \
        DS:mboxCacheSize:GAUGE:600:0:125000000000 \
        DS:msgCacheSize:GAUGE:600:0:125000000000 \
        DS:fdCacheSize:GAUGE:600:0:125000000000 \
        DS:fdCacheHitRate:GAUGE:600:0:125000000000 \
        DS:aclCacheHitRate:GAUGE:600:0:125000000000 \
        DS:accountCacheSize:GAUGE:600:0:125000000000 \
        DS:accountCacheHitRate:GAUGE:600:0:125000000000 \
        DS:cosCacheSize:GAUGE:600:0:125000000000 \
        DS:cosCacheHitRate:GAUGE:600:0:125000000000 \
        DS:domainCacheSize:GAUGE:600:0:125000000000 \
        DS:domainCacheHitRate:GAUGE:600:0:125000000000 \
        DS:serverCacheSize:GAUGE:600:0:125000000000 \
        DS:serverCacheHitRate:GAUGE:600:0:125000000000 \
        DS:ucsvcCacheSize:GAUGE:600:0:125000000000 \
        DS:ucsvcCacheHitRate:GAUGE:600:0:125000000000 \
        DS:zimletCacheSize:GAUGE:600:0:125000000000 \
        DS:zimletCacheHitRate:GAUGE:600:0:125000000000 \
        DS:groupCacheSize:GAUGE:600:0:125000000000 \
        DS:groupCacheHitRate:GAUGE:600:0:125000000000 \
        DS:xmppCacheSize:GAUGE:600:0:125000000000 \
        DS:xmppCacheHitRate:GAUGE:600:0:125000000000 \
        DS:gcParnewCount:GAUGE:600:0:125000000000 \
        DS:gcParnewMs:GAUGE:600:0:125000000000 \
        DS:gcConcmarksweepCnt:GAUGE:600:0:125000000000 \
        DS:gcConcmarksweepMs:GAUGE:600:0:125000000000 \
        DS:gcMinorCount:DERIVE:600:0:125000000000 \
        DS:gcMinorMs:GAUGE:600:0:125000000000 \
        DS:gcMajorCount:DERIVE:600:0:125000000000 \
        DS:gcMajorMs:GAUGE:600:0:125000000000 \
        DS:mpoolCodeCacheUsed:GAUGE:600:0:125000000000 \
        DS:mpoolCodeCacheFree:GAUGE:600:0:125000000000 \
        DS:mpoolParEdenSpcUsed:GAUGE:600:0:125000000000 \
        DS:mpoolParEdenSpcFree:GAUGE:600:0:125000000000 \
        DS:mpoolParSurvSpcUsed:GAUGE:600:0:125000000000 \
        DS:mpoolParSurvSpcFree:GAUGE:600:0:125000000000 \
        DS:mpoolCmsOldGenUsed:GAUGE:600:0:125000000000 \
        DS:mpoolCmsOldGenFree:GAUGE:600:0:125000000000 \
        DS:mpoolCmsPermGenUsed:GAUGE:600:0:125000000000 \
        DS:mpoolCmsPermGenFree:GAUGE:600:0:125000000000 \
        DS:heapUsed:GAUGE:600:0:125000000000 \
        DS:heapFree:GAUGE:600:0:125000000000 ");

    rrdtool_update($device, $rrd_filename, "N:" . implode(':', $rrd_values));
  }

  if (is_array($zimbra['proc']))
  {
    /*
    timestamp, system, user, sys, idle, iowait, mailbox, mailbox-total-cpu, mailbox-utime, mailbox-stime, mailbox-totalMB, mailbox-rssMB, mailbox-sharedMB, mailbox-process-count, mysql, mysql-total-cpu, mysql-utime, mysql-stime, mysql-totalMB, mysql-rssMB, mysql-sharedMB, mysql-process-count, convertd, convertd-total-cpu, convertd-utime, convertd-stime, convertd-totalMB, convertd-rssMB, convertd-sharedMB, convertd-process-count, ldap, ldap-total-cpu, ldap-utime, ldap-stime, ldap-totalMB, ldap-rssMB, ldap-sharedMB, ldap-process-count, postfix, postfix-total-cpu, postfix-utime, postfix-stime, postfix-totalMB, postfix-rssMB, postfix-sharedMB, postfix-process-count, amavis, amavis-total-cpu, amavis-utime, amavis-stime, amavis-totalMB, amavis-rssMB, amavis-sharedMB, amavis-process-count, clam, clam-total-cpu, clam-utime, clam-stime, clam-totalMB, clam-rssMB, clam-sharedMB, clam-process-count, zmstat, zmstat-total-cpu, zmstat-utime, zmstat-stime, zmstat-totalMB, zmstat-rssMB, zmstat-sharedMB, zmstat-process-count
    04/27/2013 18:05:30, system, 3.9, 1.0, 94.4, 0.7, mailbox, 0.4, 0.4, 0.0, 1074.9, 863.8, 5.8, 1, mysql, 0.1, 0.1, 0.0, 956.5, 785.3, 4.1, 1, convertd, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0, ldap, 0.0, 0.0, 0.0, 82300.6, 50.0, 4.5, 1, postfix, 0.0, 0.0, 0.0, 54.1, 1.5, 0.6, 5, amavis, 0.0, 0.0, 0.0, 150.8, 52.3, 3.4, 11, clam, 0.0, 0.0, 0.0, 42.4, 1.9, 1.0, 2, zmstat, 0.1, 0.1, 0.0, 4.0, 0.6, 0.4, 15
    */

    foreach (array('mailbox', 'mysql', 'convertd', 'ldap', 'postfix', 'amavis', 'clam', 'zmstat') as $app)
    {
      $rrd_filename = "app-zimbra-proc-$app.rrd";
      unset($rrd_values);

      if ($zimbra['proc'][0][$app.'-process-count'])
      {
        foreach (array('total-cpu','utime','stime','totalMB','rssMB','sharedMB','process-count') as $key)
        {
          $rrd_values[] = (is_numeric($zimbra['proc'][0][$app.'-'.$key]) ? $zimbra['proc'][0][$app.'-'.$key] : "U");
        }

        rrdtool_create($device, $rrd_filename, " \
            DS:totalCPU:GAUGE:600:0:100 \
            DS:utime:GAUGE:600:0:U \
            DS:stime:GAUGE:600:0:U \
            DS:totalMB:GAUGE:600:0:U \
            DS:rssMB:GAUGE:600:0:U \
            DS:sharedMB:GAUGE:600:0:U \
            DS:processCount:GAUGE:600:0:U ");

        rrdtool_update($device, $rrd_filename, "N:" . implode(':', $rrd_values));
      }
    }
  }

  /// FIXME - All commands listed in a year of my CSVs - may be incomplete?
  $commandlist['imap'] = array('APPEND','AUTHENTICATE','BASIC','CAPABILITY','CHECK','CLOSE','CREATE','DELETE','ENABLE','EXAMINE','EXPUNGE','FETCH','GETACL','GETQUOTAROOT',
    'ID','IDLE','LIST','LOGIN','LOGOUT','LSUB','MYRIGHTS','NAMESPACE','NOOP','RENAME','SEARCH','SELECT','STARTTLS','STATUS','STORE','SUBSCRIBE','UID','UNSELECT',
    'UNSUBSCRIBE','XLIST');

  $commandlist['ldap'] = array('CREATE_ENTRY','DELETE_ENTRY','GET_CONN','GET_ENTRY','GET_SCHEMA','MODIFY_ATTRS','OPEN_CONN','SEARCH_ACCOUNT_BY_ID','SEARCH_ACCOUNT_BY_NAME',
    'SEARCH_ACCOUNTS_BY_GRANTS','SEARCH_ACCOUNTS_HOMED_ON_SERVER','SEARCH_ACCOUNTS_HOMED_ON_SERVER_ACCOUNTS_ONLY','SEARCH_ADMIN_ACCOUNT_BY_RDN','SEARCH_ADMIN_SEARCH',
    'SEARCH_ALL_ALIASES','SEARCH_ALL_CALENDAR_RESOURCES','SEARCH_ALL_DATA_SOURCES','SEARCH_ALL_GROUPS','SEARCH_ALL_IDENTITIES','SEARCH_ALL_MIME_ENTRIES',
    'SEARCH_ALL_NON_SYSTEM_ACCOUNTS','SEARCH_ALL_NON_SYSTEM_ARCHIVING_ACCOUNTS','SEARCH_ALL_NON_SYSTEM_INTERNAL_ACCOUNTS','SEARCH_ALL_SERVERS','SEARCH_ALL_SIGNATURES',
    'SEARCH_ALL_UC_SERVICES','SEARCH_ALL_ZIMLETS','SEARCH_DATA_SOURCE_BY_ID','SEARCH_DISTRIBUTION_LIST_BY_ID','SEARCH_DISTRIBUTION_LIST_BY_NAME','SEARCH_DISTRIBUTION_LISTS_BY_MEMBER_ADDRS',
    'SEARCH_DOMAIN_BY_ID','SEARCH_DOMAIN_BY_NAME','SEARCH_DOMAIN_BY_VIRTUAL_HOSTNAME','SEARCH_DYNAMIC_GROUP_BY_NAME','SEARCH_DYNAMIC_GROUPS_STATIC_UNIT_BY_MEMBER_ADDR',
    'SEARCH_EXTERNAL_ACCOUNTS_HOMED_ON_SERVER','SEARCH_GAL_SEARCH','SEARCH_GROUP_BY_ID','SEARCH_GROUP_BY_NAME','SEARCH_LDAP_AUTHENTICATE','SEARCH_MIME_ENTRY_BY_MIME_TYPE',
    'SEARCH_SEARCH_GRANTEE','SEARCH_SERVER_BY_SERVICE','SEARCH_SHARE_LOCATOR_BY_ID','SEARCH_TODO');

  $commandlist['pop3'] = array('DELE','LIST','RETR','QUIT','STAT');

  $commandlist['sync'] = array('FolderSync','GetAttachment','GetItemEstimate','ItemOperations','MeetingResponse','MoveItems','Ping_after_suspend','Ping_before_suspend',
    'Provision','Search','Settings','Sync');

  $commandlist['soap'] = array('ActivateLicenseRequest','AddAccountAliasRequest','AddDistributionListAliasRequest','AddDistributionListMemberRequest','AddMsgRequest',
    'ApplyFilterRulesRequest','AuthRequest','AutoCompleteGalRequest','AutoCompleteRequest','BackupQueryRequest','BackupRequest','BrowseRequest','BulkImportAccountsRequest',
    'CancelAppointmentRequest','CancelTaskRequest','ChangePasswordRequest','CheckAuthConfigRequest','CheckLicenseRequest','CheckPermissionRequest','CheckRecurConflictsRequest',
    'CheckRightsRequest','CheckSpellingRequest','ClearCookieRequest','ComputeAggregateQuotaUsageRequest','ContactActionRequest.delete','ContactActionRequest.move','ContactActionRequest.update',
    'ConvActionRequest.delete','ConvActionRequest.flag','ConvActionRequest.!flag','ConvActionRequest.move','ConvActionRequest.read','ConvActionRequest.!read','ConvActionRequest.spam',
    'ConvActionRequest.!spam','ConvActionRequest.trash','CounterAppointmentRequest','CountObjectsRequest','CreateAccountRequest','CreateAppointmentExceptionRequest','CreateAppointmentRequest',
    'CreateCalendarResourceRequest','CreateContactRequest','CreateDistributionListRequest','CreateDomainRequest','CreateFolderRequest','CreateGalSyncAccountRequest','CreateIdentityRequest',
    'CreateMountpointRequest','CreateSignatureRequest','CreateTagRequest','CreateTaskRequest','CreateWaitSetRequest','DelegateAuthRequest','DeleteAccountRequest','DeleteCalendarResourceRequest',
    'DeleteIdentityRequest','DeleteSignatureRequest','DestroyWaitSetRequest','DiscoverRightsRequest','DismissCalendarItemAlarmRequest','EndSessionRequest','FlushCacheRequest','FolderActionRequest.check',
    'FolderActionRequest.!check','FolderActionRequest.color','FolderActionRequest.delete','FolderActionRequest.empty','FolderActionRequest.grant','FolderActionRequest.!grant','FolderActionRequest.move',
    'FolderActionRequest.read','FolderActionRequest.rename','FolderActionRequest.retentionpolicy','FolderActionRequest.revokeorphangrants','FolderActionRequest.trash','FolderActionRequest.update',
    'ForwardAppointmentRequest','GenerateBulkProvisionFileFromLDAPRequest','GetAccountDistributionListsRequest','GetAccountInfoRequest','GetAccountMembershipRequest','GetAccountRequest',
    'GetAdminConsoleUICompRequest','GetAdminExtensionZimletsRequest','GetAdminSavedSearchesRequest','GetAggregateQuotaUsageOnServerRequest','GetAllConfigRequest','GetAllCosRequest','GetAllRightsRequest',
    'GetAllServersRequest','GetAllSkinsRequest','GetAllUCProvidersRequest','GetAllUCServicesRequest','GetAllVolumesRequest','GetAllZimletsRequest','GetAppointmentRequest','GetAttributeInfoRequest',
    'GetAvailableCsvFormatsRequest','GetAvailableLocalesRequest','GetAvailableSkinsRequest','GetBulkIMAPImportTaskListRequest','GetCalendarResourceRequest','GetCertRequest','GetConfigRequest',
    'GetContactsRequest','GetConvRequest','GetCosRequest','GetCreateObjectAttrsRequest','GetCSRRequest','GetCurrentVolumesRequest','GetDataSourcesRequest','GetDevicesCountRequest','GetDeviceStatusRequest',
    'GetDistributionListMembershipRequest','GetDistributionListMembersRequest','GetDistributionListRequest','GetDomainInfoRequest','GetDomainRequest','GetEffectiveRightsRequest','GetFilterRulesRequest',
    'GetFolderRequest','GetFreeBusyRequest','GetGrantsRequest','GetHsmStatusRequest','GetIdentitiesRequest','GetInfoRequest','GetItemRequest','GetLicenseRequest','GetLoggerStatsRequest',
    'GetMailboxMetadataRequest','GetMailboxRequest','GetMailQueueInfoRequest','GetMailQueueRequest','GetMiniCalRequest','GetMsgRequest','GetOutgoingFilterRulesRequest','GetPermissionRequest',
    'GetPrefsRequest','GetQuotaUsageRequest','GetRightRequest','GetRightsRequest','GetServerNIfsRequest','GetServerRequest','GetServiceStatusRequest','GetSessionsRequest','GetShareInfoRequest',
    'GetSignaturesRequest','GetSystemRetentionPolicyRequest','GetTaskRequest','GetVersionInfoRequest','GetWhiteBlackListRequest','GetWorkingHoursRequest','GrantPermissionRequest','GrantRightsRequest',
    'ImportAppointmentsRequest','InstallLicenseRequest','ItemActionRequest.copy','ItemActionRequest.delete','ItemActionRequest.move','ItemActionRequest.read','ItemActionRequest.!read','ItemActionRequest.tag',
    'ItemActionRequest.!tag','ItemActionRequest.trash','ItemActionRequest.update','MailQueueActionRequest','ModifyAccountRequest','ModifyAdminSavedSearchesRequest','ModifyAppointmentRequest',
    'ModifyCalendarResourceRequest','ModifyConfigRequest','ModifyContactRequest','ModifyCosRequest','ModifyDistributionListRequest','ModifyDomainRequest','ModifyFilterRulesRequest','ModifyIdentityRequest',
    'ModifyPrefsRequest','ModifyPropertiesRequest','ModifyServerRequest','ModifySignatureRequest','ModifyTaskRequest','ModifyWhiteBlackListRequest','ModifyZimletPrefsRequest','MsgActionRequest.delete',
    'MsgActionRequest.flag','MsgActionRequest.!flag','MsgActionRequest.move','MsgActionRequest.read','MsgActionRequest.!read','MsgActionRequest.spam','MsgActionRequest.trash','MsgActionRequest.update',
    'NoOpRequest','RankingActionRequest.delete','RemoveAccountAliasRequest','RemoveDeviceRequest','RemoveDistributionListMemberRequest','RenameAccountRequest','SaveDocumentRequest','SaveDraftRequest',
    'SearchCalendarResourcesRequest','SearchConvRequest','SearchDirectoryRequest','SearchGalRequest','SearchRequest','SendDeliveryReportRequest','SendInviteReplyRequest','SendMsgRequest',
    'SendShareNotificationRequest','SetAppointmentRequest','SetMailboxMetadataRequest','SetTaskRequest','SnoozeCalendarItemAlarmRequest','SyncGalAccountRequest','SyncGalRequest','SyncRequest',
    'UndeployZimletRequest','VersionCheckRequest','WaitSetRequest');

  /// FIXME ldap & soap currently disabled: command names longer than allowed DS length
  foreach (array('imap','pop3','sync') as $protocol)
  {
    if (is_array($zimbra[$protocol]))
    {
      /*
      timestamp,command,exec_count,exec_ms_avg
      04/27/2013 22:33:38,CHECK,1,0
      */

      $rrd_filename = "app-zimbra-proto-$protocol.rrd";
      unset($rrd_values, $commands, $ds_list);

      foreach ($zimbra[$protocol] as $line)
      {
        $commands[$line['command']]['exec_count'] = $line['exec_count'];
        $commands[$line['command']]['exec_msg_avg'] = $line['exec_ms_avg'];
      }

      foreach ($commandlist[$protocol] as $command)
      {
        foreach (array('exec_count','exec_ms_avg') as $key)
        {
          $rrd_values[] = (is_numeric($commands[$command][$key]) ? $commands[$command][$key] : "0");
        }

        $ds_cmd = str_replace('!','N',substr($command,0,18));
        $ds_list .= "DS:c$ds_cmd:GAUGE:600:0:U DS:a$ds_cmd:GAUGE:600:0:U ";
      }

        rrdtool_create($device, $rrd_filename, " "
          . $ds_list );

      rrdtool_update($device, $rrd_filename, "N:" . implode(':', $rrd_values));
    }
  }
}

// EOF
