<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage definitions
 * @copyright  (C) 2006-2015 Adam Armstrong
 *
 */

/// FIXME. $config['status_states'] >> $config['status']['states']

// Status indicator state names

// CISCO-ENVMON-MIB
// See: http://tools.cisco.com/Support/SNMP/do/BrowseOID.do?local=en&translate=Translate&typeName=CiscoEnvMonState
$config['status_states']['cisco-envmon-state'][1] = array('name' => 'normal',         'event' => 'ok');
$config['status_states']['cisco-envmon-state'][2] = array('name' => 'warning',        'event' => 'warning');
$config['status_states']['cisco-envmon-state'][3] = array('name' => 'critical',       'event' => 'alert');
$config['status_states']['cisco-envmon-state'][4] = array('name' => 'shutdown',       'event' => 'down');
$config['status_states']['cisco-envmon-state'][5] = array('name' => 'notPresent',     'event' => 'ignore');
$config['status_states']['cisco-envmon-state'][6] = array('name' => 'notFunctioning', 'event' => 'ignore');

// CISCO-ENTITY-SENSOR-MIB
$config['status_states']['cisco-entity-state'][1] = array('name' => 'true',         'event' => 'ok');
$config['status_states']['cisco-entity-state'][2] = array('name' => 'false',        'event' => 'alert');

// FASTPATH-BOXSERVICES-PRIVATE-MIB
// Note: this is for the official Broadcom FastPath Box Services MIB. The idiots at Netgear modified this MIB, swapping
// status values around for no reason at all. That won't work. The tree is under a different OID, but if someone ever wants
// to implement support for their MIB, don't use this same 'fastpath-boxservices-private-state' as it will be incorrect.
$config['status_states']['fastpath-boxservices-private-state'][1] = array('name' => 'notPresent',  'event' => 'ignore');
$config['status_states']['fastpath-boxservices-private-state'][2] = array('name' => 'operational', 'event' => 'ok');
$config['status_states']['fastpath-boxservices-private-state'][3] = array('name' => 'failed',      'event' => 'alert');

$config['status_states']['fastpath-boxservices-private-temp-state'][1] = array('name' => 'normal',         'event' => 'ok');
$config['status_states']['fastpath-boxservices-private-temp-state'][2] = array('name' => 'warning',        'event' => 'warning');
$config['status_states']['fastpath-boxservices-private-temp-state'][3] = array('name' => 'critical',       'event' => 'alert');
$config['status_states']['fastpath-boxservices-private-temp-state'][4] = array('name' => 'shutdown',       'event' => 'warning');
$config['status_states']['fastpath-boxservices-private-temp-state'][5] = array('name' => 'notPresent',     'event' => 'ignore');
$config['status_states']['fastpath-boxservices-private-temp-state'][6] = array('name' => 'notOperational', 'event' => 'ignore');

// RADLAN-HWENVIRONMENT
$config['status_states']['radlan-hwenvironment-state'][1] = array('name' => 'normal',         'event' => 'ok');
$config['status_states']['radlan-hwenvironment-state'][2] = array('name' => 'warning',        'event' => 'warning');
$config['status_states']['radlan-hwenvironment-state'][3] = array('name' => 'critical',       'event' => 'alert');
$config['status_states']['radlan-hwenvironment-state'][4] = array('name' => 'shutdown',       'event' => 'down');
$config['status_states']['radlan-hwenvironment-state'][5] = array('name' => 'notPresent',     'event' => 'ignore');
$config['status_states']['radlan-hwenvironment-state'][6] = array('name' => 'notFunctioning', 'event' => 'ignore');

// AC-SYSTEM-MIB
$config['status_states']['ac-system-fan-state'][0] = array('name' => 'cleared',         'event' => 'ok');
$config['status_states']['ac-system-fan-state'][1] = array('name' => 'indeterminate',   'event' => 'ignore');
$config['status_states']['ac-system-fan-state'][2] = array('name' => 'warning',         'event' => 'warning');
$config['status_states']['ac-system-fan-state'][3] = array('name' => 'minor',           'event' => 'ok');
$config['status_states']['ac-system-fan-state'][4] = array('name' => 'major',           'event' => 'warning');
$config['status_states']['ac-system-fan-state'][5] = array('name' => 'critical',        'event' => 'alert');
$config['status_states']['ac-system-power-state'][1] = array('name' => 'cleared',       'event' => 'ok');
$config['status_states']['ac-system-power-state'][2] = array('name' => 'indeterminate', 'event' => 'ignore');
$config['status_states']['ac-system-power-state'][3] = array('name' => 'warning',       'event' => 'warning');
$config['status_states']['ac-system-power-state'][4] = array('name' => 'minor',         'event' => 'ok');
$config['status_states']['ac-system-power-state'][5] = array('name' => 'major',         'event' => 'warning');
$config['status_states']['ac-system-power-state'][6] = array('name' => 'critical',      'event' => 'alert');

