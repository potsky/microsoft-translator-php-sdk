<?php namespace MicrosoftTranslator;

interface AuthInterface
{
	/**
	 * Get an access token
	 *
	 * If available, the stored access token will be used
	 * If not available or if $force_new is true, a new one will be generated
	 *
	 * @param bool|false $force_new
	 */
	public function getAccessToken( $force_new = false );

	/**
	 * @return \MicrosoftTranslator\GuardInterface
	 */
	public function getGuard();
}