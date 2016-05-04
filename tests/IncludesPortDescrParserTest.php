<?php

include(dirname(__FILE__) . '/../includes/defaults.inc.php');
//include(dirname(__FILE__) . '/../config.php'); // Do not include user editable config here
include(dirname(__FILE__) . '/data/test_definitions.inc.php'); // Fake definitions for testing
include(dirname(__FILE__) . '/../includes/definitions.inc.php');
include(dirname(__FILE__) . '/../includes/functions.inc.php');
include(dirname(__FILE__) . '/../includes/port-descr-parser.inc.php');

class IncludesPortDescrParserTest extends PHPUnit_Framework_TestCase
{
  /**
  * @dataProvider providerParser
  */
  public function testParser($string, $result)
  {
    global $config;

    // Add in custom interface groups for testing
    $config['int_groups'] = array('TestGroup1', 'TestGroup2');

    $this->assertSame($result, custom_port_parser(array('ifAlias' => $string)));
  }

  public function providerParser()
  {
    return array(
      array('Cust: Example Customer',
            array('type'    => 'cust',
                  'descr'   => 'Example Customer',
                  'circuit' => null,
                  'speed'   => null,
                  'notes'   => null,
            )
      ),
      array('Cust: Example Customer {CIRCUIT}',
            array('type'    => 'cust',
                  'descr'   => 'Example Customer',
                  'circuit' => 'CIRCUIT',
                  'speed'   => null,
                  'notes'   => null,
            )
      ),
      array('Cust: Example Customer [SPEED]',
            array('type'    => 'cust',
                  'descr'   => 'Example Customer',
                  'circuit' => null,
                  'speed'   => 'SPEED',
                  'notes'   => null,
            )
      ),
      array('Cust: Example Customer (NOTE)',
            array('type'    => 'cust',
                  'descr'   => 'Example Customer',
                  'circuit' => null,
                  'speed'   => null,
                  'notes'   => 'NOTE',
            )
      ),
      array('Cust: Example Customer {CIRCUIT} (NOTE)',
            array('type'    => 'cust',
                  'descr'   => 'Example Customer',
                  'circuit' => 'CIRCUIT',
                  'speed'   => null,
                  'notes'   => 'NOTE',
            )
      ),
      array('Cust: Example Customer {CIRCUIT} [SPEED]',
            array('type'    => 'cust',
                  'descr'   => 'Example Customer',
                  'circuit' => 'CIRCUIT',
                  'speed'   => 'SPEED',
                  'notes'   => null,
            )
      ),
      array('Cust: Example Customer [SPEED] (NOTE)',
            array('type'    => 'cust',
                  'descr'   => 'Example Customer',
                  'circuit' => null,
                  'speed'   => 'SPEED',
                  'notes'   => 'NOTE',
            )
      ),
      array('Cust: Example Customer {CIRCUIT} [SPEED] (NOTE)',
            array('type'    => 'cust',
                  'descr'   => 'Example Customer',
                  'circuit' => 'CIRCUIT',
                  'speed'   => 'SPEED',
                  'notes'   => 'NOTE',
            )
      ),
      array('Cust: Example Customer{CIRCUIT}[SPEED](NOTE)',
            array('type'    => 'cust',
                  'descr'   => 'Example Customer',
                  'circuit' => 'CIRCUIT',
                  'speed'   => 'SPEED',
                  'notes'   => 'NOTE',
            )
      ),
      array('Cust: !@#$%^&*_-=+/|\.,`~";:<>?\' {CIRCUIT}[SPEED](NOTE)',
            array('type'    => 'cust',
                  'descr'   => '!@#$%^&*_-=+/|\.,`~";:<>?\'',
                  'circuit' => 'CIRCUIT',
                  'speed'   => 'SPEED',
                  'notes'   => 'NOTE',
            )
      ),

      # Transit
      array('Transit: Example Provider {CIRCUIT} [SPEED] (NOTE)',
            array('type'    => 'transit',
                  'descr'   => 'Example Provider',
                  'circuit' => 'CIRCUIT',
                  'speed'   => 'SPEED',
                  'notes'   => 'NOTE',
            )
      ),

      # Core
      array('Core: Example Core {CIRCUIT} [SPEED] (NOTE)',
            array('type'    => 'core',
                  'descr'   => 'Example Core',
                  'circuit' => 'CIRCUIT',
                  'speed'   => 'SPEED',
                  'notes'   => 'NOTE',
            )
      ),

      # Peering
      array('Peering: Example Peer {CIRCUIT} [SPEED] (NOTE)',
            array('type'    => 'peering',
                  'descr'   => 'Example Peer',
                  'circuit' => 'CIRCUIT',
                  'speed'   => 'SPEED',
                  'notes'   => 'NOTE',
            )
      ),

      # Server
      array('Server: Example Server {CIRCUIT} [SPEED] (NOTE)',
            array('type'    => 'server',
                  'descr'   => 'Example Server',
                  'circuit' => 'CIRCUIT',
                  'speed'   => 'SPEED',
                  'notes'   => 'NOTE',
            )
      ),

      # L2TP
      array('L2TP: Example L2TP {CIRCUIT} [SPEED] (NOTE)',
            array('type'    => 'l2tp',
                  'descr'   => 'Example L2TP',
                  'circuit' => 'CIRCUIT',
                  'speed'   => 'SPEED',
                  'notes'   => 'NOTE',
            )
      ),

      # Custom: TestGroup1
      array('TestGroup1: Test Group 1 {CIRCUIT} [SPEED] (NOTE)',
            array('type'    => 'testgroup1',
                  'descr'   => 'Test Group 1',
                  'circuit' => 'CIRCUIT',
                  'speed'   => 'SPEED',
                  'notes'   => 'NOTE',
            )
      ),

      # Custom: TestGroup2
      array('TestGroup2: Test Group 2 {CIRCUIT} [SPEED] (NOTE)',
            array('type'    => 'testgroup2',
                  'descr'   => 'Test Group 2',
                  'circuit' => 'CIRCUIT',
                  'speed'   => 'SPEED',
                  'notes'   => 'NOTE',
            )
      ),

      # Errors

      # Missing description
      array('Core: {CIRCUIT} [SPEED] (NOTE)',
            array(),
      ),
      # Missing type
      array('Example {CIRCUIT} [SPEED] (NOTE)',
            array(),
      ),
      # B0rken circuit
      array('Core: Example {CIRCUIT',
            array('type'    => 'core',
                  'descr'   => 'Example',
                  'circuit' => null,
                  'speed'   => null,
                  'notes'   => null,
            )
      ),
      # B0rken circuit
      array('Core: Example CIRCUIT}',
            array('type'    => 'core',
                  'descr'   => 'Example CIRCUIT',
                  'circuit' => null,
                  'speed'   => null,
                  'notes'   => null,
            )
      ),
      # B0rken speed
      array('Core: Example [SPEED',
            array('type'    => 'core',
                  'descr'   => 'Example',
                  'circuit' => null,
                  'speed'   => null,
                  'notes'   => null,
            )
      ),
      # B0rken speed
      array('Core: Example SPEED]',
            array('type'    => 'core',
                  'descr'   => 'Example SPEED',
                  'circuit' => null,
                  'speed'   => null,
                  'notes'   => null,
            )
      ),
      # B0rken notes
      array('Core: Example (NOTE',
            array('type'    => 'core',
                  'descr'   => 'Example',
                  'circuit' => null,
                  'speed'   => null,
                  'notes'   => null,
            )
      ),
      # B0rken notes
      array('Core: Example NOTE)',
            array('type'    => 'core',
                  'descr'   => 'Example NOTE',
                  'circuit' => null,
                  'speed'   => null,
                  'notes'   => null,
            )
      ),
      # Bogus type
      array('Foo: Example {CIRCUIT} [SPEED] (NOTE)',
            array(),
      ),
    );
  }
}

// EOF
