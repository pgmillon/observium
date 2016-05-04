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

if ($poll_device['sysDescr'] == "SNMP TME") { $hardware = "TME"; }
else if ($poll_device['sysDescr'] == "TME") { $hardware = "TME"; }
else if ($poll_device['sysDescr'] == "TH2E") { $hardware = "TH2E"; }

// EOF
