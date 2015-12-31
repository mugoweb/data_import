<?php 

$module = $Params[ 'Module' ];
$http = eZHTTPTool::instance();

$remote_id = $_REQUEST[ 'remote_id' ] ? $_REQUEST[ 'remote_id' ] : $Params[ 'remote_id' ];

if( $remote_id )
{
	$ezObj = eZContentObject::fetchByRemoteID( $remote_id );
	
	if( $ezObj instanceof eZContentObject )
	{
		$mainNode = $ezObj->attribute( 'main_node' );

		if( $mainNode instanceof eZContentObjectTreeNode )
		{
			$module->redirectTo( $mainNode->attribute( 'url_alias' ) );
		}
	}
}

$Result[ 'content' ] = '<h1>Lookup remote ID</h1><form><label>Remote ID:</label><input name="remote_id" value="' . $remote_id. '" /></form>'; // <br /> <a href="' . $http->sessionVariable( "LastAccessedModifyingURI" ) . '">Back</a>';
