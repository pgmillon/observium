<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage definitions
 * @copyright  (C) 2006-2014 Adam Armstrong
 *
 */

/// FIXME. $config['sensor_states'] >> $config['sensor']['states']

// Sensor state names

// CISCO-ENVMON-MIB
// See: http://tools.cisco.com/Support/SNMP/do/BrowseOID.do?local=en&translate=Translate&typeName=CiscoEnvMonState
$config['sensor_states']['cisco-envmon-state'][1] = array('name' => 'normal',         'event' => 'up');
$config['sensor_states']['cisco-envmon-state'][2] = array('name' => 'warning',        'event' => 'warning');
$config['sensor_states']['cisco-envmon-state'][3] = array('name' => 'critical',       'event' => 'alert');
$config['sensor_states']['cisco-envmon-state'][4] = array('name' => 'shutdown',       'event' => 'down');
$config['sensor_states']['cisco-envmon-state'][5] = array('name' => 'notPresent',     'event' => 'ignore');
$config['sensor_states']['cisco-envmon-state'][6] = array('name' => 'notFunctioning', 'event' => 'ignore');

// CISCO-ENTITY-SENSOR-MIB
$config['sensor_states']['cisco-entity-state'][1] = array('name' => 'true',         'event' => 'up');
$config['sensor_states']['cisco-entity-state'][2] = array('name' => 'false',        'event' => 'alert');

// FASTPATH-BOXSERVICES-PRIVATE-MIB
// Note: this is for the official Broadcom FastPath Box Services MIB. The idiots at Netgear modified this MIB, swapping
// status values around for no reason at all. That won't work. The tree is under a different OID, but if someone ever wants
// to implement support for their MIB, don't use this same 'fastpath-boxservices-private-state' as it will be incorrect.
$config['sensor_states']['fastpath-boxservices-private-state'][1] = array('name' => 'notPresent',  'event' => 'ignore');
$config['sensor_states']['fastpath-boxservices-private-state'][2] = array('name' => 'operational', 'event' => 'up');
$config['sensor_states']['fastpath-boxservices-private-state'][3] = array('name' => 'failed',      'event' => 'alert');

$config['sensor_states']['fastpath-boxservices-private-temp-state'][1] = array('name' => 'normal',         'event' => 'up');
$config['sensor_states']['fastpath-boxservices-private-temp-state'][2] = array('name' => 'warning',        'event' => 'warn');
$config['sensor_states']['fastpath-boxservices-private-temp-state'][3] = array('name' => 'critical',       'event' => 'alert');
$config['sensor_states']['fastpath-boxservices-private-temp-state'][4] = array('name' => 'shutdown',       'event' => 'warn');
$config['sensor_states']['fastpath-boxservices-private-temp-state'][5] = array('name' => 'notPresent',     'event' => 'ignore');
$config['sensor_states']['fastpath-boxservices-private-temp-state'][6] = array('name' => 'notOperational', 'event' => 'ignore');

// RADLAN-HWENVIRONMENT
$config['sensor_states']['radlan-hwenvironment-state'][1] = array('name' => 'normal',         'event' => 'up');
$config['sensor_states']['radlan-hwenvironment-state'][2] = array('name' => 'warning',        'event' => 'alert');
$config['sensor_states']['radlan-hwenvironment-state'][3] = array('name' => 'critical',       'event' => 'alert');
$config['sensor_states']['radlan-hwenvironment-state'][4] = array('name' => 'shutdown',       'event' => 'alert');
$config['sensor_states']['radlan-hwenvironment-state'][5] = array('name' => 'notPresent',     'event' => 'ignore');
$config['sensor_states']['radlan-hwenvironment-state'][6] = array('name' => 'notFunctioning', 'event' => 'alert');

