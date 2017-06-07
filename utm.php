<?

	class utm {

		private $json = array();
		private $data = array();

		private function get($get) {

			if (empty($get)) {
				return false;
			}

			$fields = array(
				'utm_source' => 'Источник',
				'utm_medium' => 'Тип трафика',
				'utm_campaign' => 'Рекламная компания',
				'utm_content' => 'Объявление',
				'utm_term' => 'Ключевое слово',

				'from' => 'Источник',

				'q' => 'Поисковый запрос',
				'text' => 'Поисковый запрос',
				'query' => 'Поисковый запрос',

				'source_type' => 'Тип площадки',
				'source' => 'Площадка',
				'position_type' => 'Расположение',
				'position' => 'Позиция',
				'keyword' => 'Ключевое слово',
				'network' => 'Тип площадки',
				'placement' => 'Площадка',
				'adposition' => 'Позиция',
				'matchtype' => 'Тип соответствия',
				'device' => 'Тип устройства',
				'devicemodel' => 'Модель устройства',
			);

			$names = array(
				'utm_medium' => array(
					'cpc' => 'Реклама',
					'cpa' => 'Партнерка',
					'banner' => 'Баннер',
					'social' => 'Социалки',
					'(direct)' => 'Прямой',
					'organic' => 'Поисковики',
					'referal' => 'Внешние сайты',
					'email' => 'E-mail рассылки',
				),
				'source_type' => array(
					'search' => 'Поиск',
					'context' => 'РСЯ',
				),
				'position_type' => array(
					'premium' => 'Спецразмещение',
					'other' => 'Гарнтия',
				),
				'network' => array(
					'g' => 'Поиск',
					's' => 'Поисковые партнеры',
					'd' => 'КМС',
				),
				'matchtype' => array(
					'e' => 'Точное соответствие',
					'p' => 'Фразовое',
					'b' => 'Широкое',
				),
				'device' => array(
					'm' => 'Мобильный телефон',
					't' => 'Планшетный ПК',
					'd' => 'Компьютер',
				),
			);

			foreach ($get as $key => $value) {
				if (!empty($fields[$key])) {
					$this->data[$key] = $value;
					if (!empty($names[$key][$value])) {
						$this->json[$fields[$key]] = $names[$key][$value];
					} else {
						$this->json[$fields[$key]] = $value;
					}
				}
			}

			return true;
		}

		private function source() {
			if (!empty($_SERVER['HTTP_REFERER'])) {
				$this->data['referer'] = parse_url($_SERVER['HTTP_REFERER']);
				$this->json['Направивший'] = $_SERVER['HTTP_REFERER'];

				preg_match('/([^.]+\.[^.]+)$/u', $this->data['referer']['host'], $matches);
				$this->data['referer']['clip'] = current($matches);
			}

			$this->data['request'] = parse_url($_SERVER['REQUEST_URI']);
			$this->data['request']['host'] = $_SERVER['HTTP_HOST'];
			$this->data['request']['scheme'] = !empty($_SERVER['HTTPS']) ? 'https' : 'http';
			$this->json['Посадочная'] = $this->data['request']['scheme'] . '://' . $this->data['request']['host'] . $this->data['request'];

			preg_match('/([^.]+\.[^.]+)$/u', $this->data['request']['host'], $matches);
			$this->data['request']['clip'] = current($matches);

			if (empty($this->data['referer']['clip']) or ($this->data['referer']['clip'] == $this->data['request']['clip'])) {
				return false;
			}

			return true;
		}

		private function medium($host) {

			if (empty($host)) {
				return '(direct)';
			} else {

				$organic = array(
					'yandex',
					'google',
					'go.mail',
					'rambler',
					'yahoo',
					'bing',
					'baidu',
					'sputnik',
				);

				$social = array(
					'vk.com',
					'facebook',
					'twitter',
					'instagram',
					'ok.ru',
				);

				foreach ($organic as $key) {
					if (strripos($host, $key) !== false) {
						return 'organic';
					}
				}

				foreach ($social as $key) {
					if (strripos($host, $key) !== false) {
						return 'social';
					}
				}

				return 'referer';
			}
		}

		private function run($host) {
			$this->source();

			$this->get($_GET);

			if (empty($this->data['utm_medium'])) {
				if (empty($this->data['utm_source'])) {
					$this->data['utm_source'] = $this->data['referer']['host'];
				}
				$this->data['utm_medium'] = $this->medium($this->data['utm_source']);
			}
		}
	}

?>