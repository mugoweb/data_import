<?php

$Module = array ( 'name' => 'CSV Export' );

$ViewList = array();

$ViewList[ 'index' ] = array (
	'script' => 'index.php',
	'params' => array( 'class_id', 'attribute_ids' ),
    'unordered_params' => array( 'language' => 'language' )
);

?>