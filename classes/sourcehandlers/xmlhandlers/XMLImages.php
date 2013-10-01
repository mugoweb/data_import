<?php

class XMLImages extends XmlHandlerPHP
{
	var $handlerTitle = 'Images Handler';

	var $current_loc_info = array();

	var $logfile = 'images_import.log';

	var $remoteID = "";

	const REMOTE_IDENTIFIER = 'xmlimage_';	

	function Images()
	{}

	function writeLog( $message, $newlogfile = '')
	{
		if($newlogfile)
			$logfile = $newlogfile;
		else
			$logfile = $this->logfile;
		
		$this->logger->write( self::REMOTE_IDENTIFIER.$this->current_row->getAttribute('id').': '.$message , $logfile );
	}
	
	// mapping for xml field name and attribute name in ez publish
	function geteZAttributeIdentifierFromField()
	{
		$field_name = $this->current_field->getAttribute('name');
		
		switch ( $field_name )
		{						
			default:
				return $field_name; 
		}
	}
	
	/* (non-PHPdoc)
	 * @see SourceHandler::getValueFromField()
	 */
	public function getValueFromField( eZContentObjectAttribute $contentObjectAttribute )
	{
		switch( $this->current_field->getAttribute('name') )
		{
			
			case 'image':
				$file = 'extension/data_import/dataSource/examples/'.$this->current_field->nodeValue;
				
				if( file_exists( $file ) )
				{
					return $file;
				}
				else
				{
					if( strlen($this->current_field->nodeValue) > 0 )
						$this->writeLog( 'Could not find image: '.$file, 'import_images.log' );
					
					return false;
				}
			break;
			
			default:
			{
				return $this->current_field->nodeValue;
			}
		}
	}

	/* (non-PHPdoc)
	 * @see SourceHandler::getParentNodeId()
	*/
	public function getParentRemoteNodeId()
	{
		return 'xmlfolder_30';
	}
	
	/* (non-PHPdoc)
	 * @see SourceHandler::getDataRowId()
	 */
	function getDataRowId()
	{
		return self::REMOTE_IDENTIFIER.$this->current_row->getAttribute('id');
	}

	/* (non-PHPdoc)
	 * @see SourceHandler::getTargetContentClass()
	 */
	function getTargetContentClass()
	{
		return 'image';
	}

	/* (non-PHPdoc)
	 * @see SourceHandler::readData()
	 */
	function readData()
	{
		return $this->parse_xml_document( 'extension/data_import/dataSource/examples/images.xml', 'all' );
	}

}

?>