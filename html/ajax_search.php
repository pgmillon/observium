<?php
/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage ajax
 * @author     Adam Armstrong <adama@memetic.org>
 * @copyright  (C) 2006-2014 Adam Armstrong
 *
 */

if (isset($_GET['debug']) && $_GET['debug'])
{
  ini_set('display_errors', 1);
  ini_set('display_startup_errors', 0);
  ini_set('log_errors', 0);
  ini_set('allow_url_fopen', 0);
  ini_set('error_reporting', E_ALL);
}

include_once("../includes/defaults.inc.php");
include_once("../config.php");
include_once("../includes/definitions.inc.php");

//include($config['install_dir'] . "/includes/common.inc.php");
//include($config['install_dir'] . "/includes/rewrites.inc.php");
//include($config['install_dir'] . "/includes/dbFacile.php");
include($config['install_dir'] . "/includes/functions.inc.php");
include($config['html_dir'] . "/includes/functions.inc.php");
include($config['html_dir'] . "/includes/authenticate.inc.php");

if (!$_SESSION['authenticated']) { echo('<li class="nav-header">Session expired, please log in again!</li>'); exit; }

include($config['html_dir'] . "/includes/cache-data.inc.php");

// Is there a POST/GET query string?
if (isset($_REQUEST['queryString']))
{
  $queryString = mres($_REQUEST['queryString']);

  // Is the string length greater than 0?
  if (strlen($queryString) > 0)
  {
    $found = 0;

    /// SEARCH DEVICES
    $query_permitted_device = generate_query_permitted(array('device'), array('device_table' => 'devices'));
    $results = dbFetchRows("SELECT * FROM `devices`
                            WHERE (`hostname` LIKE '%$queryString%' OR `location` LIKE '%$queryString%') $query_permitted_device
                            ORDER BY `hostname` LIMIT 8");
    if (count($results))
    {
      $found = 1;
      echo('<li class="nav-header">Devices found: '.count($results).'</li>' . PHP_EOL);

      foreach ($results as $result)
      {
        echo('<li class="divider" style="margin: 0px;"></li>' . PHP_EOL);
        echo('<li style="margin: 0px;">' . PHP_EOL . '  <a href="'.generate_device_url($result).'">' . PHP_EOL);
        humanize_device($result);

        $name = $result['hostname'];
        if (strlen($name) > 35) { $name = substr($name, 0, 35) . "..."; }

        $num_ports = dbFetchCell("SELECT COUNT(*) FROM `ports` WHERE device_id = ?", array($result['device_id']));
        echo('    <dl style="border-left: 10px solid '.$result['html_tab_colour'].'; " class="dl-horizontal dl-search">
      <dt style="padding-left: 10px; text-align: center;">'.getImage($result).'</dt>
        <dd>
          <strong>'.highlight_search(htmlentities($name)).'
            <small>'.htmlentities($result['hardware']).' | '.htmlentities($config['os'][$result['os']]['text']).' '. htmlentities($result['version']) .'
            <br /> '.highlight_search(htmlentities($result['location'], 0, 'UTF-8')).' | '.$num_ports.' ports</small>
          </strong>
        </dd>
    </dl>
  </a>
</li>' . PHP_EOL);
      }
    }

    /// SEARCH PORTS
    $query_permitted_port = generate_query_permitted(array('port'));
    $results = dbFetchRows("SELECT * FROM `ports`
                            LEFT JOIN `devices` ON `ports`.`device_id` = `devices`.`device_id`
                            WHERE (`ifAlias` LIKE '%$queryString%' OR `ifDescr` LIKE '%$queryString%') $query_permitted_port
                            ORDER BY `ifDescr` LIMIT 8;");

    if (count($results))
    {
      $found = 1;
      echo('<li class="nav-header">Ports found: '.count($results).'</li>');

      foreach ($results as $result)
      {
        humanize_port($result);
        echo('<li class="divider" style="margin: 0px;"></li>');
        echo('<li>');
        echo('<a href="'.generate_port_url($result).'">');
        $name = rewrite_ifname($result['ifDescr']);
        if (strlen($name) > 35) { $name = substr($name, 0, 35) . "..."; }
        $description = $result['ifAlias'];
        if (strlen($description) > 80) { $description = substr($description, 0, 80) . "..."; }
        $type = rewrite_iftype($result['ifType']);
        if ($description) { $type .= ' | '; }

        echo('<dl style="border-left: 10px solid '.$result['table_tab_colour'].'; " class="dl-horizontal dl-search">
                <dt style="padding-left: 10px; text-align: center;"><img src="images/icons/'.$result['icon'].'.png" /></dt>
                <dd><strong>'.highlight_search(htmlentities($name)).' <small>'.$result['hostname'].
                '<br />'.$type . highlight_search(htmlentities($description)).'</small></strong></dd>
                </dl>');

       }

       echo("</a></li>");
     }

    /// SEARCH SENSORS
    $results = dbFetchRows("SELECT * FROM `sensors`
                            LEFT JOIN `devices` ON `sensors`.`device_id` = `devices`.`device_id`
                            WHERE `sensor_descr` LIKE '%$queryString%' $query_permitted_device
                            ORDER BY `sensor_descr` LIMIT 8");

    if (count($results))
    {
      $found = 1;
      echo('<li class="nav-header">Sensors found: '.count($results).'</li>');

      foreach ($results as $result)
      {
        echo('<li class="divider" style="margin: 0px;"></li>');
        echo('<li>');
        echo('<a href="graphs/type=sensor_'  . $result['sensor_class'] . '/id=' . $result['sensor_id'] . '/">');
        $name = $result['sensor_descr'];
        if (strlen($name) > 35) { $name = substr($name, 0, 35) . "..."; }

        /// FIXME: once we have alerting, colour this to the sensor's status
        $tab_colour = '#194B7F'; // FIXME: This colour pulled from functions.inc.php humanize_device, maybe set it centrally in definitions?

        echo('<dl style="border-left: 10px solid '.$tab_colour.'; " class="dl-horizontal dl-search">
                <dt style="padding-left: 10px; text-align: center;">
                  <i class="'.$config['sensor_types'][$result['sensor_class']]['icon'].'"></i></dt>
                <dd><strong>'.highlight_search(htmlentities($name)).'</h5>
                     <small>'.$result['hostname'].'<br />
                     '.htmlentities($result['location'], 0, 'UTF-8') . ' | ' . nicecase($result['sensor_class']).' sensor</small></strong></dd>
                </dl>');
      }

      echo("</a></li>");
    }

    /// SEARCH ACCESSPOINTS
    $results = dbFetchRows("SELECT * FROM `wifi_accesspoints`
                            WHERE `name` LIKE '%$queryString%'
                            ORDER BY `name` LIMIT 8");

    if (count($results))
    {
      $found = 1;
      echo('<li class="nav-header">APs found: '.count($results).'</li>');

      foreach ($results as $result)
      {
        echo('<li class="divider" style="margin: 0px;"></li>');
        echo('<li>');
        echo('<a href="'. generate_url(array('page' => 'device', 'device' => $result['device_id'], 'tab' => 'wifi', 'view' => 'accesspoint', 'accesspoint' => $result['wifi_accesspoint_id'])).'">');
        $name = $result['name'];
        if (strlen($name) > 35) { $name = substr($name, 0, 35) . "..."; }

        /// FIXME: once we have alerting, colour this to the sensor's status
        $tab_colour = '#194B7F'; // FIXME: This colour pulled from functions.inc.php humanize_device, maybe set it centrally in definitions?

        echo('<dl style="border-left: 10px solid '.$tab_colour.'; " class="dl-horizontal dl-search">
                <dt style="padding-left: 10px; text-align: center;">
                  <img src="images/icons/wifi.png" /></dt>
                <dd><strong>'.highlight_search(htmlentities($name)).'</h5>
                     <small>'.$result['hostname'].'<br />
                     '.htmlentities($result['location'], 0, 'UTF-8') . ' | Accesspoint</small></strong></dd>
                </dl>');
      }

      echo("</a></li>");
    }

    /// SEARCH IP ADDRESSES

    list($addr, $mask) = explode('/', $queryString);
    $address_type = "ipv4";
    if (is_numeric(stripos($queryString, ':abcdef'))) { $address_type = 'ipv6'; }

    switch ($address_type)
    {
      case 'ipv6':
        $ip_valid = Net_IPv6::checkIPv6($addr);
        break;
      case 'ipv4':
        $ip_valid = Net_IPv4::validateIP($addr);
        break;
    }
#    if ($ip_valid)
#    {
#      // If address valid -> seek occurrence in network
#      if (!$mask) { $mask = ($address_type === 'ipv4') ? '32' : '128'; }#

#     } else {
      // If address not valid -> seek LIKE
      $where .= ' AND A.`ipv4_address` LIKE ?';
      $param[] = '%'.$addr.'%';
#    }

    // FIXME no v6 yet.
    $query =  'SELECT * ';
    $query .= 'FROM `ipv4_addresses` AS A ';
    $query .= 'LEFT JOIN `ports` ON `A`.`port_id` = `ports`.`port_id` ';
    $query .= 'WHERE deleted=0 ';
    $query .= $where;
    $query .= ' ORDER BY A.`ipv4_address` LIMIT 8';

#  $debug=1;

    // Query addresses
    $results = dbFetchRows($query, $param);

    if (count($results))
    {
      $found = 1;

      foreach ($results as $result)
      {
        $addr_ports[$result['port_id']][] = $result;
      }

      echo('<li class="nav-header">IPs found: '.count($results).' (on '.count($addr_ports).' ports)</li>');

#      foreach ($addr_ports as $port_id => $result)

      foreach ($results as $result)
      {

        $port = get_port_by_id_cache($result['port_id']);
        $device = device_by_id_cache($port['device_id']);

        echo('<li class="divider" style="margin: 0px;"></li>');
        echo('<li>');
        echo('<a href="'.generate_port_url($port).'">');

        $descr = $device['hostname'].' | '.rewrite_ifname($port['label']);

        $name = $result['ipv4_address'].'/'.$result['ipv4_prefixlen'];
        if (strlen($name) > 35) { $name = substr($name, 0, 35) . "..."; }

        /// FIXME: once we have alerting, colour this to the sensor's status
        $tab_colour = '#194B7F'; // FIXME: This colour pulled from functions.inc.php humanize_device, maybe set it centrally in definitions?

        echo('<dl style="border-left: 10px solid '.$tab_colour.'; " class="dl-horizontal dl-search">
                <dt style="padding-left: 10px; text-align: center;">
                  <i class="oicon-magnifier-zoom-actual"></i></dt>
                <dd><strong>'.highlight_search(htmlentities($name)).'<br />
                     <small>'.htmlentities($descr).'</small></strong></dd>
                </dl>');
      }

      echo("</a></li>");
    }

    if (!$found)
    {
      echo('<li class="nav-header">No search results.</li>');
    }
  } // There is a queryString.
} else {
  echo('<li class="nav-header">There should be no direct access to this script! Please reload the page.</li>');
}

// DOCME needs phpdoc block
// TESTME needs unit testing
function highlight_search($text)
{
  return preg_replace("/".preg_quote($GLOBALS['queryString'], "/")."/i", "<em class='text-danger'>$0</em>", $text);
}

// EOF
