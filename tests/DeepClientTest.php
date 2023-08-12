<?php

use DeepFoundation\DeepClient\DeepClient;
use DeepFoundation\DeepClient\DeepClientOptions;
use GuzzleHttp\Client as GuzzleHttpClient;

class DeepClientTest extends PHPUnit\Framework\TestCase
{
	function test()
	{
		$url = 'https://3006-deepfoundation-dev-8obbvtvqm0y.ws-eu102.gitpod.io/gql';
		$headers = [
			'Authorization' => 'Bearer eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJodHRwczovL2hhc3VyYS5pby9qd3QvY2xhaW1zIjp7IngtaGFzdXJhLWFsbG93ZWQtcm9sZXMiOlsibGluayJdLCJ4LWhhc3VyYS1kZWZhdWx0LXJvbGUiOiJsaW5rIiwieC1oYXN1cmEtdXNlci1pZCI6Ijk3NCJ9LCJpYXQiOjE2OTAxOTg5NDN9.ChszX6XybPR2KEfNci7xEMoXwW4pwbIaVuagREkcf80'

		];

		$guzzleClient = new GuzzleHttpClient();

		$options = new DeepClientOptions($guzzleClient);
		$client = new DeepClient($options);
	}
}