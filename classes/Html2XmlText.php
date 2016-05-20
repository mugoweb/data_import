<?php

class Html2XmlText
{
	/** @var  array */
	protected $error_messages;

	/**
	 * Use eZ Publish parser to translate a given HMTL to XMLTEXT syntax the ezxml datatype understands
	 *
	 * @param string $html
	 * @return string
	 */
	public function execute( $html )
	{
		// Reset error messages
		$this->error_messages = array();

		$html = $this->cleanUp( $html );

		if( $html !== false )
		{
			$html = $this->simplify( $html );

			if( $html !== false )
			{
				// Parse resulting HTML with ezp parser
				$parser = new eZSimplifiedXMLInputParser( null );
				$document = $parser->process( $html );

				// Handle errors
				if( $document !== false )
				{
					return $document->saveXML();
				}
				else
				{
					$fullMessage = 'eZSimplifiedXMLInputParser:';

					foreach( $parser->getMessages() as $message )
					{
						$fullMessage .= ' ' . $message;
					}

					$this->error_messages[] = $fullMessage;
				}
			}
		}

		// Something in the process failed, so let's wrap the given HTML into a literal tag
		return '<?xml version="1.0" encoding="utf-8"?>'. "\n" .'<section xmlns:image="http://ez.no/namespaces/ezpublish3/image/" xmlns:xhtml="http://ez.no/namespaces/ezpublish3/xhtml/" xmlns:custom="http://ez.no/namespaces/ezpublish3/custom/"><paragraph xmlns:tmp="http://ez.no/namespaces/ezpublish3/temporary/"><literal class="html">'. htmlspecialchars( $html, ENT_XML1 ) .'</literal></paragraph></section>';
	}

	/**
	 * Return false if it fails to clean up the given HTML
	 *
	 * @param string $html
	 * @return bool|string
	 */
	protected function cleanUp( $html )
	{
		return $html;
	}

	/**
	 * ezoe cannot handle all HTML tags
	 *
	 * @param string $html
	 * @return string|boolean
	 */
	protected function simplify( $html )
	{
		return $html;
	}

	/**
	 * @return array
	 */
	public function getErrorMessages()
	{
		return $this->error_messages;
	}

}
