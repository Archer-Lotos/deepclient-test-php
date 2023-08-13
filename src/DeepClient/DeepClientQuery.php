<?php

namespace DeepFoundation\DeepClient;
class DeepClientQuery
{
	public static function generate_query_data_old(array $options): callable
	{
		return function (string $alias, $index) use ($options) {
			$defs = [];
			$args = [];
			foreach ($options['fields'] as $field) {
				$defs[] = '$' . $field . strval($index) . ': ' . $options['fieldTypes'][$field];
				$args[] = $field . ': $' . $field . strval($index);
			}

			$resultAlias = $alias . (is_int($index) ? strval($index) : '');
			$resultVariables = [];
			foreach ($options['variables'] as $v => $variable) {
				$resultVariables[$v . strval($index)] = $variable;
			}

			return [
				'tableName' => $options['tableName'],
				'operation' => $options['operation'] ?? 'query',
				'queryName' => $options['queryName'] ?? $options['tableName'],
				'returning' => $options['returning'] ?? 'id',
				'variables' => $options['variables'] ?? [],
				'resultReturning' => $options['returning'] ?? 'id',
				'fields' => $options['fields'],
				'fieldTypes' => $options['fieldTypes'],
				'index' => $index,
				'defs' => $defs,
				'args' => $args,
				'alias' => $alias,
				'resultAlias' => $resultAlias,
				'resultVariables' => $resultVariables,
			];
		};
	}

	public static function fields_inputs(string $table_name): array
	{
		return [
			'distinct_on' => '[' . $table_name . '_select_column!]',
			'limit' => 'Int',
			'offset' => 'Int',
			'order_by' => '[' . $table_name . '_order_by!]',
			'where' => $table_name . '_bool_exp!',
		];
	}

	public static function generate_query_old(array $options): array
	{
		$queries = $options["queries"] ?? [];
		$operation = $options["operation"] ?? "query";
		$name = $options["name"] ?? "QUERY";
		$alias = $options["alias"] ?? "q";

		$called_queries = array_map(function ($m, $i) use ($alias) {
			return is_callable($m) ? $m($alias, $i) : $m;
		}, $queries, array_keys($queries));

		$defs = implode(",", array_map(function ($m) {
			return implode(",", $m["defs"]);
		}, $called_queries));

		$query_body = implode(",", array_map(function ($m) {
			return "{$m['resultAlias']}: {$m['queryName']}(" . implode(",", $m['args']) . ") { {$m['resultReturning']} }";
		}, $called_queries));

		$query_string = "{$operation} {$name} ($defs) {{$query_body}}";
		$query = self::gql($query_string);

		$variables = [];
		foreach ($called_queries as $action) {
			foreach ($action["resultVariables"] as $v => $variable) {
				$variables[$v] = $variable;
			}
		}

		return [
			"query" => $query,
			"variables" => $variables,
			"query_string" => $query_string,
		];
	}

	public static function gql(string $query_string)
	{
	}

	public static function generateQuery($options) {
		$queries = $options['queries'] ?? [];
		$operation = $options['operation'] ?? 'query';
		$name = $options['name'] ?? 'QUERY';
		$alias = $options['alias'] ?? 'q';

		$calledQueries = array_map(function($m, $i) use ($alias){
			return is_callable($m) ? $m($alias, $i) : $m;
		}, $queries, array_keys($queries));

		$defs = join(',', array_map(function($m){
			return join(',', $m['defs']);
		}, $calledQueries));

		$queryString = "${operation} ${name} (${defs}) { " . join('',
				array_map(function($m) {
					return "${m['resultAlias']}: ${m['queryName']}(${join(',', $m['args'])}) { ${m['resultReturning']} }";
				}, $calledQueries)
			) . " }";

		$query = $queryString; //Replace this line with your own gql function in PHP
		$variables = [];

		foreach ($calledQueries as $action) {
			if (is_array($action['resultVariables'])) {
				foreach ($action['resultVariables'] as $v => $variable) {
					$variables[$v] = $variable;
				}
			}
		}

		$result = [
			'query' => $query,
			'variables' => $variables,
			'queryString' => $queryString
		];

		return $result;
	}

	public static function fieldsInputs($tableName): array {
		return [
			'distinct_on' => "[$tableName" . "_select_column!]",
			'limit' => "Int",
			'offset' => "Int",
			'order_by' => "[$tableName" . "_order_by!]",
			'where' => "$tableName" . "_bool_exp!"
		];
	}
	public static function generateQueryData($options) {
		$tableName = $options['tableName'];
		$operation = $options['operation'] ?? 'query';
		$queryName = $options['queryName'] ?? $tableName;
		$returning = $options['returning'] ?? 'id';
		$variables = $options['variables'];

		$fields = ['distinct_on', 'limit', 'offset', 'order_by', 'where'];
		$fieldTypes = self::fieldsInputs($tableName); //Need to implement fieldsInputs function

		return function ($alias, $index) use ($tableName, $operation, $queryName, $returning, $variables, $fields, $fieldTypes) {
			$defs = [];
			$args = [];

			for ($f = 0; $f < count($fields); $f++) {
				$field = $fields[$f];
				array_push($defs, "$" . $field . $index . ": " . $fieldTypes[$field]);
				array_push($args, $field . ": $" . $field . $index);
			}

			$resultAlias = $alias . (is_numeric($index) ? $index : '');
			$resultVariables = [];

			foreach ($variables as $v => $variable) {
				$resultVariables[$v . $index] = $variable;
			}

			$result = [
				'tableName' => $tableName,
				'operation' => $operation,
				'queryName' => $queryName,
				'returning' => $returning,
				'variables' => $variables,
				'resultReturning' => $returning,
				'fields' => $fields,
				'fieldTypes' => $fieldTypes,
				'index' => $index,
				'defs' => $defs,
				'args' => $args,
				'alias' => $alias,
				'resultAlias' => $resultAlias,
				'resultVariables' => $resultVariables,
			];

			return $result;
		};
	}
}