<?php

/**
 * @author pkamps
 *
 */
class ImportOperator
{
	/**
	 * @var SourceHandler
	 */
	public $source_handler;
	/**
	 * @var eZContentObject
	 */
	public $current_eZ_object;
	/**
	 * @var eZContentObjectVersion
	 */
	public $current_eZ_version;
	/**
	 * @var eZCLI
	 */
	public $cli;

	/**
	 * Just sets the cli instance
	 */
	public function __construct()
	{
		$this->cli = eZCLI::instance();
		$this->cli->setUseStyles( true );
	}

	/**
	 * Main function
	 */
	public function run()
	{
		$this->cli->output( $this->cli->stylize( 'cyan', 'Starting import with "' . $this->source_handler->handlerTitle . '" handler' . "\n" ), false );

		if( $this->source_handler->readData() )
		{
			$force_exit = false;
			$iteration = 0;
			while( $this->source_handler->getNextRow() && !$force_exit )
			{
				$iteration++;

				$this->current_eZ_object = null;
				$this->current_eZ_version = null;

				//TODO: rename getDataRowId
				$remoteID = $this->source_handler->getDataRowId();
				$targetLanguage = $this->source_handler->getTargetLanguage();

				$this->outputLineStart( $remoteID, $iteration );

				// Check if we get valid node locations
				$newXmlNodes = $this->source_handler->getNodeAssignments();

				if( !empty( $newXmlNodes ) && $newXmlNodes->length )
				{
					if( $this->save_eZ_object( $remoteID, $targetLanguage, $force_exit ) )
					{
						if( $this->save_eZ_nodes( $newXmlNodes, $force_exit ) )
						{
							if( $this->publish_eZ_node( $force_exit ) )
							{
								$this->cli->output( 'done] ' . $this->cli->stylize( 'green', 'object ID ( ' . $this->current_eZ_object->attribute( 'id' ) . ' )' . ".\n" ), false );
							} else
							{
								$this->cli->output( $this->cli->stylize( 'red', 'failed. Post handling after publish not successful.' . "\n" ), false );
							}
						} else
						{
							//TODO: resulting in "node-less" objects
							$this->cli->output( $this->cli->stylize( 'red', 'failed. Post handling after save not successful.' . "\n" ), false );
						}

						// Clear content object from $GLOBALS - to prevent OOM (not mana)
						unset( $GLOBALS[ 'eZContentObjectContentObjectCache' ] );
						unset( $GLOBALS[ 'eZContentObjectDataMapCache' ] );
						unset( $GLOBALS[ 'eZContentObjectVersionCache' ] );
					} else
					{
						$this->cli->output( '..' . $this->cli->stylize( 'gray', 'skipped.' . "\n" ), false );
					}
				} else
				{
					$this->cli->output( 'no target nodes] ' . $this->cli->stylize( 'yellow', 'skipping.' . "\n" ), false );
				}
			}
		} else
		{
			$this->cli->output( $this->cli->stylize( 'red', 'Failed to read data.' . "\n" ), true );
		}

		$this->cli->output( $this->cli->stylize( 'cyan', 'Finished.' . "\n" ), false );
	}


	/**
	 * @param string $remoteID
	 * @param string $targetLanguage
	 * @param boolean $force_exit
	 *
	 * @return boolean
	 */
	protected function save_eZ_object( $remoteID, $targetLanguage, &$force_exit )
	{
		$return = false;

		$this->current_eZ_object = eZContentObject::fetchByRemoteID( $remoteID );

		if( !$this->current_eZ_object )
		{
			$this->create_eZ_object( $remoteID, $targetLanguage );
		} else
		{
			$this->update_eZ_object( $remoteID, $targetLanguage );
		}

		// check if create_eZ_node or update_eZ_node did the job
		if( $this->current_eZ_object && $this->current_eZ_version )
		{
			// update eZContentObject States
			$stateIds = $this->source_handler->getStateIds();
			MugoHelpers::updateObjectStates( $this->current_eZ_object, $stateIds );

			// set eZContentObject Attributes
			$eZObjectAttributes = array_merge(
				$this->source_handler->getEzObjAttributes(),
				array( 'remote_id' => $remoteID )
			);

			foreach( $eZObjectAttributes as $key => $value )
			{
				$this->current_eZ_object->setAttribute( $key, $value );
			}

			// update data_map with the content we get from the source_handler
			$dataMap = $this->current_eZ_version->attribute( 'data_map' );

			while( $this->source_handler->getNextField() )
			{
				$attributeIdentifier = $this->source_handler->geteZAttributeIdentifierFromField();

				if( $attributeIdentifier )
				{
					if( isset( $dataMap[ $attributeIdentifier ] ) )
					{
						$this->save_eZ_attribute( $dataMap[ $attributeIdentifier ] );
					} else
					{
						$this->cli->output( $this->cli->stylize( 'red', 'Skipping unknown attribute (' . $attributeIdentifier . '), ' ), false );
					}
				}
			}

			$this->current_eZ_object->store();

			$return = $this->source_handler->post_save_handling( $force_exit );
		}

		return $return;
	}

