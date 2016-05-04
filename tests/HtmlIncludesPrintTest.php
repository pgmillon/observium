<?php

include(dirname(__FILE__) . '/../includes/defaults.inc.php');
//include(dirname(__FILE__) . '/../config.php');
include(dirname(__FILE__) . '/../includes/definitions.inc.php');
include(dirname(__FILE__) . '/../includes/functions.inc.php');
include(dirname(__FILE__) . '/../html/includes/functions.inc.php');

class HtmlIncludesPrintTest extends PHPUnit_Framework_TestCase
{
  /**
   * @dataProvider providerGetTableHeader
   * @group common
   */
  public function testGetTableHeader($vars, $result)
  {
    $cols = array(
                    array(NULL,            'class="state-marker"'),
                    array(NULL,            'style="width: 1px;"'),
      'device'   => array('Local address', 'style="width: 150px;"'),
                    array(NULL,            'style="width: 20px;"'),
      'peer_ip'  => array('Peer address',  'style="width: 150px;"'),
      'type'     => array('Type',          'style="width: 50px;"'),
                    array('Family'),
      'peer_as'  => 'Remote AS',
      'state'    => 'State',
                    'Uptime / Updates',
                    NULL
    );
    $this->assertSame($result, get_table_header($cols, $vars));
  }

  public function providerGetTableHeader()
  {
    return array(
      array( // Sorting enabled
        array('page' => 'routing', 'protocol' => 'bgp', 'type' => 'all'),
        '  <thead>
    <tr>
      <th class="state-marker"></th>
      <th style="width: 1px;"></th>
      <th style="width: 150px;"><a href="routing/protocol=bgp/type=all/sort=device/">Local address</a></th>
      <th style="width: 20px;"></th>
      <th style="width: 150px;"><a href="routing/protocol=bgp/type=all/sort=peer_ip/">Peer address</a></th>
      <th style="width: 50px;"><a href="routing/protocol=bgp/type=all/sort=type/">Type</a></th>
      <th >Family</th>
      <th><a href="routing/protocol=bgp/type=all/sort=peer_as/">Remote AS</a></th>
      <th><a href="routing/protocol=bgp/type=all/sort=state/">State</a></th>
      <th>Uptime / Updates</th>
      <th></th>
    </tr>
  </thead>' . PHP_EOL
      ),
      array( // Sorting enabled, selected type 
        array('page' => 'routing', 'protocol' => 'bgp', 'type' => 'all', 'sort' => 'type'),
        '  <thead>
    <tr>
      <th class="state-marker"></th>
      <th style="width: 1px;"></th>
      <th style="width: 150px;"><a href="routing/protocol=bgp/type=all/sort=device/">Local address</a></th>
      <th style="width: 20px;"></th>
      <th style="width: 150px;"><a href="routing/protocol=bgp/type=all/sort=peer_ip/">Peer address</a></th>
      <th style="width: 50px;"><a href="routing/protocol=bgp/type=all/sort=type/sort_order=desc/">Type&nbsp;&darr;</a></th>
      <th >Family</th>
      <th><a href="routing/protocol=bgp/type=all/sort=peer_as/">Remote AS</a></th>
      <th><a href="routing/protocol=bgp/type=all/sort=state/">State</a></th>
      <th>Uptime / Updates</th>
      <th></th>
    </tr>
  </thead>' . PHP_EOL
      ),
      array( // Sorting disabled
        array(),
        '  <thead>
    <tr>
      <th class="state-marker"></th>
      <th style="width: 1px;"></th>
      <th style="width: 150px;">Local address</th>
      <th style="width: 20px;"></th>
      <th style="width: 150px;">Peer address</th>
      <th style="width: 50px;">Type</th>
      <th >Family</th>
      <th>Remote AS</th>
      <th>State</th>
      <th>Uptime / Updates</th>
      <th></th>
    </tr>
  </thead>' . PHP_EOL
      ),
    );
  }
}

// EOF
