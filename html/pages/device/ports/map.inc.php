<?php

/**
 * Observium Network Management and Monitoring System
 * Copyright (C) 2006-2015, Adam Armstrong - http://www.observium.org
 *
 * @package    observium
 * @subpackage webui
 * @author     Adam Armstrong <adama@memetic.org>
 * @copyright  (C) 2006-2015 Adam Armstrong
 *
 */

?>
<div style="height:100%">
    <object data="map.php?device=<?php echo($device['device_id']); ?>&amp;format=svg" type="image/svg+xml" style="width: 100%; height:100%">
    </object>
</div>

<?php
$page_title[] = "Map";

// EOF
