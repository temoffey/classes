<?php

	class auth {

		private $mysql = null;
		private $user_id = 0;
		private $session_id = 0;
		private $remember = 0;

		public $logged = false;
		public $user = array();

		public function check($login) {
			$fields = array(
				'login',
				'email'
			);

			foreach ($fields as $field) {
				if ($this->mysql->check('users', array($field => $login))) {
					return true;
				}
			}
			
			return false;
		}

		public function login($login, $password, $remember = true) {
			$fields = array(
				array('login', 'password'),
				array('email', 'password'),
				array('email', 'smtp'),
				array('login', 'smtp'),
			);

			foreach ($fields as $field) {
				if ($this->mysql->check('users', array($field[0] => $login, $field[1] => $password))) {
					$user_id = $this->mysql->id('users', array($field[0] => $login, $field[1] => $password));
					$this->user_id = $user_id;
					$this->user = $this->mysql->row('users', $this->user_id);
					if ($remember) {
						$this->remember = 1;
						session_set_cookie_params(time() + 604800);
					}
					$this->mysql->insert('sessions', array('session_id' => $this->session_id, 'user_id' => $this->user_id, 'remember' => $this->remember));
					$this->logged = true;

					return true;
				}
			}

			return false;
		}

		public function logout() {
			$this->mysql->delete('sessions', array('session_id' => $this->session_id));
			$this->logged = false;
			unset($_SESSION['thx']);
			return true;
		}

		public function relogin($login, $password, $remember = true) {
			$this->logout();
			return $this->login($login, $password, $remember);
		}

		public function is_logged($role = '') {
			if (!empty($role)) {
				return $this->has_role($role) or $this->in_group_has_role($role);
			} else {
				return $this->logged;
			}
		}

		public function in_group($group) {
			$check = $this->mysql->sql('select user_group.user_id from groups join user_group on (groups.id = user_group.group_id) where user_group.user_id = "' . $this->user_id . '" and groups.name = "' . $group . '"');
			return !empty($check);
		}

		public function has_role($role) {
			$check = $this->mysql->sql('select user_role.user_id from roles join user_role on (roles.id = user_role.role_id) where user_role.user_id = "' . $this->user_id . '" and roles.name = "' . $role . '"');
			return !empty($check);
		}

		public function in_group_has_role($role) {
			$check = $this->mysql->sql('select user_group.user_id from roles join group_role on (roles.id = group_role.role_id) join user_group on (group_role.group_id = user_group.group_id) where user_group.user_id = "' . $this->user_id . '" and roles.name = "' . $role . '"');
			return !empty($check);			
		}

		public function auth() {
			$this->mysql = mysql::factory(config::factory('mysql'));

			session_name('session_id');
			session_start();
			$this->session_id = session_id();

			$session = $this->mysql->row('sessions', array('session_id' => $this->session_id));

			if ($session) {
				$session = $this->mysql->row('sessions', array('session_id' => $this->session_id));
				$this->user_id = $session['user_id'];

				$user = $this->mysql->row('users', $this->user_id);

				if ($user) {
					$this->user = $this->mysql->row('users', $this->user_id);
					$this->logged = true;
				}
			}
		}

		public static function factory() {
			return new auth();
		}
	}

?>