	/**
	 * Create new eZ Publish version for existing eZ Object
	 *
	 * @param string $remoteID
	 * @param string $targetLanguage
	 * @return boolean
	 */
	protected function update_eZ_object( $remoteID, $targetLanguage = null )
	{
		$this->cli->output( 'updating, ', false );
		$this->current_eZ_version = $this->current_eZ_object->createNewVersion( false, true, $targetLanguage );
		return true;
	}


	/**
	 * Create new eZ publish object in Database
	 *
	 * @param string $remoteId
	 * @param string $targetLanguage
	 * @return boolean
	 */
	protected function create_eZ_object( $remoteId, $targetLanguage = null )
	{
		$return = false;

		$targetContentClass = $this->source_handler->getTargetContentClass();
		$this->cli->output( 'creating (' . $this->cli->stylize( 'emphasize', $targetContentClass ) . '), ', false );

		$eZ_object = MugoHelpers::createContentObject( array(), $targetContentClass );

		if( $eZ_object )
		{
			$this->current_eZ_object = $eZ_object;
			$this->current_eZ_version = $eZ_object->currentVersion();

			$return = true;
		} else
		{
			$this->cli->output( $this->cli->stylize( 'red', 'Could not create eZContentObject. (Wrong content class identifer?)' ), false );
		}

		return $return;
	}

	/**
	 * Updates the given nodes from the source handler. If the node
	 * doesn't exist yet, it will create it.
	 *
	 * @param boolean $force_exit
	 */
	protected function save_eZ_nodes( $newXmlNodes, &$force_exit )
	{
		foreach( $newXmlNodes as $dom_node )
		{
			$this->save_eZ_node( $dom_node );
		}

		$this->remove_eZ_nodes();

		//TODO: consider a post handler function
		$force_exit = false;

		return true;
	}

	/**
	 * Compares existing nodes with nodes from the source handler.
	 * Any existing nodes that are not specified in the source handler
	 * get removed.
	 *
	 * @return boolean
	 */
	protected function remove_eZ_nodes()
	{
		/*
		$assigned_nodes_org = $this->current_eZ_object->attribute( 'assigned_nodes' );
		
		foreach( $assigned_nodes_org as $node )
		{
			$exsting_remote_ids[ $node->attribute( 'remote_id' ) ] = '';
		}
		*/

		return true;
	}

	/**
	 * @param DOMElement $dom_node
	 */
	protected function save_eZ_node( DOMElement $dom_node )
	{
		$remoteID = $dom_node->getAttribute( 'remote-id' );
		$node = eZContentObjectTreeNode::fetchByRemoteID( $remoteID );

		if( is_object( $node ) )
		{
			return $this->update_eZ_node( $node, $dom_node );
		} else
		{
			return $this->create_eZ_node( $dom_node );
		}
	}

	/**
	 * @param DOMElement $dom_node
	 */
	protected function create_eZ_node( DOMElement $dom_node )
	{
		$return = false;

		$parentNodeRemoteID = $dom_node->getAttribute( 'parent-node-remote-id' );

		if( $parentNodeRemoteID )
		{
			$parentNode = eZContentObjectTreeNode::fetchByRemoteID( $parentNodeRemoteID );

			if( $parentNode )
			{
				$parentNodeID = $parentNode->attribute( 'node_id' );

				$nodeInfo = array(
					'contentobject_id' => $this->current_eZ_object->attribute( 'id' ),
					'contentobject_version' => $this->current_eZ_version->attribute( 'version' ),
					'parent_node' => $parentNodeID,
					'sort_field' => eZContentObjectTreeNode::sortFieldID( $dom_node->getAttribute( 'sort-field' ) ),
					'parent_remote_id' => $dom_node->getAttribute( 'remote-id' ), // meaning processed node remoteID (not parent)
					'sort_order' => $dom_node->getAttribute( 'sort-order' ),
					'priority' => $dom_node->getAttribute( 'priority' ),
					'is_main' => $dom_node->getAttribute( 'is-main-node' )
				);

				$existNodeAssignment = eZPersistentObject::fetchObject(
					eZNodeAssignment::definition(),
					null,
					$nodeInfo
				);

				if( !is_object( $existNodeAssignment ) )
				{
					$nodeAssignment = eZNodeAssignment::create( $nodeInfo );
					$nodeAssignment->store();

					$this->cli->output( 'add node, ', false );

					$return = true;
					//TODO: figure out if we can set the priority in the node DB row
				} else
				{
					$this->cli->output( $this->cli->stylize( 'red', 'Cannot create a node that already exists. ' ), false );
				}
			} else
			{
				$this->cli->output( $this->cli->stylize( 'red', 'Given "parent-node-remote-id" cannot be found. ' ), false );
			}
		} else
		{
			$this->cli->output( $this->cli->stylize( 'red', 'Missing "parent-node-remote-id" attribute for given dom node. Cannot create it. ' ), false );
		}

		return $return;
	}

