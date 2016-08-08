<?php

	class view {

		private $template = '';
		private $type = '';
		private $data = array();

		public function render() {
			if ($this->type == 'jade') {
				extract($this->data);
				$jade = new Pug\Pug();
				eval(' ?>' . $jade->compile($this->template) . '<?php ');
				unset($jade);
			} else {
				extract($this->data);
				include $this->template;
			}
		}

		public function get() {
			ob_start();
			$this->render();
			return ob_get_clean();
		}

		public function __toString() {
			return $this->get();
		}

		public function view($template, $data = array()) {

			$types = array(
				'php',
				'html',
				'jade'
			);

			foreach ($types as $type) {
				if (file_exists($_SERVER['DOCUMENT_ROOT'] . 'views/' . $template . '.' . $type)) {
					$this->template = $_SERVER['DOCUMENT_ROOT'] . 'views/' . $template . '.' . $type;
					$this->type = $type;
					break;
				}
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