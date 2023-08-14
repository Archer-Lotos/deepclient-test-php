<?php

namespace Tests;

use Exception;

class DeepClientSOTest extends DeepTestCase
{
	/**
	 * @var null
	 */
	protected $deepClient = null;

	/**
	 * @throws Exception
	 */
	function testSelect()
	{
		$dotenv = \Dotenv\Dotenv::createImmutable(__DIR__);
		$dotenv->load();

		$this->deepClient = make_deep_client(
			$_ENV['BEARER_TOKEN'] ?: '',
			$_ENV['GQL_URN'] ?: 'http://localhost:3006/gql'
		);

		var_dump($this->deepClient->select(100));
	}
}