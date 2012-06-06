<?php

class DryRun extends ImportOperator
{
	
	var $updated_array;

	function DryRun( $handler )
	{
		parent::ImportOperator( $handler );
	}

	function update_eZ_node( $remoteID, $row, $targetContentClass )
	{
		
		$this->updated_array[$remoteID] = 1;
		
		return false;
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
			
			$this->cli->output( 'Dry running remote object ('.$this->cli->stylize( 'emphasize', $remoteID ).') as eZ object ('.$this->cli->stylize( 'emphasize', $targetContentClass ).')... ' , false );
			//echo 'Dry running remote object ('.$remoteID.') as eZ object ('.$targetContentClass.')... ';
			$exists = '';
			//$this->current_eZ_object =& eZContentObject::fetchByRemoteID( $remoteID );
			
			$this->save_eZ_node();
			
			$this->cli->output( '..'.$this->cli->stylize( 'green', " ..completed\n" ), false );
			//echo " ..completed\n";
		}	
	}
	
	function create_eZ_node( $remoteID, $row, $targetContentClass )
	{
		return false;
	}
	
	function save_eZ_node()
	{
		while( $this->source_handler->getNextField() )
		{
			$field = $this->source_handler->geteZAttributeIdentifierFromField();
			$value = $this->source_handler->getValueFromField();
		}
	}	
	
	function publish_eZ_node()
	{
		return false;
	}	
}

?>