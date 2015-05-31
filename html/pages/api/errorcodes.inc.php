<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage api
 * @copyright  (C) 2006-2014 Adam Armstrong
 *
 */

?>
<h2 style="margin-bottom: 10px;">Simple Observium API - Error codes</h2>
<div class="alert alert-info">
  <i class="oicon-white oicon-info-sign"></i> <strong>Information:</strong>
  <p>
    Here you can find out what the error number means when it returns a errorcode when
    you call the simple api.
  </p>
</div>
<table class="table table-striped table-bordered table-hover table-condensed table-rounded">
  <thead>
    <tr>
      <th>Code</th>
      <th>Message</th>
    </tr>
  </thead>
  <tbody>
<?php

include_once("includes/api/errorcodes.inc.php");

foreach ($errorcodes as $item=>$value) {
  echo("<tr><td>".$value['code']."</td><td>".$value['msg']."</td></tr>");
}

?>
  </tbody>
</table>