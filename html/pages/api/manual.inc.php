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

include("includes/api/functions.inc.php");

?>
<h2 style="margin-bottom: 10px">Simple Observium API - Manual</h2>
<div class="row">
  <div class="span8 well">
    <h3>What is it?</h3>
    <p>
     Simple Observium API is a lightweight api that returns raw data located in the mysql
     database in a json format so it can be used in 3th party software. Not all of the
     options that are available in observium will be availble thru the API, you can look
     at the right site to see the supported modules. It is also possible for your customer
     logins to use this API and to encrypt the data and decrypt it on the other end.
    </p>
  </div>
  <div class="col-lg-4 well">
    <h3>Available Modules:</h3>
    <p>
      <?php echo api_show_modules(); ?>
    </p>
  </div>
</div>
<?php

  include_once("pages/api/manual.general.inc.php");

  api_show_manual();

?>