<?php

	class view {

		private $template = '';
		private $type = '';
		private $data = array();

		public function render($speed = false) {
			if ($this->type == 'jade') {
				echo $this->get();
			} else {
				if ($speed) {
					echo speed::factory(config::factory('speed'), $this->get());
				} else {
					extract($this->data);
					include $this->template;
				}
			}
		}

		public function get($speed = false) {
			if ($this->type == 'jade') {
				$jade = new Pug\Pug();
				return $jade->render($this->template, $this->data);
			} else {
				ob_start();
				$this->render();
				if ($speed) {
					return speed::factory(config::factory('speed'), ob_get_clean());
				} else {
					return ob_get_clean();
				}
			}
		}

		public function __toString() {
			return $this->get();
		}

		public function view($template, $data = array()) {
			global $dir;
			$path = 'views';

			$types = array(
				'php',
				'html',
				'jade'
			);

			foreach ($types as $type) {
				$include = $dir . '/' . $path . '/' . $template . '.' . $type;

				if (file_exists($include)) {
					$this->template = $include;
					$this->type = $type;
					break;
				}
			}

			if (empty($this->template)) {
				$type = 'jade';
				$include = $include = $dir . '/' . $path . '/' . 'template' . '.' . $type;
				file_put_contents($include, $template);
				$this->template = $include;
				$this->type = $type;
			}

			if ($data) {
				$this->data = $data;
			}
		}

		public static function factory($template, $data = array()) {
			return new view($template, $data);
		}

	}

?>