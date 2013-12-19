<?php

class eZXMLHandlerPHP extends XmlHandlerPHP
{
	/**
	 * @var string
	 */
	public $handlerTitle = 'ezxml Handler';
	
	/**
	 * Used to temporarily store image data
	 * 
	 * @var array
	 */
	protected $tmpFiles;
		
	/* (non-PHPdoc)
	 * @see XmlHandlerPHP::getNextField()
	 */
	public function getNextField()
	{
		if( $this->first_field )
		{
			$this->first_field = false;
			//TODO: probably likely to break to go by index 3
			$this->current_field = $this->current_row->childNodes->item(3)->firstChild;
		}
		else
		{
			$this->current_field = $this->current_field->nextSibling;
		}
	
		if( is_object( $this->current_field ) && $this->current_field->nodeType != 1 ) //ignore xml #text nodes
		{
			$this->current_field = $this->getNextValidNode( $this->current_field );
		}
	
		return $this->current_field;
	}
	
	/* (non-PHPdoc)
	 * @see XmlHandlerPHP::geteZAttributeIdentifierFromField()
	 */
	public function geteZAttributeIdentifierFromField()
	{
		return $this->current_field->getAttribute( 'id' );
	}
	
	/* (non-PHPdoc)
	 * @see XmlHandlerPHP::getValueFromField()
	 */
	public function getValueFromField( eZContentObjectAttribute $contentObjectAttribute )
	{

		switch( $this->current_field->getAttribute( 'type' ) )
		{
			case 'ezxmltext':
			{
				$value = $this->current_field->ownerDocument->saveXML( $this->current_field );
				
				// cut out surrounding <a> tag
				$value = preg_replace( '/<a .*?>/', '', $value );
				$value = preg_replace( '/<\/a>/', '', $value );
				
				return $value;
			}
			break;
			
			// create a temp copy of the remote file to local FS
			case 'ezimage':
			{
				$parts = explode( '|', str_replace( '&amp;', '&', $this->current_field->nodeValue ) );
				$filename = $parts[ 0 ];
				
				if( $filename )
				{
					$filename = $this->copyRemoteFileToLocalTemp( $filename );
				}
				
				return $filename . '|' . $parts[ 1 ];
			}
			break;
			
			default:
			{
				return str_replace( '&amp;', '&', $this->current_field->nodeValue );
			}
		}
	}
	
	/* (non-PHPdoc)
	 * @see SourceHandler::getParentNodeId()
	 */
	public function getDomNodes()
	{
		$return = array();
		
		foreach( $this->current_row->childNodes->item(1)->childNodes as $node_assignment )
		{
			$return[] = $node_assignment;
		}

		return $return;
	}

	/* (non-PHPdoc)
	 * @see SourceHandler::getDataRowId()
	 */
	public function getDataRowId()
	{
		return $this->current_row->getAttribute( 'id' );
	}

	/* (non-PHPdoc)
	 * @see SourceHandler::getTargetContentClass()
	 */
	public function getTargetContentClass()
	{
		return $this->current_row->getAttribute( 'class' );
	}

	/* (non-PHPdoc)
	 * @see SourceHandler::readData()
	 */
	public function readData()
	{
		// you need to implement it in a child class
	}

	/**
	 * @param string $uri
	 * @param array $info
	 * @return string
	 */
	protected function copyRemoteFileToLocalTemp( $uri )
	{
		$return = '';

		$ch = curl_init();

		curl_setopt($ch, CURLOPT_URL, $uri);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1 );
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0 );

		$content = curl_exec( $ch );
		$info	= curl_getinfo( $ch );

		if( curl_errno( $ch ) )
		{
			//log error
		}

		curl_close( $ch );

		if( $info[ 'http_code' ] != '200' )
		{
			$content = null;
		}

		if( $content )
		{
			// Assuming ezp image alias URLs
			$uriParts = explode( '/', $uri );
			$fileName = array_pop( $uriParts );
			
			$return = sys_get_temp_dir() . '/' . $fileName;

			// store reference to remove the file later
			$this->tmpFiles[] = $return;
			
			file_put_contents( $return, $content );
		}

		return $return;
	}
	
	function post_publish_handling( &$force_exit )
	{
		if( !empty( $this->tmpFiles ) )
		{
			foreach( $this->tmpFiles as $file )
			{
				unlink( $file );
			}
		}
		
		$force_quit = false;
		return true;
	}
	
}

?>
