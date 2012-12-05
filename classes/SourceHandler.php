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
	protected $parameters;
	
	public $logger = false;
	public $db;
	public $node_priority = false;
	
	public function getPriorityForNode()
	{
		return $this->node_priority;
	}

	/**
	 * @param array $parameters
	 */
	public function init( array $parameters )
	{
		$this->parameters = $parameters;
		return $this;
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

	/**
	 * Get the class attribute identifier for the current field
	 * 
	 * @return string
	 */
	public function geteZAttributeIdentifierFromField()
	{
		return '';
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
	 * 
	 * @return int
	 */
	public function getParentNodeId()
	{
		return 2;
	}

	/*
	 * Logic how to build the remote id
	 * 
	 * @return string
	 */
	public function getDataRowId()
	{
		return $this->idPrepend . 'actual_id_value';
	}

	/**
	 * @return string
	 */
	public function getTargetContentClass()
	{
		return 'folder';
	}

	/**
	 * Language idenfier
	 * 
	 * @return string
	 */
	public function getTargetLanguage()
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
	
	/**
	 * Returns an array of ez publish object state ids.
	 * 
	 * @return multitype:
	 */
	public function getStateIds()
	{
		return array();
	}
}

?>