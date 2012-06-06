<?php

class XMLFolders extends XmlHandlerPHP
{
	var $handlerTitle = 'Folders Handler';

	var $current_loc_info = array();

	var $logfile = 'folders_import.log';

	var $remoteID = "";

	const REMOTE_IDENTIFIER = 'xmlfolder_';	

	function Folders()
	{}

	function writeLog( $message, $newlogfile = '')
	{
		if($newlogfile)
			$logfile = $newlogfile;
		else
			$logfile = $this->logfile;
		
		$this->logger->write( self::REMOTE_IDENTIFIER.$this->current_row->getAttribute('id').': '.$message , $logfile );
	}
	
	// mapping for xml field name to an attribute name in ez publish
	function geteZAttributeIdentifierFromField()
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
	
	// handles xml fields before storing them in ez publish
	function getValueFromField()
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
	
	// logic where to place the current content node into the content tree
	function getParentNodeId()
	{
		$parent_id = 2; // fallback is the root node
		
		$parent_remote_id = $this->current_row->getAttribute('parent_id');

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

	function getDataRowId()
	{
		return self::REMOTE_IDENTIFIER.$this->current_row->getAttribute('id');
	}

	function getTargetContentClass()
	{
		return 'folder';
	}

	function readData()
	{
		return $this->parse_xml_document( 'extension/data_import/dataSource/examples/folder_structure.xml', 'all' );
	}

	function post_publish_handling( $eZ_object, $force_exit )
	{
	    $force_exit = false;		
		return true;
	}

}

?>