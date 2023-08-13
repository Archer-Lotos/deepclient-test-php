<?php
namespace DeepFoundation\DeepClient;

use Exception;
use GraphQL\Client;
use GraphQL\Query;

class DeepClient extends DeepClientCore
{
	/**
	 * @var DeepClientOptions|mixed
	 */
	public mixed $options;
	/**
	 * @var Client|mixed|null
	 */
	public mixed $graphQLClient;

	public function __construct($options = null)
	{
		if ($options === null) {
			$this->options = new DeepClientOptions();
		} else {
			$this->options = $options;
		}

		$this->graphQLClient = $this->options->gql_client ?? null;
	}

	const _boolExpFields = [
		"_and" => true,
		"_not" => true,
		"_or" => true,
	];

	public function path_to_where($start, ...$path): array
	{
		$pckg = is_string($start) ? ["type_id" => self::_ids["@deep-foundation/core"]["Package"], "value" => $start] : ["id" => $start];
		$where = $pckg;
		foreach ($path as $item) {
			if (!is_bool($item)) {
				$nextWhere = ["in" => ["type_id" => self::_ids["@deep-foundation/core"]["Contain"], "value" => $item, "from" => $where]];
				$where = $nextWhere;
			}
		}
		return $where;
	}

	public function type_to_name($value_type): string {
		if ($value_type === "integer" || $value_type === "int") {
			return 'number';
		}
		if ($value_type === "string") {
			return 'string';
		}
		return '';
	}

	/**
	 * @throws Exception
	 */
	public function serialize_where($exp, $env = 'links') {
		if (is_array($exp)) {
			return array_map(function($e) use ($env) {
				return $this->serialize_where($e, $env);
			}, $exp);
		} elseif (is_array($exp)) {
			$keys = array_keys($exp);
			$result = array();
			foreach ($keys as $key) {
				$key_type = gettype($exp[$key]);
				$setted = false;
				$is_id_field = in_array($key, ['type_id', 'from_id', 'to_id']);
				if ($env == 'links') {
					if ($key_type === 'string' || $key_type === 'integer') {
						if ($key === 'value' || $key === $this->type_to_name($key_type)) {
							$setted = $result[$this->type_to_name($key_type)] = ['value' => ['_eq' => $exp[$key]]];
						} else {
							$setted = $result[$key] = ['_eq' => $exp[$key]];
						}
					} elseif (!in_array($key, self::_boolExpFields ) && is_array($exp[$key])) {
						$setted = $result[$key] = $this->serialize_where($this->path_to_where(...$exp[$key]));
					}
				} elseif ($env == 'value') {
					if ($key_type === 'string' || $key_type === 'integer') {
						$setted = $result[$key] = ['_eq' => $exp[$key]];
					}
				}

				$ids = [
					'rule_id', 'action_id', 'subject_id', 'object_id',
					'link_id', 'tree_id', 'root_id', 'parent_id'
				];
				if (
					$key_type === 'array'
					&& isset($exp[$key]['_type_of'])
					&& (
						($env === 'links' && ($is_id_field || $key === 'id')) ||
						($env === 'selector' && $key === 'item_id') ||
						($env === 'can' && in_array($key, $ids)) ||
						($env === 'tree' && in_array($key, $ids)) ||
						($env === 'value' && $key === 'link_id')
					)
				) {
					$_temp = $setted = [
						'_by_item' => [
							'path_item_id' => ['_eq' => $exp[$key]['_type_of']],
							'group_id' => ['_eq' => 0]
						]
					];
					if ($key === 'id') {
						$result['_and'] = isset($result['_and']) ? [...$result['_and'], $_temp] : [$_temp];
					} else {
						$result[substr($key, 0, -3)] = $_temp;
					}
				} elseif (
					$key_type === 'array'
					&& isset($exp[$key]['_id'])
					&& (
						($env === 'links' && ($is_id_field || $key === 'id')) ||
						($env === 'selector' && $key === 'item_id') ||
						($env === 'can' && in_array($key, $ids)) ||
						($env === 'tree' && in_array($key, $ids)) ||
						($env === 'value' && $key === 'link_id')
					)
					&& is_array($exp[$key]['_id'])
					&& count($exp[$key]['_id']) >= 1
				) {
					$_temp = $setted = $this->serialize_where(
						$this->path_to_where($exp[$key]['_id'][0], ...array_slice($exp[$key]['_id'], 1)), 'links'
					);
					if ($key === 'id') {
						$result['_and'] = isset($result['_and']) ? [...$result['_and'], $_temp] : [$_temp];
					} else {
						$result[substr($key, 0, -3)] = $_temp;
					}
				}

				if (!$setted) {
					$_temp = (
						in_array($key, self::_boolExpFields) ? $this->serialize_where($exp[$key], $env) : (
							(isset(self::_serialize[$env]['relations'][$key])
								? $this->serialize_where($exp[$key], self::_serialize[$env]['relations'][$key]) : $exp[$key])
						)
					);
					if ($key === '_and') {
						$result['_and'] = isset($result['_and']) ? [...$result['_and'], ...$_temp] : $_temp;
					} else {
						$result[$key] = $_temp;
					}
            	}
			}
			return $result;
		} else {
			if ($exp === null) {
				throw new Exception('undefined in query');
			}
			return $exp;
		}
	}

