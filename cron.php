<?php

	class cron {

		private $config = array();
		private $now = array();

		public function run() {
			foreach ($this->config as $task) {
				$run = true;
				
				$task['condition'] = explode(' ', $task['condition']);
				foreach ($this->now as $key => $value) {
					if (is_array($value)) {
						$run = $run && ($this->check($task['condition'][$key], reset($value)) || $this->check($task['condition'][$key], end($value)));
					} else {
						$run = $run && $this->check($task['condition'][$key], $value);
					}
				}

				if ($run) {
					if (is_array($task['command'])) {
						$controller = strtolower($task['command']['controller']);
						$action = strtolower($task['command']['action']);
					} else {
						$controller = strtolower($task['command']);
						$action = 'cron';
					}
					$controller = new $controller;
					$controller->$action();
				}
			}
		}

		public function check($condition, $now) {
			if ($condition == '*') {
				return true;
			} elseif (strtolower($condition) == strtolower($now)) {
				return true;
			} elseif (strpos($condition, ',')) {
				$list = explode(',', $condition);

				foreach ($list as $item) {
					if ($item == '*') {
						return true;
					} elseif (strtolower($item) == strtolower($now)) {
						return true;
					} elseif (is_numeric($now) and strpos($item, '-')) {
						$range = explode('-', $item);
						$start = current($start);
						$end = end($end);

						if (($start <= $now) and ($now <= $end)) {
							return true;
						}
					}
				}
			} elseif (is_numeric($now) and strpos($condition, '/')) {
				$fraction = explode('/', $condition);
				$numerator = current($fraction);
				$denominator = end($fraction);

				if ($numerator == '*') {
					if ($now % $denominator == 0) {
						return true;
					}
				} elseif (strpos($numerator, '-')) {
					$range = explode('-', $numerator);
					$start = current($range);
					$end = end($range);

					if (($start <= $now) and ($now <= $end)) {
						if (($now - $start) % $denominator == 0) {
							return true;
						}
					}
				}
			} elseif (is_numeric($now) and strpos($condition, '-')) {
				$range = explode('-', $condition);
				$start = current($start);
				$end = end($end);

				if (($start <= $now) and ($now <= $end)) {
					return true;
				}
			}

			return false;
		}

		public function cron($config) {
			$this->config = $config;
			$this->now = array(
				date('i'),
				date('H'),
				date('d'),
				array(
					date('m'),
					date('M')
				),
				array(
					date('w'),
					date('D')
				)
			);
		}

		public static function factory($config) {
			return new cron($config);
		}
	}

?>