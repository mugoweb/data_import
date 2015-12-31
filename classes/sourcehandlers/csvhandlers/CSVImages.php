<?php

class CSVImages extends csvHandler
{
	public $handlerTitle = 'Image CSV';
	public $source_file = 'extension/data_import/dataSource/examples/images.csv';
	public $idPrepend = 'csvImage_';

	protected $mapping = array(
		1 => 'name',
		2 => 'tags',
		3 => 'image'
	);

	/**
	 * return eternal index of data row
	 */
	public function getDataRowId()
	{
		return $this->idPrepend . $this->current_row[0];
	}

	public function getValueFromField( eZContentObjectAttribute $contentObjectAttribute )
	{
		$value = null;
		$current_field_index = key( $this->mapping );
		
		switch( $current_field_index )
		{
			case '3':
			{
				$file = 'extension/data_import/dataSource/examples/' . $this->current_row[ $current_field_index ];
				
				if( file_exists( $file ) )
				{
					$value = $file;
				}
				else
				{
					if( strlen($this->current_field->nodeValue) > 0 )
						$this->log( 'Could not find image: '.$file );
					
					$value = false;
				}
			}
			break;
			
			default:
			{
				$value = $this->current_row[ $current_field_index ];
			}
		}
		
		return $value;
	}
	
	public function getParentNode()
	{
		return eZContentObjectTreeNode::fetchByRemoteID( 'csvFolder_30' );
	}
	
	public function getTargetContentClass()
	{
		return 'image';
	}

}
