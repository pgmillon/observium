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

foreach (dbFetchRows("SELECT * FROM `p2p_radios` WHERE `device_id` = ?", array($device['device_id'])) as $radio)
{
  $GLOBALS['cache']['p2p_radios'][$radio['radio_mib']][$radio['radio_index']] = $radio;
}

$include_dir = "includes/polling/p2p-radios";
include("includes/include-dir-mib.inc.php");

foreach ($GLOBALS['cache']['p2p_radios'] AS $mib_radios)
{

  foreach ($mib_radios AS $radio)
  {

    if (!$GLOBALS['valid']['p2p_radio'][$radio['radio_mib']][$radio['radio_index']])
    {
      dbDelete('p2p_radios', '`radio_id` = ?', array($radio['radio_id']));
      echo('-');
    }

  }

}

// EOF
