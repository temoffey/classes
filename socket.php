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
			while ($str = fgets($this->socket, 515)) {
				$data .= $str;
				if (substr($str, 3, 1) == ' ') {
					break;
				}
			}
			return $data;
		}

		public function send($data, $receive = true) {
			fputs($this->socket, $data . "\r\n");
			if ($receive) {
				$this->receive();
			}
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