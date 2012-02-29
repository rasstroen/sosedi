<?php

class users_module extends BaseModule {

	public $id;
	private $shelfCountOnMain = 5;

	function getAuth() {
		global $current_user;
		/* @var $current_user CurrentUser */
		$this->data['user']['authorized'] = 0;
		if ($current_user->authorized) {
			// авторизован
			$this->data['user'] = $current_user->getListData();
			//list($this->data['user']['new_messages'], $this->data['user']['new_notifications']) = $current_user->getNewMessagesCount();
			$this->data['user']['picture'] = $current_user->getAvatar();
			$this->data['user']['authorized'] = 1;
		}
	}

	function generateData() {
		global $current_user;

		if (isset($this->params['user_id']) && !is_numeric($this->params['user_id'])) {
			if ($this->params['user_id'] == 'me') {
				$this->params['user_id'] = $current_user->id;
			} else {
				$query = 'SELECT `id` FROM `users` WHERE `nickname`=' . Database::escape($this->params['user_id']);
				$this->params['user_id'] = (int) Database::sql2single($query);
			}
		}

		$this->id = isset($this->params['user_id']) ? (int) $this->params['user_id'] : $current_user->id;
		$this->genre_id = isset($this->params['genre_id']) ? $this->params['genre_id'] : false;

		switch ($this->action) {
			case 'edit':
				$current_user->can_throw('users_edit', Users::getById($this->id));
				switch ($this->mode) {
					case 'notifications':
						$this->editNotifications();
						break;
					default:
						$this->getProfile($edit = true);
						break;
				}
				break;
			case 'show':
				switch ($this->mode) {
					case 'auth':
						$this->getAuth();
						break;
					case 'subscriptions':
						$this->getSubscriptions();
						break;
					case 'contribution':
						$this->getContribution();
						break;
					case 'quotes':
						$this->getQuotes();
						break;
					case 'contacts':
						$this->getContacts();
						break;
					default:
						$this->getProfile();
						break;
				}
				break;
			case 'list':
				switch ($this->mode) {
					case 'search':
						$this->getSearch();
						break;
					case 'friends':
						$this->getFriends();
						break;
					case 'friends_main':
						$this->getAllFriends();
						break;
					case 'likes':
						$this->getLikes();
						break;
					case 'followers':
						$this->getFollowers();
						break;
					case 'compare_interests':
						$this->getCompareInterests();
						break;
					default:
						throw new Exception('no mode #' . $this->mode . ' for ' . $this->moduleName);
						break;
				}
				break;
			default:
				throw new Exception('no action #' . $this->action . ' for ' . $this->moduleName);
				break;
		}
	}

	function editNotifications($edit = true) {
		global $current_user;
		/* @var $current_user CurrentUser */
		/* @var $user User */
		$user = ($current_user->id === $this->id) ? $current_user : Users::getById($this->id);
		if ($edit && ($user->id != $current_user->id)) {
			$current_user->can_throw('users_edit', $user);
		}

		$this->data['notify_rules'] = $user->getNotifyRules();
		$this->data['user'] = $user->getListData();
	}

	function _list($ids, $opts = array(), $limit = false) {
		$users = Users::getByIdsLoaded($ids);
		$out = array();
		/* @var $user User */
		$i = 0;
		if (is_array($users))
			foreach ($users as $user) {
				if ($limit && ++$i > $limit)
					return $out;
				$out[] = $user->getListData();
			}
		return $out;
	}

	function getSearch() {
		$query_string = isset(Request::$get_normal['s']) ? Request::$get_normal['s'] : false;
		$query_string_prepared = ('%' . mysql_escape_string($query_string) . '%');
		$cond = new Conditions();
		$per_page = 0;
		if (isset($this->params['per_page']))
			$per_page = (int) $this->params['per_page'];
		$per_page = $per_page > 0 ? $per_page : 60;
		$pagingName = isset($this->params['paging_parameter_name']) ? $this->params['paging_parameter_name'] : 'p';
		$where = 'WHERE `nickname` LIKE \'' . $query_string_prepared . '\' OR `email` LIKE \'' . $query_string_prepared . '\' OR `id`=\'' . $query_string_prepared . '\'';
		$order = 'ORDER BY `regTime` DESC ';
		$group_by = '';
		$query = 'SELECT COUNT(1) FROM `users` ' . $where . ' ' . $group_by . '';
		$count = Database::sql2single($query);
		$cond->setPaging($count, $per_page, $pagingName);
		$limit = $cond->getLimit();
		$limit = ' LIMIT ' . $limit;
		$query = 'SELECT * FROM `users`' . $where . ' ' . $group_by . ' ' . $order . ' ' . $limit;
		$data = Database::sql2array($query);
		foreach ($data as $row) {
			$user = new User($row['id'], $row);
			Users::add($user);
			$this->data['users'][] = $user->getListData(true);
		}
		$this->data['users']['title'] = 'Пользователи';
		$this->data['users']['count'] = $count;
		$this->data['conditions'] = $cond->getConditions();
	}

