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

// USW-24P-250, 3.3.5.3734, Linux 3.6.5

$data = explode(', ', $poll_device['sysDescr']);
if (count($data) > 2)
{
  $hardware = $data[0];
  $version  = $data[1];
}

// EOF
