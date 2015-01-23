<?php

function data_import_ContentActionHandler( &$module, &$http, &$objectID )
{
	if( $http->hasPostVariable( 'ActionExport' ) )
	{
		$objId = (int) $_REQUEST[ 'ContentObjectID' ];
        
		if( $objId )
		{
			$module->redirectTo( 'data_import/exportobject/'. $objId );
		}
	}
}