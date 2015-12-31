<?php

class csvHandler extends SourceHandler
{
	public $handlerTitle = 'Comma-Separated-Value Handler';
	public $source_file = 'some.csv';
	public $delimiter = ',';
	public $enclosure = '"';
	public $ignore_first_row = true;
	
	/**
	 * mapping is the key for this script
	 * it maps a col number to an eZ Attribute
	 * zero based index
	 *
	 * @var array
	 */
	protected $mapping = array(
		0 => 'title',
		3 => 'body'
	);

	protected $arrayPointer = -1;

	/**
	 * init the data, read from file, etc.
	 */
	public function readData()
	{
		if( !file_exists( $this->source_file ) )
		{
			die( "Can not proceed: Can not locate data file: '" . $this->source_file . "'" );
		}

		$this->data = fopen( $this->source_file , 'r' );

		return true;
	}

	/**
	 * use the file handler to navigate in the CSV file
	 */
	public function getNextRow()
	{
		$this->node_priority = false;
		
		// start from the beginning
		reset( $this->mapping );
		$this->arrayPointer = -1;

		if( $this->ignore_first_row )
		{
			$this->ignore_first_row = false;
			fgetcsv( $this->data , 100000, $this->delimiter, $this->enclosure ); // nirvana
		}

		$this->current_row = fgetcsv(
			$this->data,
			100000,
			$this->delimiter,
			$this->enclosure
		);

		return $this->current_row;
	}

	/**
	 * use php array pointers -- to bad there is not way
	 * to get the current pointer position
	 *
	 * @return string
	 */
	public function getNextField()
	{
		$this->arrayPointer++;

		if( $this->arrayPointer )
		{
			return next( $this->mapping );
		}
		else
		{
			return current( $this->mapping );
		}
	}

	/**
	 * returns the content-class attribute-name
	 *
	 * @return string
	 */
	function geteZAttributeIdentifierFromField()
	{
		return current( $this->mapping );
	}

	/**
	 * @param eZContentObjectAttribute $contentObjectAttribute
	 * @return mixed
	 */
	public function getValueFromField( eZContentObjectAttribute $contentObjectAttribute )
	{
		return $this->current_row[ key( $this->mapping ) ];
	}

}