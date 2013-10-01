<?php

class DataImportTemplateOperators
{
	function operatorList()
	{
		return array(
				'to_string',
				'node_serialize'
		);
	}
	
	function namedParameterPerOperator()
	{
		return true;
	}
	
	function namedParameterList()
	{
		return array();
	}
	
	function modify( $tpl,
			$operatorName,
			$operatorParameters,
			$rootNamespace,
			$currentNamespace,
			&$operatorValue,
			$namedParameters )
	{
		switch ( $operatorName )
		{
			case 'to_string':
			{
				if( $operatorValue instanceof eZContentObjectAttribute )
				{
					switch( $operatorValue->attribute( 'data_type_string' ) )
					{
						case 'ezxmltext':
						{
							$value = $operatorValue->toString();
							$operatorValue = preg_replace( '/(<\?xml .*?\?>)/', '', $value );
						}
						break;
						
						// translate relative webpath to full url
						case 'ezimage':
						{
							$value = $operatorValue->toString();
							$parts = explode( '|', $value );
							$url   = $parts[0];
							
							if( $url )
							{
								$sys = eZSys::instance();
								$url = $sys->serverURL() . $sys->wwwDir() . '/' . $url;
								$value = $url . '|' . $parts[1];
							}
							
							$operatorValue = str_replace( '&', '&amp;', $value );
						}
						break;
						
						default:
						{
							$value = $operatorValue->toString();
							$operatorValue = str_replace( '&', '&amp;', $value );
						}
					}
				}
			}
			break;
			
			case 'node_serialize':
			{
				if( $operatorValue instanceof eZContentObjectTreeNode )
				{
					// make serialize happy
					$contentNodeIDArray = array();
					$contentNodeIDArray[ $operatorValue->attribute( 'node_id' ) ] = 'whatever';

					$domElement = $operatorValue->serialize( array(), $contentNodeIDArray );
					$operatorValue = $domElement->ownerDocument->saveXML( $domElement );
				}
				else
				{
					$operatorValue = '';
				}
			}
			break;
	
			default:
		}
	}
}

?>