// AC-SYSTEM-MIB
$config['sensor_states']['ac-system-fan-state'][0] = array('name' => 'cleared',       'event' => 'up');
$config['sensor_states']['ac-system-fan-state'][1] = array('name' => 'indeterminate', 'event' => 'ignore');
$config['sensor_states']['ac-system-fan-state'][2] = array('name' => 'warning',       'event' => 'warning');
$config['sensor_states']['ac-system-fan-state'][3] = array('name' => 'minor',         'event' => 'up');
$config['sensor_states']['ac-system-fan-state'][4] = array('name' => 'major',         'event' => 'warning');
$config['sensor_states']['ac-system-fan-state'][5] = array('name' => 'critical',      'event' => 'alert');
$config['sensor_states']['ac-system-power-state'][1] = array('name' => 'cleared',       'event' => 'up');
$config['sensor_states']['ac-system-power-state'][2] = array('name' => 'indeterminate', 'event' => 'ignore');
$config['sensor_states']['ac-system-power-state'][3] = array('name' => 'warning',       'event' => 'warning');
$config['sensor_states']['ac-system-power-state'][4] = array('name' => 'minor',         'event' => 'up');
$config['sensor_states']['ac-system-power-state'][5] = array('name' => 'major',         'event' => 'warning');
$config['sensor_states']['ac-system-power-state'][6] = array('name' => 'critical',      'event' => 'alert');

// ACME-ENVMON-MIB
$config['sensor_states']['acme-env-state'][2] = array('name' => 'normal',         'event' => 'up');
$config['sensor_states']['acme-env-state'][3] = array('name' => 'minor',          'event' => 'alert');
$config['sensor_states']['acme-env-state'][4] = array('name' => 'major',          'event' => 'alert');
$config['sensor_states']['acme-env-state'][5] = array('name' => 'critical',       'event' => 'alert');
$config['sensor_states']['acme-env-state'][5] = array('name' => 'shutdown',       'event' => 'down');
$config['sensor_states']['acme-env-state'][7] = array('name' => 'notPresent',     'event' => 'ignore');
$config['sensor_states']['acme-env-state'][8] = array('name' => 'notFunctioning', 'event' => 'alert');

// DELL-Vendor-MIB
$config['sensor_states']['dell-vendor-state'][1] = array('name' => 'normal',         'event' => 'up');
$config['sensor_states']['dell-vendor-state'][2] = array('name' => 'warning',        'event' => 'alert');
$config['sensor_states']['dell-vendor-state'][3] = array('name' => 'critical',       'event' => 'alert');
$config['sensor_states']['dell-vendor-state'][4] = array('name' => 'shutdown',       'event' => 'alert');
$config['sensor_states']['dell-vendor-state'][5] = array('name' => 'notPresent',     'event' => 'ignore');
$config['sensor_states']['dell-vendor-state'][6] = array('name' => 'notFunctioning', 'event' => 'alert');

// DNOS-BOXSERVICES-PRIVATE-MIB
$config['sensor_states']['dnos-boxservices-state'][1] = array('name' => 'notpresent',     'event' => 'ignore');
$config['sensor_states']['dnos-boxservices-state'][2] = array('name' => 'operational',    'event' => 'up');
$config['sensor_states']['dnos-boxservices-state'][3] = array('name' => 'failed',         'event' => 'alert');
$config['sensor_states']['dnos-boxservices-state'][4] = array('name' => 'powering',       'event' => 'ignore');
$config['sensor_states']['dnos-boxservices-state'][5] = array('name' => 'nopower',        'event' => 'alert');
$config['sensor_states']['dnos-boxservices-state'][6] = array('name' => 'notpowering',    'event' => 'alert');
$config['sensor_states']['dnos-boxservices-state'][7] = array('name' => 'incompatible',   'event' => 'alert');
$config['sensor_states']['dnos-boxservices-temp-state'][0] = array('name' => 'low',             'event' => 'warning');
$config['sensor_states']['dnos-boxservices-temp-state'][1] = array('name' => 'normal',          'event' => 'up');
$config['sensor_states']['dnos-boxservices-temp-state'][2] = array('name' => 'warning',         'event' => 'warning');
$config['sensor_states']['dnos-boxservices-temp-state'][3] = array('name' => 'critical',        'event' => 'alert');
$config['sensor_states']['dnos-boxservices-temp-state'][4] = array('name' => 'shutdown',        'event' => 'alert');
$config['sensor_states']['dnos-boxservices-temp-state'][5] = array('name' => 'notpresent',      'event' => 'ignore');
$config['sensor_states']['dnos-boxservices-temp-state'][6] = array('name' => 'notoperational',  'event' => 'alert');