	function getSubscriptions() {
		global $current_user;
		$user = Users::getByIdsLoaded(array($this->params['user_id']));
		$user = isset($user[$this->params['user_id']]) ? $user[$this->params['user_id']] : $current_user;
		/* @var $user User */
		$subscriptions = $user->getSubscriptions();

		$this->data['subscriptions'] = $subscriptions;

		$this->data['user']['unused_points'] = $user->getPoints();
		$this->data['user']['points_total'] = $user->getProperty('points_total');
		$this->data['subscriptions']['active'] = $user->isSubscriptionEnabled() ? '1' : 0;
		$this->data['subscriptions']['end'] = $user->getSubscriptionEnd() ? date('Y/m/d H:i:s ', $user->getSubscriptionEnd()) : 0;
	}

	// все, кому что-то нравится
	function getLikes() {
		if (!$this->genre_id)
			return;
		$query = 'SELECT * FROM `genre` WHERE `name`=' . Database::escape($this->genre_id);
		$data = Database::sql2row($query);
		if ($data['id']) {
			
		}
	}

	function getCompareInterests() {
		$ids = Database::sql2array('SELECT id FROM users LIMIT 50', 'id');
		$this->data['users'] = $this->_list(array_keys($ids), array(), 15);
		$this->data['users']['link_url'] = 'user/' . $this->params['user_id'] . '/compare';
		$this->data['users']['link_title'] = 'Все единомышленники';
		$this->data['users']['title'] = 'Люди с похожими интересами';
		$this->data['users']['count'] = count($ids);
	}

	function getAllFriends() {
		$cond = new Conditions();
		$user = Users::getById($this->params['user_id']);
		$radiofilters = array(
		    'following' => array('title' => 'Мои друзья', 'default' => 1),
		    'followers' => array('title' => 'Я в друзьях', 'default' => 0),
		    'blacklisted' => array('title' => 'Чёрный список', 'default' => 0),
		);
		$cond->setRadioFilters($radiofilters, 't');
		$type = $cond->getRadioFilterValue('t');
		switch ($type) {
			case 'following':
				$ids = $user->getFollowing();
				break;
			case 'followers':
				$ids = $user->getFollowers();
				break;
			case 'blacklisted':
				$ids = array();
				break;
		}

		$this->data['users'] = $this->_list($ids, array());
		$uids = array();
		foreach ($this->data['users'] as $uid => $data) {
			$uids[$data['id']] = $data['id'];
		}

		$sh = $rv = array();
		if (count($uids)) {
			$query = 'SELECT COUNT(1) as cnt, id_user FROM `users_bookshelf` WHERE `id_user` IN(' . implode(',', $uids) . ') GROUP BY id_user';
			$sh = Database::sql2array($query, 'id_user');
			$query = 'SELECT COUNT(1) as cnt, id_user FROM `book_review` WHERE `id_user` IN(' . implode(',', $uids) . ') GROUP BY id_user';
			$rv = Database::sql2array($query, 'id_user');
		}
		foreach ($this->data['users'] as &$f) {
			$f['books_count'] = isset($sh[$f['id']]) ? $sh[$f['id']]['cnt'] : 0;
			$f['reviews_count'] = isset($rv[$f['id']]) ? $rv[$f['id']]['cnt'] : 0;
			$f['books_path'] = Config::need('www_path') . '/user/' . $f['id'] . '/books';
			$f['contribution_path'] = Config::need('www_path') . '/user/' . $f['id'] . '/contribution';
		}

		$this->data['users']['user_id'] = $user->id;

		$this->data['conditions'] = $cond->getConditions();
	}

	function getFriends() {
		global $current_user;
		$user = Users::getById($this->params['user_id']);
		$followingids = $user->getFollowing();
		$this->data['users'] = $this->_list($followingids, array(), 10);
		$this->data['users']['link_url'] = Config::need('www_path') . '/user/' . $this->params['user_id'] . '/friends';
		//$this->data['users']['link_title'] = 'Все друзья';
		//$this->data['users']['title'] = 'Друзья';
		$this->data['users']['count'] = count($followingids);
		$this->data['users']['user_id'] = $user->id;
	}

	function getFollowers() {
		global $current_user;
		/* @var $user User */
		$user = Users::getById($this->params['user_id']);
		$followersids = $user->getFollowers();
		$this->data['users'] = $this->_list($followersids, array(), 10);
		$this->data['users']['link_url'] = 'user/' . $this->params['user_id'] . '/followers';
		$this->data['users']['link_title'] = 'Все поклонники';
		$this->data['users']['title'] = 'Поклонники';
		$this->data['users']['count'] = count($followersids);
	}

	function getContribution() {
		$this->getProfile();
		$this->getSubscriptions();
	}

