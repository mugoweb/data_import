<?php

class RemoveClass extends RemoveOperator
{

	var $source_handler;
	var $current_eZ_object;
	var $current_eZ_version;
	var $updated_array;

	function RemoveClass( $handler )
	{
		parent::RemoveOperator( $handler );
	}

	function run()
	{
		$this->source_handler->readData();
		
		$classList = array();
		
		$force_exit = false;
		while( $row = $this->source_handler->getNextRow() && !$force_exit )
		{
			$this->current_eZ_object = null;
			$this->current_eZ_version = null;
			
		    $remoteID           = $this->source_handler->getDataRowId();
			$targetContentClass = $this->source_handler->getTargetContentClass();
				
			if(!in_array( $targetContentClass, $classList ))
				$classList[] = $targetContentClass;
		}
		
		foreach($classList as $targetClass)
		{
			$deleteIDArray = null;
			$deleteIDArray = array();
			
			$nodeArray = eZContentObjectTreeNode::subTree( array(
															'ClassFilterType' => 'include',
															'ClassFilterArray' => array( $targetClass ),
															), 
															2
			);
			
			foreach ( $nodeArray as $node )
			{
				$id = $node->attribute('contentobject_id');
				if(!in_array($id, $deleteIDArray))
					$deleteIDArray[] = $id;
			}
			
			foreach($deleteIDArray as $object_id)
			{
				$this->cli->output( 'Removing object-id "'.$this->cli->stylize( 'emphasize', $object_id ).'" ('.$this->cli->stylize( 'emphasize', $targetClass ).') ... ' , false );
				//echo "Removing object-id ".$object_id." (".$targetClass.") ... ";
				
				$this->current_eZ_object = eZContentObject::fetch($object_id);
							
				$this->remove_eZ_object();
			}
		}

	}
	
	
	
	function remove_eZ_object()
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