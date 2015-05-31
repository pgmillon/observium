<?php

// NBS-CMMC-MIB::nbsCmmcChassisModel.1 = STRING: NC316BU-16/15AC

$hardware = snmp_get($device, "NBS-CMMC-MIB::nbsCmmcChassisModel.1", "-Ovqsn", "NBS-CMMC-MIB", mib_dirs("mrv"));
$version  = snmp_get($device, "NBS-CMMC-MIB::nbsCmmcSysFwVers.0", "-Ovqsn", "NBS-CMMC-MIB", mib_dirs("mrv"));

//EOF
