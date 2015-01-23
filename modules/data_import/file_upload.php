<?php

die( 'not yet implemented - need a way to select an import handle for the file upload' );
$tpl = eZTemplate::factory();

if( $_REQUEST[ 'submit' ] && $_FILES[ 'file' ][ 'error' ] == false )
{
    $import_operator = new ImportOperator();
    $import_operator->cli->setIsQuiet(  true );
    
    $source_handler  = new eZXMLHootsuite();
    $source_handler->sourceFilePath = $_FILES[ 'file' ][ 'tmp_name' ];
    $source_handler->init();

    $import_operator->source_handler = $source_handler;
    $source_handler->import_operator = $import_operator;

    $import_operator->run();
}

$Result = array();
$Result['content'] = $tpl->fetch( 'design:modules/data_import/file_upload.tpl' );
