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

echo generate_box_open();

?>

<table class="table table-hover table-striped  " style="vertical-align: middle;">
  <tbody>
    <tr class="up" style="vertical-align: middle;">
      <td class="state-marker"></td>
      <td style="vertical-align: middle; padding: 10px 14px ;"><span style="font-size: 20px; color: #193d7f;">BGP AS<?php echo($device['bgpLocalAs']); ?></span>
      </td>
      <td>

<?php

    $sessions = array();
    foreach (dbFetchRows('SELECT `bgpPeer_id`,`bgpPeerState`,`bgpPeerAdminStatus`,`bgpPeerRemoteAs` FROM `bgpPeers` WHERE `device_id` = ?;', array($device['device_id'])) as $bgp)
    {
      $sessions['count']++;
      if ($bgp['bgpPeerAdminStatus'] == 'start' || $bgp['bgpPeerAdminStatus'] == 'running')
      {
        $sessions['enabled']++;
        if ($bgp['bgpPeerState'] != 'established')
        {
          $sessions['alerts']++;
        } else {
          $sessions['connected']++;
        }
      } else {
        $sessions['shutdown']++;
      }
      if ($bgp['bgpPeerRemoteAs'] == $device['bgpLocalAs'])
      {
        $sessions['internal']++;
      } else {
        $sessions['external']++;
      }
    }

?>
      </td>

      <td style="text-align: right;">

        <div class="btn-group" style="margin: 5px;">
          <div class="btn btn-small"><strong>Total Sessions</strong></div>
          <div class="btn btn-small"> <?php echo $sessions['count']+0; ?></div>
        </div>

        <div class="btn-group" style="margin: 5px;">
          <div class="btn btn-small"><strong>Errored Sessions</strong></div>
          <div class="btn btn-small btn-danger"> <?php echo $sessions['alerts']+0; ?></div>
        </div>

        <div class="btn-group" style="margin: 5px;">
          <div class="btn btn-small btn-inactive"><strong>iBGP</strong></div>
          <div class="btn btn-small btn-primary"> <?php echo $sessions['internal']+0; ?></div>
        </div>

        <div class="btn-group" style="margin: 5px;">
          <div class="btn btn-small"><strong>eBGP</strong></div>
          <div class="btn btn-small btn-info"> <?php echo $sessions['external']+0; ?></div>
        </div>
      </td>

     </tr>
   </tbody>
</table>

<?php

echo generate_box_close();

if (!isset($vars['view'])) { $vars['view'] = 'details'; }

unset($navbar);
$link_array = array('page'    => 'device',
                    'device'  => $device['device_id'],
                    'tab'     => 'routing',
                    'proto'   => 'bgp');

$types = array('all'      => 'All',
               'internal' => 'iBGP',
               'external' => 'eBGP');

foreach ($types as $option => $text)
{
  $navbar['options'][$option]['text'] = $text;
  if ($vars['type'] == $option || (empty($vars['type']) && $option == 'all'))
  {
    $navbar['options'][$option]['class'] .= " active";
    $bgp_options = array('type' => NULL);
  } else {
    $bgp_options = array('type' => $option);
  }
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

$bgp_graphs = array();
foreach ($device['graphs'] as $entry)
{
  if (preg_match('/^bgp_(?<subtype>prefixes)_(?<afi>ipv[46])(?<safi>[a-z]+)/', $entry['graph'], $matches))
  {
    if (!isset($bgp_graphs[$matches['safi']]))
    {
      $bgp_graphs[$matches['safi']] = array('text' => nicecase($matches['safi']));
    }
    $bgp_graphs[$matches['safi']]['types'][$matches['subtype'].'_'.$matches['afi'].$matches['safi']] = nicecase($matches['afi']) . ' ' . nicecase($matches['safi']) . ' ' . nicecase($matches['subtype']);
  }
}

$bgp_graphs['mac'] = array('text' => 'MACaccounting');
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
print_bgp_table($vars);

// EOF