// ACME-ENVMON-MIB
$config['status_states']['acme-env-state'][2] = array('name' => 'normal',         'event' => 'ok');
$config['status_states']['acme-env-state'][3] = array('name' => 'minor',          'event' => 'warning');
$config['status_states']['acme-env-state'][4] = array('name' => 'major',          'event' => 'alert');
$config['status_states']['acme-env-state'][5] = array('name' => 'critical',       'event' => 'alert');
$config['status_states']['acme-env-state'][5] = array('name' => 'shutdown',       'event' => 'down');
$config['status_states']['acme-env-state'][7] = array('name' => 'notPresent',     'event' => 'ignore');
$config['status_states']['acme-env-state'][8] = array('name' => 'notFunctioning', 'event' => 'ignore');

// DELL-Vendor-MIB
$config['status_states']['dell-vendor-state'][1] = array('name' => 'normal',         'event' => 'ok');
$config['status_states']['dell-vendor-state'][2] = array('name' => 'warning',        'event' => 'warning');
$config['status_states']['dell-vendor-state'][3] = array('name' => 'critical',       'event' => 'alert');
$config['status_states']['dell-vendor-state'][4] = array('name' => 'shutdown',       'event' => 'down');
$config['status_states']['dell-vendor-state'][5] = array('name' => 'notPresent',     'event' => 'ignore');
$config['status_states']['dell-vendor-state'][6] = array('name' => 'notFunctioning', 'event' => 'ignore');

// DNOS-BOXSERVICES-PRIVATE-MIB
$config['status_states']['dnos-boxservices-state'][1] = array('name' => 'notpresent',           'event' => 'ignore');
$config['status_states']['dnos-boxservices-state'][2] = array('name' => 'operational',          'event' => 'ok');
$config['status_states']['dnos-boxservices-state'][3] = array('name' => 'failed',               'event' => 'alert');
$config['status_states']['dnos-boxservices-state'][4] = array('name' => 'powering',             'event' => 'ignore');
$config['status_states']['dnos-boxservices-state'][5] = array('name' => 'nopower',              'event' => 'alert');
$config['status_states']['dnos-boxservices-state'][6] = array('name' => 'notpowering',          'event' => 'alert');
$config['status_states']['dnos-boxservices-state'][7] = array('name' => 'incompatible',         'event' => 'ignore');
$config['status_states']['dnos-boxservices-temp-state'][0] = array('name' => 'low',             'event' => 'ok');
$config['status_states']['dnos-boxservices-temp-state'][1] = array('name' => 'normal',          'event' => 'ok');
$config['status_states']['dnos-boxservices-temp-state'][2] = array('name' => 'warning',         'event' => 'warning');
$config['status_states']['dnos-boxservices-temp-state'][3] = array('name' => 'critical',        'event' => 'alert');
$config['status_states']['dnos-boxservices-temp-state'][4] = array('name' => 'shutdown',        'event' => 'down');
$config['status_states']['dnos-boxservices-temp-state'][5] = array('name' => 'notpresent',      'event' => 'ignore');
$config['status_states']['dnos-boxservices-temp-state'][6] = array('name' => 'notoperational',  'event' => 'ignore');

// SPAGENT-MIB
$config['status_states']['spagent-state'][1] = array('name' => 'noStatus',     'event' => 'ignore');
$config['status_states']['spagent-state'][2] = array('name' => 'normal',       'event' => 'ok');
$config['status_states']['spagent-state'][4] = array('name' => 'highCritical', 'event' => 'alert');
$config['status_states']['spagent-state'][6] = array('name' => 'lowCritical',  'event' => 'warning');
$config['status_states']['spagent-state'][7] = array('name' => 'sensorError',  'event' => 'alert');
$config['status_states']['spagent-state'][8] = array('name' => 'relayOn',      'event' => 'ok');
$config['status_states']['spagent-state'][9] = array('name' => 'relayOff',     'event' => 'ok');

// OADWDM-MIB
$config['status_states']['oadwdm-fan-state'][1] = array('name' => 'none',       'event' => 'ignore');
$config['status_states']['oadwdm-fan-state'][2] = array('name' => 'active',     'event' => 'ok');
$config['status_states']['oadwdm-fan-state'][3] = array('name' => 'notActive',  'event' => 'warning');
$config['status_states']['oadwdm-fan-state'][4] = array('name' => 'fail',       'event' => 'down');

$config['status_states']['oadwdm-powersupply-state'][1] = array('name' => 'none',       'event' => 'ignore');
$config['status_states']['oadwdm-powersupply-state'][2] = array('name' => 'active',     'event' => 'ok');
$config['status_states']['oadwdm-powersupply-state'][3] = array('name' => 'notActive',  'event' => 'warning');
$config['status_states']['oadwdm-powersupply-state'][4] = array('name' => 'fail',       'event' => 'down');

// PowerNet-MIB
$config['status_states']['powernet-status-state'][1] = array('name' => 'fail',     'event' => 'alert');
$config['status_states']['powernet-status-state'][2] = array('name' => 'ok',       'event' => 'ok');

