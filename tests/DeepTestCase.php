<?php

namespace Tests;

use GraphQL\Client;
use GuzzleHttp\Client as GuzzleClient;
use InvalidArgumentException;

class DeepTestCase extends \PHPUnit\Framework\TestCase
{
	public Client $graphQLClient;

	function makeDeepClient($token, $url): Client
	{
		if (!$token) {
			throw new InvalidArgumentException("No token provided");
		}
		$httpClient = new GuzzleClient(['base_uri' => $url]);
		return new Client(
			$url,
			['Authorization' => "Bearer $token"],
			[],
			$httpClient
		);
	}
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