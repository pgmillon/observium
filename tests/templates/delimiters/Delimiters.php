<?php

    $arr = array('start' => "It worked the first time.",

    'middle' => array(
            array('item' => "And it worked the second time."),
            array('item' => "As well as the third."),
        ),

    'final' => "Then, surprisingly, it worked the final time.");

echo json_encode($arr);
