<?php

	class smsc {

		public $balance = 0;

		private $curl = null;
		private $config = array();

		public function phone($phone) {
			$phone = preg_replace('/([^\d])/i', '', $phone);
			$phone = preg_replace('/^(7|8)?(\d{10})$/i', '7$2', $phone);
			return $phone;
		}

		public function balance() {

			$request = array(
				'login' => $this->config['login'],
				'psw' => $this->config['password'],
		 		'charset' => 'utf-8',
				'fmt' => '3'
			);

			$response = $this
				->curl
				->path('/sys/balance.php')
				->json(null, $request);

			return $response['balance'];

		}

		public function send($message) {

			$request = array(
				'login' => $this->config['login'],
				'psw' => $this->config['password'],
				'phones' => array(),
				'mes' => $message['text'],
		 		'charset' => 'utf-8',
				'fmt' => '3'
			);

			if (is_array($message['phone'])) {
				foreach ($message['phone'] as $phone) {
					$request['phones'][] = $this->phone($phone);
				}
				$request['phones'] = implode(',', $request['phones']);
			} else {
				$request['phones'] = $this->phone($message['phone']);
			}

			if (!empty($message['sender'])) {
				$request['sender'] = $message['sender'];
			}

			if (!empty($message['id'])) {
				$request['id'] = $message['id'];
			}

			$response = $this
				->curl
				->path('/sys/send.php')
				->json(null, $request);

			return $response;

		}

		public function statuses($messages) {

			$request = array(
				'login' => $this->config['login'],
				'psw' => $this->config['password'],
				'phone' => array(),
				'id' => array(),
		 		'charset' => 'utf-8',
				'fmt' => '3',
				'all' => '1'
			);

			if (is_array(current($messages))) {
				foreach ($messages as $message) {
					$request['phone'][] = $this->phone($message['phone']);
					$request['id'][] = $message['id'];
				}
				$request['phone'] = implode(',', $request['phone']);
				$request['id'] = implode(',', $request['id']);
			} else {
				$request['phone'] = $this->phone($messages['phone']);
				$request['id'] = $messages['id'];
			}

			$response = $this
				->curl
				->path('/sys/status.php')
				->json(null, $request);

			if (is_array(current($response))) {
				return $response;
			} else {
				$response['id'] = $request['id'];
				return array($response);
			}

		}

		public function info($phone) {

			$request = array(
				'login' => $this->config['login'],
				'psw' => $this->config['password'],
				'phone' => '',
				'get_operator' => '1',
		 		'charset' => 'utf-8',
				'fmt' => '3'
			);

			$request['phone'] = $this->phone($phone);

			$response = $this
				->curl
				->path('/sys/send.php')
				->json(null, $request);

			return $response;

		}

		public function smsc($config) {
			$this->config = $config;
			$this->curl = curl::factory('https://smsc.ru');
			$this->balance = $this->balance();
		}

		public static function factory($config) {
			return new smsc($config);
		}

	}

?>