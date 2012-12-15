<?php

/**
 * @author pkamps
 *
 */
class NoNewVersion extends ImportOperator
{
	/* (non-PHPdoc)
	 * @see ImportOperator::update_eZ_node()
	 */
	protected function update_eZ_node( $remoteID, $targetLanguage = null )
	{
		$this->cli->output( 'updating ' , false );
				
		$this->do_publish = false;
		$this->current_eZ_version = $this->current_eZ_object;
		return true;
	}

	/* (non-PHPdoc)
	 * @see ImportOperator::publish_eZ_node()
	 */
	protected function publish_eZ_node( &$force_exit )
	{
		if( $this->storeMode == 'create' )
		{
			eZOperationHandler::execute(
					'content',
					'publish',
					array(
							'object_id' => $this->current_eZ_object->attribute( 'id' ),
							'version'   => $this->current_eZ_version->attribute( 'version' ),
					)
			);
		}
			
		return $this->source_handler->post_publish_handling( $this->current_eZ_object, $force_exit );
	}
	
}

?>