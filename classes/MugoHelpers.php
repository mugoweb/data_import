<?php

/**
 * Collection of some usefull functions
 * 
 * @author pkamps
 */
class MugoHelpers
{
	
	/**
	 * @param array $attributes
	 * @param int $parent_node_id
	 * @param string $content_class_identifier
	 * @return eZContentObjectTreeNode
	 */
	public static function createNode( array $attributes, $parent_node_id, $content_class_identifier )
	{
		$return = null;
		
		if( $parent_node_id && $content_class_identifier )
		{
			// Post eZP 4.3 on cluster setup to force eZP
			// to query the master on the currentVersion() and similar calls
			$db = eZDB::instance();
			$db->begin();
			
			$ez_obj      = self::createEzObject( null, $content_class_identifier, $parent_node_id );
			$obj_version = $ez_obj->currentVersion();
			$data_map    = $obj_version->attribute( 'data_map' );
			
			foreach( $attributes as $key => $value )
			{
				if( $data_map[ $key ] instanceof eZContentObjectAttribute )
				{
					$data_map[ $key ]->fromString( $value );
					$data_map[ $key ]->store();
				}
			}
		
			eZOperationHandler::execute(
			                            'content',
			                            'publish',
			                            array(
			                                  'object_id' => $ez_obj->attribute( 'id' ),
			                                  'version'   => $obj_version->attribute( 'version' ),
			                                 )
			                           );
	
			$db->commit();
			
			//refetch the object
			$ez_obj = eZContentObject::fetch( $ez_obj->attribute( 'id' ) );	
			$return = $ez_obj->attribute( 'main_node' );
		}
		
		return $return;
	}

	/**
	 * @param unknown_type $attributes
	 * @param unknown_type $ezobject
	 * @param unknown_type $new_version
	 * @return boolean
	 */
	public static function update( $attributes, $ezobject, $new_version = false )
	{
		$return = false;
		
		if( $ezobject && is_array( $attributes ) )
		{
			$obj_version = $new_version ? $ezobject->createNewVersion() : $ezobject->currentVersion();
	
			$data_map = $obj_version->attribute( 'data_map' );
			
			foreach( $attributes as $key => $value )
			{
				if( $data_map[ $key ] instanceof eZContentObjectAttribute )
				{
					$data_map[ $key ]->fromString( $value );
					$data_map[ $key ]->store();
				}
			}
		
			eZOperationHandler::execute(
			                            'content',
			                            'publish',
			                            array(
			                                  'object_id' => $ezobject->attribute( 'id' ),
			                                  'version'   => $obj_version->attribute( 'version' ),
			                                 )
			                           );
	
			$return = $ezobject->attribute( 'main_node_id' );
		}
		
		return $return;
	}

	/**
	 * @param unknown_type $ezobject
	 */
	public static function remove( $ezobject )
	{
		$main_node_id = $ezobject->attribute( 'main_node_id' );
		return eZContentObjectTreeNode::removeSubtrees( array( $main_node_id ), false );
	}

	
	/**
	 * Builds ezp folder structure based on given path
	 * 
	 * @param string $path
	 * @param int $parentNodeId
	 * @param string $folderClass
	 * @return eZContentObjectTreeNode
	 */
	public static function createPath( $path, $parentNodeId = 2, $folderClass = 'folder' )
	{
		$return = null;
		
		$pathParts = explode( '/', $path );

		if( !empty( $pathParts ) )
		{
			foreach( $pathParts as $part )
			{
				if( $part != '' )
				{
					$result = eZFunctionHandler::execute( 'content', 'list', array(
							'parent_node_id'     => $parentNodeId,
							'class_filter_type'  => 'include',
							'class_filter_array' => array( $folderClass ),
							'attribute_filter'   => array( array( 'name', '=', $part ) ),
							'limitation'         => array()
					) );
					
					if( !empty( $result ) )
					{
						$return = $result[ 0 ];
						// Update parentNodeId for next iteration
						$parentNodeId = $return->attribute( 'node_id' );
						continue;
					}
					else
					{
						$content = array( 'name' => $part );
						
						$return = self::createNode( $content, $parentNodeId, $folderClass );
						
						if( $return )
						{
							// Update parentNodeId for next iteration
							$parentNodeId = $return->attribute( 'node_id' );
						}
						else
						{
							// couldn't create the folder node
							$return = null;
							break;
						}
					}
				}
			}
		}

		return $return;
	}
	
	/**
	 * @param array $attributes
	 * @param string $class_idenfier
	 * @param int $parent_node_id
	 * @return eZContentObject
	 */
	public static function createEzObject( array $attributes, $class_idenfier, $parent_node_id )
	{
		$eZ_object = null;
		
		$eZClass = eZContentClass::fetchByIdentifier( $class_idenfier );
	
		if( $eZClass )
		{
			$eZ_object = $eZClass->instantiate( false, 0, false );
			
			//set attributes
			if( !empty( $attributes ) )
			{
				foreach( $attributes as $key => $value )
				{
					$eZ_object->setAttribute( $key, $value );
				}
			}

			$eZ_object->store();
	
			// Assign object to node
			$nodeAssignment = eZNodeAssignment::create(
					array(
							'contentobject_id'		=> $eZ_object->attribute( 'id' ),
							'contentobject_version'	=> 1,
							'parent_node' => $parent_node_id,
							'is_main' => 1
					)
			);
	
			if( $nodeAssignment )
			{
				$nodeAssignment->store();
			}
			else
			{
				// TODO: report error
				//die('could not assign the object to a node');
			}
		}
	
		return $eZ_object;
	}
	
}

?>