$config['status_states']['powernet-sync-state'][1] = array('name' => 'inSync',     'event' => 'ok');
$config['status_states']['powernet-sync-state'][2] = array('name' => 'outOfSync',  'event' => 'alert');

$config['status_states']['powernet-mupscontact-state'][1] = array('name' => 'unknown', 'event' => 'warning');
$config['status_states']['powernet-mupscontact-state'][2] = array('name' => 'noFault', 'event' => 'ok');
$config['status_states']['powernet-mupscontact-state'][3] = array('name' => 'fault',   'event' => 'alert');

$config['status_states']['powernet-rpdusupply1-state'][1] = array('name' => 'powerSupplyOneOk',         'event' => 'ok');
$config['status_states']['powernet-rpdusupply1-state'][2] = array('name' => 'powerSupplyOneFailed',     'event' => 'alert');

$config['status_states']['powernet-rpdusupply2-state'][1] = array('name' => 'powerSupplyTwoOk',         'event' => 'ok');
$config['status_states']['powernet-rpdusupply2-state'][2] = array('name' => 'powerSupplyTwoFailed',     'event' => 'alert');
$config['status_states']['powernet-rpdusupply2-state'][3] = array('name' => 'powerSupplyTwoNotPresent', 'event' => 'ignore');

$config['status_states']['powernet-rpdu2supply-state'][1] = array('name' => 'normal',       'event' => 'ok');
$config['status_states']['powernet-rpdu2supply-state'][2] = array('name' => 'alarm',        'event' => 'alert');
$config['status_states']['powernet-rpdu2supply-state'][3] = array('name' => 'notInstalled', 'event' => 'ignore');

$config['status_states']['powernet-upsbasicoutput-state'][1]  = array('name' => 'unknown',                  'event' => 'warning');
$config['status_states']['powernet-upsbasicoutput-state'][2]  = array('name' => 'onLine',                   'event' => 'ok');
$config['status_states']['powernet-upsbasicoutput-state'][3]  = array('name' => 'onBattery',                'event' => 'alert');
$config['status_states']['powernet-upsbasicoutput-state'][4]  = array('name' => 'onSmartBoost',             'event' => 'warning');
$config['status_states']['powernet-upsbasicoutput-state'][5]  = array('name' => 'timedSleeping',            'event' => 'warning');
$config['status_states']['powernet-upsbasicoutput-state'][6]  = array('name' => 'softwareBypass',           'event' => 'alert');
$config['status_states']['powernet-upsbasicoutput-state'][7]  = array('name' => 'off',                      'event' => 'alert');
$config['status_states']['powernet-upsbasicoutput-state'][8]  = array('name' => 'rebooting',   '             event' => 'warning');
$config['status_states']['powernet-upsbasicoutput-state'][9]  = array('name' => 'switchedBypass',           'event' => 'warning');
$config['status_states']['powernet-upsbasicoutput-state'][10] = array('name' => 'hardwareFailureBypass',    'event' => 'alert');
$config['status_states']['powernet-upsbasicoutput-state'][11] = array('name' => 'sleepingUntilPowerReturn', 'event' => 'warning');
$config['status_states']['powernet-upsbasicoutput-state'][12] = array('name' => 'onSmartTrim',              'event' => 'warning');
$config['status_states']['powernet-upsbasicoutput-state'][13] = array('name' => 'ecoMode',                  'event' => 'ok');
$config['status_states']['powernet-upsbasicoutput-state'][14] = array('name' => 'hotStandby',               'event' => 'ok');
$config['status_states']['powernet-upsbasicoutput-state'][15] = array('name' => 'onBatteryTest',            'event' => 'warning');
$config['status_states']['powernet-upsbasicoutput-state'][16] = array('name' => 'emergencyStaticBypass',    'event' => 'alert');
$config['status_states']['powernet-upsbasicoutput-state'][17] = array('name' => 'staticBypassStandby',      'event' => 'warning');
$config['status_states']['powernet-upsbasicoutput-state'][18] = array('name' => 'powerSavingMode',          'event' => 'ok');
$config['status_states']['powernet-upsbasicoutput-state'][19] = array('name' => 'spotMode',                 'event' => 'warning');
$config['status_states']['powernet-upsbasicoutput-state'][20] = array('name' => 'eConversion',              'event' => 'ok');

$config['status_states']['powernet-cooling-input-state'][0] = array('name' => 'Open',            'event' => 'ok');
$config['status_states']['powernet-cooling-input-state'][1] = array('name' => 'Closed',          'event' => 'alert');

$config['status_states']['powernet-cooling-output-state'][0] = array('name' => 'Abnormal',       'event' => 'alert');
$config['status_states']['powernet-cooling-output-state'][1] = array('name' => 'Normal',         'event' => 'ok');

$config['status_states']['powernet-cooling-powersource-state'][0] = array('name' => 'Primary',   'event' => 'ok');
$config['status_states']['powernet-cooling-powersource-state'][1] = array('name' => 'Secondary', 'event' => 'warning');