	public static function serializeQuery($exp, $env = 'links'): array
	{
		$limit = $exp['limit'] ?? null;
		$order_by = $exp['order_by'] ?? null;
		$offset = $exp['offset'] ?? null;
		$distinct_on = $exp['distinct_on'] ?? null;
		$where = $exp;
		unset($where['limit'], $where['order_by'], $where['offset'], $where['distinct_on']);

		$result = ['where' => is_array($exp) ? (is_array($exp['id']) ? ['id' => ['_in' => $exp]] : serializeWhere($where, $env)) : ['id' => ['_eq' => $exp]]];

		if ($limit) $result['limit'] = $limit;
		if ($order_by) $result['order_by'] = $order_by;
		if ($offset) $result['offset'] = $offset;
		if ($distinct_on) $result['distinct_on'] = $distinct_on;
		return $result;
	}

	/**
	 * @throws Exception
	 */
	public function select($exp, $options = null) {
		if (!$exp) {
			return [
				'error' => ['message' => '!exp'],
				'data' => null,
				'loading' => false,
				'networkStatus' => null
			];
		}

		$query = self::serializeQuery($exp, $options['table'] ?? 'links');
		$table = $options['table'] ?? $this->options->table;
		$returning = $options['returning'] ??
			($table === 'links' ? $this->options->linksSelectReturning :
				(in_array($table, ['strings', 'numbers', 'objects']) ? $this->options->valuesSelectReturning :
					($table === 'selectors' ? $this->options->selectorsSelectReturning :
						($table === 'files' ? $this->options->filesSelectReturning : 'id'))));
		$variables = $options['variables'];
		$name = $options['name'] ?? $this->options->defaultSelectName;

		try {
			$q = $this->apolloClient->query(DeepClientQuery::generateQuery([
				'queries' => [
					DeepClientQuery::generateQueryData([
						'tableName' => $table,
						'returning' => $returning,
						'variables' => array_merge($variables, $query)
					])
				],
				'name' => $name
			]));

			return array_merge($q, ['data' => $q['data']['q0']]);
		} catch (Exception $e) {
			throw new Exception("DeepClient Select Error: " . $e->getMessage());
		}
	}

	public function default_returning(string $table): string
	{
		if ($table === 'links') {
			return $this->options->links_select_returning;
		} elseif (in_array($table, ['strings', 'numbers', 'objects'])) {
			return $this->options->values_select_returning;
		} elseif ($table === 'selectors') {
			return $this->options->selectors_select_returning;
		} elseif ($table === 'files') {
			return $this->options->files_select_returning;
		} else {
			return "id";
		}
	}

	/**
	 * @throws Exception
	 */
	public function id($start, ...$path) {
		if (is_array($start)) {
			$result = $this->select($start);
			$data = $result["data"];
			return $data[0]["id"] ?? null;
		}

		if (isset($this->_ids[$start][$path[0]])) {
			return $this->_ids[$start][$path[0]];
		}

		$where = $this->path_to_where($start, ...$path);
		$result = $this->select($where);
		if (isset($result["error"])) {
			throw new Exception($result["error"]["message"]);
		}

		$data = $result["data"];
		$result = $data[0]["id"] ?? null;
		if (!$result && $path[count($path) - 1] !== true) {
			throw new Exception("Id not found by " . json_encode([$start, ...$path]));
		}

		return $result;
	}
}