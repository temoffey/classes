<?php

	class pdf {

		private $config = array();

		public function gen($text) {
			file_put_contents($this->config['input'], $text);
			exec('xvfb-run -a --server-args="' . $this->config['xvfb'] . '" /usr/bin/wkhtmltopdf ' . $this->config['wkhtmltopdf'] . ' ' . $this->config['input'] . '  ' . $this->config['output']);
			$text = file_get_contents($this->config['output']);
			unlink($this->config['output']);
			unlink($this->config['input']);
			return $text;
		}

		public function pdf($config) {
			$this->config = $config;
		}

		public static function factory($config) {
			return new pdf($config);
		}

	}

?>
