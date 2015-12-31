<?php
/*
 */

$Module = array( 'name' => 'Data Import Module' );

$ViewList = array();

$ViewList[ 'search_remote_id' ] = array(
	'functions' => array( 'search_remote_id' ),
	'script' => 'remote_id_search.php',
	'params' => array( 'remote_id' ),
);

$ViewList[ 'exportobject' ] = array(
        'functions' => array( 'export' ),
		'script' => 'exportobject.php',
		'params' => array( 'contentobject_id' ),
);

$ViewList[ 'exportsubtree' ] = array(
        'functions' => array( 'export' ),
		'script' => 'exportsubtree.php',
		'params' => array( 'node_id' ),
);

$ViewList[ 'file_upload' ] = array(
        'functions' => array( 'file_upload' ),
		'script' => 'file_upload.php',
);

$FunctionList = array();
$FunctionList[ 'file_upload' ] = array();
$FunctionList[ 'export' ] = array();
$FunctionList[ 'search_remote_id' ] = array();