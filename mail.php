
<?php

	class mail {

		private $smtp = array();
		private $headers = '';
		private $subject = '';
		private $boundary = '';
		private $multipart = '';

		public function smtp($smtp) {

			$this->smtp = $smtp;

			return $this;
		}

		public function subject($subject) {

			$this->subject = '=?utf-8?B?' . base64_encode($subject) . '?=';

			return $this;
		}

		public function headers($sender) {
			$headers = '';

			$headers .= 'MIME-Version: 1.0;' . "\r\n";
			$headers .= 'From: ' . '=?utf-8?B?' . base64_encode($sender['name']) . '?=' . ' <' . $sender['email'] . '>' . "\r\n";
			$headers .= 'Sender: ' . '=?utf-8?B?' . base64_encode($sender['name']) . '?=' . ' <' . $sender['email'] . '>' . "\r\n";
			$headers .= 'Reply-To: ' . $sender['email'] . "\r\n";

			$headers .= 'Content-Type: multipart/mixed; boundary="' . $this->boundary . '"' . "\r\n";

			$this->headers = $headers;

			return $this;
		}

		public function text($text) {
			$multipart = '';

			$multipart .= '--' . $this->boundary . "\r\n";
			$multipart .= 'Content-Type: text/plain; charset=utf8' . "\r\n";
			$multipart .= 'Content-Transfer-Encoding: base64' . "\r\n\r\n";
			$multipart .= chunk_split(base64_encode($text)) . "\r\n";
			$multipart .= '--' . $this->boundary . "\r\n";

			$this->multipart = $multipart;
			return $this;
		}

		public function html($html) {
			$multipart = '';

			$multipart .= '--' . $this->boundary . "\r\n";
			$multipart .= 'Content-Type: text/html; charset=utf8' . "\r\n";
			$multipart .= 'Content-Transfer-Encoding: base64' . "\r\n\r\n";
			$multipart .= chunk_split(base64_encode($html)) . "\r\n";
			$multipart .= '--' . $this->boundary . "\r\n";

			$this->multipart = $multipart;
			return $this;
		}

		public function files($files) {
			$multipart = '';

			if (is_string($files) and file_exists($files) and is_dir($files)) {
				$dir = $files;
				$files = scandir($dir);
				foreach ($files as $key => $file) {
					$files[$key] = $dir . '/' . $file;
				}
			}

			if (is_array($files)) {

				foreach ($files as $key => $file) {

					if (is_string($file) and file_exists($file) and is_file($file)) {
						$file = array(
							is_string($key) ? $key : basename($file),
							mime_content_type($file),
							file_get_contents($file),
						);
					}

					if (is_array($file)) {
						$multipart .= 'Content-Type: ' . $file[1] . ' name="' . $file[0] . '"' . "\r\n";
						$multipart .= 'Content-Transfer-Encoding: base64' . "\r\n"; 
						$multipart .= 'Content-Disposition: attachment; filename="' . $file[0] . '"' . "\r\n\r\n";
						$multipart .= chunk_split(base64_encode($file[2])) . "\r\n";
						$multipart .= '--' . $this->boundary . "\r\n";
					}
				}
			}

			$this->multipart .= $multipart;
			return $this;
		}

		public function send($recipient, $smtp = false) {
			if (!empty($smtp)) {
				$smtp = smtp::factory($this->smtp['host'], $this->smtp['port'], $this->smtp['email'], $this->smtp['smtp']);
				return $smtp->send($recipient, $this->subject, $this->multipart, $this->headers);
			} else {
				return mail($recipient, $this->subject, $this->multipart, $this->headers);
			}
		}

		public function mail($subject = '', $sender = array(), $smtp = array()) {
			$this->boundary = '--'.md5(uniqid(time()));
			if (!empty($subject)) {
				$this->subject($subject);
			}
			if (!empty($sender)) {
				$this->headers($sender);
			}
			if (!empty($smtp)) {
				$this->smtp = $smtp;
			}
		}

		public static function factory($subject = '', $sender = array(), $smtp = array()) {
			return new mail($subject, $sender, $smtp);
		}

	}

?>