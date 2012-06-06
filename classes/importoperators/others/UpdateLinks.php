<?php

class UpdateLinks extends ImportOperator
{
	
	var $updated_array;
	var $last_node_id = 0;
	var $prefixes = array('review','comparison','carnews','autoshow','feature','column','daily_auto_insider');
	var $error_ids = array();
	var $remote_id = 0;
	var $report_file_suffix = 'default';
	var $logger = false;
	
	function save_ids( $error_ids )
	{
		$this->error_ids[$this->remote_id] = array_merge($this->error_ids[$this->remote_id], $error_ids);
	}
	
	function create_report()
	{
		$report_file = 'var/log/inline_links_report_'.$this->report_file_suffix.'.txt';
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
	
	function UpdateLinks( $handler, $suffix = 'default' )
	{
		$this->logger = new eZLog();
		$this->report_file_suffix = $suffix;
		parent::ImportOperator( $handler );
	}

	function run()
	{
		$this->source_handler->readData();

		while( $row = $this->source_handler->getNextRow() )
		{
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
		
		//$this->create_report();
	}
	
	function update_inline_links()
	{
		$dataMap  = $this->current_eZ_object->attribute( 'data_map' );
		$objectID =  $this->current_eZ_object->attribute( 'contentobject_id' );
		
		while( $this->source_handler->getNextField() )
		{
			$contentObjectAttribute = $dataMap[ $this->source_handler->geteZAttributeIdentifierFromField() ];
			if( $contentObjectAttribute )
			{
				if($contentObjectAttribute->attribute( 'data_type_string' ) == 'ezxmltext')
				{
					$value = $this->source_handler->getValueFromField();
					$value = $this->specialLinkHandling( $value );
					$value = $this->specialXmlTextHandling( $value, $objectID );
					
					//echo $value;
					
					// fromString returns false - even when it is successfull
					// create a bug report for that
					$contentObjectAttribute->fromString( $value );
					$contentObjectAttribute->store();
				}
			}
			else
			{
				echo "\n".'eZ Attribute ('.$this->source_handler->geteZAttributeIdentifierFromField().') does not exist.';
				exit;
			}
		}
	}
	
	function specialLinkHandling( $xml )
	{
		preg_match_all('/\<cad\_link(.*?)\/\>/', $xml, $cad_links, PREG_PATTERN_ORDER);
		
		//print_r($cad_links);
		
		$error = false;
		$replaced = 0;
		$error_ids=array();
		
		foreach($cad_links[1] as $string_key => $cad_link)
		{		
			preg_match_all('/(.*?)\=\"(.*?)\"/', $cad_link, $attr_array, PREG_PATTERN_ORDER);
			
			foreach($attr_array[1] as $attribute_key => $attribute_name)
			{
				$attributes[trim($attribute_name)] = trim($attr_array[2][$attribute_key]);
			}
			
			if($attributes['id'])
			{
				// Try to get eZ Object
				$eZ_object = false;
				
				foreach($this->prefixes as $prefix)
				{
					$remote_id = $prefix.'_'.$attributes['id'];
					
					if($attributes['page_number'] && (int)$attributes['page_number'] > 1)
						$remote_id = $remote_id . '_'.$attributes['page_number'];
					
					if(!$eZ_object)
						$eZ_object = eZContentObject::fetchByRemoteID( $remote_id );
				}
				
				if($eZ_object)
				{
					$string = $cad_links[0][$string_key];
					
					if($attributes['name'])
						$name = $attributes['name'];
					else
						$name = $eZ_object->attribute('name');
					
					$replace = '<link node_id="'.$this->get_non_bg_node( $eZ_object ).'">'.$name.'</link>';
					
					$xml = str_replace($string, $replace, $xml);
					$replaced++;
				}
				else
				{
					if($attributes['name'])
					{
						$string = $cad_links[0][$string_key];
						$name = $attributes['name'];
						$replace = $name;
						$xml = str_replace($string, $replace, $xml);
					}	
					
					$error_ids[] = $attributes['id'];
					$this->logger->write( 'Remote article-id '.$attributes['id'].' not found in object '.$this->current_eZ_object->attribute( 'remote_id' ).' ('.$this->current_eZ_object->attribute('name').')', 'inline_links.log' );
					//$error .= "..failed (id=".$attributes['id'].")..";
				}
			}
			else
			{
				$error .= "..found cad_link but missing attribute 'id'..";
			}
		}
		
		$this->save_ids($error_ids);
		
		if(count($cad_links[0]) == 0)
		{
			$this->cli->output( '..'.$this->cli->stylize( 'gray', "skipped" ), false );
			//echo "..skipped";
		}
		else
		{
			if(count($error_ids)>0)
			{
				$this->cli->output( '..'.$this->cli->stylize( 'red', "failed (ids: ".implode(",", $error_ids).")" ), false );
				//echo "..failed (".implode(",", $error_ids).")";
			}
			if($replaced > 0)
			{
				$this->cli->output( '..'.$this->cli->stylize( 'green', "success ".'('.$this->cli->stylize( 'yellow', $replaced )." links)" ), false );
				//echo "..success (".$replaced." links updated)";
			}
		}
		

			
		
		return $xml;
	}
	
	function get_non_bg_node( $eZ_object )
	{
		$return_node_id = $eZ_object->attribute('main_node_id');
		$node_assignmets = $eZ_object->attribute('assigned_nodes');
		
		if( is_array( $node_assignmets ) )
		{
			foreach( $node_assignmets as $node )
			{
				$path = $node->attribute('path_array');
				
				if( $path[2] != 63 ) // hey got a spot somewhere outside BG
				{
					$return_node_id = $path[ count( $path ) -1 ];
					break;
				}
			}
		}
		
		return $return_node_id;
	}
}

?>