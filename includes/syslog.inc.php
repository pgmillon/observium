<?php

/**
 * Observium Network Management and Monitoring System
 * Copyright (C) 2006-2015, Adam Armstrong - http://www.observium.org
 *
 * @package    observium
 * @subpackage syslog
 * @author     Adam Armstrong <adama@observium.org>
 * @copyright  (C) 2006-2013 Adam Armstrong, (C) 2013-2016 Observium Limited
 *
 */

// FIXME use db functions properly

// DOCME needs phpdoc block
// TESTME needs unit testing
function get_cache($host, $value)
{
  global $dev_cache;

  $host = strtolower(trim($host));

  // Check cache expiration
  $now = time();
  $expired = TRUE;
  if (isset($dev_cache[$host]['lastchecked']))
  {
    if (($now - $dev_cache[$host]['lastchecked']) < 600) { $expired = FALSE; } // will expire after 10 min
  }
  if ($expired) { $dev_cache[$host]['lastchecked'] = $now; }

  if (!isset($dev_cache[$host][$value]) || $expired)
  {
    switch($value)
    {
      case 'device_id':
        // Try by hostname
        $dev_cache[$host]['device_id'] = dbFetchCell('SELECT `device_id` FROM `devices` WHERE `hostname` = ? OR `sysName` = ?', array($host, $host));
        // If failed, try by IP
        if (!is_numeric($dev_cache[$host]['device_id']))
        {
          if (preg_match('/::ffff:(\d+\.\d+\.\d+\.\d+)/i', $host, $matches))
          {
            // IPv4 mapped to IPv6, like ::ffff:192.0.2.128
            // See: http://jira.observium.org/browse/OBSERVIUM-1274
            $ip = $matches[1];
          } else {
            $ip = $host;
          }
          $ip_version = get_ip_version($ip);
          if ($ip_version !== FALSE)
          {
            if ($ip_version == 6) { $ip = Net_IPv6::uncompress($ip, TRUE); }
            $address_count = dbFetchCell('SELECT COUNT(*) FROM `ipv'.$ip_version.'_addresses` WHERE `ipv'.$ip_version.'_address` = ?;', array($ip));
            if ($address_count)
            {
              $query = 'SELECT `device_id` FROM `ipv'.$ip_version.'_addresses` AS A, `ports` AS I WHERE A.`ipv'.$ip_version.'_address` = ? AND I.`port_id` = A.`port_id`';
              // If more than one IP address, also check the status of the port.
              if ($address_count > 1) { $query .= " AND I.`ifOperStatus` = 'up'"; }
              $dev_cache[$host]['device_id'] = dbFetchCell($query, array($ip));
            }
          }
        }
        break;
      case 'os':
      case 'version':
        $dev_cache[$host][$value] = dbFetchCell('SELECT `'.$value.'` FROM `devices` WHERE `device_id` = ?', array(get_cache($host, 'device_id')));
        break;
      case 'os_group':
        $os = get_cache($host, 'os');
        $dev_cache[$host]['os_group'] = (isset($GLOBALS['config']['os'][$os]['group']) ? $GLOBALS['config']['os'][$os]['group'] : '');
        break;
      default:
        return NULL;
    }
  }
  return $dev_cache[$host][$value];
}

