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

if (preg_match('/AXIS (?<hardware>[\w\-\+]+);.+?; (?<version>[\d\.]+)/', $poll_device['sysDescr'], $matches))
{
  // ; AXIS 241S; Video Server; 4.47; May 30 2008 15:19; 11C.1; 1;
  // ; AXIS M1011-W; Network Camera; 5.20.2; Sep 09 2011 10:44; 171; 1;
  // ; AXIS P7214; Network Video Encoder; 5.50.4; May 23 2014 12:23; 197.2; 1;
  $hardware = $matches['hardware'];
  $version  = $matches['version'];
}
else if (preg_match('/AXIS (?<hardware>[\w\-\+]+) [\w ]+?V(?<version>[\d\.]+)/', $poll_device['sysDescr'], $matches))
{
  // AXIS 5600+ Network Print Server V7.10.2 Jan 30 2007
  $hardware = $matches['hardware'];
  $version  = $matches['version'];
}

// EOF
