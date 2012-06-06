<?php

include_once 'kernel/classes/ezcontentobjecttreenode.php';


class RemoveOperator extends ImportOperator
{

	function RemoveOperator( $handler )
	{
		parent::ImportOperator( $handler );
		
		$this->cli->output( $this->cli->stylize( 'cyan', 'Starting to remove imports with "'.$handler->handlerTitle.'" handler'."\n" ), false );
		//echo 'Starting to remove imports with "'.$handler->handlerTitle.'" handler'."\n";
	}

	function run()
	{
		$this->source_handler->readData();
		
		$force_exit = false;
		while( $row = $this->source_handler->getNextRow() && !$force_exit )
		{
			$this->current_eZ_object = null;
			$this->current_eZ_version = null;
			
		    $remoteID           = $this->source_handler->getDataRowId();
			$targetContentClass = $this->source_handler->getTargetContentClass();
			
			$this->cli->output( 'Removing remote object ('.$this->cli->stylize( 'emphasize', $remoteID ).') ... ' , false );	
			//echo 'Removing remote object ('.$remoteID.')... ';
			$exists = '';
			$this->current_eZ_object = eZContentObject::fetchByRemoteID( $remoteID );
			
			if( !$this->current_eZ_object )
			{
				$exists = 'not found.';
				// Create new eZ publish object in Database
				//$this->create_eZ_node( $remoteID, $row, $targetContentClass );
				$this->cli->output( '..'.$this->cli->stylize( 'gray', "not found, skipping\n" ), false );
				//echo "..not found, skipping\n";
			}
			else
			{
				$exists = 'found.';
				// Create new eZ Publish version for existing eZ Object
				//$this->update_eZ_node( $remoteID, $row, $targetContentClass );
				$this->remove_eZ_node();
			}
			
			
		}
	}
	
	function remove_eZ_node()
	{
		if( $this->current_eZ_object )
		{
			//$this->current_eZ_object->remove();
			$object_id = $this->current_eZ_object->attribute('id');
			
			$assigned_nodes = $this->current_eZ_object->attribute('assigned_nodes');
			
			$deleteIDArray = array();
			$moveToTrash = false;
			
			foreach($assigned_nodes as $assigned_node)
			{
				$assigned_node->remove();
			}
				
			$this->current_eZ_object->remove();
			$this->current_eZ_object->purge();
			
			$this->cli->output( '..'.$this->cli->stylize( 'green', 'successfully removed'."\n" ), false );
			//echo '..successfully removed'."\n";
		}
		else
		{
			$this->cli->output( '..'.$this->cli->stylize( 'gray', "not found, skipping\n" ), false );
			//echo "..not found, skipping\n";
		}
	}

}

?>