	function getQuotes($edit =false) {
		global $current_user;


		/* @var $current_user CurrentUser */
		/* @var $user User */
		$user = ($current_user->id === $this->id) ? $current_user : Users::getById($this->id);
		$this->data['user']['quote'] = $user->getPropertySerialized('quote');
	}

	function getContacts($edit =false) {
		global $current_user;


		/* @var $current_user CurrentUser */
		/* @var $user User */
		$user = ($current_user->id === $this->id) ? $current_user : Users::getById($this->id);
		$this->data['user']['contacts'] = array();
		$this->data['user']['contacts'][] = array('name' => 'fb', 'id' => $user->getPropertySerialized('link_fb'), 'path' => $user->getPropertySerialized('link_fb'));
		$this->data['user']['contacts'][] = array('name' => 'vk', 'id' => $user->getPropertySerialized('link_vk'), 'path' => $user->getPropertySerialized('link_vk'));
		$this->data['user']['contacts'][] = array('name' => 'tw', 'id' => $user->getPropertySerialized('link_tw'), 'path' => $user->getPropertySerialized('link_tw'));
		$this->data['user']['contacts'][] = array('name' => 'lj', 'id' => $user->getPropertySerialized('link_lj'), 'path' => $user->getPropertySerialized('link_lj'));
		$this->data['user']['contacts'][] = array('name' => 'skype', 'id' => $user->getPropertySerialized('link_skype'), 'path' => $user->getPropertySerialized('link_skype'));
		$this->data['user']['contacts'][] = array('name' => 'jabber', 'id' => $user->getPropertySerialized('link_jabber'), 'path' => $user->getPropertySerialized('link_jabber'));
		$this->data['user']['contacts'][] = array('name' => 'icq', 'id' => $user->getPropertySerialized('link_icq'), 'path' => $user->getPropertySerialized('link_icq'));
	}

	function getProfile($edit =false) {

		global $current_user;


		/* @var $current_user CurrentUser */
		/* @var $user User */
		$user = ($current_user->id === $this->id) ? $current_user : Users::getById($this->id);

		if ($edit && ($user->id != $current_user->id)) {
			$current_user->can_throw('users_edit', $user);
		}


		foreach (Users::$rolenames as $id => $role)
			$this->data['roles'][] = array('id' => $id, 'title' => $role);

		try {
			$user->load();
		} catch (Exception $e) {
			throw new Exception('Пользователя не существует');
		}
		if ($user->loaded) {
			
		}
		else
			return;
		$this->data['user'] = $user->getListData();
		/*
		  Если
		  1. У юзера нет друзей / фоловеров
		  2. Не добавил ни одной книжки
		  3. Не добавил в любимые ни одного объекта
		 */


		$this->data['user']['role'] = $user->getRole();

		$this->data['user']['id_city'] = $user->getProperty('id_city');
		$this->data['user']['city'] = Database::sql2single('SELECT `name` FROM `lib_city` WHERE `id`=' . (int) $user->getProperty('id_city'));

		$this->data['user']['id_country'] = $user->getProperty('id_country');
		$this->data['user']['country'] = Database::sql2single('SELECT `name` FROM `lib_country` WHERE `id`=' . (int) $user->getProperty('id_country'));

		$this->data['user']['id_region'] = $user->getProperty('id_region');
		$this->data['user']['region'] = Database::sql2single('SELECT `name` FROM `lib_region` WHERE `id`=' . (int) $user->getProperty('id_region'));

		$this->data['user']['id_street'] = $user->getProperty('id_street');
		$this->data['user']['street'] = Database::sql2single('SELECT `name` FROM `lib_street` WHERE `id`=' . (int) $user->getProperty('id_street'));


		$this->data['user']['picture'] = $user->getAvatar();
		$this->data['user']['rolename'] = $user->getRoleName();

		$bdayunix = max(0, strtotime($user->getBday()));

		if (!$edit) {
			$this->data['user']['bday'] = date('d M Y г.', $bdayunix);
			$en = array(
			    '/JAN/isU',
			    '/FEB/isU',
			    '/MAR/isU',
			    '/APR/isU',
			    '/MAY/isU',
			    '/JUN/isU',
			    '/JUL/isU',
			    '/AUG/isU',
			    '/SEP/isU',
			    '/OCT/isU',
			    '/NOV/isU',
			    '/DEC/isU',
			);
			$ru = array(
			    'января',
			    'февраля',
			    'марта',
			    'апреля',
			    'мая',
			    'июня',
			    'июля',
			    'августа',
			    'сентября',
			    'октября',
			    'ноября',
			    'декабря',
			);
			$this->data['user']['bday'] = preg_replace($en, $ru, $this->data['user']['bday']);
		} else {
			$this->data['user']['bday'] = date('Y-d-m', $bdayunix);
		}
		$this->data['user']['path'] = $user->getUrl();
		$this->data['user']['path_edit'] = $user->getUrl() . '/edit';

		// additional
	}

}
