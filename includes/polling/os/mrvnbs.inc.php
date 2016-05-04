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

// NBS-CMMC-MIB::nbsCmmcChassisModel.1 = STRING: NC316BU-16/15AC

$hardware = snmp_get($device, "NBS-CMMC-MIB::nbsCmmcChassisModel.1", "-Ovqsn", "NBS-CMMC-MIB", mib_dirs("mrv"));
$version  = snmp_get($device, "NBS-CMMC-MIB::nbsCmmcSysFwVers.0", "-Ovqsn", "NBS-CMMC-MIB", mib_dirs("mrv"));

//EOF
