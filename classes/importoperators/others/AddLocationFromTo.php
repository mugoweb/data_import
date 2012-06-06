<?php

class AddLocationFromTo extends AddLocation
{
	
	var $from;
	var $to;
	var $iterator;
	var $below_msg_shown = false;
	var $above_msg_shown = false;
	
	function AddLocationFromTo( $handler, $from = 1, $to = 100 )
	{
		$this->from = $from;
		$this->to   = $to;
		$this->iterator = 0;
		
		parent::ImportOperator( $handler );
	}

	function run()
	{
		$this->source_handler->readData();
		
		$force_exit = false;
		while( $row = $this->source_handler->getNextRow() && !$force_exit )
		{
			$this->iterator++;
			
			if($this->iterator >= $this->from && $this->iterator <= $this->to)
			{
				$this->cli->output( $this->cli->stylize( 'gray', "(entry-".$this->iterator.")" ), false );
				//echo "(entry-".$this->iterator.") ";
				
				$this->current_eZ_object = null;
				$this->current_eZ_version = null;
				
			    $remoteID           = $this->source_handler->getDataRowId();
				$targetContentClass = $this->source_handler->getTargetContentClass();
					
				$this->cli->output( 'Importing remote object ('.$this->cli->stylize( 'emphasize', $remoteID ).') as eZ object ('.$this->cli->stylize( 'emphasize', $targetContentClass ).')... ' , false );
				//echo 'Importing remote object ('.$remoteID.') as eZ object ('.$targetContentClass.')... ';
				$exists = '';
				$this->current_eZ_object = eZContentObject::fetchByRemoteID( $remoteID );
	
				if( !$this->current_eZ_object )
				{
					$exists = 'created.';
					// Create new eZ publish object in Database
					$this->create_eZ_node( $remoteID, $row, $targetContentClass );
				}
				else
				{
					$exists = 'updated.';
					// Create new eZ Publish version for existing eZ Object
					$this->update_eZ_node( $remoteID, $row, $targetContentClass );
				}
	
				if( $this->current_eZ_object && $this->current_eZ_version )
				{
					$this->save_eZ_node();
	
					// Seems like post handling does not update assigned nodes etc if node is not published.
					if( $exists == 'created.' )
						$success = true;
					else
						$success = $this->source_handler->post_publish_handling( $this->current_eZ_object, $force_exit );

					if( $success )
					{
						$this->publish_eZ_node();

						// Make sure we run it after it is published.
						if( $exists == 'created.' )
						{
							$this->source_handler->post_publish_handling( $this->current_eZ_object, $force_exit );
							$this->setNodesPriority();
						}	
						$this->cli->output( '..'.$this->cli->stylize( 'green', 'successfully '.$exists."\n" ), false );
						//echo '..successfully '.$exists."\n";
					}
					else
					{
						$this->cli->output( '..'.$this->cli->stylize( 'red', 'not successful.'."\n" ), false );
						//echo '..not successful.'."\n"';
					}
				}
				else
				{
					$this->cli->output( '..'.$this->cli->stylize( 'gray', 'skipped.'."\n" ), false );
					//echo '..skipped '."\n";
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
	}

}

?>