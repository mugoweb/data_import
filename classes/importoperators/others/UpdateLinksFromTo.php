<?php

class UpdateLinksFromTo extends UpdateLinks
{
	var $from;
	var $to;
	var $iterator;
	var $below_msg_shown = false;
	var $above_msg_shown = false;
	var $logger = false;
	
	function UpdateLinksFromTo( $handler, $suffix = 'default', $from = 1, $to = 100 )
	{
		$this->logger = new eZLog();
		$this->report_file_suffix = $suffix;
		$this->from = $from;
		$this->to   = $to;
		$this->iterator = 0;

		parent::ImportOperator( $handler );
	}
	
	function create_report()
	{
		$report_file = 'var/log/inline_links_report_'.$this->report_file_suffix.'_'.$this->from.'_'.$this->to.'.txt';
		$fp = fopen($report_file, 'w+');
		fwrite($fp, "eZ RemoteID, Link ArticleID\n");
		
		foreach($this->error_ids as $remote_id => $error_ids)
		{
			foreach($error_ids as $error_id)
			{
				fwrite($fp, $remote_id.", ".$error_id."\n");
			}
		}
		
		fclose($fp);
		
		$this->cli->output( $this->cli->stylize( 'yellow', "Wrote report to ".$report_file."\n" ), false );
		//echo "Wrote report to ".$report_file."\n";
	}	

	function run()
	{
		$this->source_handler->readData();

		while( $row = $this->source_handler->getNextRow() )
		{
			$this->iterator++;

			if($this->iterator >= $this->from && $this->iterator <= $this->to)
			{
				$this->cli->output( $this->cli->stylize( 'gray', "(entry-".$this->iterator.")" ), false );
				
				$this->current_eZ_object = null;
				$this->current_eZ_version = null;
				
			    $remoteID           = $this->source_handler->getDataRowId();
				$targetContentClass = $this->source_handler->getTargetContentClass();
				$parentId 			= $this->source_handler->getParentNodeId();
				
				$this->current_eZ_object = eZContentObject::fetchByRemoteID( $remoteID );
				
				if( $this->current_eZ_object )
				{
					$this->cli->output( 'Updating remote object ('.$this->cli->stylize( 'emphasize', $remoteID ).') with inline-links ... ' , false );	
					//echo 'Updating remote object ('.$remoteID.') with inline-links ... ';
	
					$this->remote_id = $remoteID;
					$this->update_inline_links();
					
					$this->current_eZ_object->store();
					
					eZContentCacheManager::clearContentCache( $this->current_eZ_object->attribute('id') );
				
					echo "\n";
				}
				else
				{
					echo "unable to fetch object, skipping..\n";
				}
						
			}
			else
			{
				if( $this->iterator < $this->from && !$this->below_msg_shown )
				{
					$this->cli->output( $this->cli->stylize( 'yellow', "Pointer below start-entry point (".$this->from.")...\n" ), false );
					//echo "Pointer below start-entry point (".$this->from.")...\n";
					$this->below_msg_shown = true;
					usleep(300);
				}
				if( $this->iterator > $this->to && !$this->above_msg_shown )
				{
					$this->cli->output( $this->cli->stylize( 'yellow', "Pointer above start-entry point (".$this->to.")...\n" ), false );
					//echo "Pointer above start-entry point (".$this->to.")...\n";
					$this->above_msg_shown = true;
					usleep(300);
				}
			}
		}
		
		//$this->create_report();
	}
	
}

?>