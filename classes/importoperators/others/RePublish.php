<?php

class RePublish extends ImportOperator
{
	
	var $updated_array;

	function RePublish( $handler )
	{
		parent::ImportOperator( $handler );
	}

	function run()
	{
		$this->source_handler->readData();
		
		$force_exit = false;
		$updated = array();
		
		while( $row = $this->source_handler->getNextRow() )
		{
			$this->current_eZ_object = null;
			$this->current_eZ_version = null;
			
		    $remoteID           = $this->source_handler->getDataRowId();
			$targetContentClass = $this->source_handler->getTargetContentClass();
				
			$this->cli->output( 'Re-publishing remote object ('.$this->cli->stylize( 'emphasize', $remoteID ).') as eZ object ('.$this->cli->stylize( 'emphasize', $targetContentClass ).')... ' , false );

			if( empty($updated[$remoteID]) )
			{
				$updated[$remoteID] = '1';
				
				$this->current_eZ_object = eZContentObject::fetchByRemoteID( $remoteID );
	
				if( $this->current_eZ_object )
				{
	
						$this->current_eZ_version = $this->current_eZ_object->createNewVersion();
						$this->publish_eZ_node();
						$this->cli->output( '..'.$this->cli->stylize( 'green', "successfully\n" ), false );
				}
				else
				{
					$this->cli->output( '..'.$this->cli->stylize( 'gray', 'object not found, skipped.'."\n" ), false );
				}
			}
			else
			{
				$this->cli->output( '..'.$this->cli->stylize( 'gray', 'allready published, skipped.'."\n" ), false );
			}
		}
	}
}

?>