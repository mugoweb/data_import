<?php

class SkipDuplicates extends ImportOperator
{
	
	var $updated_array;

	function SkipDuplicates( $handler )
	{
		parent::ImportOperator( $handler );
	}

	function update_eZ_node( $remoteID, $row, $targetContentClass )
	{
		if( !empty($this->updated_array[$remoteID]) )
		{
			return false;
		}
		else
		{
			$this->updated_array[$remoteID] = 1;
			return parent::update_eZ_node( $remoteID, $row, $targetContentClass );
		}
	}
	
	
	function create_eZ_node( $remoteID, $row, $targetContentClass )
	{
		if( !empty($this->updated_array[$remoteID]) )
		{
			return false;
		}
		else
		{
			$this->updated_array[$remoteID] = 1;
			return parent::create_eZ_node( $remoteID, $row, $targetContentClass );
		}
	}
}

?>