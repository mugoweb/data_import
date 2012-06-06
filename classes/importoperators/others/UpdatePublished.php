<?php

class UpdatePublished extends ImportOperator
{
	
	var $updated_array;
	var $last_node_id = 0;
	
	function UpdatePublished( $handler )
	{
		parent::ImportOperator( $handler );
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
			$parentId 			= $this->source_handler->getParentNodeId();
			
			$this->current_eZ_object = eZContentObject::fetchByRemoteID( $remoteID );
			
			if( $this->current_eZ_object )
			{
				$this->cli->output( 'Updating '.$this->cli->stylize( 'yellow', 'published/modified' ).' for remote object ('.$this->cli->stylize( 'emphasize', $remoteID ).')... ' , false );	
				//echo 'Updating remote object ('.$remoteID.') with inline-links ... ';
				
				// Fake getting values so we can get the priority
				while( $this->source_handler->getNextField() )
				{
				}
				
				$success = $this->source_handler->post_publish_handling( $this->current_eZ_object, $force_exit );

				$this->cli->output( '..'.$this->cli->stylize( 'green', "successfully" ), false );

				$this->current_eZ_object->store();

				eZContentCacheManager::clearContentCache( $this->current_eZ_object->attribute('id') );
			
				echo "\n";
			}
					
		}

	}
	
}

?>