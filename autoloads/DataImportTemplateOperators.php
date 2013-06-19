<?php

class DataImportTemplateOperators
{
	function operatorList()
	{
		return array(
				'to_string'
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
							$operatorValue = str_replace( '<?xml version="1.0" encoding="utf-8"?>' . "\n", '', $value );
						}
						break;
						
						case 'ezmatrix':
						{
							$value = $operatorValue->toString();
							$operatorValue = str_replace( '&', '&amp;', $value );
						}
						break;
						
						default:
							$operatorValue = $operatorValue->toString();
					}
				}
			}
			break;
	
			default:
		}
	}
}

?>