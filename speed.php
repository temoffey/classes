<?php

	class speed {

		private $config = array();
		private $nominify = array();
		private $combine = array();

		private function detect($string) {
			$keys = array(
				'<' => 0,
				'>' => 0,
				'{' => 0,
				'}' => 0,
				'#' => 0,
				'=' => 0
			);

			foreach ($keys as $key => $value) {
				$keys[$key] = substr_count($string, $key);
			}

			if (($keys['<'] + $keys['>']) > ($keys['{'] + $keys['}'])) {
				return 'html';
			} elseif ($keys['#'] > $keys['=']) {
				return 'js';
			} else {
				return 'css';
			}

			return 'html';
		}

		private function nominify($mathes) {
			do {
				$hash = md5(time() + rand());
			} while (!empty($nominify[$hash]));

			if (!empty($mathes[2])) {
				$this->nominify[$hash] = $mathes[3];

				if ($mathes[2] == 'styles') {
					$this->nominify[$hash] = $this->css($this->nominify[$hash]);
				} elseif ($mathes[2] == 'script') {
					$this->nominify[$hash] = $this->js($this->nominify[$hash]);
				}

				return $mathes[1] . $hash . $mathes[4];
			}
			return $hash;
		}

		private function combine($mathes) {
			if ($mathes[1] == 'link') {
				$this->combine['link'][] = $mathes[2];
			} elseif ($mathes[1] == 'script') {
				$this->combine['script'][] = $mathes[2];
			}
			return '';
		}

		private function css($css) {
			$strings = array(
				"'",'"'
			);

			foreach ($strings as $string) {
				$css = preg_replace_callback('/' . $string . '[^' . $string . ']*?' . $string . '/i', array($this, 'nominify'), $css);
			}

			$css = preg_replace('/\/\*[\s\S]+?\*\//i', '', $css);
			$css = preg_replace('/\s+/i', ' ', $css);
			$css = preg_replace('/\s*([{:,;}])\s*/i', '$1', $css);
			$css = preg_replace('/;}/i', '}', $css);

			if (!empty($this->nominify)) {
				$css = str_replace(array_keys($this->nominify), array_values($this->nominify), $css);
			}

			return $css;
		}

		private function js($js) {
			$js = preg_replace('/\/\/[\s\S]*?\r\n/i', '', $js);
			$js = preg_replace('/\/\*[\s\S]*?\*\//i', '', $js);
			$js = preg_replace('/\s+/i', ' ', $js);
			$js = preg_replace('/\s*([{:,;}])\s*/i', '$1', $js);
			$js = preg_replace('/;}/i', '}', $js);
			return $js;
		}

		private function html($html) {
			$tags = array(
				'code','pre','script','style','textarea',
			);
			foreach ($tags as $tag) {
				$html = preg_replace_callback('/(<(' . $tag . ')[^>]*>)([\s\S]*?)(<\/' . $tag . '>)/i', array($this, 'nominify'), $html);
			}
			$html = preg_replace('/<!--[\s\S]+?-->/i', '', $html);
			$html = preg_replace('/\s+/i', ' ', $html);
			$blocks = array(
				'aside','body','code','div','footer','form','h1','h2','h3','h4','h5','h6','head','header','html','hr','input','li','main','meta','ol','p','pre','section','select','script','table','tbody','tfoot','td','th','thead','tr','ul'
			);
			foreach ($blocks as $block) {
				$html = preg_replace('/\s+(<' . $block . '[^>]*\/?>)/i', '$1', $html);
				$html = preg_replace('/(<\/' . $block . '>)\s+/i', '$1', $html);
			}
			if (!empty($this->nominify)) {
				$html = str_replace(array_keys($this->nominify), array_values($this->nominify), $html);
			}
			if ($this->config['combine']) {
				$html = $this->concatenate($html);
			}
			if ($this->config['cdn']) {
				$html = $this->cdn($html);
			}
			return $html;
		}

		private function minify($data, $type = '') {

			if (!empty($type)) {
				if (in_array($type, array('css','style','styles'))) {
					$action = 'css';
				} elseif (in_array($type, array('js','script','scripts','javascript'))) {
					$action = 'js';
				} elseif (in_array($type, array('html'))) {
					$action = 'html';
				}
			}

			if (is_string($data)) {
				if (empty($action)) {
					$action = $this->detect($data);
				}
				return $this->$action($data);
			} elseif (is_array($data)) {
				global $dir;

				$return = array();

				foreach ($data as $string) {
					if (file_exists($dir . $string)) {
						$string = file_get_contents($dir . $string);
					}

					if (empty($action)) {
						$action = $this->detect($string);
					}
					$return[] = $this->$action($string);
				}

				return implode("\r\n", $return);
			}
		}

		private function concatenate($html) {
			$strings = array(
				"'",'"'
			);
			foreach ($strings as $string) {
				$html = preg_replace_callback('/<(link)[^>]+href=' . $string . '(\/[^\/][^' . $string . ']+)' . $string . '[^>]+rel=' . $string . 'stylesheet' . $string . '[^>]*>/i', array($this, 'combine'), $html);
				$html = preg_replace_callback('/<(link)[^>]+href=' . $string . '(\/[^\/][^' . $string . ']+)' . $string . '[^>]+rel=' . $string . 'subresource' . $string . '[^>]*>/i', array($this, 'combine'), $html);
				$html = preg_replace_callback('/<(link)[^>]+rel=' . $string . 'stylesheet' . $string . '[^>]+href=' . $string . '(\/[^\/][^' . $string . ']+)' . $string . '[^>]*>/i', array($this, 'combine'), $html);
				$html = preg_replace_callback('/<(link)[^>]+rel=' . $string . 'subresource' . $string . '[^>]+href=' . $string . '(\/[^\/][^' . $string . ']+)' . $string . '[^>]*>/i', array($this, 'combine'), $html);
				$html = preg_replace_callback('/<(script).+?src=' . $string . '(\/[^\/][^' . $string . ']+)' . $string . '[^>]*>.*?<\/script>/i', array($this, 'combine'), $html);
			}
			if (!empty($this->combine['link'])) {
				array_unique($this->combine['link']);
				$html = str_replace('</head>', '<link href="' . $this->config['combine'] . '?links=' . implode(',', $this->combine['link']) . '&type=css" rel="stylesheet" /></head>', $html);
			}
			if (!empty($this->combine['script'])) {
				array_unique($this->combine['script']);
				$html = str_replace('</body>', '<script src="' . $this->config['combine'] . '?links=' . implode(',', $this->combine['script']) . '&type=js"></script></body>', $html);
			}
			return $html;
		}

		private function cdn($html) {
			$strings = array(
				"'",'"'
			);
			foreach ($strings as $string) {
				$string = preg_replace('/src=' . $string . '(\/[^\/][^' . $string . ']+)' . $string . '/i', 'src=' . $string . '//' . $this->config['cdn'] . '$1' . $string, $html);
				$string = preg_replace('/href=' . $string . '(\/[^\/][^' . $string . ']+)' . $string . '/i', 'href=' . $string . '//' . $this->config['cdn'] . '$1' . $string, $html);
			}
			return $html;
		}

		public function speed($config) {
			$this->config = $config;
		}

		public static function factory($config, $data, $type = '') {
			$speed = new speed($config);
			return $speed->minify($data, $type);
		}
	}
?>