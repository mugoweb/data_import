<?php

$output = '';
$ezobj_id = (int)$Params[ 'contentobject_id' ];

if( $ezobj_id )
{
	$ezobj = eZContentObject::fetch( $ezobj_id );

	if( $ezobj )
	{
		header( 'Content-Type: application/xml' );
		
		$tpl = eZTemplate::factory();
		$tpl->setVariable( 'contentobject', $ezobj );
		$output = $tpl->fetch( 'design:modules/data_import/exportobject.tpl' );
	}
}

if( !$output )
{
	$output = 'Failed';
}

echo $output;
eZExecution::cleanExit();

?>