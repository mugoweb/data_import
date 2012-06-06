<?php

class KeywordsOperator extends ImportOperator
{
	
	var $updated_array;
	var $uri = false;
	var $metadata = false;

	function KeywordsOperator( $handler )
	{
		parent::ImportOperator( $handler );
	}
	
	function update_metadata()
	{
		$node = eZContentObjectTreeNode::fetchByURLPath( $this->uri, true );
		
		if( $node )
		{
			$object = $node->attribute('object');
			
			$this->cli->output( 'Updating keywords for remote object id ('.$this->cli->stylize( 'emphasize', $object->attribute('remote_id') ).') ... ' , false );

			$data_map = $object->attribute('data_map');
			
			if( $data_map['meta_keyword'] )
			{
				$data_map['meta_keyword']->fromString( $this->metadata );
				$data_map['meta_keyword']->store();
				
				$object->store();
				
				eZContentCacheManager::clearContentCache( $object->attribute('id') );
				
				$this->cli->output( '..'.$this->cli->stylize( 'green', 'successfully'."\n" ), false );
			}
			else
			{
				$this->cli->output( '..'.$this->cli->stylize( 'red', 'meta_keyword attribute not found.'."\n" ), false );
			}
		}
		else
		{
			$this->cli->output( '..'.$this->cli->stylize( 'red', 'unable to fetch object for uri '.$this->uri.'.'."\n" ), false );
		}
		
	}
		
	function cleanURI( $uri )
	{
		$uri = str_replace( 'http://caranddriver.dnsalias.com/', '', $uri );
		
		return $uri;
	}
	
	function run()
	{
		$this->source_handler->readData();
		
		while( $row = $this->source_handler->getNextRow() )
		{
			$this->current_eZ_object = null;
			$this->uri = false;
			$this->metadata = false;
			
			
			while( $field = $this->source_handler->getNextField() )
			{	
				$value = $field->get_content();
				
				switch( $field->tagname )
				{
					case 'Page':
						$this->uri = $this->cleanURI( $value );
						break;
						
					case 'Keyword':
						$this->metadata = $value;
						break;
					
					default:
						break;
				}
			}

			if( $this->uri && $this->metadata )
				$this->update_metadata();

		}
	}
	
}

?>