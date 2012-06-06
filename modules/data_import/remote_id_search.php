<?php 

$module = $Params['Module'];
$http = eZHTTPTool::instance();

$remote_id = $_REQUEST[ 'remote_id' ];

if( $remote_id )
{
	$obj = eZContentObject::fetchByRemoteID( $remote_id );
	
	if( is_object( $obj ) )
	{
		$module->redirect( 'content', 'view', array( 'full',  $obj->attribute( 'main_node_id' ) ) );
	}
}

$Result[ 'content' ] = '<h1>Could not find given remote ID</h1><b>Remote ID:</b> "' . $remote_id. '"'; // <br /> <a href="' . $http->sessionVariable( "LastAccessedModifyingURI" ) . '">Back</a>'; 

?>