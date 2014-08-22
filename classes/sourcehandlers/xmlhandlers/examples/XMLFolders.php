<?php

class XMLFolders extends XmlHandlerPHP
{
	public $handlerTitle = 'Folders Handler';
	public $current_loc_info = array();
	public $logfile = 'folders_import.log';
	public $remoteID = '';

	const REMOTE_IDENTIFIER = 'xmlfolder_';	

	function writeLog( $message, $newlogfile = '')
	{
		if($newlogfile)
			$logfile = $newlogfile;
		else
			$logfile = $this->logfile;
		
		$this->logger->write( self::REMOTE_IDENTIFIER.$this->current_row->getAttribute('id').': '.$message , $logfile );
	}
	
	/* (non-PHPdoc)
	 * @see XmlHandlerPHP::geteZAttributeIdentifierFromField()
	 */
	public function geteZAttributeIdentifierFromField()
	{
		$field_name = $this->current_field->getAttribute('name');
		
		switch ( $field_name )
		{
			case 'shortname':
				return 'short_name';

			case 'showsubitems':
				return 'show_children';

			case 'publishdate':
				return 'publish_date';
						
			default:
				return $field_name; 
		}
	}
	
	/* (non-PHPdoc)
	 * @see XmlHandlerPHP::getValueFromField()
	 */
	public function getValueFromField( eZContentObjectAttribute $contentObjectAttribute )
	{
		switch( $this->current_field->getAttribute('name') )
		{
			case 'publishdate':
			{
				$return_unix_ts = time();
				
				$us_formated_date = $this->current_field->nodeValue;
				$parts = explode('/', $us_formated_date );
				
				if( count( $parts ) == 3)
				{
					$return_unix_ts = mktime( 0,0,0, $parts[0], $parts[1] , $parts[2] );
				}
				return $return_unix_ts;
				
			}
			break;
			
			case 'short_description':
			case 'description':
			{
				$xml_text_parser = new XmlTextParser();
				$xmltext = $xml_text_parser->Html2XmlText( $this->current_field->nodeValue );

				if($xmltext !== false)
				{
					return $xmltext;
				}
				else
				{
					//TODO: add logging
					return "";
				}
				
			}
			break;
			
			default:
			{
				return $this->current_field->nodeValue;
			}
		}
	}
	
	/* (non-PHPdoc)
	 * @see SourceHandler::getParentNode()
	 */
	public function getParentNode()
	{
		$id = self::REMOTE_IDENTIFIER . $this->current_row->getAttribute( 'parent_id' );
		return eZContentObjectTreeNode::fetchByRemoteID( $id );
	}

	public function getDataRowId()
	{
		return self::REMOTE_IDENTIFIER . $this->current_row->getAttribute('id');
	}

	public function getTargetContentClass()
	{
		return 'folder';
	}

	public function readData()
	{
		return $this->parse_xml_document( 'extension/data_import/dataSource/examples/folder_structure.xml', 'all' );
	}

}

?>