<?php

	class smtp {

		private $socket = null;
		private $host = '';
		private $port = 0;
		private $login = '';
		private $password = '';

		public function host($host) {
			$this->host = $host;
			return $this;
		}

		public function port($port) {
			$this->port = $port;
			return $this;
		}

		public function login($login) {
			$this->login = $login;
			return $this;
		}

		public function password($password) {
			$this->password = $password;
			return $this;
		}

		public function send($recipient, $subject, $content, $header) {

			if ($recipient) {
				$header .= 'To: ' . $recipient . "\r\n";
			}

			if ($subject) {
				$header .= 'Subject: ' . $subject . "\r\n";
			}

			$this->socket = socket::factory('sslv3://' . $this->host, $this->port);

			$this->socket->receive();

			$this->socket
				->send('EHLO ' . $this->host)
				->send('AUTH LOGIN')
				->send(base64_encode($this->login))
				->send(base64_encode($this->password), false);

			if (strpos($this->socket->receive(), '535') !== false) {
				return false;
			}

			$this->socket
				->send('MAIL FROM:' . $this->login)
				->send('RCPT TO:' . $recipient)
				->send('DATA')
				->send($header, false)
				->send($content, false)
				->send('.', false)
				->send('QUIT');

			return true;
		}

		public function smtp($host, $port, $login = '', $password = '') {
			$this->host = $host;
			$this->port = $port;
			if ($login) {
				$this->login = $login;
			}
			if ($password) {
				$this->password = $password;
			}
		}

		public static function factory($host, $port, $login = '', $password = '') {
			return new smtp($host, $port, $login, $password);
		}
	}

?>