<?php

class ProfileWriteModule extends BaseWriteModule {

	private static $cover_sizes = array(
	    array(50, 50, true), // small_
	    array(100, 100, true), // default_
	    array(120, 120, true), // big_
	    array(250, 250, true), // orig_
	);

	function write() {
		global $current_user;
		/* @var $current_user CurrentUser */



		$mask = array(
		    'id' => 'int',
		    'nickname' => array(
			'type' => 'string',
			'regexp' => '/^[A-Za-z][A-Za-z0-9_]+$/',
			'min_length' => 3,
			'max_length' => 26,
			'*' => true,
		    ),
		    'role' => array(
			'type' => 'int',
			'*' => true,
		    ),
		    'link_fb' => array(
			'type' => 'string',
			'*' => true,
		    ),
		    'link_vk' => array(
			'type' => 'string',
			'*' => true,
		    ),
		    'link_lj' => array(
			'type' => 'string',
			'*' => true,
		    ),
		    'link_tw' => array(
			'type' => 'string',
			'*' => true,
		    ),
		    'quote' => array(
			'type' => 'string',
			'*' => true,
		    ),
		    'about' => array(
			'type' => 'string',
			'*' => true,
		    ),
		);
		$params = Request::checkPostParameters($mask);


		$uid = isset($params['id']) ? $params['id'] : 0;
		if (!$uid)
			throw new Exception('illegal user id');

		if ($current_user->id != $params['id']) {
			if ($current_user->getRole() >= User::ROLE_BIBER) {
				$editing_user = Users::getByIdsLoaded(array($params['id']));
				$editing_user = isset($editing_user[$params['id']]) ? $editing_user[$params['id']] : false;
			}
		}else
			$editing_user = $current_user;

		$current_user->can_throw('users_edit', $editing_user);



		if ($editing_user) {
			if (trim($params['nickname']) != $editing_user->getNickName()) {
				if (!$editing_user->checkNickChanging()) {
					throw new Exception('You can\'t change your nickname');
				}
			}
			//avatar
			if (isset($_FILES['picture']) && $_FILES['picture']['tmp_name']) {
				$filename = Config::need('avatar_upload_path') . '/' . $editing_user->id . '.jpg';
				$folder = Config::need('avatar_upload_path');

				$filename_normal = $folder . '/default_' . $editing_user->id . '.jpg';
				$filename_small = $folder . '/small_' . $editing_user->id . '.jpg';
				$filename_big = $folder . '/big_' . $editing_user->id . '.jpg';
				$filename_orig = $folder . '/orig_' . $editing_user->id . '.jpg';

				$thumb = new Thumb();
				$thumb->createThumbnails($_FILES['picture']['tmp_name'], array($filename_small, $filename_normal, $filename_big, $filename_orig), self::$cover_sizes);



				$editing_user->setProperty('picture', 1);
				$editing_user->setProperty('lastSave', time());
			}
			if ($editing_user->getRole() < User::ROLE_SITE_ADMIN) {
				if ($current_user->getRole() == User::ROLE_BIBER) {
					if (($new_role = (int) $params['role']) !== false) {
						foreach (Users::$rolenames as $id => $name) {
							if ($id == $new_role) {
								if ($new_role < User::ROLE_SITE_ADMIN) {
									$editing_user->setRole($new_role);
								}
							}
						}
					}
				}
				if ($current_user->getRole() > User::ROLE_BIBER) {
					if (($new_role = (int) $params['role']) !== false) {
						foreach (Users::$rolenames as $id => $name) {
							if ($id == $new_role) {
								if ($new_role <= User::ROLE_SITE_ADMIN) {
									$editing_user->setRole($new_role);
								}
							}
						}
					}
				}
			}

			$editing_user->save();
			// после редактирования профиля надо посбрасывать кеш со страницы профиля
			// и со страницы редактирования профиля
			// кеш в остальных модулях истечет сам
			Users::dropCache($editing_user->id);
		}
		else
			Error::CheckThrowAuth(User::ROLE_SITE_ADMIN);
	}

}