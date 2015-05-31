<?php

    $arr = array('start' => "It worked the first time.",

    'middle' => array(
        'foo' => 'And it worked the second time.',
        'bar' => 'As well as the third.'
    ),

    'final' => "Then, surprisingly, it worked the final time.");

  echo json_encode($arr);
