<?php namespace MicrosoftTranslator;

interface GuardInterface
{
	/**
	 * Tell if there is a valid stored Access Token
	 * It must answer false if there is one but it is expired
	 *
	 * @param array $param
	 *
	 * @return bool
	 */
	public function hasAccessToken( array $param = array() );

	/**
	 * Store an access token for a amount of time
	 *
	 * @param string $access_token
	 * @param int    $seconds
	 * @param array  $param
	 *
	 * @return bool
	 */
	public function storeAccessTokenForSeconds( $access_token , $seconds , array $param = array() );

	/**
	 * Get a stored Access Token
	 * It must answer only if the Access Token is valid and not expired
	 *
	 * @param array $param
	 *
	 * @return string
	 */
	public function getAccessToken( array $param = array() );

	/**
	 * Delete an Access Token
	 *
	 * @param array $param
	 *
	 * @return bool
	 */
	public function deleteAccessToken( array $param = array() );

	/**
	 * Remove all expired Access Tokens
	 *
	 * @param array $param
	 *
	 * @return int the count of deleted Access Tokens
	 */
	public function cleanAccessTokens( array $param = array() );

	/**
	 * Try to delete all stored Access Token
	 *
	 * @param array $param
	 *
	 * @return int the count of deleted Access Tokens
	 */
	public function deleteAllAccessTokens( array $param = array() );
}