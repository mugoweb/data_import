<?php

/**
 * @author pkamps
 *
 */
class SkipIfExists extends ImportOperator
{
	protected function update_eZ_object( $remoteID, $targetLanguage = null )
	{
		$this->cli->output( $this->cli->stylize( 'yellow', 'already exists, ' ), false );

		// causing the ImportOperator to ignore the entry
		$this->current_eZ_version = null;

		return true;
	}
}
