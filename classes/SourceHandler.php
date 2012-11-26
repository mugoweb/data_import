<?php

/**
 * @author pkamps
 *
 */
class SourceHandler
{
	public $data;
	public $current_row;
	public $current_field;
	public $idPrepend = 'remoteID_';
	public $handlerTitle = 'Abstract Handler';
	
	public $logger = false;
	public $db;
	public $node_priority = false;
	
	public function getPriorityForNode()
	{
		return $this->node_priority;
	}

	/**
	 * Gets the next row from object var 'data'
	 * It may be necessary to implement a point for the data row
	 * Sets and returns the object var 'current_row'
	 * Returns false if no more rows are available
	 * 
	 * @return NULL
	 */
	public function getNextRow()
	{
		$this->current_row = null;
		return $this->current_row;
	}

	/**
	 * // sets the internal point to next field or return false
	 * 
	 * @return boolean
	 */
	public function getNextField()
	{
		return false;
	}

	public function geteZAttributeIdentifierFromField()
	{
		return 'eZ Attribute Identifier';
	}

	/**
	 * "fromString" value from data source for an eZ Attribute
	 * 
	 * @param eZContentObjectAttribute $contentObjectAttribute
	 * @return string
	 */
	public function getValueFromField( eZContentObjectAttribute $contentObjectAttribute )
	{
		return ''; 
	}

	/*
	 * You may want to implement a smart logic in order
	 * to return an existing parent node in your content tree
	 */
	public function getParentNodeId()
	{
		return 2;
	}

	/*
	 * Logic how to build the remote id
	 */
	public function getDataRowId()
	{
		return $this->idPrepend . 'Get id for current row';
	}

	function getTargetContentClass()
	{
		return 'eZ Content Class identifier';
	}

	function getTargetLanguage()
	{
		return null;
	}

	/*
	 * Read the data source
	 * Can be an xml file, csv file or queries to a remote DB
	 * Sets object var "data"
	 */
	function readData()
	{
		$this->data = null;
	}

	function read_file( $location )
	{
		$content = '';

		$handle = fopen( $location , 'r');
		if ( $handle )
		{
			while (!feof($handle))
			{
				$content .= fgets($handle, 40960);
			}
			fclose( $handle );
		}
		else
		{
			echo 'Could not open file '.$location.'.'."\n";
			exit;
		}

		return $content;
	}


	/*
	 * Method is called after all attributes are saved and
	 * before the node gets published
	 */
	function post_save_handling( $eZ_object, $force_exit )
	{
		$force_quit = false;
		return true;
	}
	
	/*
	 * The method is called after the node was published
	 */
	function post_publish_handling( $eZ_object, $force_exit )
	{
		$force_quit = false;
		return true;
	}

	
	function updatePublished($eZ_object)
	{
		return false;
	}
	
	/**
	 * Returns an array of eZContentObject attribute values like
	 * publish_data, owner etc
	 * 
	 * @return multitype:
	 */
	public function getEzObjAttributes()
	{
		return array();
	}
}

?>