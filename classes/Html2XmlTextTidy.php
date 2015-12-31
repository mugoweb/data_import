<?php

class Html2XmlTextTidy extends Html2XmlText
{
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
}
