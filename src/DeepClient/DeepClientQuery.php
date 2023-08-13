<?php

namespace DeepFoundation\DeepClient;
class DeepClientQuery
{
	public static function generate_query_data(array $options): callable
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

	public static function generate_query(array $options): array
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
}