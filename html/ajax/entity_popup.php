<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage ajax
 * @author     Adam Armstrong <adama@observium.org>
 * @copyright  (C) 2006-2013 Adam Armstrong, (C) 2013-2016 Observium Limited
 *
 */

$config['install_dir'] = "../..";

require_once($config['install_dir']."/includes/sql-config.inc.php");

include($config['html_dir'] . "/includes/functions.inc.php");
include($config['html_dir'] . "/includes/authenticate.inc.php");

if (!$_SESSION['authenticated']) { print_error('Session expired, please log in again!'); exit; }

$vars = get_vars();

$vars['page'] = "popup";

switch ($vars['entity_type'])
{
  case "port":
    if (is_numeric($vars['entity_id']) && (port_permitted($vars['entity_id'])))
    {
      $port = get_port_by_id($vars['entity_id']);
      echo generate_port_popup($port);
    } else {
      print_warning("You are not permitted to view this port.");
    }
    exit;
    break;

  case "device":
    if (is_numeric($vars['entity_id']) && device_permitted($vars['entity_id']))
    {
      $device = device_by_id_cache($vars['entity_id']);
      echo generate_device_popup($device, $vars, $start, $end);
    } else {
      print_warning("You are not permitted to view this device.");
    }
    exit;
    break;

  case "group":
    if (is_numeric($vars['entity_id']) && $_SESSION['userlevel'] >= 5)
    {
      $group = get_group_by_id($vars['entity_id']);
      generate_group_popup_header($group, array());
    } else {
      print_warning("You are not permitted to view this device.");
    }
    exit;
    break;

  // FIXME : mac is not an observium entity. This should go elsewhere!
  case "mac":
    if (Net_MAC::check($vars['entity_id']))
    {
      // Other way by using Pear::Net_MAC, see here: http://pear.php.net/manual/en/package.networking.net-mac.importvendors.php
      $url = 'http://api.macvendors.com/' . urlencode($vars['entity_id']);
      $response = get_http_request($url);
      if ($response)
      {
        echo 'MAC vendor: ' . $response;
      } else {
        echo 'Not Found';
      }
    } else {
      echo 'Not correct MAC address';
    }
    exit;
    break;

  case "ip":
    list($ip) = explode('/', $vars['entity_id']);
    $ip_version = get_ip_version($ip);
    if ($ip_version)
    {
      if (isset($_SESSION['cache']['response_' . $vars['entity_type'] . '_' . $ip]))
      {
        echo $_SESSION['cache']['response_' . $vars['entity_type'] . '_' . $ip];
        //echo '<h2>CACHED!</h2>';
        exit;
      }

      $response = '';
      $reverse_dns = gethostbyaddr6($ip);
      if ($reverse_dns)
      {
        $response .= '<h4>' . $reverse_dns . '</h4><hr />' . PHP_EOL;
      }

      // WHOIS
      if (is_executable($config['whois']) && !isset($config['http_proxy']))
      {
        // Use direct whois cmd query (preferred)
        // NOTE, for now not tested and not supported for KRNIC, ie: 202.30.50.0, 2001:02B8:00A2::
        $cmd = $config['whois'] . ' ' . $ip;
        $whois = external_exec($cmd);

        $multi_whois = explode('# start', $whois); // Some time whois return multiple (ie: whois 8.8.8.8), than use last
        if (count($multi_whois) > 1)
        {
          $whois = array_pop($multi_whois);
        }

        $org = 0;
        foreach (explode("\n", $whois) as $line)
        {
          if (preg_match('/^(\w[\w\s\-\/]+):.*$/', $line, $matches))
          {
            if (in_array($matches[1], array('Ref', 'source', 'nic-hdl-br')))
            {
              if ($org === 1)
              {
                $response .= PHP_EOL;
                $org++;
                continue;
              } else {
                break;
              }
            }
            else if (in_array($matches[1], array('Organization', 'org', 'mnt-irt')))
            {
              $org++; // has org info
            }
            else if ($matches[1] == 'Comment')
            {
              continue; // skip comments
            }
            $response .= $line . PHP_EOL;
          }
        }
      } else {
        // Use RIPE whois API query
        $whois_url  = 'https://stat.ripe.net/data/whois/data.json?';
        $whois_url .= 'sourceapp=' . urlencode(OBSERVIUM_PRODUCT . '-' . get_unique_id());
        $whois_url .= '&resource='  . urlencode($ip);
        $request = get_http_request($whois_url);
        if ($request)
        {
          $request = json_decode($request, TRUE); // Convert to array
          if ($request['status'] == 'ok' && count($request['data']['records']))
          {
            $whois_parts = array();
            foreach ($request['data']['records'] as $i => $parts)
            {
              $key = $parts[0]['key'];

              if (in_array($key, array('NetRange', 'inetnum', 'inet6num')))
              {
                $org = 0;

                $whois_parts[0] = '';
                foreach ($parts as $part)
                {
                  if (in_array($part['key'], array('Ref', 'source', 'nic-hdl-br')))
                  {
                    break;
                  }
                  else if (in_array($part['key'], array('Organization', 'org', 'mnt-irt')))
                  {
                    $org = 1; // has org info
                    $org_name = $part['value'];
                  }
                  else if ($part['key'] == 'Comment')
                  {
                    continue; // skip comments
                  }
                  $whois_parts[0] .= sprintf('%-16s %s' . PHP_EOL, $part['key'] . ':', $part['value']);
                }

              }
              else if ($org === 1 && $key == 'OrgName' && strpos($org_name, $parts[0]['value']) === 0)
              {

                $whois_parts[1] = '';
                foreach ($parts as $part)
                {
                  if (in_array($part['key'], array('Ref', 'source', 'nic-hdl-br')))
                  {
                    break;
                  }
                  else if ($part['key'] == 'Comment')
                  {
                    continue; // skip comments
                  }
                  $whois_parts[1] .= sprintf('%-16s %s' . PHP_EOL, $part['key'] . ':', $part['value']);
                }

                break;
              }
            }
            $response .= implode(PHP_EOL, $whois_parts);

            //print_vars($request['data']['records']);
          }
        }
      }

      if ($response)
      {
        $_SESSION['cache']['response_' . $vars['entity_type'] . '_' . $ip] = '<pre class="small">' . $response . '</pre>';
        echo $_SESSION['cache']['response_' . $vars['entity_type'] . '_' . $ip];
      } else {
        echo 'Not Found';
      }
    } else {
      echo 'Not correct IP address';
    }
    exit;
    break;

  default:
    if (is_array($config['entities'][$vars['entity_type']]))
    {
      if (is_numeric($vars['entity_id']) && (is_entity_permitted($vars['entity_id'], $vars['entity_type'])))
      {
        $entity = get_entity_by_id_cache($vars['entity_type'], $vars['entity_id']);
        echo generate_entity_popup($entity, $vars);
      } else {
        print_warning("You are not permitted to view this entity.");
      }
    } else {
      print_error("Unknown entity type.");
    }
    exit;
    break;
}

// EOF
