<?php

class SkipUpdates extends ImportOperator
{
	
	var $updated_array;

	function SkipUpdates( $handler )
	{
		parent::ImportOperator( $handler );
	}

	function update_eZ_node( $remoteID, $row, $targetContentClass )
	{
		
		$this->updated_array[$remoteID] = 1;
		
		$this->do_publish = false;
		
		return false;
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