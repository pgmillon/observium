<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage discovery
 * @copyright  (C) 2006-2013 Adam Armstrong, (C) 2013-2016 Observium Limited
 *
 */

if (!$os && ($sysObjectId == '.1.3.6.1.4.1.890' || strpos($sysObjectId, '.1.3.6.1.4.1.890.') === 0))
{
  if (strpos($sysDescr, "ZyWALL") !== FALSE) { $os = "zywall"; }
  else if (preg_match("/^X?(ES|GS)/", $sysDescr)) { $os = "zyxeles"; }
  else if (strpos($sysDescr, "NWA-") === 0) { $os = "zyxelnwa"; }
  else if (strpos($sysDescr, "P") === 0) { $os = "prestige"; }
  else if (strpos($sysDescr, "IES") !== FALSE) { $os = "ies"; }
  else if (strpos($sysDescr, "Alcatel") === FALSE) { $os = "ies"; } // All other ZyXEL DSL, except Alcatel
}

// EOF
