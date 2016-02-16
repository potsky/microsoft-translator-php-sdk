<?php namespace MicrosoftTranslator;

interface LoggerInterface
{
	/**
	 * Info message
	 *
	 * @param string $object
	 * @param string $category
	 * @param string $message
	 *
	 * @return bool
	 */
	public function info( $object , $category , $message );

	/**
	 * Warning message
	 *
	 * @param string $object
	 * @param string $category
	 * @param string $message
	 *
	 * @return bool
	 */
	public function warning( $object , $category , $message );

	/**
	 * Debug message
	 *
	 * @param string $object
	 * @param string $category
	 * @param string $message
	 *
	 * @return bool
	 */
	public function debug( $object , $category , $message );

	/**
	 * Error message
	 *
	 * @param string $object
	 * @param string $category
	 * @param string $message
	 *
	 * @return bool
	 */
	public function error( $object , $category , $message );

	/**
	 * fatal message and throw an MicrosoftTranslator\Exception
	 *
	 * @param string $object
	 * @param string $category
	 * @param string $message
	 *
	 * @throws \MicrosoftTranslator\Exception
	 */
	public function fatal( $object , $category , $message );

}