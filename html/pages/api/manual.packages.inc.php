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
<div class="row">
  <div class="col-md-12 well">
    <h3>Packages:</h3>
    <p>
      With this module it is possible to export the installed package list that is
      installed on the server.
    </p>
    <dl>
      <dt>device</dt>
      <dd>The device id of the device you want to grab the package list from</dd>
    </dl>
    <pre>
      <strong>Explanation :</strong> Collect the package list of server with id 5
      <strong>Example 1   :</strong> http://<?php echo($_SERVER['SERVER_NAME']); ?>/api.php?username=demo&password=demo&module=packages&device=5
      <strong>Result      :</strong> {"login":{"success":{"code":"101","msg":"User authentification succeeded"}},"data":[{"pkg_id":"1","device_id":"5","name":"accountsservice","manager":"deb","status":"1","version":"0.6.15-2ubuntu9","build":"","arch":"i386","size":"323584"},{"pkg_id":"2","device_id":"5","name":"acl","manager":"deb","status":"1","version":"2.2.51-5ubuntu1","build":"","arch":"i386","size":"188416"}, ..., {"pkg_id":"471","device_id":"5","name":"zlib1g","manager":"deb","status":"1","version":"1:1.2.3.4.dfsg-3ubuntu4","build":"","arch":"i386","size":"150528"}]}</pre>
  </div>
</div>

