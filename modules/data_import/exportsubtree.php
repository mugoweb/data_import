<?php

$output = '';
$node_id = (int)$Params[ 'node_id' ];

if( $node_id )
{
	header( 'Content-Type: application/xml' );
	
	$tpl = eZTemplate::factory();
	$tpl->setVariable( 'parent_node_id', $node_id );
	$output = $tpl->fetch( 'design:modules/data_import/exportsubtree.tpl' );
}

if( !$output )
{
	$output = 'Failed';
}

echo $output;
eZExecution::cleanExit();

?>