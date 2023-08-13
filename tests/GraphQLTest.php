<?php

namespace Tests;

use GraphQL\QueryBuilder\QueryBuilder;
use GuzzleHttp\Client as GuzzleClient;
use GraphQL\Client;
use GraphQL\Query;
use InvalidArgumentException;

class GraphQLTest extends \PHPUnit\Framework\TestCase
{
	private Client $deepClient;

	function __construct(string $name)
	{
		$dotenv = \Dotenv\Dotenv::createImmutable(__DIR__);
		$dotenv->load();
		$url = $_ENV['GQL_URN'];
		$token = $_ENV['BEARER_TOKEN'];
		$this->deepClient = $this->makeDeepClient($token, $url, 0);
		parent::__construct($name);
	}

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

	function testPromiseLinks(): void
	{
		$query = new Query('promise_links');
		$query->setSelectionSet(['id']);
		$response = $this->deepClient->runQuery($query);

		var_dump($response->getData());

	}

	/*
	function testPromiseLinks(): void
	{
		$query = new Query('promise_links');
		$query->setSelectionSet(['id']);
		$response = $this->deepClient->runQuery($query);

		var_dump($response->getData());
	}*/
}