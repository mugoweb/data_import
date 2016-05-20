<?php 

class eZXMLImportHtml extends eZXMLHandlerPHP
{
	public function getValueFromField( eZContentObjectAttribute $contentObjectAttribute )
	{
		var_dump( $this->current_field );

		switch( $this->current_field->getAttribute( 'id' ) )
		{
			case 'body':
			{
				$html = $this->current_field->nodeValue;

				$parser = new Html2XmlTextDefault();

				$eZXmlText = $parser->execute( $html );

				$errors = $parser->getErrorMessages();

				if( !empty( $errors ) )
				{
					foreach( $errors as $error )
					{
						$this->log( $this->getDataRowId() . ' : ' . $error, true, 'html2xmltext.log' );
					}
				}

				return $eZXmlText;
			}
			break;

			default:
				return parent::getValueFromField( $contentObjectAttribute );
		}
	}

	/* (non-PHPdoc)
	 * @see SourceHandler::readData()
	*/
	public function readData()
	{
		return $this->parse_xml_document( 'extension/data_import/dataSource/examples/import_html.ezxml', 'all' );
	}
}
