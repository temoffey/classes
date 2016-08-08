<?php

	class router {

		private $config = array();

		public function route() {

			$params = array();

			foreach ($this->config as $route) {
				$url = $route['url'];
				$url = $url . '/?';
				$url = str_replace(')', ')?', $url);
				$url = str_replace('/', '\/', $url);
				$url = '/' . $url . '/';
				foreach ($route['params'] as $name => $param) {
					$url = str_replace('{' . $name . '}', '(?\'' . $name . '\'' . (empty($param['regexp']) ? '^\/' : $param['regexp']) . ')', $url);
				}
				preg_match($url, $_SERVER['REQUEST_URI'], $matches);
				if (!empty($matches)) {
					foreach ($route['params'] as $name => $param) {
						$params[$name] = (empty($matches[$name]) ? (empty($param['default']) ? null : $param['default']) : $matches[$name]);
					}
					break;
				}
			}

			$controller = strtolower($params['controller']);
			$controller = new $controller;
			$action = strtolower($params['action']);
			$controller->$action($params);
		}

		public function router($config) {
			$this->config = $config;
		}

		public static function factory($config) {
			return new router($config);
		}
	}

?>