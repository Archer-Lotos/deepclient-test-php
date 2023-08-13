<?php

namespace Tests;

use GraphQL\Query;

class GraphQLTest extends DeepTestCase
{
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