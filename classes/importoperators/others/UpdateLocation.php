<?php

include_once( 'kernel/classes/ezcontentcachemanager.php' );
include_once( 'kernel/classes/ezcontentobjecttreenodeoperations.php' );

class UpdateLocation extends ImportOperator
{
	
	var $updated_array;

	function UpdateLocation( $handler )
	{
		parent::ImportOperator( $handler );
	}

	function run()
	{
		$this->source_handler->readData();

		while( $row = $this->source_handler->getNextRow() )
		{
			$this->current_eZ_object = null;
			$this->current_eZ_version = null;
			
		    $remoteID           = $this->source_handler->getDataRowId();
			$targetContentClass = $this->source_handler->getTargetContentClass();
			$parentNodeId		= $this->source_handler->getParentNodeId();
			
			$this->current_eZ_object = eZContentObject::fetchByRemoteID( $remoteID );
			
			if( $this->current_eZ_object )
			{
				$this->cli->output( 'Verifying correct parent-node-id for object ('.$this->cli->stylize( 'emphasize', $remoteID ).') ... ' , false );
			
				$main_node = $this->current_eZ_object->attribute('main_node');
				
				if( $main_node )
				{
					$main_node_parent_id = $main_node->attribute('parent_node_id');
					
					if( $parentNodeId == $main_node_parent_id )
					{
						$this->cli->output( '..'.$this->cli->stylize( 'green', "verified\n" ), false );
					}
					elseif( $main_node_parent_id > 0 && $parentNodeId > 0 )
					{
						//echo "need to move here\n";
						
						if( !eZContentObjectTreeNodeOperations::move( $this->current_eZ_object->attribute( 'main_node_id' ), $parentNodeId ) )
						{
							$this->cli->output( '..'.$this->cli->stylize( 'red', 'moving failed.'."\n" ), false );
						}
						else
						{
							$this->cli->output( '..'.$this->cli->stylize( 'yellow', "moved to new parent-node-id\n" ), false );
						}
					}
					else
					{
						echo "\n";
					}
				}
				else
				{
					echo "\n";
				}
			}
			
	
		}
	}

}

?>