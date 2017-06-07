<?php

	class curl {

		private $curl = null;
		private $host = '';
		private $path = '';
		private $auth = '';
		private $method = '';
		private $head = array();
		private $cookie = '';

		public function host($host) {
			$this->host = $host;
			return $this;
		}

		public function path($path) {
			if (!empty($path) and $path[0] != '/') {
				$this->path = '/'.$path;
			} else {
				$this->path = $path;
			}
			return $this;
		}

		public function head($head) {
			if (!is_array($head)) {
				$this->head = array($head);
			} else {
				$this->head = $head;
			}
			return $this;
		}

		public function cookie($cookie) {
			if (!empty($cookie) or $cookie[0] != '/' or $cookie[0] != '.') {
				$this->cookie = __DIR__.'/'.$cookie;
			} else {
				$this->cookie = $cookie;
			}
			return $this;
		}

		public function auth($login, $password) {
			$this->auth = $login . ':' . $password;
			return $this;
		}

		public function method($method) {
			$this->method = $method;
			return $this;
		}

		public function get($get = array(), $head = array()) {
			if (!empty($get)) {
				curl_setopt($this->curl, CURLOPT_URL, $this->host.$this->path.'?'.http_build_query($get));
			} else {
				curl_setopt($this->curl, CURLOPT_URL, $this->host.$this->path);
			}
			curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, 1);
			if (!empty($this->head) or !empty($head)) {
				curl_setopt($this->curl, CURLOPT_HTTPHEADER, array_unique(array_merge($this->head, $head)));
			}
			if (!empty($this->auth)) {
				curl_setopt($this->curl, CURLOPT_USERPWD, $this->auth);
			}
			if (!empty($this->cookie)) {
				curl_setopt($this->curl, CURLOPT_COOKIEFILE, $this->cookie);
				curl_setopt($this->curl, CURLOPT_COOKIEJAR, $this->cookie);
				curl_setopt($this->curl, CURLOPT_FOLLOWLOCATION, 1);
			}
			return curl_exec($this->curl);
		}

		public function post($post, $get = array(), $head = array()) {
			if (!empty($get)) {
				curl_setopt($this->curl, CURLOPT_URL, $this->host.$this->path.'?'.http_build_query($get));
			} else {
				curl_setopt($this->curl, CURLOPT_URL, $this->host.$this->path);
			}
			curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($this->curl, CURLOPT_CUSTOMREQUEST, 'POST');
			curl_setopt($this->curl, CURLOPT_POSTFIELDS, $post);
			if (!empty($this->head) or !empty($head)) {
				curl_setopt($this->curl, CURLOPT_HTTPHEADER, array_unique(array_merge($this->head, $head)));
			}
			if (!empty($this->auth)) {
				curl_setopt($this->curl, CURLOPT_USERPWD, $this->auth);
			}
			if (!empty($this->cookie)) {
				curl_setopt($this->curl, CURLOPT_COOKIEFILE, $this->cookie);
				curl_setopt($this->curl, CURLOPT_COOKIEJAR, $this->cookie);
				curl_setopt($this->curl, CURLOPT_FOLLOWLOCATION, 1);
			}
			return curl_exec($this->curl);
		}

		public function json($json = array(), $post = array(), $get = array(), $head = array(), $method = '') {
			$this->curl = curl_init();
			if (!empty($get)) {
				curl_setopt($this->curl, CURLOPT_URL, $this->host.$this->path.'?'.http_build_query($get));
			} else {
				curl_setopt($this->curl, CURLOPT_URL, $this->host.$this->path);
			}
			curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, 1);
			if (!empty($json)) {
				if (!empty($method)) {
					curl_setopt($this->curl, CURLOPT_CUSTOMREQUEST, $method);
				} elseif (!empty($this->method)) {
					curl_setopt($this->curl, CURLOPT_CUSTOMREQUEST, $this->method);
				} else {
					curl_setopt($this->curl, CURLOPT_CUSTOMREQUEST, 'POST');
				}
				curl_setopt($this->curl, CURLOPT_POSTFIELDS, json_encode($json));
				curl_setopt($this->curl, CURLOPT_HTTPHEADER, array_unique(array_merge($this->head, $head, array('Content-Type: application/json'))));
			} elseif (!empty($post)) {
				if (!empty($method)) {
					curl_setopt($this->curl, CURLOPT_CUSTOMREQUEST, $method);
				} elseif (!empty($this->method)) {
					curl_setopt($this->curl, CURLOPT_CUSTOMREQUEST, $this->method);
				} else {
					curl_setopt($this->curl, CURLOPT_CUSTOMREQUEST, 'POST');
				}
				curl_setopt($this->curl, CURLOPT_POSTFIELDS, $post);
				curl_setopt($this->curl, CURLOPT_HTTPHEADER, array_unique(array_merge($this->head, $head)));
			} else {
				curl_setopt($this->curl, CURLOPT_HTTPHEADER, array_unique(array_merge($this->head, $head)));
			}
			if (!empty($this->auth)) {
				curl_setopt($this->curl, CURLOPT_USERPWD, $this->auth);
			}
			if (!empty($this->cookie)) {
				curl_setopt($this->curl, CURLOPT_COOKIEFILE, $this->cookie);
				curl_setopt($this->curl, CURLOPT_COOKIEJAR, $this->cookie);
				curl_setopt($this->curl, CURLOPT_FOLLOWLOCATION, 1);
			}
			return json_decode(curl_exec($this->curl), true);
		}

		public function xml($xml = array(), $post = array(), $get = array(), $head = array(), $method = '') {
			$this->curl = curl_init();
			if (!empty($get)) {
				curl_setopt($this->curl, CURLOPT_URL, $this->host.$this->path.'?'.http_build_query($get));
			} else {
				curl_setopt($this->curl, CURLOPT_URL, $this->host.$this->path);
			}
			curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, 1);
			if (!empty($xml)) {
				if (!empty($method)) {
					curl_setopt($this->curl, CURLOPT_CUSTOMREQUEST, $method);
				} elseif (!empty($this->method)) {
					curl_setopt($this->curl, CURLOPT_CUSTOMREQUEST, $this->method);
				} else {
					curl_setopt($this->curl, CURLOPT_CUSTOMREQUEST, 'POST');
				}
				curl_setopt($this->curl, CURLOPT_POSTFIELDS, $xml);
				curl_setopt($this->curl, CURLOPT_HTTPHEADER, array_unique(array_merge($this->head, $head, array('Content-Type: application/xml'))));
			} elseif (!empty($post)) {
				if (!empty($method)) {
					curl_setopt($this->curl, CURLOPT_CUSTOMREQUEST, $method);
				} elseif (!empty($this->method)) {
					curl_setopt($this->curl, CURLOPT_CUSTOMREQUEST, $this->method);
				} else {
					curl_setopt($this->curl, CURLOPT_CUSTOMREQUEST, 'POST');
				}
				curl_setopt($this->curl, CURLOPT_POSTFIELDS, $post);
				curl_setopt($this->curl, CURLOPT_HTTPHEADER, array_unique(array_merge($this->head, $head)));
			} else {
				curl_setopt($this->curl, CURLOPT_HTTPHEADER, array_unique(array_merge($this->head, $head)));
			}
			if (!empty($this->auth)) {
				curl_setopt($this->curl, CURLOPT_USERPWD, $this->auth);
			}
			if (!empty($this->cookie)) {
				curl_setopt($this->curl, CURLOPT_COOKIEFILE, $this->cookie);
				curl_setopt($this->curl, CURLOPT_COOKIEJAR, $this->cookie);
				curl_setopt($this->curl, CURLOPT_FOLLOWLOCATION, 1);
			}
			return curl_exec($this->curl);
		}

		public function file($file = null, $get = array(), $head = array()) {
			if (!empty($get)) {
				curl_setopt($this->curl, CURLOPT_URL, $this->host.$this->path.'?'.http_build_query($get));
			} else {
				curl_setopt($this->curl, CURLOPT_URL, $this->host.$this->path);
			}
			curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, 1);
			if (!empty($this->head) or !empty($head)) {
				curl_setopt($this->curl, CURLOPT_HTTPHEADER, array_unique(array_merge($this->head, $head)));
			}
			if (!empty($this->auth)) {
				curl_setopt($this->curl, CURLOPT_USERPWD, $this->auth);
			}
			if (!empty($this->cookie)) {
				curl_setopt($this->curl, CURLOPT_COOKIEFILE, $this->cookie);
				curl_setopt($this->curl, CURLOPT_COOKIEJAR, $this->cookie);
				curl_setopt($this->curl, CURLOPT_FOLLOWLOCATION, 1);
			}
			curl_setopt($this->curl, CURLOPT_SSL_VERIFYHOST, 0);
			curl_setopt($this->curl, CURLOPT_SSL_VERIFYPEER, 0);
			curl_setopt($this->curl, CURLOPT_TIMEOUT, 300);
			set_time_limit(300);
			if (!empty($file)) {
				if (is_string($file)) {
					$file = fopen($file, 'w+');
				}
				curl_setopt($this->curl, CURLOPT_FILE, $file);
				curl_exec($this->curl);
				fclose($file);
				return true;
			} else {
				curl_setopt($this->curl, CURLOPT_HEADER, 1);
				$file = array();
				$response = curl_exec($this->curl);
				$response = explode("\r\n\r\n", $response, 2);
				$response[0] = explode("\r\n", $response[0]);
				foreach ($response[0] as $value) {
					if (strpos($value, 'Content-Disposition') !== false) {
						$file['name'] = explode('=', $value)[1];
					}
				}
				$file['body'] = $response[1];
				return $file;
			}
		}

		public function error() {
			return curl_error($this->curl);
		}

		public function info() {
			return curl_getinfo($this->curl);
		}

		public function headers() {
			return get_headers($this->curl);
		}

		public function curl($host = '', $path = '', $head = array(), $cookie = '') {
			$this->curl = curl_init();
			if (!empty($host)) $this->host = $host;
			if (!empty($path) and $path[0] != '/') {
				$this->path = '/'.$path;
			} else {
				$this->path = $path;
			}
			if (!empty($head)) 
				if (!is_array($head)) {
					$this->head = array($head);
				} else {
					$this->head = $head;
				}
			if (!empty($cookie)) 
				if ($cookie[0] != '/' and $cookie[0] != '.') {
					$this->cookie = __DIR__.'/'.$cookie;
				} else {
					$this->cookie = $cookie;
				}
		}

		public static function factory($host = '', $path = '', $head = array(), $cookie = '') {
			return new curl($host, $path, $head, $cookie);
		}
	}

?>