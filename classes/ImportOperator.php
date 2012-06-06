<?php

class ImportOperator
{

	public $source_handler;
	var $current_eZ_object;
	var $current_eZ_version;
	var $updated_array;
	var $do_publish = true;
	var $cli;

	public function __construct()
	{
		# At some point I should use the components logger and console tools
		$this->cli = eZCLI::instance();
		$this->cli->setUseStyles( true );
	}

	public function run()
	{
		$this->cli->output( $this->cli->stylize( 'cyan', 'Starting import with "' . $this->source_handler->handlerTitle . '" handler'."\n" ), false );

		$this->source_handler->readData();
		
		$force_exit = false;
		
		while( $row = $this->source_handler->getNextRow() && !$force_exit )
		{
			$this->current_eZ_object = null;
			$this->current_eZ_version = null;
			
		    $remoteID       = $this->source_handler->getDataRowId();
			$targetLanguage = $this->source_handler->getTargetLanguage();
			
			$this->cli->output( 'Importing remote object ('.$this->cli->stylize( 'emphasize', $remoteID ).') ', false );

			$this->current_eZ_object = eZContentObject::fetchByRemoteID( $remoteID );

			$update_method = '';
			if( !$this->current_eZ_object )
			{
				$targetContentClass = $this->source_handler->getTargetContentClass();

				$this->cli->output( 'creating (' . $this->cli->stylize( 'emphasize', $targetContentClass ) . ') ' , false );

				// Create new eZ publish object in Database
				$this->create_eZ_node( $remoteID, $row, $targetContentClass, $targetLanguage );
			}
			else
			{
				$this->cli->output( 'updating ' , false );

				// Create new eZ Publish version for existing eZ Object
				$this->update_eZ_node( $remoteID, $row, $targetLanguage );
			}

			if( $this->current_eZ_object && $this->current_eZ_version )
			{
				$this->save_eZ_node();

				$post_save_success = $this->source_handler->post_save_handling( $this->current_eZ_object, &$force_exit );

				if( $post_save_success )
				{
					$this->publish_eZ_node();

					$post_publish_success = $this->source_handler->post_publish_handling( $this->current_eZ_object, &$force_exit );
					
					if( $post_publish_success )
					{
						$this->setNodesPriority();

						$this->cli->output( $this->cli->stylize( 'green', 'object ID ( '. $this->current_eZ_object->attribute( 'id' ) . ' )' . ".\n" ), false );
					}
					else
					{
						$this->cli->output( $this->cli->stylize( 'red', 'failed. Post handling after publish not successful.'."\n" ), false );
					}
				}
				else
				{
					$this->cli->output( $this->cli->stylize( 'red', 'failed. Post handling after save not successful.'."\n" ), false );
				}
				
				# Clear content object from $GLOBALS - to prevent OOM (not mana)
				unset( $GLOBALS[ 'eZContentObjectContentObjectCache' ] );
				unset( $GLOBALS[ 'eZContentObjectDataMapCache' ] );
				unset( $GLOBALS[ 'eZContentObjectVersionCache' ] );	
			}
			else
			{
				$this->cli->output( '..'.$this->cli->stylize( 'gray', 'skipped.'."\n" ), false );
			}
		}

		$this->cli->output( $this->cli->stylize( 'cyan', 'Finished.' . "\n" ), false );
	}

	protected function update_eZ_node( $remoteID, $row, $targetLanguage = null )
	{
		// Create new eZ Publish version for existing eZ Object
		// TODO - check parent nod id consitence - and create 2nd location if needed
		$this->do_publish = true;

		$this->current_eZ_version = $this->current_eZ_object->createNewVersion( false, true, $targetLanguage );
		
		return true;
	}
	
	
	protected function create_eZ_node( $remoteID, $row, $targetContentClass, $targetLanguage = null )
	{
		$eZClass = eZContentClass::fetchByIdentifier( $targetContentClass );

		if( $eZClass )
		{
			$eZ_object = $eZClass->instantiate( false, 0, false, $targetLanguage );
			
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
						
			$this->do_publish = true;
			
			return true;
		}
		else
		{
			$this->cli->output( $this->cli->stylize( 'red', 'Target content class invalid' ), false );
		}

		return false;
	}

