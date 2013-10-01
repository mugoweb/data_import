<?php

class CSVImages extends csvHandler
{

	var $handlerTitle = 'Image CSV';
	var $source_file = 'extension/data_import/dataSource/examples/images.csv';
	
	var $mapping = array( 1 => 'name',
	                      2 => 'tags',
	                      3 => 'image' );
	
	const REMOTE_IDENTIFIER = 'csvimage_';	
	
	function Images()
	{}

	/*
	 * return eternal index of data row
	 */
	function getDataRowId()
	{
		return self::REMOTE_IDENTIFIER.$this->row[0];
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
			case '3':
			{
				$file = 'extension/data_import/dataSource/examples/' . $this->row[ $current_field_index ];
				
				if( file_exists( $file ) )
				{
					$value = $file;
				}
				else
				{
					if( strlen($this->current_field->nodeValue) > 0 )
						$this->writeLog( 'Could not find image: '.$file, 'import_images.log' );
					
					$value = false;
				}
			}
			break;
			
			default:
			{
				$value = $this->row[ $current_field_index ];
			}
		}
		
		return $value;
	}
	
	/* (non-PHPdoc)
	 * @see SourceHandler::getParentNodeId()
	*/
	public function getParentRemoteNodeId()
	{
		return 'csvfolder_30';
	}
	
	/* (non-PHPdoc)
	 * @see csvHandler::getTargetContentClass()
	 */
	function getTargetContentClass()
	{
		return 'image';
	}

}

?>