$config['status_states']['powernet-cooling-unittype-state'][0] = array('name' => 'Undefined',    'event' => 'ignore');
$config['status_states']['powernet-cooling-unittype-state'][1] = array('name' => 'Standard',     'event' => 'ok');
$config['status_states']['powernet-cooling-unittype-state'][2] = array('name' => 'HighTemp',     'event' => 'ok');

$config['status_states']['powernet-cooling-opmode-state'][0] = array('name' => 'Standby',     'event' => 'ignore');
$config['status_states']['powernet-cooling-opmode-state'][1] = array('name' => 'On',          'event' => 'ok');
$config['status_states']['powernet-cooling-opmode-state'][2] = array('name' => 'Idle',        'event' => 'ok');
$config['status_states']['powernet-cooling-opmode-state'][3] = array('name' => 'Maintenance', 'event' => 'warning');

$config['status_states']['powernet-cooling-flowcontrol-state'][0] = array('name' => 'Under', 'event' => 'alert');
$config['status_states']['powernet-cooling-flowcontrol-state'][1] = array('name' => 'Okay',  'event' => 'ok');
$config['status_states']['powernet-cooling-flowcontrol-state'][2] = array('name' => 'Over',  'event' => 'alert');
$config['status_states']['powernet-cooling-flowcontrol-state'][3] = array('name' => 'NA',    'event' => 'ignore');

// TRAPEZE-NETWORKS-SYSTEM-MIB
$config['status_states']['trapeze-state'][1] = array('name' => 'other',         'event' => 'warning');
$config['status_states']['trapeze-state'][2] = array('name' => 'unknown',       'event' => 'warning');
$config['status_states']['trapeze-state'][3] = array('name' => 'ac-failed',     'event' => 'alert');
$config['status_states']['trapeze-state'][4] = array('name' => 'dc-failed',     'event' => 'alert');
$config['status_states']['trapeze-state'][5] = array('name' => 'ac-ok-dc-ok',   'event' => 'ok');

// GEIST-MIB-V3
$config['status_states']['geist-mib-v3-door-state'][1]        = array('name' => 'closed', 'event' => 'ok');
$config['status_states']['geist-mib-v3-door-state'][99]       = array('name' => 'open',   'event' => 'alert');
$config['status_states']['geist-mib-v3-digital-state'][1]     = array('name' => 'off',    'event' => 'alert');
$config['status_states']['geist-mib-v3-digital-state'][99]    = array('name' => 'on',     'event' => 'ok');
$config['status_states']['geist-mib-v3-smokealarm-state'][1]  = array('name' => 'clear',  'event' => 'ok');
$config['status_states']['geist-mib-v3-smokealarm-state'][99] = array('name' => 'smoky',  'event' => 'alert');
$config['status_states']['geist-mib-v3-climateio-state'][0]   = array('name' => '0V',     'event' => 'ok');
$config['status_states']['geist-mib-v3-climateio-state'][99]  = array('name' => '5V',     'event' => 'ok');
$config['status_states']['geist-mib-v3-climateio-state'][100] = array('name' => '5V',     'event' => 'ok');
$config['status_states']['geist-mib-v3-relay-state'][0]       = array('name' => 'off',    'event' => 'ok');
$config['status_states']['geist-mib-v3-relay-state'][1]       = array('name' => 'on',     'event' => 'ok');

// GEIST-V4-MIB
$config['status_states']['geist-v4-mib-io-state'][0]   = array('name' => '0V', 'event' => 'ok');
$config['status_states']['geist-v4-mib-io-state'][100] = array('name' => '5V', 'event' => 'ok');

// CPQHLTH-MIB
$config['status_states']['cpqhlth-state'][1] = array('name' => 'other',                       'event' => 'ignore');
$config['status_states']['cpqhlth-state'][2] = array('name' => 'ok',                          'event' => 'ok');
$config['status_states']['cpqhlth-state'][3] = array('name' => 'degraded',                    'event' => 'warning');
$config['status_states']['cpqhlth-state'][4] = array('name' => 'failed',                      'event' => 'alert');

// CPQIDA-MIB
$config['status_states']['cpqida-cntrl-state'][1] = array('name' => 'other',                  'event' => 'ignore');
$config['status_states']['cpqida-cntrl-state'][2] = array('name' => 'ok',                     'event' => 'ok');
$config['status_states']['cpqida-cntrl-state'][3] = array('name' => 'generalFailure',         'event' => 'alert');
$config['status_states']['cpqida-cntrl-state'][4] = array('name' => 'cableProblem',           'event' => 'alert');
$config['status_states']['cpqida-cntrl-state'][5] = array('name' => 'poweredOff',             'event' => 'alert');

$config['status_states']['cpqida-smart-state'][1] = array('name' => 'other',                  'event' => 'ignore');
$config['status_states']['cpqida-smart-state'][2] = array('name' => 'ok',                     'event' => 'ok');
$config['status_states']['cpqida-smart-state'][3] = array('name' => 'replaceDrive',           'event' => 'alert');
$config['status_states']['cpqida-smart-state'][4] = array('name' => 'replaceDriveSSDWearOut', 'event' => 'warning');

