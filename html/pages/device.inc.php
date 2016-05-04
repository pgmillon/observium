<?php

/**
 * Observium Network Management and Monitoring System
 * Copyright (C) 2006-2015, Adam Armstrong - http://www.observium.org
 *
 * @package    observium
 * @subpackage webui
 * @author     Adam Armstrong <adama@observium.org>
 * @copyright  (C) 2006-2013 Adam Armstrong, (C) 2013-2016 Observium Limited
 *
 */

// If we've been given a hostname, try to retrieve the device_id

if (isset($vars['device']) && !is_numeric($vars['device']))
{
  $vars['hostname'] = $vars['device'];
  unset($vars['device']);
}

if (empty($vars['device']) && !empty($vars['hostname']))
{
  $vars['device'] = get_device_id_by_hostname($vars['hostname']);

  // If device lookup fails, generate an error.
  if (empty($vars['device']))
  {
    print_error('<h4>Invalid Hostname</h4>
                   A device matching the given hostname was not found. Please retype the hostname and try again.');
    return;
  }
}

// If there is no device specified in the URL, generate an error.
if (empty($vars['device']))
{
  print_error('<h4>No device specified</h4>
                   A valid device was not specified in the URL. Please retype and try again.');
  return;
}

// Allow people to see this page if they have permission to see one of the ports, but don't show them tabs.
$permit_tabs = array();
if ($vars['tab'] == "port" && is_numeric($vars['device']) && (isset($vars['port']) || isset($vars['ifdescr'])))
{
  // If we've been given a 'ifdescr' variable, try to work out the port_id from this
  if (!is_numeric($vars['port']) && !empty($vars['ifdescr']))
  {
    $ifdescr = base64_decode($vars['ifdescr']);
    if (!$ifdescr) { $ifdescr = $vars['ifdescr']; }
    $vars['port'] = get_port_id_by_ifDescr($vars['device'], $ifdescr);
  }

  if (port_permitted($vars['port']) && $vars['device'] == get_device_id_by_port_id($vars['port']))
  {
    $permit_tabs['ports'] = TRUE;
  }
}

if ($vars['tab'] == "health" && is_numeric($vars['id']) && (isset($vars['id'])))
{
  if (is_entity_permitted($vars['id'], 'sensor'))
  // && $vars['device'] == get_device_id_by_port_id($vars['port']))
  {
    $permit_tabs['health'] = TRUE;
  }
}

// print_vars($permit_tabs);

// If there is no valid device specified in the URL, generate an error.
if (!isset($cache['devices']['id'][$vars['device']]) && !count($permit_tabs))
{
  print_error('<h4>No valid device specified</h4>
                  A valid device was not specified in the URL. Please retype and try again.');
  return;
}

// Only show if the user has access to the whole device or a single port.
if (isset($cache['devices']['id'][$vars['device']]) || count($permit_tabs))
{
  $selected['iface'] = "active";

  $tab = str_replace(".", "", $vars['tab']);

  if (!$tab)
  {
    $tab = "overview";
  }

  $select[$tab] = "active";

  // Populate device array from pre-populated cache
  $device = device_by_id_cache($vars['device']);

  // Populate the attributes array for this device
  $attribs = get_dev_attribs($device['device_id']);

  // Populate the entityPhysical state array for this device
  $entity_state = get_dev_entity_state($device['device_id']);

  // Populate the device state array from the serialized entry
  $device_state = unserialize($device['device_state']);

  // Add the device hostname to the page title array
  $page_title[] = escape_html($device['hostname']);

  // If the device's OS type has a group, set the device's os_group
  if ($config['os'][$device['os']]['group']) { $device['os_group'] = $config['os'][$device['os']]['group']; }

//// DEV
?>

<div class="row">

<div class="col-xl-4 visible-xl">

    <?php print_device_header($device, array('no_graphs' => TRUE)); ?>


    <?php include("pages/device/overview/information_extended.inc.php"); ?>

<div class="box box-solid">
  <div class="box-body no-padding">
<?php

    // Only show graphs for device_permitted(), don't show device graphs to users who can only see a single entity.

    if (isset($config['os'][$device['os']]['graphs']))
    {
      $graphs = $config['os'][$device['os']]['graphs'];
    }
    else if (isset($device['os_group']) && isset($config['os'][$device['os_group']]['graphs']))
    {
      $graphs = $config['os'][$device['os_group']]['graphs'];
    } else {
      $graphs = $config['os']['default']['graphs'];
    }

    $graph_array = array();
    $graph_array['height'] = "100";
    $graph_array['width']  = "218";
    $graph_array['to']     = $config['time']['now'];
    $graph_array['device'] = $device['device_id'];
    $graph_array['type']   = "device_bits";
    $graph_array['from']   = $config['time']['day'];
    $graph_array['legend'] = "no";
    $graph_array['bg']     = "FFFFFF00";

    // Preprocess device graphs array
    foreach ($device['graphs'] as $graph)
    {
      $graphs_enabled[] = $graph['graph'];
    }

    echo '<div class="row">';

    foreach ($graphs as $entry)
    {
      if ($entry && in_array(str_replace('device_', '', $entry), $graphs_enabled) !== FALSE)
      {
        $graph_array['type'] = $entry;

        preg_match('/^(?P<type>[a-z0-9A-Z-]+)_(?P<subtype>[a-z0-9A-Z-_]+)/', $entry, $graphtype);

        if (isset($graphtype['type']) && isset($graphtype['subtype']))
        {
          $type = $graphtype['type'];
          $subtype = $graphtype['subtype'];

          $text = $config['graph_types'][$type][$subtype]['descr'];
        } else {
          $text = nicecase($subtype); // Fallback to the type itself as a string, should not happen!
        }

        echo('<div class="col-xl-6">');
        echo("<div style='padding: 0px; text-align: center;'><h4>$text</h4></div>");
        print_graph_popup($graph_array);
        echo("</div>");
      }
    }
    echo '</div>';


unset($graph_array);

?>
  </div>
</div>
</div>

<div class="col-xl-8 col-lg-12">

<?php

/////

  // Print the device header

  echo '<div class="hidden-xl">';
  print_device_header($device);
  echo '</div>';

  // Show tabs if the user has access to this device
  if (device_permitted($device['device_id']))
  {
    if ($config['show_overview_tab'])
    {
      $navbar['options']['overview'] = array('text' => 'Overview', 'icon' => 'oicon-server');
    }

    $navbar['options']['graphs'] = array('text' => 'Graphs', 'icon' => 'oicon-chart-up');

    $health =  dbFetchCell('SELECT COUNT(*) FROM `storage` WHERE device_id = ?', array($device['device_id'])) +
               dbFetchCell('SELECT COUNT(*) FROM `sensors` WHERE device_id = ?', array($device['device_id'])) +
               dbFetchCell('SELECT COUNT(*) FROM `mempools` WHERE device_id = ?', array($device['device_id'])) +
               dbFetchCell('SELECT COUNT(*) FROM `processors` WHERE device_id = ?', array($device['device_id']));

    if ($health)
    {
      $navbar['options']['health'] = array('text' => 'Health', 'icon' => 'oicon-system-monitor');
    }

    // Print applications tab if there are matching entries in `applications` table
    if (dbFetchCell('SELECT COUNT(app_id) FROM applications WHERE device_id = ?', array($device['device_id'])) > '0')
    {
      $navbar['options']['apps'] = array('text' => 'Apps', 'icon' => 'oicon-application-icon-large');
    }

    // Print the collectd tab if there is a matching directory
    if (isset($config['collectd_dir']) && is_dir($config['collectd_dir'] . "/" . $device['hostname'] ."/"))
    {
      $navbar['options']['collectd'] = array('text' => 'Collectd', 'icon' => 'oicon-chart-up-color');
    }

    // Print the munin tab if there are matchng entries in the munin_plugins table
    if (dbFetchCell('SELECT COUNT(mplug_id) FROM munin_plugins WHERE device_id = ?', array($device['device_id'])) > '0')
    {
      $navbar['options']['munin'] = array('text' => 'Munin', 'icon' => 'oicon-chart-up');
    }

    // Print the port tab if there are matching entries in the ports table
    if (dbFetchCell('SELECT COUNT(port_id) FROM ports WHERE device_id = ?', array($device['device_id'])) > '0')
    {
      $navbar['options']['ports'] = array('text' => 'Ports', 'icon' => 'oicon-network-ethernet');
    }

    // Print the SLAs tab if there are matching entries in the slas table
    if (dbFetchCell('SELECT COUNT(*) FROM `slas` WHERE `device_id` = ? AND `deleted` = 0', array($device['device_id'])) > '0')
    {
      $navbar['options']['slas'] = array('text' => 'SLAs', 'icon' => 'oicon-chart-up');
    }

    // Print the access points tab if there are matching entries in the accesspoints table (Aruba Legacy)
    if (dbFetchCell('SELECT COUNT(radio_id) FROM p2p_radios WHERE device_id = ?', array($device['device_id'])) > '0')
    {
      $navbar['options']['p2pradios'] = array('text' => 'Radios', 'icon' => 'oicon-transmitter');
    }

    // Print the access points tab if there are matching entries in the accesspoints table (Aruba Legacy)
    if (dbFetchCell('SELECT COUNT(accesspoint_id) FROM accesspoints WHERE device_id = ?', array($device['device_id'])) > '0')
    {
      $navbar['options']['accesspoints'] = array('text' => 'Access Points', 'icon' => 'oicon-wi-fi-zone');
    }

    // Print the wifi tab if wifi things exist

    $device_ap_count    = dbFetchCell('SELECT COUNT(wifi_accesspoint_id) FROM `wifi_accesspoints` WHERE `device_id` = ?', array($device['device_id']));
    $device_radio_count = dbFetchCell('SELECT COUNT(wifi_radio_id)       FROM `wifi_radios`       WHERE `device_id` = ?', array($device['device_id']));
    $device_wlan_count  = dbFetchCell('SELECT COUNT(wlan_id)             FROM `wifi_wlans`        WHERE `device_id` = ?', array($device['device_id']));

    if ($device_ap_count > 0 || $device_radio_count > 0)
    {
      $navbar['options']['wifi'] = array('text' => 'WiFi', 'icon' => 'oicon-wi-fi-zone');
    }

    // Build array of smokeping files for use in tab building and smokeping page.
    if (isset($config['smokeping']['dir']))
    {
      $smokeping_files = get_smokeping_files();
    }

    // Print latency tab if there are smokeping files with source or destination matching this hostname
    if (count($smokeping_files['incoming'][$device['hostname']]) || count($smokeping_files['outgoing'][$device['hostname']]))
    {
      $navbar['options']['latency'] = array('text' => 'Ping', 'icon' => 'oicon-paper-plane');
    }

    // Print vlans tab if there are matching entries in the vlans table
    if (dbFetchCell('SELECT COUNT(vlan_id) FROM vlans WHERE device_id = ?', array($device['device_id'])) > '0')
    {
      $navbar['options']['vlans'] = array('text' => 'VLANs', 'icon' => 'oicon-arrow-branch-bgr');
    }

    // Pring Virtual Machines tab if there are matching entries in the vminfo table
    if (dbFetchCell('SELECT COUNT(vm_id) FROM vminfo WHERE device_id = ?', array($device['device_id'])) > '0')
    {
      $navbar['options']['vm'] = array('text' => 'VMs', 'icon' => 'oicon-network-cloud');
    }

    // $loadbalancer_tabs is used in device/loadbalancer/ to build the submenu. we do it here to save queries

    // Check for Netscaler vservers and services
    if ($device['os'] == "netscaler") // Netscaler
    {
      $device_loadbalancer_count['netscaler_vsvr'] = dbFetchCell("SELECT COUNT(*) FROM `netscaler_vservers` WHERE `device_id` = ?", array($device['device_id']));
      if ($device_loadbalancer_count['netscaler_vsvr']) { $loadbalancer_tabs[] = 'netscaler_vsvr'; }

      $device_loadbalancer_count['netscaler_services'] = dbFetchCell("SELECT COUNT(*) FROM `netscaler_services` WHERE `device_id` = ?", array($device['device_id']));
      if ($device_loadbalancer_count['netscaler_services']) { $loadbalancer_tabs[] = 'netscaler_services'; }

      $device_loadbalancer_count['netscaler_servicegroupmembers'] = dbFetchCell("SELECT COUNT(*) FROM `netscaler_servicegroupmembers` WHERE `device_id` = ?", array($device['device_id']));
      if ($device_loadbalancer_count['netscaler_servicegroupmembers']) { $loadbalancer_tabs[] = 'netscaler_servicegroupmembers'; }
    }

    $device_loadbalancer_count['lb_virtuals'] = dbFetchCell("SELECT COUNT(*) FROM `lb_virtuals` WHERE `device_id` = ?", array($device['device_id']));
    if ($device_loadbalancer_count['lb_virtuals']) { $loadbalancer_tabs[] = 'lb_virtuals'; }

    // Check for Cisco ACE vservers
    if ($device['os'] == "acsw")  // Cisco ACE
    {
      $device_loadbalancer_count['loadbalancer_vservers'] = dbFetchCell("SELECT COUNT(*) FROM `loadbalancer_vservers` WHERE `device_id` = ?", array($device['device_id']));
      if ($device_loadbalancer_count['loadbalancer_vservers']) { $loadbalancer_tabs[] = 'loadbalancer_vservers'; }

      $device_loadbalancer_count['loadbalancer_rservers'] = dbFetchCell("SELECT COUNT(*) FROM `loadbalancer_rservers` WHERE `device_id` = ?", array($device['device_id']));
      if ($device_loadbalancer_count['loadbalancer_rservers']) { $loadbalancer_tabs[] = 'loadbalancer_rservers'; }
    }

    // Print the load balancer tab if the loadbalancer_tabs array has entries.
    if (is_array($loadbalancer_tabs))
    {
      $navbar['options']['loadbalancer'] = array('text' => 'Load Balancer', 'icon' => 'oicon-arrow-split');
    }

    // $routing_tabs is used in device/routing/ to build the tabs menu. we built it here to save some queries

    $device_routing_count['ipsec_tunnels'] = dbFetchCell("SELECT COUNT(*) FROM `ipsec_tunnels` WHERE `device_id` = ?", array($device['device_id']));
    if ($device_routing_count['ipsec_tunnels']) { $routing_tabs[] = 'ipsec_tunnels'; }

    $device_routing_count['bgp'] = dbFetchCell("SELECT COUNT(*) FROM `bgpPeers` WHERE `device_id` = ?", array($device['device_id']));
    if ($device_routing_count['bgp']) { $routing_tabs[] = 'bgp'; }

    $device_routing_count['ospf'] = dbFetchCell("SELECT COUNT(*) FROM `ospf_instances` WHERE `ospfAdminStat` = 'enabled' AND `device_id` = ?", array($device['device_id']));
    if ($device_routing_count['ospf']) { $routing_tabs[] = 'ospf'; }

    $device_routing_count['eigrp'] = dbFetchCell("SELECT COUNT(*) FROM `eigrp_ports` WHERE `device_id` = ?", array($device['device_id']));
    if ($device_routing_count['eigrp']) { $routing_tabs[] = 'eigrp'; }

    $device_routing_count['cef'] = dbFetchCell("SELECT COUNT(*) FROM `cef_switching` WHERE `device_id` = ?", array($device['device_id']));
    if ($device_routing_count['cef']) { $routing_tabs[] = 'cef'; }

    $device_routing_count['vrf'] = dbFetchCell("SELECT COUNT(*) FROM `vrfs` WHERE `device_id` = ?", array($device['device_id']));
    if ($device_routing_count['vrf']) { $routing_tabs[] = 'vrf'; }

    // Print routing tab if any of the routing tables contain matching entries
    if (is_array($routing_tabs))
    {
      $navbar['options']['routing'] = array('text' => 'Routing', 'icon' => 'oicon-arrow-branch-000-left');
    }

    // Print the pseudowire tab if any of the routing tables contain matching entries
    if (dbFetchCell("SELECT COUNT(*) FROM `pseudowires` WHERE `device_id` = ?", array($device['device_id'])))
    {
      $navbar['options']['pseudowires'] = array('text' => 'Pseudowires', 'icon' => 'oicon-layer-shape-curve');
    }

    // Print the packages tab if there are matching entries in the packages table
    if (dbFetchCell('SELECT COUNT(*) FROM `packages` WHERE `device_id` = ?', array($device['device_id'])) > '0')
    {
      $navbar['options']['packages'] = array('text' => 'Pkgs', 'icon' => 'oicon-box-zipper');
    }

    // Print the inventory tab if inventory is enabled and either entphysical or hrdevice tables have entries
    if (dbFetchCell('SELECT COUNT(*) FROM `entPhysical` WHERE `device_id` = ?', array($device['device_id'])) > '0')
    {
      $navbar['options']['entphysical'] = array('text' => 'Inventory', 'icon' => 'oicon-wooden-box');
    }
    elseif (dbFetchCell('SELECT COUNT(*) FROM `hrDevice` WHERE `device_id` = ?', array($device['device_id'])) > '0')
    {
      $navbar['options']['hrdevice'] = array('text' => 'Inventory', 'icon' => 'oicon-wooden-box');
    }

    // Print service tab if show_services enabled and there are entries in the services table
    ## DEPRECATED
    if ($config['show_services'] && dbFetchCell('SELECT COUNT(service_id) FROM services WHERE device_id = ?', array($device['device_id'])) > '0')
    {
      $navbar['options']['services'] = array('text' => 'Services', 'icon' => 'oicon-target');
    }

    // Print toner tab if there are entries in the toner table
    if (dbFetchCell('SELECT COUNT(toner_id) FROM toner WHERE device_id = ?', array($device['device_id'])) > '0')
    {
      $navbar['options']['printing'] = array('text' => 'Printing', 'icon' => 'oicon-printer-color');
    }

    // Always print logs tab
    $navbar['options']['logs'] = array('text' => 'Logs', 'icon' => 'oicon-clipboard-audit');

    // Print alerts tab
    $navbar['options']['alerts'] = array('text' => 'Alerts', 'icon' => 'oicon-bell');

    // If the user has secure global read privileges, check for a device config.
    if ($_SESSION['userlevel'] >= 7)
    {
      $device_config_file = get_rancid_filename($device['hostname']);

      // Print the config tab if we have a device config
      if ($device_config_file)
      {
        $navbar['options']['showconfig'] = array('text' => 'Config', 'icon' => 'oicon-application-terminal');
      }
    }

/*
    // If the user has global read privileges, check for device notes.
    if ($_SESSION['userlevel'] >= 5)
    {
      // Promoted to real tab when notes is filled in, otherwise demoted to icon on the right
      $navbar['options']['notes'] = array('text' => ($attribs['notes'] ? 'Notes' : NULL), 'icon' => 'oicon-notebook', 'right' => ($attribs['notes'] == ''));
    }
*/

    // If nfsen is enabled, check for an nfsen file
    if ($config['nfsen_enable'])
    {
      $nfsen_rrd_file = get_nfsen_filename($device['hostname']);
    }

    // Print the netflow tab if we have an nfsen file
    if ($nfsen_rrd_file)
    {
      $navbar['options']['nfsen'] = array('text' => 'Netflow', 'icon' => 'oicon-funnel');
    }

    // If the user has global write permissions, show them the edit tab
    if ($_SESSION['userlevel'] >= "10")
    {
      $navbar['options']['tools']                             = array('text' => '', 'icon' => 'oicon-gear', 'url' => '#', 'right' => TRUE, 'class' => "dropdown-toggle", 'link_opts' => 'data-hover="dropdown" data-toggle="dropdown"');
      $navbar['options']['tools']['suboptions']['data']       = array('text' => 'Device Data', 'icon' => 'oicon-application-list');
      $navbar['options']['tools']['suboptions']['perf']       = array('text' => 'Performance Data', 'icon' => 'oicon-time');
      $navbar['options']['tools']['suboptions']['divider_1']  = array('divider' => TRUE);

      if (is_array($config['os'][$device['os']]['remote_access']))
      {
        foreach ($config['os'][$device['os']]['remote_access'] as $name => $ra_array)
        {
          if (!is_array($ra_array)) { $name = $ra_array; $ra_array = array(); }

          $ra_array = array_merge($ra_array, $config['remote_access'][$name]);

          // Check if a device specific attribute has been set by code
          $ra_array['url'] = get_dev_attrib($device, 'ra_url_' . $name);

          // If no attribute set, default to protocol://hostname:port
          if ($ra_array['url'] == '')
          {
            $ra_array['url'] = $name.'://'.$device['hostname'].':'.$ra_array['port'];
          }

          $navbar['options']['tools']['suboptions'][$name]       = array('text' => 'Connect via '.$ra_array['name'], 'icon' => $ra_array['icon'], 'url' => $ra_array['url'], 'link_opts' => 'target="_blank"');
        }
        $navbar['options']['tools']['suboptions']['divider_2']  = array('divider' => TRUE);
      }

      $navbar['options']['tools']['suboptions']['delete']['url']  = "#delete_device_modal";
      $navbar['options']['tools']['suboptions']['delete']['text'] = 'Delete Device';
      $navbar['options']['tools']['suboptions']['delete']['link_opts'] = 'data-toggle="modal"';
      $navbar['options']['tools']['suboptions']['delete']['icon'] = 'oicon-server--minus';

      $navbar['options']['tools']['suboptions']['divider_3']  = array('divider' => TRUE);

      $navbar['options']['tools']['suboptions']['edit']       = array('text' => 'Properties', 'icon' => 'oicon-gear');

    }
?>

<script>

(function ($) {

        $(function() {

                // fix sub nav on scroll
                var $win = $(window),
                                $body = $('body'),
                                $nav = $('.subnav'),
                                navHeight = $('.navbar').first().height(),
                                subnavHeight = $('.subnav').first().height(),
                                subnavTop = $('.subnav').length && $('.subnav').offset().top,
                                marginTop = parseInt($body.css('margin-top'), 10);
                                isFixed = 0;

//                                 subnavTop = $('.subnav').length && $('.subnav').offset().top - navHeight,

                processScroll();

                $win.on('scroll', processScroll);

                function processScroll() {
                        var i, scrollTop = $win.scrollTop();

                        if (scrollTop >= subnavTop && !isFixed) {
                                isFixed = 1;
                                $nav.addClass('subnav-fixed');
                                $body.css('margin-top', marginTop + subnavHeight + 'px');
                        } else if (scrollTop <= subnavTop && isFixed) {
                                isFixed = 0;
                                $nav.removeClass('subnav-fixed');
                                $body.css('margin-top', marginTop + 'px');
                        }
                }

        });

})(window.jQuery);
</script>

<?php

    $navbar['class'] = 'navbar-narrow subnav';

    foreach ($navbar['options'] as $option => $array)
    {
      if (!isset($vars['tab'])) { $vars['tab'] = $option; }
      if ($vars['tab'] == $option) { $navbar['options'][$option]['class'] .= " active"; }
      if (!isset($navbar['options'][$option]['url'])) { $navbar['options'][$option]['url'] = generate_device_url($device, array('tab' => $option)); }

      if (isset($navbar['options'][$option]['suboptions']))
      {
        foreach ($navbar['options'][$option]['suboptions'] as $sub_option => $sub_array)
        {
          if (!isset($navbar['options'][$option]['suboptions'][$sub_option]['url'])) { $navbar['options'][$option]['suboptions'][$sub_option]['url'] = generate_device_url($device, array('tab' => $sub_option)); }
        }
      }
    }

    if ($vars['tab'] == 'port')        { $navbar['options']['ports']['class'] .= " active"; }
    if ($vars['tab'] == 'alert')       { $navbar['options']['alerts']['class'] .= " active"; }
    if ($vars['tab'] == 'accesspoint') { $navbar['options']['accesspoints']['class'] .= " active"; }

    print_navbar($navbar);
    unset($navbar);
  }

  // Delete device modal

?>

<div id="delete_device_modal" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="delete_device" aria-hidden="true">

  <div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
    <h3 id="myModalLabel"><i class="oicon-minus-circle"></i> Delete Device '<?php echo $device['hostname']; ?>'</h3>
  </div>
  <div class="modal-body">

<?php
      $form = array('type'      => 'horizontal',
                    'id'        => 'delete_host',
                    //'space'     => '20px',
                    'title'     => 'Delete device',
                    'icon'      => 'oicon-server--minus',
                    //'class'     => 'box box-solid',
                    'url'       => 'delhost/'
                    );

      $form['row'][0]['id']   = array(
                                      'type'        => 'hidden',
                                      'value'       => $device['device_id']);
      $form['row'][4]['deleterrd'] = array(
                                      'type'        => 'checkbox',
                                      'name'        => 'Delete RRDs',
                                      'onchange'    => "javascript: showDiv(this.checked);",
                                      'value'       => 'confirm');
      $form['row'][5]['confirm'] = array(
                                      'type'        => 'checkbox',
                                      'name'        => 'Confirm Deletion',
                                      'onchange'    => "javascript: toggleAttrib('disabled', 'delete_modal');",
                                      'value'       => 'confirm');
      $form['row'][6]['delete_modal'] = array(
                                      'type'        => 'submit',
                                      'name'        => 'Delete device',
                                      'icon'        => 'icon-remove icon-white',
                                      //'right'       => TRUE,
                                      'class'       => 'btn-danger',
                                      'disabled'    => TRUE);

  print_warning("<h4>Warning!</h4>
      This will delete this device from Observium including all logging entries, but will not delete the RRDs.");

      print_form($form);
      unset($form);

?>

 </div>
</div>

<?php

  // Check that the user can view the device, or is viewing a permitted port on the device
  if (isset($cache['devices']['id'][$device['device_id']]) || $permit_tabs[$tab])
  {
    // If this device has never been polled, print a warning here
    if (!$device['last_polled'] || $device['last_polled'] == '0000-00-00 00:00:00')
    {
      print_warning('<h4>Device not yet polled</h4>
This device has not yet been successfully polled. System information and statistics will not be populated and graphs will not draw.
Please wait 5-10 minutes for graphs to draw correctly.');
    }

    // If this device has never been discovered, print a warning here
    if (!$device['last_discovered'] || $device['last_discovered'] == '0000-00-00 00:00:00')
    {
      print_warning('<h4>Device not yet discovered</h4>
This device has not yet been successfully discovered. System information and statistics will not be populated and graphs will not draw.
This device should be automatically discovered within 10 minutes.');
    }

    if (is_file($config['html_dir']."/pages/device/".basename($tab).".inc.php"))
    {
      include($config['html_dir']."/pages/device/".basename($tab).".inc.php");
    } else {
      print_error('<h4>Tab does not exist</h4>
The requested tab does not exist. Please correct the URL and try again.');
    }

  } else {
    print_error_permission();
  }
}

echo '</div>';
echo '</div>';

// EOF
