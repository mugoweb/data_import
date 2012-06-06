<?php

include_once( 'kernel/classes/ezcontentcachemanager.php' );

class UpdateRelated extends ImportOperator
{
	
	var $updated_array;
	var $source_not_found = array();
	var $related_not_found = array();
	var $relations_done = 0;
	var $skipped = 0;
	
	function UpdateRelated( $handler )
	{
		parent::ImportOperator( $handler );
	}

	function run()
	{
		$this->source_handler->readData();
		$db = eZDB::instance();
		
		$force_exit = false;
		while( $row = $this->source_handler->getNextRow() && !$force_exit )
		{
			$article_id = intval($this->source_handler->getCol(0));
			$related_id = intval($this->source_handler->getCol(1));
			
			$source_object = $this->fetchEzObject($article_id);
			$related_object = $this->fetchEzObject($related_id);
            
            $relatedObjectIDArray = array();
            
            if($source_object && $related_object)
            {	            
	            $this->cli->output( 'Importing relation between "'.$this->cli->stylize( 'emphasize', $source_object->attribute('remote_id') ).'" and "'.$this->cli->stylize( 'emphasize', $related_object->attribute('remote_id') ).'"... ' , false );	
	            //echo "Importing relation between \"".$source_object->attribute('remote_id')."\" and \"".$related_object->attribute('remote_id')."\"... ";
	            
	            $relatedObjects = $source_object->relatedContentObjectArray( $source_object->attribute('current_version'), false, 0, array( 'AllRelations' => EZ_CONTENT_OBJECT_RELATION_COMMON ) );

		        foreach ( $relatedObjects as  $relatedObject )
		            $relatedObjectIDArray[] = $relatedObject->attribute( 'id' );

	            $db->begin();
	            
	            if ( $source_object->attribute('id') == $related_object->attribute('id') )
	            {
	            	$this->cli->output( '..'.$this->cli->stylize( 'gray', "equal id's, skipping\n" ), false );
	            	//echo "..equal id's, skipping\n";
	            	$this->skipped++;
	            }
	            elseif(in_array( $related_object->attribute('id'), $relatedObjectIDArray ))
	            {
	            	$this->cli->output( '..'.$this->cli->stylize( 'gray', "objects allready related, skipping\n" ), false );
	            	//echo "..objects allready related, skipping\n";
	            	$this->skipped++;
	            }
	            else
	            {
	            	$source_object->addContentObjectRelation( $related_object->attribute('id'), $source_object->attribute('current_version') );
	            	eZContentCacheManager::clearContentCacheIfNeeded( $source_object->attribute('id') );
	            	$this->cli->output( '..'.$this->cli->stylize( 'green', "successfully\n" ), false );
	            	//echo "..successfully\n";
	            	$this->relations_done++;
	            }
	               
	            $db->commit();
	            
	            unset($relatedObjectIDArray);
            }
            else
            {
            	//echo "Unable to fetch ";
            	
            	if(!$source_object)
            	{
            		$this->source_not_found[] = $article_id;
            		//echo "source article ".$article_id." ";
            	}
            		
            	if(!$related_object)
            	{
            		$this->related_not_found[] = $related_id;
            		//echo "related article ".$related_id." ";
            	}
            		
            	//echo "..skipping\n";*/
            }

		}
		
		$this->cli->output( $this->cli->stylize( 'emphasize', "\nCompleted!\n" ), false );
		$this->cli->output( $this->cli->stylize( 'emphasize', "Relations done: ".$this->relations_done."\n" ), false );
		$this->cli->output( $this->cli->stylize( 'emphasize', "Skipped: ".$this->skipped."\n" ), false );
		$this->cli->output( $this->cli->stylize( 'emphasize', "Source articles missing: ".count($this->source_not_found)."\n" ), false );
		$this->cli->output( $this->cli->stylize( 'emphasize', "Related articles missing: ".count($this->related_not_found)."\n" ), false );
		
		/*
		echo "\nCompleted!\n";
		echo "Relations done: ".$this->relations_done."\n";
		echo "Skipped: ".$this->skipped."\n";
		echo "Source articles missing: ".count($this->source_not_found)."\n";
		echo "Related articles missing: ".count($this->related_not_found)."\n";
		*/
	}
	
	function fetchEzObject($id)
	{
		$prefixes = array('review','comparison','carnews','autoshow','feature','column','daily_auto_insider');
		$eZ_object = false;
		
		foreach($prefixes as $prefix)
		{
			$remote_id = $prefix.'_'.$id;
			
			if(!$eZ_object)
				$eZ_object = eZContentObject::fetchByRemoteID( $remote_id );
		}
		
		return $eZ_object;
	}

}

?>