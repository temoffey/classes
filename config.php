<?php

	class config {

		private $file = '';
		private $config = false;

		public function load($file) {
			global $dir;
			$this->file = $file;
			$path = 'configs';

			$type = 'json';
			$include = $dir . '/' . $path . '/' . $file . '.' . $type;

			if (file_exists($include)) {
				$this->config = json_decode(file_get_contents($include), true);
				return $this;
			}

			$type = 'php';
			$include = $dir . '/' . $path . '/' . $file . '.' . $type;

			if (file_exists($include)) {
				$this->config = include $include;
				return $this;
			}
		}

		public function get($section = '') {
			if (!empty($section) and !empty($this->config[$section])) {
				return $this->config[$section];
			} else {
				return $this->config;
			}
		}

		public function set($data, $section = '', $file = '') {
			if (!empty($section) and !empty($this->config[$section])) {
				$this->config[$section] = $data;
			} else {
				$this->config = $data;
			}
			return $this->save($file);
		}

		public function save($file = '') {
			global $dir;
			if (empty($file)) {
				$file = $this->file;
			}
			$path = 'configs';

			$type = 'json';
			$include = $dir . '/' . $path . '/' . $file . '.' . $type;

			return file_put_contents($include, json_encode($this->config));
		}

		public function config($file = '') {
			if (!empty($file)) {
				$this->load($file);
			}
		}

		public static function factory($file = '', $section = '', $data = array()) {
			$config = new config($file);
			if (!empty($data)) {
				$config->set($data, $section, $file);
			}
			return $config->get($section);
		}
	}

?>