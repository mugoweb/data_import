<?php

class eZXMLHandlerPHP extends XmlHandlerPHP
{
	/**
	 * @var string
	 */
	public $handlerTitle = 'ezxml Handler';

	protected $fieldList;
	protected $rowList;
	protected $fieldPointer = -1;
	protected $rowPointer = -1;

	protected $rowCount;

	/**
	 * Used to temporarily store image data
	 * 
	 * @var array
	 */
	protected $tmpFiles;


	/**
	 *
	 * @return bool
	 */
	public function getNextRow()
	{
		// reset field pointer
		$this->fieldPointer = -1;
		$this->fieldList = null;

		$this->rowPointer++;

		$row = $this->getRowList()->item( $this->rowPointer );

		if( $row )
		{
			$this->current_row = $row;
			return true;
		}
		else
		{
			return false;
		}
	}

	protected function getRowList()
	{
		if( ! $this->rowList )
		{
			$xpath = new DOMXPath( $this->data->ownerDocument );

			$result = $xpath->query( '/all/o' );

			if( $result->length )
			{
				$this->rowList = $result;
			}
		}

		return $this->rowList;
	}

	public function getNextField()
	{
		$this->fieldPointer++;

		$field = $this->getFieldList()->item( $this->fieldPointer );

		if( $field )
		{
			$this->current_field = $field;
			return true;
		}
		else
		{
			return false;
		}
	}

	protected function getFieldList()
	{
		if( ! $this->fieldList )
		{
			$xpath = new DOMXPath( $this->current_row->ownerDocument );

			$result = $xpath->query( 'as/a', $this->current_row );

			if( $result->length )
			{
				$this->fieldList = $result;
			}
		}

		return $this->fieldList;
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

	/**
	 * @return DOMNodeList
	 */
	public function getNodeAssignments()
	{
		$xpath = new DOMXPath( $this->data->ownerDocument );

		return $xpath->query( 'ns/n', $this->current_row );
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
		return true;
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
			$this->log( 'Download failed: ' . $uri );
		}

		curl_close( $ch );

		if( $info[ 'http_code' ] != '200' )
		{
			$this->log( 'Download failed - does not exists: ' . $uri );
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

	public function getRowCount()
	{
		if( !isset( $this->rowCount ) )
		{
			$xpath = new DOMXPath( $this->data->ownerDocument );
			$this->rowCount = $xpath->query( '/all/o' )->length;

		}

		return $this->rowCount;
	}
}
