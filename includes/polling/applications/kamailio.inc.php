<?php
if (!empty($agent_data['app']['kamailio']))
{
  $rrd_filename = "app-kamailio-".$app['app_id'].".rrd";

  unset($data);

  $key_trans_table = array(
    'core:bad_URIs_rcvd' => 'corebadURIsrcvd',
    'core:bad_msg_hdr' => 'corebadmsghdr',
    'core:drop_replies' => 'coredropreplies',
    'core:drop_requests' => 'coredroprequests',
    'core:err_replies' => 'coreerrreplies',
    'core:err_requests' => 'coreerrrequests',
    'core:fwd_replies' => 'corefwdreplies',
    'core:fwd_requests' => 'corefwdrequests',
    'core:rcv_replies' => 'corercvreplies',
    'core:rcv_requests' => 'corercvrequests',
    'core:unsupported_methods' => 'coreunsupportedmeth',
    'dns:failed_dns_request' => 'dnsfaileddnsrequest',
    'mysql:driver_errors' => 'mysqldrivererrors',
    'registrar:accepted_regs' => 'registraraccregs',
    'registrar:default_expire' => 'registrardefexpire',
    'registrar:default_expires_range' => 'registrardefexpirer',
    'registrar:max_contacts' => 'registrarmaxcontact',
    'registrar:max_expires' => 'registrarmaxexpires',
    'registrar:rejected_regs' => 'registrarrejregs',
    'shmem:fragments' => 'shmemfragments',
    'shmem:free_size' => 'shmemfreesize',
    'shmem:max_used_size' => 'shmemmaxusedsize',
    'shmem:real_used_size' => 'shmemrealusedsize',
    'shmem:total_size' => 'shmemtotalsize',
    'shmem:used_size' => 'shmemusedsize',
    'siptrace:traced_replies' => 'siptracetracedrepl',
    'siptrace:traced_requests' => 'siptracetracedreq',
    'sl:1xx_replies' => 'sl1xxreplies',
    'sl:200_replies' => 'sl200replies',
    'sl:202_replies' => 'sl202replies',
    'sl:2xx_replies' => 'sl2xxreplies',
    'sl:300_replies' => 'sl300replies',
    'sl:301_replies' => 'sl301replies',
    'sl:302_replies' => 'sl302replies',
    'sl:3xx_replies' => 'sl3xxreplies',
    'sl:400_replies' => 'sl400replies',
    'sl:401_replies' => 'sl401replies',
    'sl:403_replies' => 'sl403replies',
    'sl:404_replies' => 'sl404replies',
    'sl:407_replies' => 'sl407replies',
    'sl:408_replies' => 'sl408replies',
    'sl:483_replies' => 'sl483replies',
    'sl:4xx_replies' => 'sl4xxreplies',
    'sl:500_replies' => 'sl500replies',
    'sl:5xx_replies' => 'sl5xxreplies',
    'sl:6xx_replies' => 'sl6xxreplies',
    'sl:failures' => 'slfailures',
    'sl:received_ACKs' => 'slreceivedACKs',
    'sl:sent_err_replies' => 'slsenterrreplies',
    'sl:sent_replies' => 'slsentreplies',
    'sl:xxx_replies' => 'slxxxreplies',
    'tcp:con_reset' => 'tcpconreset',
    'tcp:con_timeout' => 'tcpcontimeout',
    'tcp:connect_failed' => 'tcpconnectfailed',
    'tcp:connect_success' => 'tcpconnectsuccess',
    'tcp:current_opened_connections' => 'tcpcurrentopenedcon',
    'tcp:current_write_queue_size' => 'tcpcurrentwrqsize',
    'tcp:established' => 'tcpestablished',
    'tcp:local_reject' => 'tcplocalreject',
    'tcp:passive_open' => 'tcppassiveopen',
    'tcp:send_timeout' => 'tcpsendtimeout',
    'tcp:sendq_full' => 'tcpsendqfull',
    'tmx:2xx_transactions' => 'tmx2xxtransactions',
    'tmx:3xx_transactions' => 'tmx3xxtransactions',
    'tmx:4xx_transactions' => 'tmx4xxtransactions',
    'tmx:5xx_transactions' => 'tmx5xxtransactions',
    'tmx:6xx_transactions' => 'tmx6xxtransactions',
    'tmx:UAC_transactions' => 'tmxUACtransactions',
    'tmx:UAS_transactions' => 'tmxUAStransactions',
    'tmx:inuse_transactions' => 'tmxinusetransaction',
    'tmx:local_replies' => 'tmxlocalreplies',
    'usrloc:location-contacts' => 'usrlocloccontacts',
    'usrloc:location-expires' => 'usrloclocexpires',
    'usrloc:location-users' => 'usrloclocusers',
    'usrloc:registered_users' => 'usrlocregusers',
  );

  foreach ($key_trans_table as $key => $value){
    $data[$value] = 0;
  }

  $lines = explode("\n", $agent_data['app']['kamailio']);
  foreach ($lines as $line)
  {
    list($key, $val) = explode("=", $line);
    $key = trim($key);

    if(substr($key, 0, 6) == 'usrloc'){
      $tmp = substr($key, strpos($key, '-') + 1);
      switch($tmp){
        case 'contacts':
        case 'expires':
        case 'users':
          $key = 'usrloc:location-' . $tmp;
          break;
      }
    }

    if(isset($key_trans_table[$key])){
      $data[$key_trans_table[$key]] = (int) trim($val);
    }else{
      if($debug) {
        echo "nick - key is not : $key\n";
      }
    }
  }

  rrdtool_create($device, $rrd_filename, "\
    DS:corebadURIsrcvd:COUNTER:600:0:125000000000 \
    DS:corebadmsghdr:COUNTER:600:0:125000000000 \
    DS:coredropreplies:COUNTER:600:0:125000000000 \
    DS:coredroprequests:COUNTER:600:0:125000000000 \
    DS:coreerrreplies:COUNTER:600:0:125000000000 \
    DS:coreerrrequests:COUNTER:600:0:125000000000 \
    DS:corefwdreplies:COUNTER:600:0:125000000000 \
    DS:corefwdrequests:COUNTER:600:0:125000000000 \
    DS:corercvreplies:COUNTER:600:0:125000000000 \
    DS:corercvrequests:COUNTER:600:0:125000000000 \
    DS:coreunsupportedmeth:COUNTER:600:0:125000000000 \
    DS:dnsfaileddnsrequest:COUNTER:600:0:125000000000 \
    DS:mysqldrivererrors:COUNTER:600:0:125000000000 \
    DS:registraraccregs:COUNTER:600:0:125000000000 \
    DS:registrardefexpire:GAUGE:600:0:125000000000 \
    DS:registrardefexpirer:GAUGE:600:0:125000000000 \
    DS:registrarmaxcontact:GAUGE:600:0:125000000000 \
    DS:registrarmaxexpires:GAUGE:600:0:125000000000 \
    DS:registrarrejregs:COUNTER:600:0:125000000000 \
    DS:shmemfragments:GAUGE:600:0:125000000000 \
    DS:shmemfreesize:GAUGE:600:0:125000000000 \
    DS:shmemmaxusedsize:GAUGE:600:0:125000000000 \
    DS:shmemrealusedsize:GAUGE:600:0:125000000000 \
    DS:shmemtotalsize:GAUGE:600:0:125000000000 \
    DS:shmemusedsize:GAUGE:600:0:125000000000 \
    DS:siptracetracedrepl:COUNTER:600:0:125000000000 \
    DS:siptracetracedreq:COUNTER:600:0:125000000000 \
    DS:sl1xxreplies:COUNTER:600:0:125000000000 \
    DS:sl200replies:COUNTER:600:0:125000000000 \
    DS:sl202replies:COUNTER:600:0:125000000000 \
    DS:sl2xxreplies:COUNTER:600:0:125000000000 \
    DS:sl300replies:COUNTER:600:0:125000000000 \
    DS:sl301replies:COUNTER:600:0:125000000000 \
    DS:sl302replies:COUNTER:600:0:125000000000 \
    DS:sl3xxreplies:COUNTER:600:0:125000000000 \
    DS:sl400replies:COUNTER:600:0:125000000000 \
    DS:sl401replies:COUNTER:600:0:125000000000 \
    DS:sl403replies:COUNTER:600:0:125000000000 \
    DS:sl404replies:COUNTER:600:0:125000000000 \
    DS:sl407replies:COUNTER:600:0:125000000000 \
    DS:sl408replies:COUNTER:600:0:125000000000 \
    DS:sl483replies:COUNTER:600:0:125000000000 \
    DS:sl4xxreplies:COUNTER:600:0:125000000000 \
    DS:sl500replies:COUNTER:600:0:125000000000 \
    DS:sl5xxreplies:COUNTER:600:0:125000000000 \
    DS:sl6xxreplies:COUNTER:600:0:125000000000 \
    DS:slfailures:COUNTER:600:0:125000000000 \
    DS:slreceivedACKs:COUNTER:600:0:125000000000 \
    DS:slsenterrreplies:COUNTER:600:0:125000000000 \
    DS:slsentreplies:COUNTER:600:0:125000000000 \
    DS:slxxxreplies:COUNTER:600:0:125000000000 \
    DS:tcpconreset:GAUGE:600:0:125000000000 \
    DS:tcpcontimeout:GAUGE:600:0:125000000000 \
    DS:tcpconnectfailed:GAUGE:600:0:125000000000 \
    DS:tcpconnectsuccess:GAUGE:600:0:125000000000 \
    DS:tcpcurrentopenedcon:GAUGE:600:0:125000000000 \
    DS:tcpcurrentwrqsize:GAUGE:600:0:125000000000 \
    DS:tcpestablished:GAUGE:600:0:125000000000 \
    DS:tcplocalreject:GAUGE:600:0:125000000000 \
    DS:tcppassiveopen:GAUGE:600:0:125000000000 \
    DS:tcpsendtimeout:GAUGE:600:0:125000000000 \
    DS:tcpsendqfull:GAUGE:600:0:125000000000 \
    DS:tmx2xxtransactions:COUNTER:600:0:125000000000 \
    DS:tmx3xxtransactions:COUNTER:600:0:125000000000 \
    DS:tmx4xxtransactions:COUNTER:600:0:125000000000 \
    DS:tmx5xxtransactions:COUNTER:600:0:125000000000 \
    DS:tmx6xxtransactions:COUNTER:600:0:125000000000 \
    DS:tmxUACtransactions:COUNTER:600:0:125000000000 \
    DS:tmxUAStransactions:COUNTER:600:0:125000000000 \
    DS:tmxinusetransaction:GAUGE:600:0:125000000000 \
    DS:tmxlocalreplies:COUNTER:600:0:125000000000 \
    DS:usrlocloccontacts:GAUGE:600:0:125000000000 \
    DS:usrloclocexpires:COUNTER:600:0:125000000000 \
    DS:usrloclocusers:GAUGE:600:0:125000000000 \
    DS:usrlocregusers:GAUGE:600:0:125000000000");

  if($debug) {
    echo "nick - data: " . print_r($data, true) . "\n";
  }

  $rrd_update = 'N';
  foreach ($data as $param => $value)
  {
    $rrd_update .= ':'.$value;
  }

  rrdtool_update($device, $rrd_filename, $rrd_update);
}

// EOF
