<?php

	class replace {

		public static function render($replace, $data) {
			if (!empty($data)) {
				extract($data);
			}

			$return = array();

			foreach ($replace as $key => $value) {
				if (is_array($value)) {
					if (!empty($value['search'])) {
						$key = $value['search'];
					}

					if (@eval($value['replace']) === null) {
						$return[$key] = eval('return ' . $value['replace']);
					} else {
						$return[$key] = $value['replace'];
					}

				} else {
					$return[$key] = $value;
				}
			}

			return $return;
		}

		public static function factory($subject, $replace, $data = null) {
			$replace = self::render($replace, $data);

			if (is_string($subject)) {
				return str_replace(array_keys($replace), array_values($replace), $subject);
			} elseif (is_array($subject)) {
				foreach ($subject as $key => $value) {
					$subject[$key] = str_replace(array_keys($replace), array_values($replace), $value);
				}
				return $subject;
			}
		}
	}

?>