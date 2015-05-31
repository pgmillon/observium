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
    <h3>General Usage:</h3>
    <p>
      To use the Simple Observium API you always must start with the general usage
      commands, a list is provide below with some examples.
    </p>
    <dl>
      <dt>username</dt>
      <dd>The User you want to login with.</dd>
      <dt>password</dt>
      <dd>Your users password</dd>
      <dt>module</dt>
      <dd>Modules you want to call (must be one of the enabled modules)</dd>
      <dt>debug</dt>
      <dd>Enables the debug output (this is an optional command)</dd>
    </dl>
    <pre>
      <strong>Explanation :</strong> Collect data from the demo module (
      <strong>Example 1   :</strong> http://<?php echo($_SERVER['SERVER_NAME']); ?>/api.php?username=demo&amp;password=demo&amp;module=demo
      <strong>Result      :</strong> {"login":{"success":{"code":"101","msg":"User authentification succeeded"}},"data":{"info":{"code":"102","msg":"Demo module loaded successfully"},"value":"This is only a demo module witch doesn't return any live data."}}</pre>
    <pre>
      <strong>Explanation :</strong> Collect data from the demo module with a wrong login
      <strong>Example 2   :</strong> http://<?php echo($_SERVER['SERVER_NAME']); ?>/api.php?username=wrong&amp;password=wrong&amp;module=demo
      <strong>Result      :</strong> {"login":{"error":{"code":"301","msg":"User authentification failed"}}}</pre>
  </div>
</div>

