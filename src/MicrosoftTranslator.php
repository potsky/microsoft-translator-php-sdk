<?php

class PSKMicrosoftTranslatorAutoLoader
{
	/**
	 * @param string $class the class name
	 */
	static function autoload( $class )
	{
		$elements = explode( '\\' , $class );

		if ( @$elements[0] === 'MicrosoftTranslator' )
		{
			/** @noinspection PhpIncludeInspection */
			require __DIR__ . DIRECTORY_SEPARATOR . 'MicrosoftTranslator' . DIRECTORY_SEPARATOR . @$elements[1] . '.php';
		}
	}
}

spl_autoload_register( array( 'PSKMicrosoftTranslatorAutoLoader' , 'autoload' ) , true , true );
