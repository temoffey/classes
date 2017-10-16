<?php

	class socket {

		private $socket = null;
		private $host = '';
		private $port = 0;

		public function host($host) {
			$this->host = $host;
			return $this;
		}

		public function port($port) {
			$this->port = $port;
			return $this;
		}

		public function receive() {
			$data = '';
			while ($str = fread($this->socket, 1024)) {
				$data .= $str;
				if (mb_strlen($str) < 1024) {
					break;
				}
			}
			return $data;
		}

		public function send($data) {
			fputs($this->socket, $data . "\r\n");
			return $this;
		}

		public function exchange($data) {
			$this->send($data);
			$this->receive();
			return $this;
		}

		public function socket($host, $port) {
			$this->host = $host;
			$this->port = $port;
			$this->socket = fsockopen($host, $port);
		}

		public static function factory($host, $port) {
			return new socket($host, $port);
		}
	}

?>