// SYNOLOGY-SYSTEM-MIB
$config['status_states']['synology-status-state'][1] = array('name' => 'Normal',              'event' => 'ok');
$config['status_states']['synology-status-state'][2] = array('name' => 'Failed',              'event' => 'alert');

// SYNOLOGY-DISK-MIB
$config['status_states']['synology-disk-state'][1] = array('name' => 'Normal',                'event' => 'ok');
$config['status_states']['synology-disk-state'][2] = array('name' => 'Initialized',           'event' => 'warning');
$config['status_states']['synology-disk-state'][3] = array('name' => 'NotInitialized',        'event' => 'warning');
$config['status_states']['synology-disk-state'][4] = array('name' => 'SystemPartitionFailed', 'event' => 'alert');
$config['status_states']['synology-disk-state'][5] = array('name' => 'Crashed',               'event' => 'alert');

// EQLDISK-MIB
$config['status_states']['eql-disk-state'][1] = array('name' => 'on-line',                    'event' => 'ok');
$config['status_states']['eql-disk-state'][2] = array('name' => 'spare',                      'event' => 'ok');
$config['status_states']['eql-disk-state'][3] = array('name' => 'failed',                     'event' => 'alert');
$config['status_states']['eql-disk-state'][4] = array('name' => 'off-line',                   'event' => 'alert');
$config['status_states']['eql-disk-state'][5] = array('name' => 'alt-sig',                    'event' => 'ignore');
$config['status_states']['eql-disk-state'][6] = array('name' => 'too-small',                  'event' => 'ignore');
$config['status_states']['eql-disk-state'][7] = array('name' => 'history-of-failures',        'event' => 'ignore');
$config['status_states']['eql-disk-state'][8] = array('name' => 'unsupported-version',        'event' => 'ignore');
$config['status_states']['eql-disk-state'][9] = array('name' => 'unhealthy',                  'event' => 'warning');
$config['status_states']['eql-disk-state'][10] = array('name' => 'replacement',               'event' => 'ignore');
$config['status_states']['eql-disk-state'][11] = array('name' => 'encrypted',                 'event' => 'ignore');
$config['status_states']['eql-disk-state'][12] = array('name' => 'notApproved',               'event' => 'ignore');
$config['status_states']['eql-disk-state'][13] = array('name' => 'preempt-failed',            'event' => 'ignore');

// ExaltComProducts
$config['status_states']['exaltcomproducts-state'][0] = array('name' => 'almNORMAL',          'event' => 'ok');
$config['status_states']['exaltcomproducts-state'][1] = array('name' => 'almMINOR',           'event' => 'warning');
$config['status_states']['exaltcomproducts-state'][2] = array('name' => 'almMAJOR',           'event' => 'alert');

// MG-SNMP-UPS-MIB
$config['status_states']['mge-status-state'][1] = array('name' => 'Yes',                      'event' => 'alert');
$config['status_states']['mge-status-state'][2] = array('name' => 'No',                       'event' => 'ok');

// SUPERMICRO-HEALTH-MIB
$config['status_states']['supermicro-state'][0] = array('name' => 'Good',                     'event' => 'ok');
$config['status_states']['supermicro-state'][1] = array('name' => 'Bad',                      'event' => 'alert');

// LSI-MegaRAID-SAS-MIB
$config['status_states']['lsi-megaraid-sas-pd-state'][0]     = array('name' => 'unconfigured-good',     'event' => 'warning');
$config['status_states']['lsi-megaraid-sas-pd-state'][1]     = array('name' => 'unconfigured-bad',      'event' => 'alert');
$config['status_states']['lsi-megaraid-sas-pd-state'][2]     = array('name' => 'hot-spare',             'event' => 'ok');
$config['status_states']['lsi-megaraid-sas-pd-state'][16]    = array('name' => 'offline',               'event' => 'alert');
$config['status_states']['lsi-megaraid-sas-pd-state'][17]    = array('name' => 'failed',                'event' => 'alert');
$config['status_states']['lsi-megaraid-sas-pd-state'][20]    = array('name' => 'rebuild',               'event' => 'warning');
$config['status_states']['lsi-megaraid-sas-pd-state'][24]    = array('name' => 'online',                'event' => 'ok');
$config['status_states']['lsi-megaraid-sas-pd-state'][32]    = array('name' => 'copyback',              'event' => 'alert');
$config['status_states']['lsi-megaraid-sas-pd-state'][64]    = array('name' => 'system',                'event' => 'ok');
$config['status_states']['lsi-megaraid-sas-pd-state'][128]   = array('name' => 'unconfigured-shielded', 'event' => 'warning');
$config['status_states']['lsi-megaraid-sas-pd-state'][130]   = array('name' => 'hotspare-shielded',     'event' => 'ok');
$config['status_states']['lsi-megaraid-sas-pd-state'][144]   = array('name' => 'configured-shielded',   'event' => 'ok');

