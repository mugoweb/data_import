<?php
include_once( 'extension/data_import/classes/sourcehandlers/csvHandler.php' );

class CSVFolders extends csvHandler
{

	var $handlerTitle = 'Folder CSV';
	var $source_file = 'extension/data_import/dataSource/examples/folder_structure.csv';
	
	var $mapping = array( 2 => 'name',
	                      3 => 'short_name',
	                      4 => 'description',
	                      5 => 'show_children',
	                      6 => 'publish_date' );
	
	const REMOTE_IDENTIFIER = 'csvfolder_';	
	
	function Folders()
	{}

	/*
	 * return eternal index of data row
	 */
	function getDataRowId()
	{
		return self::REMOTE_IDENTIFIER.$this->row[0];
	}

	function getValueFromField()
	{
		$value = null;
		$current_field_index = key( $this->mapping );
		
		switch( $current_field_index )
		{
			case '6':
			{
				$return_unix_ts = time();
				
				$us_formated_date = $this->row[ $current_field_index ];
				$parts = explode('/', $us_formated_date );
				
				if( count( $parts ) == 3 )
				{
					$return_unix_ts = mktime( 0,0,0, $parts[0], $parts[1] , $parts[2] );
				}
				$value = $return_unix_ts;
				
			}
			break;
		
			default:
				$value = $this->row[ $current_field_index ];
		}
		
		return $value;
	}
	
	function getParentNodeId()
	{
		$parent_id = 2; // fallback is the root node
		
		$parent_remote_id = $this->row[1];

		if( $parent_remote_id )
		{
			$eZ_object = eZContentObject::fetchByRemoteID( self::REMOTE_IDENTIFIER.$parent_remote_id );

			if( $eZ_object )
			{
				$parent_id = $eZ_object->attribute('main_node_id');
			}
		}

		return $parent_id;
	}
	
	function getTargetContentClass()
	{
		return 'folder';
	}

}
?>