<?php namespace MicrosoftTranslator;

abstract class ResponseAbstract
{
	protected $endpoint;
	protected $url_parameters;
	protected $query_parameters;
	protected $try_to_auth;
	protected $url;
	protected $api_version;
	protected $result;
	protected $type;

	/**
	 * @param string  $endpoint
	 * @param array   $url_parameters
	 * @param array   $query_parameters
	 * @param boolean $try_to_auth
	 * @param string  $url
	 * @param mixed   $result
	 */
	public function __construct( $endpoint , $url_parameters , $query_parameters , $try_to_auth , $url , $result )
	{
		$this->endpoint         = $endpoint;
		$this->url_parameters   = $url_parameters;
		$this->query_parameters = $query_parameters;
		$this->try_to_auth      = $try_to_auth;
		$this->url              = $url;
		$this->result           = $result;
	}

	/**
	 * @return string
	 */
	public function __toString()
	{
		return json_encode( $this->result );
	}

	/**
	 * Get the HTTP response with code and body
	 *
	 * @return array
	 */
	public function getResponse()
	{
		return $this->result;
	}

	/**
	 * Get the HTTP code response
	 *
	 * @return int
	 */
	public function getCode()
	{
		return @(int)$this->result[ 'http_code' ];
	}

	/**
	 * Get the HTTP body response
	 *
	 * @return mixed
	 */
	public function getBody()
	{
		return @$this->result[ 'http_body' ];
	}

	/**
	 * Get the response type
	 *
	 * Can be :
	 * - item
	 * - collection
	 * - paginator
	 * - result
	 *
	 * @return string
	 */
	public function getType()
	{
		return $this->type;
	}

}

