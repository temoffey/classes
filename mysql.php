<?php

	class mysql {

		private $mysql = null;
		private $config = array();
		private $tables = array();
		private $fields = array();
		private $keys = array();

		public static function is_assoc(array $array) {
			$keys = array_keys($array);
			return array_keys($keys) !== $keys;
		}

		public static function of_arrays(array $array) {
			return is_array(current($array));
		}

		public function connect() {
			$this->mysql = mysqli_connect($this->config['host'], $this->config['login'], $this->config['password']);
			if (!$this->mysql) {
				return $this->mysql;
			}
			mysqli_set_charset($this->mysql, $this->config['charaster']);
			mysqli_select_db($this->mysql, $this->config['database']);
			return $this;
		}

		public function from($table) {
			if (!empty($table)) {
				if (is_array($filter)) {
					
				} else {
					return ' FROM ' . $table;
				}
			}
		}

		public function where($table, $filter = null) {

			if (!empty($filter)) {
				if (is_array($filter)) {
					if ($this->is_assoc($filter)) {
						$filter = array(
							$filter
						);
					} else {
						if (of_arrays($filter)) {

						} else {
							if (empty($this->keys[$table])) {
								$this->fields($table);
							}
							$filter = array(
								array(
									$this->keys[$table] => $filter
								)
							);
						}
					}
				} else {
					if (empty($this->keys[$table])) {
						$this->fields($table);
					}
					$filter = array(
						array(
							$this->keys[$table] => $filter
						)
					);
				}

				$filters = array();

				foreach ($filter as $variant) {

					$filters = array();

					foreach ($variant as $key => $value) {
						if (is_array($value)) {
							if (count($value) == 2) {
								$varians[] = '`' . $key . '` >= "' . $value[0] . '"';
								$varians[] = '`' . $key . '` <= "' . $value[1] . '"';
							} else {
								$varians[] = '`' . $key . '` IN ("' . implode('", "', $value) . '")';
							}
						} elseif (is_string($value)) {
							$varians[] = '`' . $key . '` LIKE "' . $value . '"';
						} elseif (is_numeric($value)) {
							$varians[] = '`' . $key . '` = "' . $value . '"';
						} elseif (is_null($value)) {
							$varians[] = '`' . $key . '` IS NULL';
						}
					}

					$filters[] = implode(' AND ', $varians);
				}

				return ' WHERE ' . implode(' OR ', $filters);
			} else {
				return '';
			}
		}

		public function order($order = null) {
			if (!empty($order)) {

				if (is_array($order)) {
					if ($this->is_assoc($order)) {
						$orders = array();
						foreach ($order as $key => $value) {
							$orders[] = '`' . $key . '` ' . $value;
						}
						return ' ORDER BY ' . implode(', ', $orders);
					} else {
						return ' ORDER BY `' . implode('`, `', $order) . '`';
					}
				} else {
					return ' ORDER BY `' . $order . '`';
				}
			} else {
				return '';
			}
		}

		public function group($group = null) {
			if (!empty($group)) {
				if (is_array($group)) {
					return ' GROUP BY `' . implode('`, `', $group) . '`';
				} else {
					return ' GROUP BY `' . $group . '`';
				}
			} else {
				return '';
			}
		}

		public function limit($limit = null) {
			if (!empty($limit)) {
				if (is_array($limit)) {
					return ' LIMIT ' . $limit[0] . ' OFFSET ' . $limit[1];
				} else {
					return ' LIMIT ' . $limit;
				}
			} else {
				return '';
			}
		}

		public function tables() {
			if (empty($this->tables)) {
				$sql = 'SHOW TABLES';
				$result = mysqli_query($this->mysql, $sql);
				if (!$result) {
					return $result;
				}
				$return = array();
				if (mysqli_num_rows($result) > 0) {
					while ($row = mysqli_fetch_row($result)) {
						$return[] = $row[0];
					}
				}
				$this->tables = $return;
			}
			return $this->tables;
		}

		public function fields($table) {
			if (empty($this->fields[$table])) {
				$sql = 'SHOW COLUMNS FROM `' . $table . '`';
				$result = mysqli_query($this->mysql, $sql);
				if (!$result) {
					return $result;
				}
				$return = array();
				if (mysqli_num_rows($result) > 0) {
					while ($row = mysqli_fetch_row($result)) {
						$return[$row[0]] = $row[1];
						if ($row[3] == "PRI") {
							$this->keys[$table] = $row[0];
						}
					}
				}
				$this->fields[$table] = $return;
			}
			return $this->fields[$table];
		}

		public function count($table, $filter = null, $order = null, $group = null, $limit = null) {

			if (empty($this->keys[$table])) {
				$this->fields($table);
			}

			$sql = 'SELECT COUNT(' . $this->keys[$table] . ') FROM ' . $table;

			$sql .= $this->where($table, $filter);

			$sql .= $this->order($order);

			$sql .= $this->group($group);

			$sql .= $this->limit($limit);

			$result = mysqli_query($this->mysql, $sql);
			if (!$result) {
				return $result;
			}
			$row = mysqli_fetch_row($result);
			return current($row);
		}

		public function check($table, $filter = null) {
			return $this->count($table, $filter) > 0;
		}

		public function sql($sql) {
			$result = mysqli_query($this->mysql, $sql);
			if (!$result) {
				return $result;
			}
			$return = array();
			if (mysqli_num_rows($result) > 0) {
				while ($row = mysqli_fetch_assoc($result)) {
					$return[] = $row;
				}
			}
			return $return;
		}

		public function select($table, $filter = null, $order = null, $group = null, $limit = null) {

			if (empty($this->keys[$table])) {
				$this->fields($table);
			}

			$sql = 'SELECT * FROM ' . $table;

			$sql .= $this->where($table, $filter);

			$sql .= $this->order($order);

			$sql .= $this->group($group);

			$sql .= $this->limit($limit);

			$result = mysqli_query($this->mysql, $sql);
			if (!$result) {
				return false;
			}
			$return = array();
			if (mysqli_num_rows($result) > 0) {
				while ($row = mysqli_fetch_assoc($result)) {
					$return[$row[$this->keys[$table]]] = $row;
				}
			}
			return $return;
		}

		public function keys($table, $filter = null, $order = null, $group = null, $limit = null) {
			return $this->check($table, $filter) ? array_keys($this->select($table, $filter, $order, $group, $limit)) : false;
		}

		public function ids($table, $filter = null, $order = null, $group = null, $limit = null) {
			return $this->keys($table, $filter, $order, $group, $limit);
		}

		public function row($table, $filter = null, $order = null, $group = null) {
			return $this->check($table, $filter) ? current($this->select($table, $filter, $order, $group, 1)) : false;
		}

		public function id($table, $filter = null, $order = null, $group = null) {
			return $this->check($table, $filter) ? current($this->ids($table, $filter, $order, $group, 1)) : false;
		}

		public function delete($table, $filter = null) {

			$sql = 'DELETE FROM ' . $table;

			$sql .= $this->where($table, $filter);

			return mysqli_query($this->mysql, $sql);
		}

		public function insert($table, $data, $update = false) {

			if ($this->of_arrays($data)) {
				$fields = array_keys(current($data));
			} else {
				$fields = array_keys($data);
			}

			$sql = 'INSERT IGNORE INTO ' . $table . ' (`' . implode('`, `', $fields) . '`) VALUES ';

			if ($this->of_arrays($data)) {
				$rows = array();
				foreach ($data as $row) {
					foreach ($row as $key => $value) {
						$row[$key] = mysqli_real_escape_string($this->mysql, $value);
					}
					$rows[] = '("' . implode('", "', $row) . '")';
				}
				$sql .= implode(',', $rows);
			} else {
				foreach ($data as $key => $value) {
					$data[$key] = mysqli_real_escape_string($this->mysql, $value);
				}
				$sql .= '("' . implode('", "', $data) . '")';
			}

			$result = mysqli_query($this->mysql, $sql);
			return $return ? mysqli_insert_id($this->mysql, $this->mysql) : $return;
		}

		public function update($table, $data, $filter = null) {

			$sql = 'UPDATE ' . $table . ' SET ';

			$values = array();
			foreach ($data as $key => $value) {
				$values[] = '`' . $key . '` = "' . mysqli_real_escape_string($this->mysql, $value) . '"';
			}
			$sql .= implode(',', $values);

			$sql .= $this->where($table, $filter);

			return mysqli_query($this->mysql, $sql);
		}

		public function error() {
			return mysqli_error($this->mysql);
		}

		public function __construct($config) {
			$this->config = $config;
			if (empty($this->config['charaster'])) {
				$this->config['charaster'] = 'utf8';
			}
			$this->connect();
			$this->tables();
		}

		public static function factory($config) {
			return new mysql($config);
		}
	}

?>