$config['status_states']['lsi-megaraid-sas-sensor-state'][1] = array('name' => 'invalid',               'event' => 'alert');
$config['status_states']['lsi-megaraid-sas-sensor-state'][2] = array('name' => 'ok',                    'event' => 'ok');
$config['status_states']['lsi-megaraid-sas-sensor-state'][3] = array('name' => 'critical',              'event' => 'alert');
$config['status_states']['lsi-megaraid-sas-sensor-state'][4] = array('name' => 'nonCritical',           'event' => 'warning');
$config['status_states']['lsi-megaraid-sas-sensor-state'][5] = array('name' => 'unrecoverable',         'event' => 'alert');
$config['status_states']['lsi-megaraid-sas-sensor-state'][6] = array('name' => 'not-installed',         'event' => 'ok');
$config['status_states']['lsi-megaraid-sas-sensor-state'][7] = array('name' => 'unknown',               'event' => 'warning');
$config['status_states']['lsi-megaraid-sas-sensor-state'][8] = array('name' => 'not-available',         'event' => 'alert');

// JUNIPER-ALARM-MIB
$config['status_states']['juniper-alarm-state'][1] = array('name' => 'other', 'event' => 'warning');
$config['status_states']['juniper-alarm-state'][2] = array('name' => 'off',   'event' => 'ok');
$config['status_states']['juniper-alarm-state'][3] = array('name' => 'on',    'event' => 'alert');

// NOKIA-IPSO-SYSTEM-MIB
$config['status_states']['ipso-temperature-state'][1] = array('name' => 'normal',          'event' => 'ok');
$config['status_states']['ipso-temperature-state'][2] = array('name' => 'overTemperature', 'event' => 'alert');

$config['status_states']['ipso-sensor-state'][1] = array('name' => 'running',    'event' => 'ok');
$config['status_states']['ipso-sensor-state'][2] = array('name' => 'notRunning', 'event' => 'alert');

// NS-ROOT-MIB
$config['status_states']['netscaler-state'][0]    = array('name' => 'normal',     'event' => 'ok');
$config['status_states']['netscaler-state'][1]    = array('name' => 'failed',     'event' => 'alert');

$config['status_states']['netscaler-ha-mode'][0] = array('name' => 'standalone', 'event' => 'ok');
$config['status_states']['netscaler-ha-mode'][1] = array('name' => 'primary',    'event' => 'ok');
$config['status_states']['netscaler-ha-mode'][2] = array('name' => 'secondary',  'event' => 'ok');
$config['status_states']['netscaler-ha-mode'][3] = array('name' => 'unknown',    'event' => 'warning');

$config['status_states']['netscaler-ha-state'][0]  = array('name' => 'unknown',         'event' => 'alert');
$config['status_states']['netscaler-ha-state'][1]  = array('name' => 'init',            'event' => 'warning');
$config['status_states']['netscaler-ha-state'][2]  = array('name' => 'down',            'event' => 'alert');
$config['status_states']['netscaler-ha-state'][3]  = array('name' => 'up',              'event' => 'ok');
$config['status_states']['netscaler-ha-state'][4]  = array('name' => 'partialFail',     'event' => 'alert');
$config['status_states']['netscaler-ha-state'][5]  = array('name' => 'monitorFail',     'event' => 'alert');
$config['status_states']['netscaler-ha-state'][6]  = array('name' => 'monitorOk',       'event' => 'ok');
$config['status_states']['netscaler-ha-state'][7]  = array('name' => 'completeFail',    'event' => 'alert');
$config['status_states']['netscaler-ha-state'][8]  = array('name' => 'dumb',            'event' => 'warning');
$config['status_states']['netscaler-ha-state'][9]  = array('name' => 'disabled',        'event' => 'warning');
$config['status_states']['netscaler-ha-state'][10] = array('name' => 'partialFailSsl',  'event' => 'alert');
$config['status_states']['netscaler-ha-state'][11] = array('name' => 'routemonitorFail', 'event' => 'alert');

// F5-BIGIP-SYSTEM-MIB
$config['status_states']['f5-bigip-state'][0]    = array('name' => 'bad',        'event' => 'alert');
$config['status_states']['f5-bigip-state'][1]    = array('name' => 'good',       'event' => 'ok');
$config['status_states']['f5-bigip-state'][2]    = array('name' => 'notpresent', 'event' => 'ignore');

// CHECKPOINT-MIB
$config['status_states']['checkpoint-ha-state'][0] = array('name' => 'OK',        'event' => 'ok');
$config['status_states']['checkpoint-ha-state'][1] = array('name' => 'WARNING',   'event' => 'warning');
$config['status_states']['checkpoint-ha-state'][2] = array('name' => 'CRITICAL',  'event' => 'alert');
$config['status_states']['checkpoint-ha-state'][3] = array('name' => 'UNKNOWN',   'event' => 'warning');

