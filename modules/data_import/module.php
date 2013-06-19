<?php
/*
 */

$Module = array( 'name' => 'Data Import Module' );

$ViewList = array();

$ViewList[ 'search_remote_id' ] = array(
		'script' => 'remote_id_search.php',
);

$ViewList[ 'exportobject' ] = array(
		'script' => 'exportobject.php',
		'params' => array( 'contentobject_id' )
);

?>