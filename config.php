<?php

	class config {

		private $config = false;

		public function load($file) {
			if (file_exists($_SERVER['DOCUMENT_ROOT'] . 'configs/' . $file . '.php')) {
				include $_SERVER['DOCUMENT_ROOT'] . 'configs/' . $file . '.php';
				$this->config = $config;
				return $this;
			} else {
				$this->config = false;
				return false;
			}
		}

		public function get($section = '') {
			if (!empty($section) and !empty($this->config[$section])) {
				return $this->config[$section];
			} else {
				return $this->config;
			}
		}

		public function config($file = '') {
			if (!empty($file)) {
				$this->load($file);
			}
		}

		public static function factory($file = '', $section = '') {
			$config = new config($file);
			return $config->get($section);
		}
	}

?>