	/**
	 * @param eZContentObjectTreeNode $node
	 * @param DOMElement $dom_node
	 */
	protected function update_eZ_node( eZContentObjectTreeNode $node, DOMElement $dom_node )
	{
		$return = true;

		$node->setAttribute( 'priority', $dom_node->getAttribute( 'priority' ) );
		$node->setAttribute( 'sort_field', eZContentObjectTreeNode::sortFieldID( $dom_node->getAttribute( 'sort-field' ) ) );
		$node->setAttribute( 'sort_order', $dom_node->getAttribute( 'sort-order' ) );
		$node->store();

		// check if node location needs to move
		$targetParentNodeRemoteID = $dom_node->getAttribute( 'parent-node-remote-id' );
		$currentParentNodeRemoteID = $node->attribute( 'parent' )->attribute( 'remote_id' );

		if( $targetParentNodeRemoteID != $currentParentNodeRemoteID )
		{
			$this->cli->output( 'moving, ', false );
			$targetParentNode = eZContentObjectTreeNode::fetchByRemoteID( $targetParentNodeRemoteID );
			$node->move( $targetParentNode->attribute( 'node_id' ) );
		}

		return $return;
	}

	/**
	 * @param boolean force_exit
	 * @return boolean
	 */
	protected function publish_eZ_node( &$force_exit )
	{
		eZOperationHandler::execute(
			'content',
			'publish',
			array(
				'object_id' => $this->current_eZ_object->attribute( 'id' ),
				'version' => $this->current_eZ_version->attribute( 'version' ),
			)
		);

		return $this->source_handler->post_publish_handling( $force_exit );
	}

	/**
	 * Fix some missing parts for the fromString methods
	 *
	 * @param eZContentObjectAttribute $contentObjectAttribute
	 */
	protected function save_eZ_attribute( eZContentObjectAttribute $contentObjectAttribute )
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

				$value = $this->source_handler->getValueFromField( $contentObjectAttribute );
			}
				break;

			case 'ezobjectrelationlist':
			{
				// Remove any exisiting value first from ezobjectrelationlist

				$content = $contentObjectAttribute->content();
				$relationList =& $content[ 'relation_list' ];
				$newRelationList = array();
				for( $i = 0; $i < count( $relationList ); ++$i )
				{
					$relationItem = $relationList[ $i ];
					eZObjectRelationListType::removeRelationObject( $contentObjectAttribute, $relationItem );
				}
				$content[ 'relation_list' ] =& $newRelationList;
				$contentObjectAttribute->setContent( $content );
				$contentObjectAttribute->store();

				$value = $this->source_handler->getValueFromField( $contentObjectAttribute );
			}
				break;

			// fromString cannot handle an empty string - it's not removing the image
			case 'ezimage':
			{
				$value = $this->source_handler->getValueFromField( $contentObjectAttribute );
				$parts = explode( '|', $value );

				// empty filename - let's remove it and we're done
				if( !$parts[ 0 ] )
				{
					eZImageType::deleteStoredObjectAttribute( $contentObjectAttribute, true );
					return true;
				}
			}
				break;

			default:
				$value = $this->source_handler->getValueFromField( $contentObjectAttribute );
		}

		// fromString returns false - even when it is successfull
		// create a bug report for that
		$contentObjectAttribute->fromString( $value );
		$contentObjectAttribute->store();

		return true;
	}

	protected function outputLineStart( $remoteId, $iteration )
	{
		$count = $this->source_handler->getRowCount();

		if( $count )
		{
			$progressText = round( $iteration / $count * 100, 2 ) . '% ';
		}

		$this->cli->output( $progressText . 'Handling object (' . $this->cli->stylize( 'emphasize', $remoteId ) . ') [', false );
	}
}
