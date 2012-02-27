<?php

class Author {

	public $data;

	function __construct($data) {
		$this->data = $data;
	}

	function getShort() {

		$avatar = Config::need('www_path') . '/static/upload/author_images/' . ceil($this->data['id'] / 500) . '/' . $this->data['id'] . '.jpg';
		$avatar_small = Config::need('www_path') . '/static/upload/author_images/' . ceil($this->data['id'] / 500) . '/' . $this->data['id'] . '_small.jpg';
		if (!$this->data['has_pic']){
			$avatar = Config::need('www_path') . '/static/default/img/avatar.jpg';
			$avatar_small = Config::need('www_path') . '/static/default/img/avatar_small.jpg';
		}
		return array(
		    'id' => $this->data['id'],
		    'username' => $this->data['username'],
		    'path' => Config::need('www_path') . '/profile/' . $this->data['username'],
		    'avatar' => $avatar,
		    'avatar_small' => $avatar_small,
		);
	}

}