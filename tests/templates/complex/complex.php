<?php

    $arr = array('header' => 'Colors',

    'item' => array(
        array('name' => 'red', 'current' => true, 'url' => '#Red'),
        array('name' => 'green', 'current' => false, 'url' => '#Green'),
        array('name' => 'blue', 'current' => false, 'url' => '#Blue'),
    ));

    $arr['isEmpty'] = count($arr['item']) === 0;
    $arr['notEmpty'] = !$arr['isEmpty'];

    echo json_encode($arr);
