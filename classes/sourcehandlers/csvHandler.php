<?php

class csvHandler extends SourceHandler
{
	var $handlerTitle = 'Comma-Separated-Value Handler';
	var $source_file = 'some.csv';
	var $delimiter = ',';
	var $enclosure = '"';
	var $ignore_first_row = true;
	
	// mapping is the key for this script
	// it maps a col number to an eZ Attribute
	// starts with 0
	var $mapping = array( 0 => 'title',
	                      3 => 'body' );

	function csvHandler()
	{}

	/*
	 * init the data, read from file, etc.
	 */
	function readData()
	{
		if( !file_exists( $this->source_file ) )
		{
			die( "Can not proceed: Can not locate data file: '" . $this->source_file . "'" );
		}
		$this->m_aData = fopen( $this->source_file , 'r');
	}

	/*
	 * the ezp target-class identifier
	 */
	function getTargetContentClass()
	{
		return 'a_valid_class_identifier';
	}

	/*
	 * return eternal index of data row
	 */
	function getDataRowId()
	{
		return 'prefix_'.'id';
	}

	function getParentNodeId()
	{
		return 2;
	}

	/*
	 * use the file handler to navigate in the CSV file
	 */
	function getNextRow()
	{
		$this->node_priority = false;
		
		// set mapping array
		$this->first_field = true;
		reset( $this->mapping );

		if( $this->ignore_first_row )
		{
			$this->ignore_first_row = false;
			fgetcsv( $this->m_aData , 100000, $this->delimiter, $this->enclosure ); // nirvana
		}

		$this->row = fgetcsv( $this->m_aData , 100000, $this->delimiter, $this->enclosure);

		return $this->row;
	}

	/*
	 * don't know what this is for - perhaps if there is something ezp specific
	 * that needs to happen to the data object - like publishing it?
	 */
	function post_publish_handling( $eZ_object, $force_exit = false )
	{
		// in case it is necessary
		//echo "no post-handling today\n\n";
		return true;
	}

	/*
	 * use php array pointers
	 */
	function getNextField()
	{
		if( $this->first_field )
		{
			$this->first_field = false;
		}
		else
		{
			next( $this->mapping ); // nirvana
		}
		
		return current( $this->mapping );
	}

	/*
	 * return the content-class attribute-name
	 */
	function geteZAttributeIdentifierFromField()
	{
		return current( $this->mapping );
	}

	/* (non-PHPdoc)
	 * @see SourceHandler::getValueFromField()
	 */
	public function getValueFromField( eZContentObjectAttribute $contentObjectAttribute )
	{
		return $this->row[ key( $this->mapping ) ];
	}

}
?>