// SPAGENT-MIB
$config['sensor_states']['spagent-state'][1] = array('name' => 'noStatus',     'event' => 'ignore');
$config['sensor_states']['spagent-state'][2] = array('name' => 'normal',       'event' => 'up');
$config['sensor_states']['spagent-state'][4] = array('name' => 'highCritical', 'event' => 'alert');
$config['sensor_states']['spagent-state'][6] = array('name' => 'lowCritical',  'event' => 'warning');
$config['sensor_states']['spagent-state'][7] = array('name' => 'sensorError',  'event' => 'alert');
$config['sensor_states']['spagent-state'][8] = array('name' => 'relayOn',      'event' => 'up');
$config['sensor_states']['spagent-state'][9] = array('name' => 'relayOff',     'event' => 'up');

// OADWDM-MIB
$config['sensor_states']['oadwdm-fan-state'][1] = array('name' => 'none',       'event' => 'ignore');
$config['sensor_states']['oadwdm-fan-state'][2] = array('name' => 'active',     'event' => 'up');
$config['sensor_states']['oadwdm-fan-state'][3] = array('name' => 'notActive',  'event' => 'warning');
$config['sensor_states']['oadwdm-fan-state'][4] = array('name' => 'fail',       'event' => 'down');

$config['sensor_states']['oadwdm-powersupply-state'][1] = array('name' => 'none',       'event' => 'ignore');
$config['sensor_states']['oadwdm-powersupply-state'][2] = array('name' => 'active',     'event' => 'up');
$config['sensor_states']['oadwdm-powersupply-state'][3] = array('name' => 'notActive',  'event' => 'warning');
$config['sensor_states']['oadwdm-powersupply-state'][4] = array('name' => 'fail',       'event' => 'down');

// PowerNet-MIB
$config['sensor_states']['powernet-status-state'][1] = array('name' => 'fail',     'event' => 'alert');
$config['sensor_states']['powernet-status-state'][2] = array('name' => 'ok',       'event' => 'up');

$config['sensor_states']['powernet-sync-state'][1] = array('name' => 'inSync',     'event' => 'up');
$config['sensor_states']['powernet-sync-state'][2] = array('name' => 'outOfSync',  'event' => 'alert');

$config['sensor_states']['powernet-mupscontact-state'][1] = array('name' => 'unknown', 'event' => 'warning');
$config['sensor_states']['powernet-mupscontact-state'][2] = array('name' => 'noFault', 'event' => 'up');
$config['sensor_states']['powernet-mupscontact-state'][3] = array('name' => 'fault',   'event' => 'alert');

$config['sensor_states']['powernet-rpdusupply1-state'][1] = array('name' => 'powerSupplyOneOk',         'event' => 'up');
$config['sensor_states']['powernet-rpdusupply1-state'][2] = array('name' => 'powerSupplyOneFailed',     'event' => 'alert');

$config['sensor_states']['powernet-rpdusupply2-state'][1] = array('name' => 'powerSupplyTwoOk',         'event' => 'up');
$config['sensor_states']['powernet-rpdusupply2-state'][2] = array('name' => 'powerSupplyTwoFailed',     'event' => 'alert');
$config['sensor_states']['powernet-rpdusupply2-state'][3] = array('name' => 'powerSupplyTwoNotPresent', 'event' => 'ignore');

