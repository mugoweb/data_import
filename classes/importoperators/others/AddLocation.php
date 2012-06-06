<?php

class AddLocation extends SkipDuplicates
{
	
	var $updated_array;
	var $last_node_id = 0;

	function AddLocation( $handler )
	{
		parent::ImportOperator( $handler );
	}

	function create_eZ_node( $remoteID, $row, $targetContentClass )
	{
		$eZClass = eZContentClass::fetchByIdentifier( $targetContentClass );

		if( $eZClass )
		{
			$eZ_object =& $eZClass->instantiate();

			$eZ_object->setAttribute( 'remote_id', $remoteID );
			$eZ_object->store();

			// Assign object to node
			$nodeAssignment = eZNodeAssignment::create(
			    array(
			        'contentobject_id'		=> $eZ_object->attribute( 'id' ),
			        'contentobject_version'	=> 1,
			        'parent_node' => $this->source_handler->getParentNodeId(),
			        'is_main' => 1
			        )
			    );

			if( $nodeAssignment )
			{
				$nodeAssignment->store();
			}
			else
			{
				die('could not assign the object to a node');
			}
			
			$this->current_eZ_object  = $eZ_object;
			$this->current_eZ_version = $eZ_object->currentVersion();
			
			$this->last_node_id = $nodeAssignment->attribute('node_id');
			
			$this->do_publish = true;
			
			return true;
		}
		else
		{
			$this->cli->output( $this->cli->stylize( 'red', 'Target content class invalid' ), false );
			//echo 'Target content class invalid';
		}

		return false;
	}
	
	function update_eZ_node( $remoteID, $row, $targetContentClass )
	{
		$this->do_publish = false;
		
		$assigned_nodes = $this->current_eZ_object->attribute('assigned_nodes');
		$parent_node_id = $this->source_handler->getParentNodeId();
		
		$nodeArray = array();
		
		foreach($assigned_nodes as $assigned_node)
		{
			$parent = $assigned_node->attribute('parent');
			$nodeArray[] = $parent->attribute('node_id');
		}
		
		// Location does not exist, add it
		if( !in_array($parent_node_id, $nodeArray) )
		{
			$insertedNode =& $this->current_eZ_object->addLocation( $parent_node_id, true );
			
			$insertedNode->setAttribute( 'contentobject_is_published', 1 );
			$insertedNode->setAttribute( 'main_node_id', $this->current_eZ_object->attribute( 'main_node_id' ) );
			$insertedNode->setAttribute( 'contentobject_version', $this->current_eZ_object->attribute( 'current_version' ) );
			// Make sure the path_identification_string is set correctly.
			$insertedNode->updateSubTreePath();
			$insertedNode->sync();
			
			$this->last_node_id = $insertedNode->attribute('node_id');
			
			eZContentCacheManager::clearContentCacheIfNeeded( $this->current_eZ_object->attribute('id') );
			
			$this->cli->output( $this->cli->stylize( 'yellow', '(added new location)' ), false );
			//echo "(added new location)";
		}
		else
		{
			foreach($assigned_nodes as $assigned_node)
			{
				$parent = $assigned_node->attribute('parent');
				if( $parent_node_id == $parent->attribute('node_id') )
				{
					$this->last_node_id = $assigned_node->attribute('node_id');
				}
			}
		}
		
		return parent::update_eZ_node( $remoteID, $row, $targetContentClass );
	}
	
	function setNodesPriority()
	{
		$node_priority = $this->source_handler->getPriorityForNode();
		
		if($node_priority !== false)
		{
			if($this->last_node_id)
			{
				$db = eZDB::instance();
				$db->begin();
				$nodeID = $this->last_node_id;
				$db->query( "UPDATE ezcontentobject_tree SET priority=$node_priority WHERE node_id=$nodeID" );
				$node = eZContentObjectTreeNode::fetch( $this->last_node_id );
				$node->updateAndStoreModified();
				$db->commit();
			}        
		}
		
		$this->last_node_id = 0;
	}		
}

?>