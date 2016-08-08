<?php

	class curl {

		private $curl = null;
		private $host = '';
		private $path = '';
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
			if (!empty($this->cookie)) {
				curl_setopt($this->curl, CURLOPT_COOKIEFILE, $this->cookie);
				curl_setopt($this->curl, CURLOPT_COOKIEJAR, $this->cookie);
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
			if (!empty($this->cookie)) {
				curl_setopt($this->curl, CURLOPT_COOKIEFILE, $this->cookie);
				curl_setopt($this->curl, CURLOPT_COOKIEJAR, $this->cookie);
			}
			return curl_exec($this->curl);
		}

		public function json($json = array(), $get = array(), $head = array()) {
			$this->curl = curl_init();
			if (!empty($get)) {
				curl_setopt($this->curl, CURLOPT_URL, $this->host.$this->path.'?'.http_build_query($get));
			} else {
				curl_setopt($this->curl, CURLOPT_URL, $this->host.$this->path);
			}
			curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, 1);
			if (!empty($json)) {
				curl_setopt($this->curl, CURLOPT_CUSTOMREQUEST, 'POST');
				curl_setopt($this->curl, CURLOPT_POSTFIELDS, json_encode($json));
				curl_setopt($this->curl, CURLOPT_HTTPHEADER, array_unique(array_merge($this->head, $head, array('Content-Type: application/json'))));
			} else {
				curl_setopt($this->curl, CURLOPT_HTTPHEADER, array_unique(array_merge($this->head, $head)));
			}
			if (!empty($this->cookie)) {
				curl_setopt($this->curl, CURLOPT_COOKIEFILE, $this->cookie);
				curl_setopt($this->curl, CURLOPT_COOKIEJAR, $this->cookie);
			}
			return json_decode(curl_exec($this->curl), true);
		}

		public function error() {
			return curl_error($this->curl);
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