$config['sensor_states']['powernet-rpdu2supply-state'][1] = array('name' => 'normal',       'event' => 'up');
$config['sensor_states']['powernet-rpdu2supply-state'][2] = array('name' => 'alarm',        'event' => 'alert');
$config['sensor_states']['powernet-rpdu2supply-state'][3] = array('name' => 'notInstalled', 'event' => 'ignore');

$config['sensor_states']['powernet-upsbasicoutput-state'][1]  = array('name' => 'unknown',                  'event' => 'warning');
$config['sensor_states']['powernet-upsbasicoutput-state'][2]  = array('name' => 'onLine',                   'event' => 'up');
$config['sensor_states']['powernet-upsbasicoutput-state'][3]  = array('name' => 'onBattery',                'event' => 'alert');
$config['sensor_states']['powernet-upsbasicoutput-state'][4]  = array('name' => 'onSmartBoost',             'event' => 'warning');
$config['sensor_states']['powernet-upsbasicoutput-state'][5]  = array('name' => 'timedSleeping',            'event' => 'warning');
$config['sensor_states']['powernet-upsbasicoutput-state'][6]  = array('name' => 'softwareBypass',           'event' => 'alert');
$config['sensor_states']['powernet-upsbasicoutput-state'][7]  = array('name' => 'off',                      'event' => 'alert');
$config['sensor_states']['powernet-upsbasicoutput-state'][8]  = array('name' => 'rebooting',   '             event' => 'warning');
$config['sensor_states']['powernet-upsbasicoutput-state'][9]  = array('name' => 'switchedBypass',           'event' => 'warning');
$config['sensor_states']['powernet-upsbasicoutput-state'][10] = array('name' => 'hardwareFailureBypass',    'event' => 'alert');
$config['sensor_states']['powernet-upsbasicoutput-state'][11] = array('name' => 'sleepingUntilPowerReturn', 'event' => 'warning');
$config['sensor_states']['powernet-upsbasicoutput-state'][12] = array('name' => 'onSmartTrim',              'event' => 'warning');
$config['sensor_states']['powernet-upsbasicoutput-state'][13] = array('name' => 'ecoMode',                  'event' => 'up');
$config['sensor_states']['powernet-upsbasicoutput-state'][14] = array('name' => 'hotStandby',               'event' => 'up');
$config['sensor_states']['powernet-upsbasicoutput-state'][15] = array('name' => 'onBatteryTest',            'event' => 'warning');
$config['sensor_states']['powernet-upsbasicoutput-state'][16] = array('name' => 'emergencyStaticBypass',    'event' => 'alert');
$config['sensor_states']['powernet-upsbasicoutput-state'][17] = array('name' => 'staticBypassStandby',      'event' => 'warning');
$config['sensor_states']['powernet-upsbasicoutput-state'][18] = array('name' => 'powerSavingMode',          'event' => 'up');
$config['sensor_states']['powernet-upsbasicoutput-state'][19] = array('name' => 'spotMode',                 'event' => 'warning');
$config['sensor_states']['powernet-upsbasicoutput-state'][20] = array('name' => 'eConversion',              'event' => 'up');

// TRAPEZE-NETWORKS-SYSTEM-MIB
$config['sensor_states']['trapeze-state'][1] = array('name' => 'other',         'event' => 'warning');
$config['sensor_states']['trapeze-state'][2] = array('name' => 'unknown',       'event' => 'warning');
$config['sensor_states']['trapeze-state'][3] = array('name' => 'ac-failed',     'event' => 'alert');
$config['sensor_states']['trapeze-state'][4] = array('name' => 'dc-failed',     'event' => 'alert');
$config['sensor_states']['trapeze-state'][5] = array('name' => 'ac-ok-dc-ok',   'event' => 'up');

