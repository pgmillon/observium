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

if (preg_match('/^(?<subtype>prefixes)_(?<afi>ipv[46])(?<safi>\w+)/', $subtype, $matches))
{
  // prefixes_ipv4unicast -> $data['bgpPeerRemoteAddr'] . ".ipv4.unicast
  $subtype = $matches['subtype'];
  $index   = $data['bgpPeerRemoteAddr'] . '.' . $matches['afi'] . '.' . $matches['safi'];
  $graph_title .= " :: Prefixes " . escape_html($data['bgpPeerRemoteAddr'] . ' - ' . $matches['afi'] . '.' . $matches['safi']);

} else {
  $subtype = 'updates';
  $graph_title .= " :: Updates " . escape_html($data['bgpPeerRemoteAddr']);
}

include($config['html_dir'] . "/includes/graphs/generic_definition.inc.php");

// EOF
