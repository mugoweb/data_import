<?php

class Html2XmlTextDefault extends Html2XmlText
{
	/** @var string */
	protected $xslFile = 'extension/data_import/xslt/ezoe.xsl';

	/** @var  array */
	static protected $xsltErrors;

	/**
	 * Return false if it fails to clean up the given HTML
	 *
	 * @param $html
	 * @return bool|string
	 */
	protected function cleanUp( $html )
	{
		$html = '<!DOCTYPE html><html><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8"/><title>dummy</title></head><body>'. $html .'</body></html>';

		$config = array(
			'doctype' => 'omit',
			//'show-body-only' => true,
			'quote-ampersand' => true,
			'wrap' => 0,
			'hide-comments' => true,
		);

		$tidy = new tidy();
		$tidy->parseString( $html, $config, 'utf8');
		$tidy->cleanRepair();

		$html = tidy_get_output( $tidy );

		if( !empty( $tidy->errorBuffer ) )
		{
			$tidyErrorMessage = str_replace( "\n", ', ', $tidy->errorBuffer );
			$this->error_messages[] = 'Tidy: ' . $tidyErrorMessage;
		}

		return $html;
	}

	protected function simplify( $html )
	{
		// needs static context to store errors
		self::$xsltErrors = array();

		$xmlDoc = new DOMDocument();
		$xmlDoc->loadHTML( $html );

		$xslDoc = new DOMDocument();
		$xslDoc->load( $this->xslFile );

		$xsl = new XSLTProcessor();
		$xsl->registerPHPFunctions();
		$xsl->importStyleSheet( $xslDoc );

		$result = $xsl->transformToXML( $xmlDoc );

		// append xslt error messages
		$this->error_messages = array_merge( $this->error_messages, self::$xsltErrors );

		return $result;
	}

	public static function xsltLog( $message )
	{
		self::$xsltErrors[] = 'XSLT: ' . $message;
	}
}