// GEIST-MIB-V3
$config['sensor_states']['geist-mib-v3-door-state'][1]        = array('name' => 'closed', 'event' => 'up');
$config['sensor_states']['geist-mib-v3-door-state'][99]       = array('name' => 'open',   'event' => 'alert');
$config['sensor_states']['geist-mib-v3-digital-state'][1]     = array('name' => 'off',    'event' => 'alert');
$config['sensor_states']['geist-mib-v3-digital-state'][99]    = array('name' => 'on',     'event' => 'up');
$config['sensor_states']['geist-mib-v3-smokealarm-state'][1]  = array('name' => 'clear',  'event' => 'up');
$config['sensor_states']['geist-mib-v3-smokealarm-state'][99] = array('name' => 'smoky',  'event' => 'alert');
$config['sensor_states']['geist-mib-v3-climateio-state'][0]   = array('name' => '0V',     'event' => 'up');
$config['sensor_states']['geist-mib-v3-climateio-state'][99]  = array('name' => '5V',     'event' => 'up');
$config['sensor_states']['geist-mib-v3-climateio-state'][100] = array('name' => '5V',     'event' => 'up');
$config['sensor_states']['geist-mib-v3-relay-state'][0]       = array('name' => 'off',    'event' => 'up');
$config['sensor_states']['geist-mib-v3-relay-state'][1]       = array('name' => 'on',     'event' => 'up');

// GEIST-V4-MIB
$config['sensor_states']['geist-v4-mib-io-state'][0]   = array('name' => '0V', 'event' => 'up');
$config['sensor_states']['geist-v4-mib-io-state'][100] = array('name' => '5V', 'event' => 'up');

// CPQHLTH-MIB
$config['sensor_states']['cpqhlth-state'][1] = array('name' => 'other',                       'event' => 'ignore');
$config['sensor_states']['cpqhlth-state'][2] = array('name' => 'ok',                          'event' => 'up');
$config['sensor_states']['cpqhlth-state'][3] = array('name' => 'degraded',                    'event' => 'warning');
$config['sensor_states']['cpqhlth-state'][4] = array('name' => 'failed',                      'event' => 'alert');

// CPQIDA-MIB
$config['sensor_states']['cpqida-cntrl-state'][1] = array('name' => 'other',                  'event' => 'ignore');
$config['sensor_states']['cpqida-cntrl-state'][2] = array('name' => 'ok',                     'event' => 'up');
$config['sensor_states']['cpqida-cntrl-state'][3] = array('name' => 'generalFailure',         'event' => 'alert');
$config['sensor_states']['cpqida-cntrl-state'][4] = array('name' => 'cableProblem',           'event' => 'alert');
$config['sensor_states']['cpqida-cntrl-state'][5] = array('name' => 'poweredOff',             'event' => 'alert');

$config['sensor_states']['cpqida-smart-state'][1] = array('name' => 'other',                  'event' => 'ignore');
$config['sensor_states']['cpqida-smart-state'][2] = array('name' => 'ok',                     'event' => 'up');
$config['sensor_states']['cpqida-smart-state'][3] = array('name' => 'replaceDrive',           'event' => 'alert');
$config['sensor_states']['cpqida-smart-state'][4] = array('name' => 'replaceDriveSSDWearOut', 'event' => 'warning');

// SYNOLOGY-SYSTEM-MIB
$config['sensor_states']['synology-status-state'][1] = array('name' => 'Normal',              'event' => 'up');
$config['sensor_states']['synology-status-state'][2] = array('name' => 'Failed',              'event' => 'alert');

// SYNOLOGY-DISK-MIB
$config['sensor_states']['synology-disk-state'][1] = array('name' => 'Normal',                'event' => 'up');
$config['sensor_states']['synology-disk-state'][2] = array('name' => 'Initialized',           'event' => 'warning');
$config['sensor_states']['synology-disk-state'][3] = array('name' => 'NotInitialized',        'event' => 'warning');
$config['sensor_states']['synology-disk-state'][4] = array('name' => 'SystemPartitionFailed', 'event' => 'alert');
$config['sensor_states']['synology-disk-state'][5] = array('name' => 'Crashed',               'event' => 'alert');

