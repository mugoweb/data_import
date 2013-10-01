<?php

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
		return self::REMOTE_IDENTIFIER . $this->row[0];
	}

	/* (non-PHPdoc)
	 * @see csvHandler::getValueFromField()
	 */
	public function getValueFromField( eZContentObjectAttribute $contentObjectAttribute )
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
	
	
	/* (non-PHPdoc)
	 * @see SourceHandler::getParentRemoteNodeId()
	 */
	public function getParentRemoteNodeId()
	{
		return self::REMOTE_IDENTIFIER . $this->row[1];
	}
		
	/* (non-PHPdoc)
	 * @see csvHandler::getTargetContentClass()
	 */
	function getTargetContentClass()
	{
		return 'folder';
	}

}
?>