// DOCME needs phpdoc block
// TESTME needs unit testing
function process_syslog($entry, $update)
{
  global $config;

  foreach ($config['syslog']['filter'] as $bi)
  {
    if (strpos($entry['msg'], $bi) !== FALSE)
    {
      //echo('D-'.$bi);
      return FALSE;
    }
  }

  $entry['device_id'] = get_cache($entry['host'], 'device_id');
  if ($entry['device_id'])
  {
    $os       = get_cache($entry['host'], 'os');
    $os_group = get_cache($entry['host'], 'os_group');

    if (in_array($os, array('ios', 'iosxe', 'catos')))
    {
      $matches = array();
#      if (preg_match('#%(?P<program>.*):( ?)(?P<msg>.*)#', $entry['msg'], $matches)) {
#        $entry['msg'] = $matches['msg'];
#        $entry['program'] = $matches['program'];
#      }
#      unset($matches);

      //NOTE. Please include examples for syslog entries, to know why need some preg_replace()
      if (strstr($entry['msg'], '%'))
      {
        //10.0.0.210||23||4||4||26644:||2013-11-08 07:19:24|| 033884: Nov  8 07:19:23.993: %FW-4-TCP_OoO_SEG: Dropping TCP Segment: seq:-1169729434 1500 bytes is out-of-order; expected seq:3124765814. Reason: TCP reassembly queue overflow - session 10.10.32.37:56316 to 93.186.239.142:80 on zone-pair Local->Internet class All_Inspection||26644
        //hostname||17||5||5||192462650:||2014-06-17 11:16:01|| %SSH-5-SSH2_SESSION: SSH2 Session request from 10.95.0.42 (tty = 0) using crypto cipher 'aes256-cbc', hmac 'hmac-sha1' Succeeded||192462650
        if (strpos($entry['msg'], ': %'))
        {
          list(,$entry['msg']) = explode(': %', $entry['msg'], 2);
          $entry['msg'] = "%" . $entry['msg'];
        }
        $entry['msg'] = preg_replace("/^%(.+?):\ /", "\\1||", $entry['msg']);
      } else {
        $entry['msg'] = preg_replace("/^.*[0-9]:/", "", $entry['msg']);
        $entry['msg'] = preg_replace("/^[0-9][0-9]\ [A-Z]{3}:/", "", $entry['msg']);
        $entry['msg'] = preg_replace("/^(.+?):\ /", "\\1||", $entry['msg']);
      }
      //$entry['msg'] = preg_replace("/^.+\.[0-9]{3}:/", "", $entry['msg']); /// FIXME. Show which entries this should replace. It's broke all entries with 'IP:PORT'.
      $entry['msg'] = preg_replace("/^.+-Traceback=/", "Traceback||", $entry['msg']);

      list($entry['program'], $entry['msg']) = explode("||", $entry['msg'], 2);
      $entry['msg'] = preg_replace("/^[0-9]+:/", "", $entry['msg']);

      if (!$entry['program'])
      {
         $entry['msg'] = preg_replace("/^([0-9A-Z\-]+?):\ /", "\\1||", $entry['msg']);
         list($entry['program'], $entry['msg']) = explode("||", $entry['msg'], 2);
      }

      if (!$entry['msg']) { $entry['msg'] = $entry['program']; unset ($entry['program']); }
    }
    else if ($os == 'iosxr')
    {
      //1.1.1.1||23||5||5||920:||2014-11-26 17:29:48||RP/0/RSP0/CPU0:Nov 26 16:29:48.161 : bgp[1046]: %ROUTING-BGP-5-ADJCHANGE : neighbor 1.1.1.2 Up (VRF: default) (AS: 11111) ||920
      //1.1.1.2||23||6||6||253:||2014-11-26 17:30:21||RP/0/RSP0/CPU0:Nov 26 16:30:21.710 : SSHD_[65755]: %SECURITY-SSHD-6-INFO_GENERAL : Client closes socket connection ||253
      //1.1.1.3||local0||err||err||83||2015-01-14 07:29:45||oly-er-01 LC/0/0/CPU0:Jan 14 07:29:45.556 CET: pfilter_ea[301]: %L2-PFILTER_EA-3-ERR_IM_CAPS : uidb set  acl failed on interface Bundle-Ether1.1501.ip43696. (null) ||94795
      list(, $entry['msg']) = explode(': %', $entry['msg'], 2);
      list($entry['program'], $entry['msg']) = explode(' : ', $entry['msg'], 2);
    }
    else if ($os == 'linux' && get_cache($entry['host'], 'version') == 'Point')
    {
      // Cisco WAP200 and similar
      $matches = array();
      if (preg_match('#Log: \[(?P<program>.*)\] - (?P<msg>.*)#', $entry['msg'], $matches))
      {
        $entry['msg'] = $matches['msg'];
        $entry['program'] = $matches['program'];
      }
      unset($matches);

    }
    else if ($os_group == 'unix')
    {
      $matches = array();
      // User_CommonName/123.213.132.231:39872 VERIFY OK: depth=1, /C=PL/ST=Malopolska/O=VLO/CN=v-lo.krakow.pl/emailAddress=root@v-lo.krakow.pl
      if ($entry['facility'] == 'daemon' && preg_match('#/([0-9]{1,3}\.) {3}[0-9]{1,3}:[0-9]{4,} ([A-Z]([A-Za-z])+( ?)) {2,}:#', $entry['msg']))
      {
        $entry['program'] = 'OpenVPN';
      }
      // pop3-login: Login: user=<username>, method=PLAIN, rip=123.213.132.231, lip=123.213.132.231, TLS
      // POP3(username): Disconnected: Logged out top=0/0, retr=0/0, del=0/1, size=2802
      else if ($entry['facility'] == 'mail' && preg_match('/^(((pop3|imap)\-login)|((POP3|IMAP)\(.*\))):/', $entry['msg']))
      {
        $entry['program'] = 'Dovecot';
      }
      // pam_krb5(sshd:auth): authentication failure; logname=root uid=0 euid=0 tty=ssh ruser= rhost=123.213.132.231
      // pam_krb5[sshd:auth]: authentication failure; logname=root uid=0 euid=0 tty=ssh ruser= rhost=123.213.132.231
      else if (preg_match('/^(?P<program>(\S((\(|\[).*(\)|\])))):(?P<msg>.*)$/', $entry['msg'], $matches))
      {
        $entry['msg']     = $matches['msg'];
        $entry['program'] = $matches['program'];
      }
      // pam_krb5: authentication failure; logname=root uid=0 euid=0 tty=ssh ruser= rhost=123.213.132.231
      // diskio.c: don't know how to handle 10 request
      else if (preg_match('/^(?P<program>[^\s\(\[]*):\ (?P<msg>.*)$/', $entry['msg'], $matches))
      {
        $entry['msg']     = $matches['msg'];
        $entry['program'] = $matches['program'];
      }
      // Wed Mar 26 12:54:17 2014 : Auth: Login incorrect (mschap: External script says Logon failure (0xc000006d)): [username] (from client 10.100.1.3 port 0 cli a4c3612a4077 via TLS tunnel)
      else if (!empty($entry['program']) && preg_match('/^.*:\ '.$entry['program'].':\ (?P<msg>[^(]+\((?P<program>[^:]+):.*)$/', $entry['msg'], $matches))
      {
        $entry['msg']     = $matches['msg'];
        $entry['program'] = $matches['program'];
      }
      // SYSLOG CONNECTION BROKEN; FD='6', SERVER='AF_INET(123.213.132.231:514)', time_reopen='60'
      // fallback, better than nothing...
      else if (empty($entry['program']) && !empty($entry['facility']))
      {
        $entry['program'] = $entry['facility'];
      }
      unset($matches);
    }
    else if ($os == 'ftos')
    {
      if (empty($entry['program']))
      {
        //1.1.1.1||23||5||5||||2014-11-23 21:48:10|| Nov 23 21:48:10.745: hostname: %STKUNIT0-M:CP %SEC-5-LOGOUT: Exec session is terminated for user rancid on line vty0||
        list(,, $entry['program'], $entry['msg']) = explode(': ', $entry['msg'], 4);
        list(, $entry['program']) = explode(' %', $entry['program'], 2);
      }
      //Jun 3 02:33:23.489: %STKUNIT0-M:CP %SNMP-3-SNMP_AUTH_FAIL: SNMP Authentication failure for SNMP request from host 176.10.35.241
      //Jun 1 17:11:50.806: %STKUNIT0-M:CP %ARPMGR-2-MAC_CHANGE: IP-4-ADDRMOVE: IP address 11.222.30.53 is moved from MAC address 52:54:00:7b:37:ad to MAC address 52:54:00:e4:ec:06 .
      //if (strpos($entry['msg'], '%STKUNIT') === 0)
      //{
      //  list(, $entry['program'], $entry['msg']) = explode(': ', $entry['msg'], 3);
      //  //$entry['timestamp'] = date("Y-m-d H:i:s", strtotime($entry['timestamp'])); // convert to timestamp
      //  list(, $entry['program']) = explode(' %', $entry['program'], 2);
      //}
    }
    else if ($os == 'netscaler')
    {
      //10/03/2013:16:49:07 GMT dk-lb001a PPE-4 : UI CMD_EXECUTED 10367926 : User so_readonly - Remote_ip 10.70.66.56 - Command "stat lb vserver" - Status "Success"
      list(,,,$entry['msg']) = explode(' ', $entry['msg'], 4);
      list($entry['program'], $entry['msg']) = explode(' : ', $entry['msg'], 3);
    }

    if ($entry['program'] == '')
    {
      /** FIXME, WHAT? Pls examples.
       $entry['program'] = $entry['msg'];
       unset($entry['msg']);
       */
      if ($entry['msg'] == '')
      {
        // Something wrong, both program and msg empty
        return $entry;
      }
    }
    else if (strpos($entry['program'], '(BZ2') === 0)
    {
      // Wtf is BZ2LR and BZ@..
      /**
       *Old: 10.10.34.10||3||6||6||hostapd:||2014-07-18 11:29:35|| ath2: STA c8:dd:c9:d1:d4:aa IEEE 802.11: associated||hostapd
       *New: 10.10.34.10||3||6||6||(BZ2LR,00272250c1cd,v3.2.5.2791)||2014-12-12 09:36:39|| hostapd: ath2: STA dc:a9:71:1b:d6:c7 IEEE 802.11: associated||(BZ2LR,00272250c1cd,v3.2.5.2791)
       */
      list($entry['program'], $entry['msg']) = explode(': ', $entry['msg'], 2);
    }

    $entry['program'] = strtoupper($entry['program']);
    array_walk($entry, 'trim');

    // Rewrite priority and level from strings to numbers
    $entry['priority'] = priority_string_to_numeric($entry['priority']);
    $entry['level']    = priority_string_to_numeric($entry['level']);

    if ($update)
    {
      dbInsert(
        array(
          'device_id' => $entry['device_id'],
          'program'   => $entry['program'],
          'facility'  => $entry['facility'],
          'priority'  => $entry['priority'],
          'level'     => $entry['level'],
          'tag'       => $entry['tag'],
          'msg'       => $entry['msg'],
          'timestamp' => $entry['timestamp']
        ),
        'syslog'
      );
    }
    unset($os);
  } else {
    /** NOT FINISHED
    // Store entries for unknown hosts to temporary table
    unset($entry['device_id']);
    dbInsert(array('host' => $entry['host'], 'entry' => json_encode($entry)), 'syslog_unknown');
     */
  }
  return $entry;
}

// EOF
