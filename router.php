<?php

	class router {

		private $config = array();

		public function route() {

			foreach ($this->config as $route) {

				$params = array();

				foreach ($route['params'] as $name => $param) {
					if (isset($param['default'])) {
						$params[$name] = $param['default'];
					} else {
						$params[$name] = null;
					}
				}

				foreach (array('url', 'host', 'query') as $pattern) {
					if (!empty($route[$pattern])) {
						$regexp = $route[$pattern];
						$regexp = str_replace(array(')', '/', '.'), array(')?', '\/', '\.'), $regexp);

						foreach ($route['params'] as $name => $param) {
							$regexp = str_replace('{' . $name . '}', '(?\'' . $name . '\'' . (empty($param['regexp']) ? '.*' : $param['regexp']) . ')', $regexp);
						}

						$regexp = '/' . $regexp . '/u';

						preg_match($regexp, $this->subject[$pattern], $matches);

						if (!empty($matches)) {
							foreach ($route['params'] as $name => $param) {
								if (!empty($matches[$name])) {
									$params[$name] = $matches[$name];
								}
							}
						} else {
							continue(2);
						}
					}
				}

				break;
			}

			$controller = strtolower($params['controller']);
			$controller = new $controller;
			$action = strtolower($params['action']);
			$controller->$action($params);
		}

		public function router($config) {
			$this->config = $config;
			$this->request = explode('?', $_SERVER['REQUEST_URI']);
			$this->subject = array(
				'url' => urldecode(current($this->request)),
				'host' => $_SERVER['HTTP_HOST'],
				'query' => urldecode(end($this->request))
			);
		}

		public static function factory($config) {
			return new router($config);
		}
	}

?>