// HP-ICF-CHASSIS
$config['status_states']['hp-icf-chassis-state'][1] = array('name' => 'unknown',    'event' => 'warning');
$config['status_states']['hp-icf-chassis-state'][2] = array('name' => 'bad',        'event' => 'alert');
$config['status_states']['hp-icf-chassis-state'][3] = array('name' => 'warning',    'event' => 'warning');
$config['status_states']['hp-icf-chassis-state'][4] = array('name' => 'good',       'event' => 'ok');
$config['status_states']['hp-icf-chassis-state'][5] = array('name' => 'notPresent', 'event' => 'ignore');

// SW-MIB
$config['status_states']['sw-mib'][1] = array('name' => 'normal',             'event' => 'ok');

// TIMETRA-CHASSIS-MIB
$config['status_states']['timetra-chassis-state'][1] = array('name' => 'deviceStateUnknown',      'event' => 'ignore');
$config['status_states']['timetra-chassis-state'][2] = array('name' => 'deviceNotEquipped',       'event' => 'ignore');
$config['status_states']['timetra-chassis-state'][3] = array('name' => 'deviceStateOk',           'event' => 'ok');
$config['status_states']['timetra-chassis-state'][4] = array('name' => 'deviceStateFailed',       'event' => 'alert');
$config['status_states']['timetra-chassis-state'][5] = array('name' => 'deviceStateOutOfService', 'event' => 'ignore');

// UNIX-AGENT
$config['status_states']['unix-agent-state'][0] = array('name' => 'fail',     'event' => 'alert');
$config['status_states']['unix-agent-state'][1] = array('name' => 'ok',       'event' => 'ok');
$config['status_states']['unix-agent-state'][2] = array('name' => 'warn',     'event' => 'warning');

// MSERIES-PORT-MIB
$config['status_states']['mseries-port-status-state'][1] = array('name' => 'idle',      'event' => 'ignore');
$config['status_states']['mseries-port-status-state'][2] = array('name' => 'down',      'event' => 'alert');
$config['status_states']['mseries-port-status-state'][3] = array('name' => 'up',        'event' => 'ok');
$config['status_states']['mseries-port-status-state'][4] = array('name' => 'high',      'event' => 'warning');
$config['status_states']['mseries-port-status-state'][5] = array('name' => 'low',       'event' => 'warning');
$config['status_states']['mseries-port-status-state'][6] = array('name' => 'eyeSafety', 'event' => 'alert');
$config['status_states']['mseries-port-status-state'][7] = array('name' => 'cd',        'event' => 'alert');
$config['status_states']['mseries-port-status-state'][8] = array('name' => 'ncd',       'event' => 'alert');

// FOUNDRY-SN-AGENT-MIB
$config['status_states']['foundry-sn-agent-oper-state'][1] = array('name' => 'other',   'event' => 'ignore');
$config['status_states']['foundry-sn-agent-oper-state'][2] = array('name' => 'normal',  'event' => 'ok');
$config['status_states']['foundry-sn-agent-oper-state'][3] = array('name' => 'failure', 'event' => 'alert');

// STEELHEAD-MIB
$config['status_states']['steelhead-system-state'][10000] = array('name' => 'healthy',          'event' => 'ok');
$config['status_states']['steelhead-system-state'][30000] = array('name' => 'degraded',         'event' => 'alert');
$config['status_states']['steelhead-system-state'][31000] = array('name' => 'admissionControl', 'event' => 'alert');
$config['status_states']['steelhead-system-state'][50000] = array('name' => 'critical',         'event' => 'alert');

$config['status_states']['steelhead-service-state'][0] = array('name' => 'none',      'event' => 'ignore');
$config['status_states']['steelhead-service-state'][1] = array('name' => 'unmanaged', 'event' => 'alert');
$config['status_states']['steelhead-service-state'][2] = array('name' => 'running',   'event' => 'ok');
$config['status_states']['steelhead-service-state'][3] = array('name' => 'sentCom1',  'event' => 'ignore');
$config['status_states']['steelhead-service-state'][4] = array('name' => 'sentTerm1', 'event' => 'ignore');
$config['status_states']['steelhead-service-state'][5] = array('name' => 'sentTerm2', 'event' => 'ignore');
$config['status_states']['steelhead-service-state'][6] = array('name' => 'sentTerm3', 'event' => 'ignore');
$config['status_states']['steelhead-service-state'][7] = array('name' => 'pending',   'event' => 'ignore');
$config['status_states']['steelhead-service-state'][8] = array('name' => 'stopped',   'event' => 'alert');

// F10-CHASSIS-MIB
$config['status_states']['f10-chassis-state'][1] = array('name' => 'normal',     'event' => 'ok');
$config['status_states']['f10-chassis-state'][2] = array('name' => 'down',       'event' => 'alert');

// ARECA-SNMP-MIB
$config['status_states']['areca-power-state'][0] = array('name' => 'Failed', 'event' => 'alert');
$config['status_states']['areca-power-state'][1] = array('name' => 'Ok',     'event' => 'ok');

// End sensor states

// FIXME. $config['sensor_types'] >> $config['sensor']['types']

