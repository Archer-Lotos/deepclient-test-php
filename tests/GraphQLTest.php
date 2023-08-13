<?php

namespace Tests;

use GraphQL\QueryBuilder\QueryBuilder;
use GuzzleHttp\Client as GuzzleClient;
use GraphQL\Client;
use GraphQL\Query;
use InvalidArgumentException;

class GraphQLTest extends DeepTestCase
{
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
		$response = $this->graphQLClient->runQuery($query);

		var_dump($response->getData());

	}


	function testBoolExp(): void
	{
		$query = new Query('bool_exp');
		$query->setSelectionSet(['id']);
		$response = $this->graphQLClient->runQuery($query);

		var_dump($response->getData());
	}
}