// EQLDISK-MIB
$config['sensor_states']['eql-disk-state'][1] = array('name' => 'on-line',                    'event' => 'up');
$config['sensor_states']['eql-disk-state'][2] = array('name' => 'spare',                      'event' => 'up');
$config['sensor_states']['eql-disk-state'][3] = array('name' => 'failed',                     'event' => 'alert');
$config['sensor_states']['eql-disk-state'][4] = array('name' => 'off-line',                   'event' => 'alert');
$config['sensor_states']['eql-disk-state'][5] = array('name' => 'alt-sig',                    'event' => 'ignore');
$config['sensor_states']['eql-disk-state'][6] = array('name' => 'too-small',                  'event' => 'ignore');
$config['sensor_states']['eql-disk-state'][7] = array('name' => 'history-of-failures',        'event' => 'ignore');
$config['sensor_states']['eql-disk-state'][8] = array('name' => 'unsupported-version',        'event' => 'ignore');
$config['sensor_states']['eql-disk-state'][9] = array('name' => 'unhealthy',                  'event' => 'warning');
$config['sensor_states']['eql-disk-state'][10] = array('name' => 'replacement',               'event' => 'ignore');
$config['sensor_states']['eql-disk-state'][11] = array('name' => 'encrypted',                 'event' => 'ignore');
$config['sensor_states']['eql-disk-state'][12] = array('name' => 'notApproved',               'event' => 'ignore');
$config['sensor_states']['eql-disk-state'][13] = array('name' => 'preempt-failed',            'event' => 'ignore');

// ExaltComProducts
$config['sensor_states']['exaltcomproducts-state'][0] = array('name' => 'almNORMAL',          'event' => 'up');
$config['sensor_states']['exaltcomproducts-state'][1] = array('name' => 'almMINOR',           'event' => 'warning');
$config['sensor_states']['exaltcomproducts-state'][2] = array('name' => 'almMAJOR',           'event' => 'alert');

// MG-SNMP-UPS-MIB
$config['sensor_states']['mge-status-state'][1] = array('name' => 'Yes',                      'event' => 'alert');
$config['sensor_states']['mge-status-state'][2] = array('name' => 'No',                       'event' => 'up');

// SUPERMICRO-HEALTH-MIB
$config['sensor_states']['supermicro-state'][0] = array('name' => 'Good',                     'event' => 'up');
$config['sensor_states']['supermicro-state'][1] = array('name' => 'Bad',                      'event' => 'alert');

// LSI-MegaRAID-SAS-MIB
$config['sensor_states']['lsi-megaraid-sas-pd-state'][0]     = array('name' => 'unconfigured-good',     'event' => 'warning');
$config['sensor_states']['lsi-megaraid-sas-pd-state'][1]     = array('name' => 'unconfigured-bad',      'event' => 'alert');
$config['sensor_states']['lsi-megaraid-sas-pd-state'][2]     = array('name' => 'hot-spare',             'event' => 'up');
$config['sensor_states']['lsi-megaraid-sas-pd-state'][16]    = array('name' => 'offline',               'event' => 'alert');
$config['sensor_states']['lsi-megaraid-sas-pd-state'][17]    = array('name' => 'failed',                'event' => 'alert');
$config['sensor_states']['lsi-megaraid-sas-pd-state'][20]    = array('name' => 'rebuild',               'event' => 'warning');
$config['sensor_states']['lsi-megaraid-sas-pd-state'][24]    = array('name' => 'online',                'event' => 'up');
$config['sensor_states']['lsi-megaraid-sas-pd-state'][32]    = array('name' => 'copyback',              'event' => 'alert');
$config['sensor_states']['lsi-megaraid-sas-pd-state'][64]    = array('name' => 'system',                'event' => 'up');
$config['sensor_states']['lsi-megaraid-sas-pd-state'][128]   = array('name' => 'unconfigured-shielded', 'event' => 'warning');
$config['sensor_states']['lsi-megaraid-sas-pd-state'][130]   = array('name' => 'hotspare-shielded',     'event' => 'up');
$config['sensor_states']['lsi-megaraid-sas-pd-state'][144]   = array('name' => 'configured-shielded',   'event' => 'up');

