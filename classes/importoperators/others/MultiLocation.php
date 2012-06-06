<?php

class MultiLocation extends ImportOperator
{
	
	var $updated_array;

	function MultiLocation( $handler )
	{
		parent::ImportOperator( $handler );
	}

	function update_eZ_node( $remoteID, $row, $targetContentClass )
	{
		// Assign object to node
			
		$node = $this->current_eZ_object->attribute('main_node');
		$parent_node = $node->attribute('parent');
		
		$parent_class = $parent_node->attribute('class_identifier');
		
		if($parent_class == 'sub_model')
		{
			$node_id = $node->attribute('node_id');
			$model = $parent_node->attribute('parent');
			$parent_node_id = $model->attribute('node_id');
			
		    include_once( 'kernel/classes/ezcontentobjecttreenodeoperations.php' );
		    if( !eZContentObjectTreeNodeOperations::move( $node_id, $parent_node_id ) )
		    	die( 'failed to move node to parent node'.$parent_node_id );
		    else
		    {
		    	$this->cli->output( $this->cli->stylize( 'yellow', '(found duplicate, moving to model)' ), false );
		    	//echo "(found duplicate, moving to model)";
		    }
		}
		
		return false;
		
		$this->do_publish = false;
		
		if($this->create_new_versions)
			$this->current_eZ_version = $this->current_eZ_object->createNewVersion();
		else
			$this->current_eZ_version = $this->current_eZ_object;
		
		return true;

		//parent::update_eZ_node( $remoteID, $row, $targetContentClass );
	}

}

?>