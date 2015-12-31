<?php

class Html2XmlText
{
	protected $error_messages;

	/**
	 * Use eZ Publish parser to translate a given HMTL to XMLTEXT syntax
	 *
	 * @param string $html
	 * @return string
	 */
	public function execute( $html )
	{
		// Reset error messages
		$this->error_messages = null;

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
					$fullMessage = 'eZSimplifiedXMLInputParser: ';

					foreach( $parser->getMessages() as $message )
					{
						$fullMessage .= $message;
					}

					$this->error_messages[] = $fullMessage;
				}
			}
		}

		// Either cleanUpHtml failed or ezp parser failed
		$return = '<?xml version="1.0" encoding="utf-8"?>'. "\n" .'<section xmlns:image="http://ez.no/namespaces/ezpublish3/image/" xmlns:xhtml="http://ez.no/namespaces/ezpublish3/xhtml/" xmlns:custom="http://ez.no/namespaces/ezpublish3/custom/"><paragraph xmlns:tmp="http://ez.no/namespaces/ezpublish3/temporary/"><literal class="html">'. htmlspecialchars( $html, ENT_XML1 ) .'</literal></paragraph></section>';

		return $return;
	}

	/**
	 * Return false if it fails to clean up the given HTML
	 *
	 * @param $html
	 * @return bool|string
	 */
	protected function cleanUp( $html )
	{
		// Not fully implemented
		return $html;

		// Parse given HTML with DOMDocument::loadHTML
		libxml_use_internal_errors( true );

		$doc = new DOMDocument();
		$doc->loadHTML( $html );

		$errors = libxml_get_errors();

		libxml_clear_errors();
		libxml_use_internal_errors( false );

		if( empty( $errors ) )
		{
			// Get html in body tag from parsed loadHtml doc
			$xpath = new DOMXPath( $doc );
			$bodyNode = $xpath->query( '//body/' )->item(0);

			var_dump( $doc->saveXML( $bodyNode ) );
			die('contains body tag :(');

		}
		else
		{
			foreach( $errors as $error)
			{
				$this->error_messages[] =
					trim( $error->message ) . ' in line ' .
					$error->line . ' column ' .
					$error->column;
			}
		}

		// remove unsupported tags

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

		//return strip_tags( $html, '<p><a><i><em><b><br><h1><h2><h3><h4><h5><h6><strong><li><ul><ol>' );
	}

	public function getErrorMessages()
	{
		return $this->error_messages;
	}

}