$config['sensor_states']['lsi-megaraid-sas-sensor-state'][1] = array('name' => 'invalid',               'event' => 'alert');
$config['sensor_states']['lsi-megaraid-sas-sensor-state'][2] = array('name' => 'ok',                    'event' => 'up');
$config['sensor_states']['lsi-megaraid-sas-sensor-state'][3] = array('name' => 'critical',              'event' => 'alert');
$config['sensor_states']['lsi-megaraid-sas-sensor-state'][4] = array('name' => 'nonCritical',           'event' => 'warning');
$config['sensor_states']['lsi-megaraid-sas-sensor-state'][5] = array('name' => 'unrecoverable',         'event' => 'alert');
$config['sensor_states']['lsi-megaraid-sas-sensor-state'][6] = array('name' => 'not-installed',         'event' => 'up');
$config['sensor_states']['lsi-megaraid-sas-sensor-state'][7] = array('name' => 'unknown',               'event' => 'warning');
$config['sensor_states']['lsi-megaraid-sas-sensor-state'][8] = array('name' => 'not-available',         'event' => 'alert');

// JUNIPER-ALARM-MIB
$config['sensor_states']['juniper-alarm-state'][1] = array('name' => 'other', 'event' => 'warning');
$config['sensor_states']['juniper-alarm-state'][2] = array('name' => 'off',   'event' => 'up');
$config['sensor_states']['juniper-alarm-state'][3] = array('name' => 'on',    'event' => 'alert');

// NOKIA-IPSO-SYSTEM-MIB
$config['sensor_states']['ipso-temperature-state'][1] = array('name' => 'normal',          'event' => 'up');
$config['sensor_states']['ipso-temperature-state'][2] = array('name' => 'overTemperature', 'event' => 'alert');

$config['sensor_states']['ipso-sensor-state'][1] = array('name' => 'running',    'event' => 'up');
$config['sensor_states']['ipso-sensor-state'][2] = array('name' => 'notRunning', 'event' => 'alert');

// NS-ROOT-MIB
$config['sensor_states']['netscaler-state'][0]    = array('name' => 'normal',     'event' => 'up');
$config['sensor_states']['netscaler-state'][1]    = array('name' => 'failed',     'event' => 'alert');

$config['sensor_states']['netscaler-ha-state'][0] = array('name' => 'standalone', 'event' => 'up');
$config['sensor_states']['netscaler-ha-state'][1] = array('name' => 'primary',    'event' => 'up');
$config['sensor_states']['netscaler-ha-state'][2] = array('name' => 'secondary',  'event' => 'up');
$config['sensor_states']['netscaler-ha-state'][3] = array('name' => 'unknown',    'event' => 'warning');

// CHECKPOINT-MIB
$config['sensor_states']['checkpoint-ha-state'][0] = array('name' => 'OK',        'event' => 'up');
$config['sensor_states']['checkpoint-ha-state'][1] = array('name' => 'WARNING',   'event' => 'warning');
$config['sensor_states']['checkpoint-ha-state'][2] = array('name' => 'CRITICAL',  'event' => 'alert');
$config['sensor_states']['checkpoint-ha-state'][3] = array('name' => 'UNKNOWN',   'event' => 'warning');

