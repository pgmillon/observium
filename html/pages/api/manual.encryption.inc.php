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
    <h3>Encryption:</h3>
    <p>
      To use this module it is only required to enable it and set the encryption key
      that will be used to salt the data. If the module is enabled all other modules
      will now return the data in the encryption format.
    </p>
    <pre>
      <strong>Explanation :</strong> Collect encrypted data from the demo module
      <strong>Example 1   :</strong> http://<?php echo($_SERVER["SERVER_NAME"]); ?>/api.php?username=demo&amp;password=demo&amp;module=demo
      <strong>Result      :</strong> [DATA]</pre>
    <br />
    <h3>Decryption:</h3>
    <p>
      To decrypt the encrypted data you can us the following function:
    </p>
    <pre style="color: #800;">
      function api_decrypt_data($data, $key) {
        $iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB);
        $iv      = mcrypt_create_iv($iv_size, MCRYPT_RAND);
        $res     = mcrypt_decrypt(MCRYPT_RIJNDAEL_256, $key, $data, MCRYPT_MODE_ECB, $iv);
        return $res;
      }</pre>
  </div>
</div>

