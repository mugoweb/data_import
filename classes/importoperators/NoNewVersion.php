<?php

/**
 * @author pkamps
 *
 */
class NoNewVersion extends ImportOperator
{
	
	/**
	 * @var boolean
	 */
	protected $do_publish;
	
	
	/* (non-PHPdoc)
	 * @see ImportOperator::update_eZ_object()
	 */
	protected function update_eZ_object( $remoteID, $targetLanguage = null )
	{
		$this->cli->output( 'updating, ' , false );
		$this->do_publish = false;
		$this->current_eZ_version = $this->current_eZ_object->attribute( 'current' );

		return true;
	}

	protected function create_eZ_object( $remoteId, $targetLanguage = null )
	{
		$this->do_publish = true;
		return parent::create_eZ_object( $remoteId, $targetLanguage );
	}
	
	/* (non-PHPdoc)
	 * @see ImportOperator::publish_eZ_node()
	 */
	protected function publish_eZ_node( &$force_exit )
	{
		if( $this->do_publish )
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
			
		return $this->source_handler->post_publish_handling( $force_exit );
	}
	
}
