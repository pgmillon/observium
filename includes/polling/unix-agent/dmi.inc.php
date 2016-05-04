<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage poller
 * @copyright  (C) 2006-2015 Adam Armstrong
 *
 */

$dmi = $agent_data['dmi'];
unset($agent_data['dmi']);

foreach (explode("\n",$dmi) as $line)
{
  list($field,$contents) = explode("=",$line,2);
  $agent_data['dmi'][$field] = trim($contents);
}

unset($dmi);

?>