<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage graphs
 * @copyright  (C) 2006-2013 Adam Armstrong, (C) 2013-2016 Observium Limited
 *
 */

if (is_numeric($vars['id']))
{

  $data = dbFetchRow("SELECT * FROM `bgpPeers` WHERE `bgpPeer_id` = ?", array($vars['id']));

  if (is_numeric($data['device_id']) && ($auth || device_permitted($data['device_id'])))
  {
    $device = device_by_id_cache($data['device_id']);

    $graph_title = $device['hostname'];
    $graph_title .= " :: AS" . escape_html($data['bgpPeerRemoteAs']);
    $auth = TRUE;
  }
}

// EOF
