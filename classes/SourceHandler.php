<?php

/*
 * function SourceHandler()
 * function getNextRow()
 * function getNextField()
 * function geteZAttributeIdentifierFromField()
 * function getValueFromField()
 * function getParentNodeId()
 * function getDataRowId()
 * function getTargetContentClass()
 * function readData()
 * function read_file( $location )
 *
 *
 */

class SourceHandler
{
	var $data;
	var $current_row;
	var $current_field;
	var $idPrepend = 'remoteID_';
	var $handlerTitle = 'Abstract Handler';
	var $logger = false;
	var $db;
	var $node_priority = false;

	function __construct()
	{}
	
	function getPriorityForNode()
	{
		return $this->node_priority;
	}

	/*
	 * Gets the next row from object var 'data'
	 * It may be necessary to implement a point for the data row
	 * Sets and returns the object var 'current_row'
	 * Returns false if no more rows are available
	 */
	function getNextRow()
	{
		$this->current_row = null;
		return $this->current_row;
	}

	function getNextField()
	{
		// sets the internal point to next field or return false
		return false;
	}

	function geteZAttributeIdentifierFromField()
	{
		return 'eZ Attribute Identifier';
	}

	function getValueFromField()
	{
		return 'Value from data source for an eZ Attribute';
	}

	/*
	 * You may want to implement a smart logic in order
	 * to return an existing parent node in your content tree
	 */
	function getParentNodeId()
	{
		return 2;
	}

	/*
	 * Logic how to build the remote id
	 */
	function getDataRowId()
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
}

?>