$config['entities']['sensor']['id_field']             = "sensor_id";
$config['entities']['sensor']['name_field']           = "sensor_descr";
$config['entities']['sensor']['table']                = "sensors";
$config['entities']['sensor']['ignore_field']         = "sensor_ignore";
$config['entities']['sensor']['disable_field']        = "sensor_disable";
$config['entities']['sensor']['icon']                 = "oicon-dashboard";
$config['entities']['sensor']['graph']                = array('type' => 'sensor_graph', 'id' => '@sensor_id');

$config['entities']['status']['id_field']             = "status_id";
$config['entities']['status']['name_field']           = "status_descr";
$config['entities']['status']['table']                = "status";
$config['entities']['status']['ignore_field']         = "status_ignore";
$config['entities']['status']['disable_field']        = "status_disable";
$config['entities']['status']['icon']                 = "oicon-traffic-light";
$config['entities']['status']['graph']                = array('type' => 'status_graph', 'id' => '@status_id');

// The order these are entered here defines the order they are shown in the web interface
$config['sensor_types']['temperature'] = array( 'symbol' => 'C',   'text' => 'Celsius',   'icon' => 'oicon-thermometer-high');
$config['sensor_types']['humidity']    = array( 'symbol' => '%',   'text' => 'Percent',   'icon' => 'oicon-water');
$config['sensor_types']['fanspeed']    = array( 'symbol' => 'RPM', 'text' => 'RPM',       'icon' => 'oicon-weather-wind');
$config['sensor_types']['airflow']     = array( 'symbol' => 'CFM', 'text' => 'Airflow',   'icon' => 'oicon-weather-wind');
$config['sensor_types']['voltage']     = array( 'symbol' => 'V',   'text' => 'Volts',     'icon' => 'oicon-voltage');
$config['sensor_types']['current']     = array( 'symbol' => 'A',   'text' => 'Amperes',   'icon' => 'oicon-current');
$config['sensor_types']['power']       = array( 'symbol' => 'W',   'text' => 'Watts',     'icon' => 'oicon-power');
$config['sensor_types']['apower']      = array( 'symbol' => 'VA',  'text' => 'VoltAmpere','icon' => 'oicon-power');
$config['sensor_types']['impedance']   = array( 'symbol' => 'Ohm', 'text' => 'Impedance', 'icon' => 'oicon-omega');
$config['sensor_types']['frequency']   = array( 'symbol' => 'Hz',  'text' => 'Hertz',     'icon' => 'oicon-frequency');
$config['sensor_types']['dbm']         = array( 'symbol' => 'dBm', 'text' => 'dBm',       'icon' => 'oicon-arrow-incident-red');
$config['sensor_types']['snr']         = array( 'symbol' => 'dB',  'text' => 'dB',        'icon' => 'oicon-transmitter');
$config['sensor_types']['capacity']    = array( 'symbol' => '%',   'text' => 'Percent',   'icon' => 'oicon-ui-progress-bar');
$config['sensor_types']['load']        = array( 'symbol' => '%',   'text' => 'Percent',   'icon' => 'oicon-asterisk');
$config['sensor_types']['runtime']     = array( 'symbol' => 'min', 'text' => 'Minutes',   'icon' => 'oicon-time');
$config['sensor_types']['state']       = array( 'symbol' => '',    'text' => '',          'icon' => 'oicon-exclamation-white');

/*
// FIXME disabled - not working.

foreach ($config['sensor_types'] as $type => $array)
{
  $config['entities'][$type] = array_merge($config['entities']['sensor'], $array);
  $config['entities'][$type]['where']             = "`sensor_class` = '".$type."' ";
  $config['entities'][$type]['humanize_function'] = "humanize_sensor";
  $config['entities'][$type]['parent_type']       = "sensor";
}
*/

// Cache this OIDs when polling
$config['sensor']['cache_oids']['netscaler-health']      = array('.1.3.6.1.4.1.5951.4.1.1.41.7.1.2');
$config['sensor']['cache_oids']['cisco-entity-sensor']   = array('.1.3.6.1.4.1.9.9.91.1.1.1.1.4');
$config['sensor']['cache_oids']['cisco-envmon']          = array('.1.3.6.1.4.1.9.9.13.1');
$config['sensor']['cache_oids']['cisco-envmon-state']    = array('.1.3.6.1.4.1.9.9.13.1');
$config['sensor']['cache_oids']['entity-sensor']         = array('.1.3.6.1.2.1.99.1.1.1.4');
$config['sensor']['cache_oids']['equallogic']            = array('.1.3.6.1.4.1.12740.2.1.6.1.3.1', '.1.3.6.1.4.1.12740.2.1.7.1.3.1');

// IPMI sensor type mappings
$config['ipmi_unit']['Volts']     = 'voltage';
$config['ipmi_unit']['degrees C'] = 'temperature';
$config['ipmi_unit']['RPM']       = 'fanspeed';
$config['ipmi_unit']['Watts']     = 'power';
$config['ipmi_unit']['CFM']       = 'airflow';
$config['ipmi_unit']['discrete']  = '';

// End includes/definitions/sensors.inc.php
