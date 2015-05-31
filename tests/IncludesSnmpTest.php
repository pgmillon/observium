<?php

include(dirname(__FILE__) . '/../includes/defaults.inc.php');
include(dirname(__FILE__) . '/../config.php');
include(dirname(__FILE__) . '/../includes/definitions.inc.php');
include(dirname(__FILE__) . '/../includes/functions.inc.php');

class IncludesSnmpTest extends PHPUnit_Framework_TestCase
{
  /**
  * @dataProvider providerMibDirs
  */
  public function testMibDirs($result, $value1, $value2)
  {
    global $config;

    $config['mib_dir'] = '/opt/observium/mibs';

    $this->assertSame($result, mib_dirs($value1, $value2));
  }

  public function providerMibDirs()
  {
    $results = array(
      array('/opt/observium/mibs/rfc:/opt/observium/mibs/net-snmp', ''),
      array('/opt/observium/mibs/rfc:/opt/observium/mibs/net-snmp', 'rfc', 'net-snmp'),
      array('/opt/observium/mibs/rfc:/opt/observium/mibs/net-snmp:/opt/observium/mibs/cisco', 'cisco'),
      array('/opt/observium/mibs/rfc:/opt/observium/mibs/net-snmp:/opt/observium/mibs/areca:/opt/observium/mibs/dell', 'areca', 'dell'),
      array('/opt/observium/mibs/rfc:/opt/observium/mibs/net-snmp:/opt/observium/mibs/areca:/opt/observium/mibs/dell', array('areca','dell')),
      array('/opt/observium/mibs/rfc:/opt/observium/mibs/net-snmp:/opt/observium/mibs/cisco:/opt/observium/mibs/dell:/opt/observium/mibs/broadcom:/opt/observium/mibs/netgear', array('cisco','dell'), array('broadcom','netgear')),
      array('/opt/observium/mibs/rfc:/opt/observium/mibs/net-snmp:/opt/observium/mibs/d-link:/opt/observium/mibs/d_link', array('d-link','d_link')),
      array('/opt/observium/mibs/rfc:/opt/observium/mibs/net-snmp:/opt/observium/mibs/dell', array('inv@lid.name','dell')),
      array('/opt/observium/mibs/rfc:/opt/observium/mibs/net-snmp:/opt/observium/mibs/banana', 'banana', '######'),
      array('/opt/observium/mibs/rfc:/opt/observium/mibs/net-snmp:/opt/observium/mibs/banana', 'banana', array('banana')),
      array('/opt/observium/mibs/rfc:/opt/observium/mibs/net-snmp:/opt/observium/mibs', 'mibs'),
    );
    return $results;
  }

  /**
  * @dataProvider providerSnmpDewrap32bit
  */
  public function testSnmpDewrap32bit($result, $value)
  {
    $this->assertSame($result, snmp_dewrap32bit($value));
  }

  public function providerSnmpDewrap32bit()
  {
    return array(
      array(         0,           0),
      array(     65000,       65000),
      array(   '65000',     '65000'),
      array(        '',          ''),
      array(  'some.0',    'some.0'),
      array(     FALSE,       FALSE),
      // Here wrong (negative) 32bit values
      array(4294967289,        '-7'),
      array(4200000080, '-94967216'),
      array(4200000066,   -94967230),
    );
  }
}

// EOF
