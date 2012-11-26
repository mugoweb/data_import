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
}

?>