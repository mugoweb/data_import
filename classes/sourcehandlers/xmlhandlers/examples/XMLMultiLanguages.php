<?php

/**
 * Make sure you have following languages installed in ezp before running this example:
 * - eng-GB
 * - fre-FR
 * - ger-DE
 * 
 * @author pek
 *
 */
class XMLMultiLanguages extends XmlHandlerPHP
{
	/**
	 * @var unknown_type
	 */
	var $handlerTitle = 'Multi Languages Handler';

	/**
	 * @var unknown_type
	 */
	var $current_loc_info = array();

	const REMOTE_IDENTIFIER = 'xmlmultilanguage_';	

	
	// mapping for xml field name and attribute name in ez publish
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
	
	/* (non-PHPdoc)
	 * @see SourceHandler::getValueFromField()
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
				
				break;
			}
			
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
	
	function getDataRowId()
	{
		return self::REMOTE_IDENTIFIER . $this->current_row->getAttribute('id');
	}

	function getTargetLanguage()
	{
		return $this->current_row->getAttribute( 'language' );
	}

	function getTargetContentClass()
	{
		return 'folder';
	}

	function readData()
	{
		return $this->parse_xml_document( 'extension/data_import/dataSource/examples/multilanguages.xml', 'all' );
	}

}
