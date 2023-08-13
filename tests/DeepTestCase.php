<?php

namespace Tests;

use GraphQL\Client;

class DeepTestCase extends \PHPUnit\Framework\TestCase
{
	public Client $graphQLClient;

	function __construct(string $name)
	{
		$dotenv = \Dotenv\Dotenv::createImmutable(__DIR__);
		$dotenv->load();
		$url = $_ENV['GQL_URN'] ?: 'http://localhost:3006/gql';
		$token = $_ENV['BEARER_TOKEN'] ?: '';
		$this->graphQLClient = $this->makeDeepClient($token, $url, 0);
		parent::__construct($name);
	}

}