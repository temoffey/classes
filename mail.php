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

			$subject = '=?utf-8?B?' . base64_encode($subject) . '?=';

			$this->subject = $subject;

			return $this;
		}

		public function headers($sender) {
			$headers = '';

			$headers .= 'MIME-Version: 1.0;' . "\r\n";
			$headers .= 'From: ' . $sender['name'] . ' <' . $sender['email'] . '>' . "\r\n";
			$headers .= 'Sender: ' . $sender['name'] . ' <' . $sender['email'] . '>' . "\r\n";
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

			foreach ($files as $key => $file) {
				$attach = array();

				if (is_array($file)) {
					$attach['name'] = $file[0];
					$attach['type'] = $file[1];
					$attach['body'] = $file[2];
				} elseif (is_string($file) and file_exists($file)) {
					$attach['name'] = is_string($key) ? $key : basename($file);
					$attach['type'] = mime_content_type($file);
					$attach['body'] = file_get_contents($file);
				}

				if (!empty($attach)) {
					$multipart .= 'Content-Type: ' . $attach['type'] . ' name="' . $attach['name'] . '"' . "\r\n";
					$multipart .= 'Content-Transfer-Encoding: base64' . "\r\n"; 
					$multipart .= 'Content-Disposition: attachment; filename="' . $attach['name'] . '"' . "\r\n\r\n";
					$multipart .= chunk_split(base64_encode($attach['body'])) . "\r\n";
					$multipart .= '--' . $this->boundary . "\r\n";
				}
			}

			$this->multipart .= $multipart;
			return $this;
		}

		public function send($recipients, $smtp = false) {

			if (!is_array($recipients)) {
				$recipients = array($recipients);
			}

			if ($smtp) {
				$smtp = smtp::factory($this->smtp['host'], $this->smtp['port'], $this->smtp['email'], $this->smtp['smtp']);

				foreach ($recipients as $recipient) {
					$smtp->send($recipient, $this->subject, $this->multipart, $this->headers);
				}
			} else {
				foreach ($recipients as $recipient) {
					mail($recipient, $this->subject, $this->multipart, $this->headers);
				}
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