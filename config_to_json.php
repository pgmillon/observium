<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage poller
 * @author     Adam Armstrong <adama@observium.org>
 * @copyright  (C) 2006-2013 Adam Armstrong, (C) 2013-2016 Observium Limited
 *
 */

 // Prints the entire $config array as a JSON block. Probably needs to be cut-down.

chdir(dirname($argv[0]));

require_once("includes/sql-config.inc.php");

if (is_cli())
{
  print(json_encode($config));
}

// EOF
