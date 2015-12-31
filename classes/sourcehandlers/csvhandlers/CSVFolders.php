<?php

class CSVFolders extends csvHandler
{

	public $handlerTitle = 'Folder CSV';
	public $source_file = 'extension/data_import/dataSource/examples/folder_structure.csv';
	public $idPrepend = 'csvFolder_';

	protected $mapping = array(
		2 => 'name',
		3 => 'short_name',
		4 => 'description',
		5 => 'show_children',
		6 => 'publish_date'
	);

	/**
	 * return eternal index of data row
	 */
	function getDataRowId()
	{
		return $this->idPrepend . $this->current_row[0];
	}

	public function getValueFromField( eZContentObjectAttribute $contentObjectAttribute )
	{
		$value = null;
		$current_field_index = key( $this->mapping );
		
		switch( $current_field_index )
		{
			case '6':
			{
				$return_unix_ts = time();
				
				$us_formatted_date = $this->current_row[ $current_field_index ];
				$parts = explode('/', $us_formatted_date );
				
				if( count( $parts ) == 3 )
				{
					$return_unix_ts = mktime( 0,0,0, $parts[0], $parts[1] , $parts[2] );
				}
				$value = $return_unix_ts;
				
			}
			break;
		
			default:
				$value = $this->current_row[ $current_field_index ];
		}
		
		return $value;
	}
	
	
	public function getParentNode()
	{
		return eZContentObjectTreeNode::fetchByRemoteID( $this->idPrepend . $this->current_row[1] );
	}
		
	public function getTargetContentClass()
	{
		return 'folder';
	}

}