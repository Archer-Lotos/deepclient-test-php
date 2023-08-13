<?php

namespace Tests;

use GraphQL\QueryBuilder\QueryBuilder;
use GuzzleHttp\Client as GuzzleClient;
use GraphQL\Client;
use InvalidArgumentException;

class GraphQLTest extends \PHPUnit\Framework\TestCase
{
	function make_deep_client($token, $url): Client
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

	function test()
	{
		$dotenv = \Dotenv\Dotenv::createImmutable(__DIR__);
		$dotenv->load();

		$url = $_ENV['GQL_URN'];
		$token = $_ENV['BEARER_TOKEN'];

		$deepClient = $this->make_deep_client($token, $url, 0);

		$builder = (new QueryBuilder('pokemon'))
			->setVariable('name', 'String', true)
			->setArgument('name', '$name')
			->selectField('id')
			->selectField('number')
			->selectField('name')
			->selectField(
				(new QueryBuilder('evolutions'))
					->selectField('id')
					->selectField('name')
					->selectField('number')
					->selectField(
						(new QueryBuilder('attacks'))
							->selectField(
								(new QueryBuilder('fast'))
									->selectField('name')
									->selectField('type')
									->selectField('damage')
							)
					)
			);

		$response = $deepClient->runQuery($builder);

		var_dump($response);

	}
}