	function save_eZ_node()
	{
		$dataMap  = $this->current_eZ_version->attribute( 'data_map' );

		while( $this->source_handler->getNextField() )
		{
			$contentObjectAttribute = $dataMap[ $this->source_handler->geteZAttributeIdentifierFromField() ];
			
			if( $contentObjectAttribute )
			{
				$this->save_eZ_attribute( $contentObjectAttribute );
			}
			else
			{
				$this->cli->output( $this->cli->stylize( 'red', 'eZ Attribute ('.$this->source_handler->geteZAttributeIdentifierFromField().') does not exist - skipped.' ), false );
			}
		}
		
		$this->current_eZ_object->store();
	}

	function publish_eZ_node()
	{
		if( true || $this->do_publish )
		{
			return eZOperationHandler::execute(
			                                   'content',
			                                   'publish',
			                                   array(
			                                         'object_id' => $this->current_eZ_object->attribute( 'id' ),
			                                         'version'   => $this->current_eZ_version->attribute( 'version' ),
			                                        )
			                                  );
		}
		else
			return true;
	}

	function save_eZ_attribute( $contentObjectAttribute )
	{
		$value = '';

		switch( $contentObjectAttribute->attribute( 'data_type_string' ) )
		{
			case 'ezobjectrelation':
			{
				// Remove any exisiting value first from ezobjectrelation
				/*
				eZContentObject::removeContentObjectRelation( $contentObjectAttribute->attribute('data_int'),
				                                              $this->current_eZ_object->attribute('current_version'),
				                                              $this->current_eZ_object->attribute('id'),
				                                              $contentObjectAttribute->attribute('contentclassattribute_id')
				                                              );
				*/
				$contentObjectAttribute->setAttribute( 'data_int', 0 );
				$contentObjectAttribute->store();

				$value = $this->source_handler->getValueFromField();
			}
			break;
			
			case 'ezobjectrelationlist':
			{
				// Remove any exisiting value first from ezobjectrelationlist
				
				$content = $contentObjectAttribute->content();
                $relationList =& $content['relation_list'];
                $newRelationList = array();
                for ( $i = 0; $i < count( $relationList ); ++$i )
                {
                    $relationItem = $relationList[$i];
                    eZObjectRelationListType::removeRelationObject( $contentObjectAttribute, $relationItem );
                }
                $content['relation_list'] =& $newRelationList;
                $contentObjectAttribute->setContent( $content );
                $contentObjectAttribute->store();
                
                $value = $this->source_handler->getValueFromField();
			}
			break;

			default:
				$value = $this->source_handler->getValueFromField();
		}
		
		// fromString returns false - even when it is successfull
		// create a bug report for that
		$contentObjectAttribute->fromString( $value );
		$contentObjectAttribute->store();
	}
			
	function setNodesPriority()
	{	
		$node_priority = $this->source_handler->getPriorityForNode();
		
		if($node_priority !== false)
		{
			$parent_node_id = $this->source_handler->getParentNodeId();
			$assigned_nodes = $this->current_eZ_object->attribute('assigned_nodes');	
			$db = eZDB::instance();

			foreach($assigned_nodes as $assigned_node)
			{
				$parent = $assigned_node->attribute('parent');
				if( $parent && $parent->attribute('node_id') == $parent_node_id)
				{
					$db->begin();
					$nodeID = $assigned_node->attribute('node_id');
					$db->query( "UPDATE ezcontentobject_tree SET priority=$node_priority WHERE node_id=$nodeID" );
					$assigned_node->updateAndStoreModified();
					$db->commit();
				}
			}
		}
	}
}

?>