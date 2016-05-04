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

$distro = $agent_data['distro'];
unset($agent_data['distro']);

foreach (explode("\n", $distro) as $line)
{
  list($field,$contents) = explode("=", $line, 2);
  $agent_data['distro'][$field] = trim($contents);
}

unset($distro);

// EOF
