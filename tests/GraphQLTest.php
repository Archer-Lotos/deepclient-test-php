<?php

use GraphQL\QueryBuilder\QueryBuilder;
use GuzzleHttp\Client as GuzzleClient;
use GraphQL\Client;

class GraphQLTest extends PHPUnit\Framework\TestCase
{
	function make_deep_client($token, $GQL_URN, $GQL_SSL): Client
	{
		if (!$token) {
			throw new InvalidArgumentException("No token provided");
		}

		$url = ($GQL_SSL) ? "https://$GQL_URN" : "http://$GQL_URN";
		$httpClient = new GuzzleClient(['base_uri' => $url]);

		return new Client(
			$GQL_URN,
			['Authorization' => "Bearer $token"],
			[],
			$httpClient
		);
	}

	function test()
	{
		$url = 'https://3006-deepfoundation-dev-8obbvtvqm0y.ws-eu102.gitpod.io/gql';
		$token = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJodHRwczovL2hhc3VyYS5pby9qd3QvY2xhaW1zIjp7IngtaGFzdXJhLWFsbG93ZWQtcm9sZXMiOlsibGluayJdLCJ4LWhhc3VyYS1kZWZhdWx0LXJvbGUiOiJsaW5rIiwieC1oYXN1cmEtdXNlci1pZCI6Ijk3NCJ9LCJpYXQiOjE2OTAxOTg5NDN9.ChszX6XybPR2KEfNci7xEMoXwW4pwbIaVuagREkcf80';

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

		if ($response->isOk()) {
			$data = $response->getData();
			var_dump($data);
		} else {
			$errors = $response->getErrors();
			var_dump($errors);
		}

	}
}