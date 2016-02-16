<?php namespace MicrosoftTranslator;

class Tools
{
	const HTTP_URL_REPLACE        = 1;       // Replace every part of the first URL when there's one of the second URL
	const HTTP_URL_JOIN_PATH      = 2;       // Join relative paths
	const HTTP_URL_JOIN_QUERY     = 4;       // Join query strings
	const HTTP_URL_STRIP_USER     = 8;       // Strip any user authentication information
	const HTTP_URL_STRIP_PASS     = 16;      // Strip any password authentication information
	const HTTP_URL_STRIP_AUTH     = 32;      // Strip any authentication information
	const HTTP_URL_STRIP_PORT     = 64;      // Strip explicit port numbers
	const HTTP_URL_STRIP_PATH     = 128;     // Strip complete path
	const HTTP_URL_STRIP_QUERY    = 256;     // Strip query string
	const HTTP_URL_STRIP_FRAGMENT = 512;     // Strip any fragments (#identifier)
	const HTTP_URL_STRIP_ALL      = 1024;    // Strip anything but scheme and host


	/**
	 * Build an URL
	 * The parts of the second URL will be merged into the first according to the flags argument.
	 *
	 * @param mixed      $url     (Part(s) of) an URL in form of a string or associative array like parse_url() returns
	 * @param mixed      $parts   Same as the first argument
	 * @param int        $flags   A bitmask of binary or'ed HTTP_URL constants (Optional)self::HTTP_URL_REPLACE is the
	 *                            default
	 * @param array|bool $new_url If set, it will be filled with the parts of the composed url like parse_url() would
	 *                            return
	 *
	 * @return string
	 */
	public static function httpBuildUrl( $url , $parts = array() , $flags = self::HTTP_URL_REPLACE , &$new_url = false )
	{
		$keys = array( 'user' , 'pass' , 'port' , 'path' , 'query' , 'fragment' );

		// self::HTTP_URL_STRIP_ALL becomes all the self::HTTP_URL_STRIP_Xs
		if ( $flags & self::HTTP_URL_STRIP_ALL )
		{
			$flags |= self::HTTP_URL_STRIP_USER;
			$flags |= self::HTTP_URL_STRIP_PASS;
			$flags |= self::HTTP_URL_STRIP_PORT;
			$flags |= self::HTTP_URL_STRIP_PATH;
			$flags |= self::HTTP_URL_STRIP_QUERY;
			$flags |= self::HTTP_URL_STRIP_FRAGMENT;
		}
		// self::HTTP_URL_STRIP_AUTH becomes self::HTTP_URL_STRIP_USER and self::HTTP_URL_STRIP_PASS
		else if ( $flags & self::HTTP_URL_STRIP_AUTH )
		{
			$flags |= self::HTTP_URL_STRIP_USER;
			$flags |= self::HTTP_URL_STRIP_PASS;
		}

		// Parse the original URL
		$parse_url = (array)parse_url( $url );

		// Scheme and Host are always replaced
		if ( isset( $parts[ 'scheme' ] ) )
		{
			$parse_url[ 'scheme' ] = $parts[ 'scheme' ];
		}
		if ( isset( $parts[ 'host' ] ) )
		{
			$parse_url[ 'host' ] = $parts[ 'host' ];
		}

		// (If applicable) Replace the original URL with it's new parts
		if ( $flags & self::HTTP_URL_REPLACE )
		{
			foreach ( $keys as $key )
			{
				if ( isset( $parts[ $key ] ) )
				{
					$parse_url[ $key ] = $parts[ $key ];
				}
			}
		}
		else
		{
			// Join the original URL path with the new path
			if ( isset( $parts[ 'path' ] ) && ( $flags & self::HTTP_URL_JOIN_PATH ) )
			{
				if ( isset( $parse_url[ 'path' ] ) )
				{
					$parse_url[ 'path' ] = rtrim( str_replace( basename( $parse_url[ 'path' ] ) , '' , $parse_url[ 'path' ] ) , '/' ) . '/' . ltrim( $parts[ 'path' ] , '/' );
				}
				else
				{
					$parse_url[ 'path' ] = $parts[ 'path' ];
				}
			}

			// Join the original query string with the new query string
			if ( isset( $parts[ 'query' ] ) && ( $flags & self::HTTP_URL_JOIN_QUERY ) )
			{
				if ( isset( $parse_url[ 'query' ] ) )
				{
					$parse_url[ 'query' ] .= '&' . $parts[ 'query' ];
				}
				else
				{
					$parse_url[ 'query' ] = $parts[ 'query' ];
				}
			}
		}

		// Strips all the applicable sections of the URL
		// Note: Scheme and Host are never stripped
		foreach ( $keys as $key )
		{
			if ( $flags & (int)constant( 'self::HTTP_URL_STRIP_' . strtoupper( $key ) ) )
			{
				unset( $parse_url[ $key ] );
			}
		}

		$new_url = $parse_url;

		return ( ( isset( $parse_url[ 'scheme' ] ) ) ? $parse_url[ 'scheme' ] . '://' : '' )
		. ( ( isset( $parse_url[ 'user' ] ) ) ? $parse_url[ 'user' ] . ( ( isset( $parse_url[ 'pass' ] ) ) ? ':' . $parse_url[ 'pass' ] : '' ) . '@' : '' )
		. ( ( isset( $parse_url[ 'host' ] ) ) ? $parse_url[ 'host' ] : '' )
		. ( ( isset( $parse_url[ 'port' ] ) ) ? ':' . $parse_url[ 'port' ] : '' )
		. ( ( isset( $parse_url[ 'path' ] ) ) ? $parse_url[ 'path' ] : '' )
		. ( ( isset( $parse_url[ 'query' ] ) ) ? '?' . $parse_url[ 'query' ] : '' )
		. ( ( isset( $parse_url[ 'fragment' ] ) ) ? '#' . $parse_url[ 'fragment' ] : '' );
	}


	/**
	 * Get a key in a multi-dimension array with array dot notation
	 *
	 * @param array  $context the haystack
	 * @param string $name    the array dot key notation needle
	 *
	 * @return mixed|null
	 */
	public static function getArrayDotValue( array $context , $name )
	{
		$pieces = explode( '.' , $name );
		foreach ( $pieces as $piece )
		{
			if ( ! is_array( $context ) || ! array_key_exists( $piece , $context ) )
			{
				return null;
			}
			$context = $context[ $piece ];
		}

		return $context;
	}

	/**
	 * @param array $arr
	 *
	 * @return bool
	 */
	public static function isAssociativeArray( array $arr )
	{
		return array_keys( $arr ) !== range( 0 , count( $arr ) - 1 );
	}

	/**
	 * @param string $haystack
	 * @param string $needle
	 *
	 * @return bool
	 */
	public static function startsWith( $haystack , $needle )
	{
		return $needle === "" || strrpos( $haystack , $needle , -strlen( $haystack ) ) !== false;
	}

	/**
	 * @param string $haystack
	 * @param string $needle
	 *
	 * @return bool
	 */
	public static function  endsWith( $haystack , $needle )
	{
		return $needle === "" || ( ( $temp = strlen( $haystack ) - strlen( $needle ) ) >= 0 && strpos( $haystack , $needle , $temp ) !== false );
	}

	/**
	 * @param string $text
	 *
	 * @return string
	 */
	public static function removeUtf8Bom( $text )
	{
		$bom  = pack( 'H*' , 'EFBBBF' );
		$text = preg_replace( "/^$bom/" , '' , $text );

		return $text;
	}

}

