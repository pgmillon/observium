<?php

/**
 * Observium Network Management and Monitoring System
 * Copyright (C) 2006-2015, Adam Armstrong - http://www.observium.org
 *
 * @package    observium
 * @subpackage webui
 * @author     Adam Armstrong <adama@observium.org>
 * @copyright  (C) 2006-2013 Adam Armstrong, (C) 2013-2016 Observium Limited
 *
 */

?>
<div class="row">
<div class="col-md-12">

<?php

unset($search, $devices);

//Search by field
$search[] = array('type'    => 'select',
                  'name'    => 'Search By',
                  'id'      => 'searchby',
                  'width'   => '120px',
                  'value'   => $vars['searchby'],
                  'values'  => array('mac' => 'MAC Address', 'ip' => 'IP Address'));
//IP version field
$search[] = array('type'    => 'select',
                  'name'    => 'IP',
                  'id'      => 'ip_version',
                  'width'   => '120px',
                  'value'   => $vars['ip_version'],
                  'values'  => array('' => 'IPv4 & IPv6', '4' => 'IPv4 only', '6' => 'IPv6 only'));
//Address field
$search[] = array('type'    => 'text',
                  'name'    => 'Address',
                  'id'      => 'address',
                  'value'   => $vars['address']);

print_search($search, 'ARP/NDP Search');

// Pagination
$vars['pagination'] = TRUE;

print_arptable($vars);

?>

  </div> <!-- col-md-12 -->

</div> <!-- row -->
<?php

// EOF
