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

/*
CYAN-NODE-MIB::cyanNodeAdminState.0 = INTEGER: adminunlocked(1)
CYAN-NODE-MIB::cyanNodeAssetTag.0 = STRING: Z33
CYAN-NODE-MIB::cyanNodeBaseMacAddress.0 = STRING: 00:1D:99:xx:xx:xx
CYAN-NODE-MIB::cyanNodeBay.0 = STRING:
CYAN-NODE-MIB::cyanNodeCity.0 = STRING:
CYAN-NODE-MIB::cyanNodeCountry.0 = STRING:
CYAN-NODE-MIB::cyanNodeCurrentTimeZone.0 = INTEGER: utc(0)
CYAN-NODE-MIB::cyanNodeDescription.0 = STRING: Z33
CYAN-NODE-MIB::cyanNodeDhcpOnConsolePort.0 = INTEGER: enabled(1)
CYAN-NODE-MIB::cyanNodeIdentifier.0 = STRING: ROOT
CYAN-NODE-MIB::cyanNodeLatitude.0 = INTEGER: 0
CYAN-NODE-MIB::cyanNodeLongitude.0 = INTEGER: 0
CYAN-NODE-MIB::cyanNodeMacBlockSize.0 = Gauge32: 4
CYAN-NODE-MIB::cyanNodeMfgCleiCode.0 = STRING:
CYAN-NODE-MIB::cyanNodeMfgEciCode.0 = STRING:
CYAN-NODE-MIB::cyanNodeMfgModuleId.0 = Gauge32: 0
CYAN-NODE-MIB::cyanNodeMfgPartNumber.0 = STRING: 910-0005-01-01
CYAN-NODE-MIB::cyanNodeMfgRevision.0 = STRING: 26
CYAN-NODE-MIB::cyanNodeMfgSerialNumber.0 = STRING: FX50141xxxxxx
CYAN-NODE-MIB::cyanNodeName.0 = STRING: Z33
CYAN-NODE-MIB::cyanNodeNationalization.0 = INTEGER: ansi(2)
CYAN-NODE-MIB::cyanNodeNodeId.0 = Gauge32: 701600xxx
CYAN-NODE-MIB::cyanNodeOidClass.0 = STRING: OID_CLASS_NODE
CYAN-NODE-MIB::cyanNodeOperState.0 = INTEGER: is(1)
CYAN-NODE-MIB::cyanNodeOperStateQual.0 = INTEGER: anr(7)
CYAN-NODE-MIB::cyanNodeOssLabel.0 = STRING: Z33
CYAN-NODE-MIB::cyanNodeOwner.0 = STRING:
CYAN-NODE-MIB::cyanNodePartNumber.0 = STRING: 910-0005-01
CYAN-NODE-MIB::cyanNodePostalCode.0 = STRING:
CYAN-NODE-MIB::cyanNodeRackUnits.0 = STRING:
CYAN-NODE-MIB::cyanNodeRegion.0 = STRING:
CYAN-NODE-MIB::cyanNodeRelayRack.0 = STRING:
CYAN-NODE-MIB::cyanNodeSecServState.0 = BITS: 00 20 00 20 04 flt(10) sgeo(26) 37
CYAN-NODE-MIB::cyanNodeStreet.0 = STRING:
CYAN-NODE-MIB::cyanNodeType.0 = INTEGER: cyanShelf8(10)
*/

$data = snmp_get_multi($device, 'cyanNodeMfgSerialNumber.0', '-OQUs', 'CYAN-NODE-MIB', mib_dirs('cyan'));

$serial  = $data[0]['cyanNodeMfgSerialNumber'];

// EOF
