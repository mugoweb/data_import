<?php

// Operator autoloading
$eZTemplateOperatorArray   = array();
$eZTemplateOperatorArray[] = array(
		'class' => 'DataImportTemplateOperators',
		'script' => 'extension/data_import/autoloads/DataImportTemplateOperators.php',
		'operator_names' => array(
				'to_string',
				'node_serialize'
));
  
?>