// HP-ICF-CHASSIS
$config['sensor_states']['hp-icf-chassis-state'][1] = array('name' => 'unknown',    'event' => 'warning');
$config['sensor_states']['hp-icf-chassis-state'][2] = array('name' => 'bad',        'event' => 'alert');
$config['sensor_states']['hp-icf-chassis-state'][3] = array('name' => 'warning',    'event' => 'warning');
$config['sensor_states']['hp-icf-chassis-state'][4] = array('name' => 'good',       'event' => 'up');
$config['sensor_states']['hp-icf-chassis-state'][5] = array('name' => 'notPresent', 'event' => 'ignore');

// SW-MIB
$config['sensor_states']['sw-mib'][1] = array('name' => 'normal',             'event' => 'up');

// UNIX-AGENT
$config['sensor_states']['unix-agent-state'][0] = array('name' => 'fail',     'event' => 'alert');
$config['sensor_states']['unix-agent-state'][1] = array('name' => 'ok',       'event' => 'up');

// FIXME. $config['sensor_types'] >> $config['sensor']['types']

$config['entities']['sensor']['id_field']             = "sensor_id";
$config['entities']['sensor']['name_field']           = "sensor_descr";
$config['entities']['sensor']['table']                = "sensors";
$config['entities']['sensor']['ignore_field']         = "sensor_ignore";
$config['entities']['sensor']['disable_field']        = "sensor_disable";
$config['entities']['sensor']['icon']                 = "oicon-contrast";
$config['entities']['sensor']['graph']                = array('type' => 'sensor_graph', 'id' => '@sensor_id');

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

// FOUNDRY-SN-AGENT-MIB
$config['sensor_states']['foundry-sn-agent-oper-state'][1] = array('name' => 'other',   'event' => 'ignore');
$config['sensor_states']['foundry-sn-agent-oper-state'][2] = array('name' => 'normal',  'event' => 'up');
$config['sensor_states']['foundry-sn-agent-oper-state'][3] = array('name' => 'failure', 'event' => 'alert');

// IPMI sensor type mappings
$config['ipmi_unit']['Volts']     = 'voltage';
$config['ipmi_unit']['degrees C'] = 'temperature';
$config['ipmi_unit']['RPM']       = 'fanspeed';
$config['ipmi_unit']['Watts']     = 'power';
$config['ipmi_unit']['CFM']       = 'airflow';
$config['ipmi_unit']['discrete']  = '';

// STEELHEAD-MIB
$config['sensor_states']['steelhead-system-state'][10000] = array('name' => 'healthy',          'event' => 'up');
$config['sensor_states']['steelhead-system-state'][30000] = array('name' => 'degraded',         'event' => 'alert');
$config['sensor_states']['steelhead-system-state'][31000] = array('name' => 'admissionControl', 'event' => 'alert');
$config['sensor_states']['steelhead-system-state'][50000] = array('name' => 'critical',         'event' => 'alert');

$config['sensor_states']['steelhead-service-state'][0] = array('name' => 'none',      'event' => 'ignore');
$config['sensor_states']['steelhead-service-state'][1] = array('name' => 'unmanaged', 'event' => 'alert');
$config['sensor_states']['steelhead-service-state'][2] = array('name' => 'running',   'event' => 'up');
$config['sensor_states']['steelhead-service-state'][3] = array('name' => 'sentCom1',  'event' => 'ignore');
$config['sensor_states']['steelhead-service-state'][4] = array('name' => 'sentTerm1', 'event' => 'ignore');
$config['sensor_states']['steelhead-service-state'][5] = array('name' => 'sentTerm2', 'event' => 'ignore');
$config['sensor_states']['steelhead-service-state'][6] = array('name' => 'sentTerm3', 'event' => 'ignore');
$config['sensor_states']['steelhead-service-state'][7] = array('name' => 'pending',   'event' => 'ignore');
$config['sensor_states']['steelhead-service-state'][8] = array('name' => 'stopped',   'event' => 'alert');

// End includes/definitions/sensors.inc.php
