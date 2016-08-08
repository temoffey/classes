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

		public function connect() {
			$this->mysql = mysql_connect($this->config['host'], $this->config['login'], $this->config['password']);
			mysql_set_charset($this->config['charaster']);
			mysql_select_db($this->config['database'], $this->mysql);
			return $this;
		}

		public function where($table, $filter = null) {
			if (!empty($filter)) {
				if (is_array($filter)) {
					if ($this->is_assoc($filter)) {
						$filters = array();
						foreach ($filter as $key => $value) {
							if (is_array($value)) {
								if (count($value) == 2) {
									if (!empty($value[0])) {
										$filters[] = '`' . $key . '` >= "' . $value[0] . '"';
									}
									if (!empty($value[1])) {
										$filters[] = '`' . $key . '` <= "' . $value[1] . '"';
									}
								} elseif (count($value) > 2) {
									$vals = array();
									foreach ($value as $val) {
										if (!empty($val)) {
											$vals[] = $val;
										}
									}
									$filters[] = '`' . $key . '` IN ("' . implode('", "', $vals) . '")';
								}
							} else {
								$filters[] = '`' . $key . '` = "' . $value . '"';
							}
						}
						return ' WHERE ' . implode(' AND ', $filters);
					} else {
						return ' WHERE `' . $this->keys[$table] . '` IN ("' . implode('", "', $filter) . '")';
					}
				} else {
					return ' WHERE `' . $this->keys[$table] . '` = "' . $filter . '"';
				}
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
					return ' LIMIT ' . $limit[0] . ' OFFER ' . $limit[1];
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
				$result = mysql_query($sql);
				$return = array();
				if (mysql_num_rows($result) > 0) {
					while ($row = mysql_fetch_row($result)) {
						$return[] = $row[0];
					}
				}
				$this->tables = $return;
			}
			return $this->tables;
		}

		public function fields($table) {
			if (empty($this->fields[$table])) {
				$sql = 'SHOW COLUMNS FROM ' . $table;
				$result = mysql_query($sql);
				$return = array();
				if (mysql_num_rows($result) > 0) {
					while ($row = mysql_fetch_row($result)) {
						$return[] = $row[0];
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

			$result = mysql_query($sql);
			$row = mysql_fetch_row($result);
			return $row[0];
		}

		public function check($table, $filter = null) {
			return $this->count($table, $filter) > 0;
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

			$result = mysql_query($sql);
			$return = array();
			if (mysql_num_rows($result) > 0) {
				while ($row = mysql_fetch_assoc($result)) {
					$return[$row[$this->keys[$table]]] = $row;
				}
			}
			return $return;
		}

		public function keys($table, $filter = null, $order = null, $group = null, $limit = null) {
			return array_keys($this->select($table, $filter, $order, $group, $limit));
		}

		public function row($table, $filter = null, $order = null, $group = null) {
			return current($this->select($table, $filter, $order, $group, 1));
		}

		public function id($table, $filter = null, $order = null, $group = null) {
			return current($this->keys($table, $filter, $order, $group, 1));
		}

		public function delete($table, $filter = null) {

			$sql = 'DELETE FROM ' . $table;

			$sql .= $this->where($table, $filter);

			$result = mysql_query($sql);
			return $result !== false;
		}

		public function insert($table, $data, $update = false) {

			if (is_array(current($data))) {
				$fields = array_keys(current($data));
			} else {
				$fields = array_keys($data);
			}

			$sql = 'INSERT IGNORE INTO ' . $table . ' (`' . implode('`, `', $fields) . '`) VALUES ';

			if (is_array(current($data))) {
				$rows = array();
				foreach ($data as $row) {
					foreach ($row as $key => $value) {
						$row[$key] = mysql_real_escape_string($value);
					}
					$rows[] = '("' . implode('", "', $row) . '")';
				}
				$sql .= implode(',', $rows);
			} else {
				foreach ($data as $key => $value) {
					$data[$key] = mysql_real_escape_string($value);
				}
				$sql .= '("' . implode('", "', $data) . '")';
			}

			$result = mysql_query($sql);
			if ($result) {
				$id = mysql_insert_id($this->mysql);
			}
			return $id ? $id : ($result !== false);
		}

		public function update($table, $data, $filter = null) {

			$sql = 'UPDATE ' . $table . ' SET ';

			$values = array();
			foreach ($data as $key => $value) {
				$values[] = '`' . $key . '` = "' . mysql_real_escape_string($value) . '"';
			}
			$sql .= implode(',', $values);

			$sql .= $this->where($table, $filter);

			$result = mysql_query($sql);
			return $result !== false;
		}

		public function error() {
			return mysql_error($this->mysql);
		}

		public function mysql($config) {
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