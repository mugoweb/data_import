<?php

/*
 * /csv_export/index/<class_id>/<attr_id>-<attr_id>-<extraFieldIdentifier>/(language)/eng-GB
 * You can export any objects by class ID.
 * Then you need to specify the attribute IDs you like to export.
 * You have an extra ability to export non-attribute fields such as product_categories.
 *
*/

namespace Modules\CSVExport;

$classID           = (int) $Params[ 'class_id' ];
$classIdentifier   = \eZContentClass::classIdentifierByID( $classID );
$attributes_string = isset( $Params[ 'attribute_ids' ] ) ? $Params[ 'attribute_ids' ] : '';
$language          = $Params['language'];

$attribute_ids = explode( '-', $attributes_string );

header( "Content-Type: text/csv" );
header( "Content-Disposition: attachment; filename=translation_export_" . $classIdentifier . ".csv");
header( "Content-Description: csv File" );
header( "Pragma: no-cache" );
header( "Expires: 0" );

if( $classID && !empty( $attribute_ids ) )
{
	$objectRows = get_csv_content( $classID, $attribute_ids );
	
	if( !empty( $objectRows ) )
	{
		echo '"Object ID","Object Name","Attribute Name","Content","Translation"' . "\n";
		$fp = fopen( 'php://output', 'w' );		
		foreach( $objectRows as $attributeRows )
		{
            foreach( $attributeRows as $attributeRow )
            {
                fputcsv( $fp, $attributeRow );
            }
		}
	}
}
else
{
	die( 'Please specify a class ID and a list of attribute IDs' );
}


\eZExecution::cleanExit();

/*
 * Functions
 */
function get_csv_content( $classID, $attribute_ids )
{
	$return = array();
	
	$nodes = \eZFunctionHandler::execute( 'content', 'tree', array(
		'parent_node_id' => 1,
		'class_filter_type' => 'include',
		'class_filter_array' => array( $classID ),
        'language' => $language,
		'main_node_only' => true,
	) );

	if( !empty( $nodes) )
	{
		foreach( $nodes as $node )
		{
			$row = array();
            // Show the object name only once
            $objectNameShown = false;
			$data_map = $node->attribute( 'data_map' );
			
			foreach( $data_map as $attribute )
			{
				if( in_array( $attribute->attribute( 'contentclassattribute_id' ), $attribute_ids ) )
				{
					$content = '';
					switch( $attribute->attribute( 'data_type_string' ) )
					{
						case 'ezxmltext':
						{
							$content = $attribute->attribute( 'content' )->attribute( 'output' )->attribute( 'output_text' );
						}
						break;
					
						case 'ezimage':
						{
							$string = $attribute->toString();
							$parts = explode( '|', $string );
							$content = $parts[ 1 ];
						}
						break;
					
						default:
						{
							$content = $attribute->toString();
						}
					}
					if( !$objectNameShown )
                    {
                        $objectName = $node->attribute( 'name' );
                        $objectNameShown = true;
                    }
                    else
                    {
                        $objectName = '';
                    }
					$row[] = array( $node->attribute( 'contentobject_id' ), $objectName, $attribute->attribute( 'contentclass_attribute_name' ), $content );
				}
			}
			
			// Adding non-attribute fields
			foreach( $attribute_ids as $id )
			{
				//Check for non-int ids
				if( !(int) $id )
				{
					switch( $id )
					{
						case 'product_categories':
						{
                            if( !$objectNameShown )
                            {
                                $objectName = $node->attribute( 'name' );
                                $objectNameShown = true;
                            }
                            else
                            {
                                $objectName = '';
                            }
                            $assignedNodes = $node->attribute( 'object' )->attribute( 'assigned_nodes' );
                            $parentNames = array();
                            foreach( $assignedNodes as $assignedNode )
                            {
                                $parentNames[] = $assignedNode->attribute( 'parent' )->attribute( 'name' );
                            }
							$row[] = array( $node->attribute( 'contentobject_id' ), $objectName, "Product Categories", implode( ',', $parentNames ) );
						}
						break;
					
						default:
					}
				}
			}
			
			$return[] = $row;
		}
	}
	
	return $return;
}

?>