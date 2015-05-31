<?php

$arr = array(
          'person' => array(
                        'name'     => array('first' => 'Chris', 'last' => 'Firescythe'),
                        'age'      => 24,
                        'hometown' => array(
                                        'city'  => 'Cincinnati',
                                        'state' => 'OH',
                                      )
                      ),
          'normal' => 'Normal'
        );

echo json_encode($arr);
