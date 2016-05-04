<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage webui
 * @copyright  (C) 2006-2015 Adam Armstrong
 *
 */

if ($_SESSION['userlevel'] < '5')
{
  include("includes/error-no-perm.inc.php");
} else {
  if (!isset($vars['view'])) { $vars['view'] = 'details'; }
  unset($navbar);
  $link_array = array('page' => 'routing',
                      'protocol' => 'bgp');

  $types = array('all'      => 'All',
                 'internal' => 'iBGP',
                 'external' => 'eBGP');
  foreach ($types as $option => $text)
  {
    $navbar['options'][$option]['text'] = $text;
    if ($vars['type'] == $option || (empty($vars['type']) && $option == 'all')) { $navbar['options'][$option]['class'] .= " active"; }
    $bgp_options = array('type' => $option);
    if ($vars['adminstatus']) { $bgp_options['adminstatus'] = $vars['adminstatus']; }
    elseif ($vars['state']) { $bgp_options['state'] = $vars['state']; }
    $navbar['options'][$option]['url'] = generate_url($link_array, $bgp_options);
  }

  $statuses = array('stop'  => 'Shutdown',
                    'start' => 'Enabled',
                    'down'  => 'Down');
  foreach ($statuses as $option => $text)
  {
    $status = ($option == 'down') ? 'state' : 'adminstatus';
    $navbar['options'][$option]['text'] = $text;
    if ($vars[$status] == $option)
    {
      $navbar['options'][$option]['class'] .= " active";
      $bgp_options = array($status => NULL);
    } else {
      $bgp_options = array($status => $option);
    }
    if ($vars['type']) { $bgp_options['type'] = $vars['type']; }
    $navbar['options'][$option]['url'] = generate_url($link_array, $bgp_options);
  }

  $navbar['options_right']['details']['text'] = 'No Graphs';
  if ($vars['view'] == 'details') { $navbar['options_right']['details']['class'] .= ' active'; }
  $navbar['options_right']['details']['url'] = generate_url($vars, array('view' => 'details', 'graph' => 'NULL'));

  $navbar['options_right']['updates']['text'] = 'Updates';
  if ($vars['graph'] == 'updates') { $navbar['options_right']['updates']['class'] .= ' active'; }
  $navbar['options_right']['updates']['url'] = generate_url($vars, array('view' => 'graphs', 'graph' => 'updates'));

  $bgp_graphs = array('unicast'   => array('text' => 'Unicast'),
                      'multicast' => array('text' => 'Multicast'),
                      'mac'       => array('text' => 'MACaccounting'));
  $bgp_graphs['unicast']['types'] = array('prefixes_ipv4unicast' => 'IPv4 Ucast Prefixes',
                                          'prefixes_ipv6unicast' => 'IPv6 Ucast Prefixes',
                                          'prefixes_ipv4vpn'     => 'VPNv4 Prefixes');
  $bgp_graphs['multicast']['types'] = array('prefixes_ipv4multicast' => 'IPv4 Mcast Prefixes',
                                            'prefixes_ipv6multicast' => 'IPv6 Mcast Prefixes');
  $bgp_graphs['mac']['types'] = array('macaccounting_bits' => 'MAC Bits',
                                      'macaccounting_pkts' => 'MAC Pkts');
  foreach ($bgp_graphs as $bgp_graph => $bgp_options)
  {
    $navbar['options_right'][$bgp_graph]['text'] = $bgp_options['text'];
    foreach ($bgp_options['types'] as $option => $text)
    {
      if ($vars['graph'] == $option)
      {
        $navbar['options_right'][$bgp_graph]['class'] .= ' active';
        $navbar['options_right'][$bgp_graph]['suboptions'][$option]['class'] = 'active';
      }
      $navbar['options_right'][$bgp_graph]['suboptions'][$option]['text'] = $text;
      $navbar['options_right'][$bgp_graph]['suboptions'][$option]['url'] = generate_url($vars, array('view' => 'graphs', 'graph' => $option));
    }
  }

  $navbar['class'] = "navbar-narrow";
  $navbar['brand'] = "BGP";
  print_navbar($navbar);

  // Pagination
  $vars['pagination'] = TRUE;

  //r($cache['bgp']);
  print_bgp($vars);
}

// EOF
