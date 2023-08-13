<?php

namespace Tests;

use DeepFoundation\DeepClient\DeepClient;
use DeepFoundation\DeepClient\DeepClientOptions;

class DeepClientTest extends DeepTestCase
{
	public DeepClient $deepClient;
	public DeepClientOptions $deepClientOptions;
	function __construct(string $name)
	{
		parent::__construct($name);
		$this->deepClientOptions = new DeepClientOptions($gql_client=$this->graphQLClient);
		$this->deepClient = new DeepClient($options=$this->deepClientOptions);
	}

	function testSelect()
	{
		var_dump($this->deepClient->select(1));
	}
}