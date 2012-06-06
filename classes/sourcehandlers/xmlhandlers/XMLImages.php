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
	
	// handles xml fields before storing them in ez publish
	function getValueFromField()
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
	
	// logic where to place the current content node into the content tree
	function getParentNodeId()
	{
		$parent_id = 2; // fallback is the root node
		
		$eZ_object = eZContentObject::fetchByRemoteID( 'xmlfolder_30' );

		if( $eZ_object )
		{
			$parent_id = $eZ_object->attribute('main_node_id');
		}

		return $parent_id;
	}

	function getDataRowId()
	{
		return self::REMOTE_IDENTIFIER.$this->current_row->getAttribute('id');
	}

	function getTargetContentClass()
	{
		return 'image';
	}

	function readData()
	{
		return $this->parse_xml_document( 'extension/data_import/dataSource/examples/images.xml', 'all' );
	}

	function post_publish_handling( $eZ_object, $force_exit )
	{
	    $force_exit = false;		
		return true;
	}

}

?>