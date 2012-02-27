<?php

class Post {

	public $data;

	function __construct($data) {
		$this->data = $data;
	}

	function getShort() {
		return array(
		    'id' => $this->data['id'],
		    'title' => $this->data['title'],
		    'pub_time' => date('Y/m/d H:i:s', $this->data['pub_time']),
		    'link' => $this->data['id'],
		    'id_author' => $this->data['id_author'],
		    'path' => Config::need('www_path') . '/post/' . $this->data['id_author'] . '/' . $this->data['id'],
		    'visits24' => $this->data['visits24'],
		    'commenters24' => $this->data['commenters24'],
		    'comments24' => $this->data['comments24'],
		    'links24' => $this->data['links24'],
		    'prev_visits24' => $this->data['prev_visits24'],
		    'prev_commenters24' => $this->data['prev_commenters24'],
		    'prev_comments24' => $this->data['prev_comments24'],
		    'prev_links24' => $this->data['prev_links24'],
		    'short' => strip_tags($this->data['short']),
		    'has_content' => ($this->data['has_content'] == 1),
		    'has_pic' => ($this->data['has_pic'] == 1),
		    'pic' => Config::need('www_path').'/static/upload/post_images/' . $this->data['id'] . '/' . $this->data['id'] . '_' . $this->data['id_author'] . '.jpg',
		);
	}
	
	function getFull() {
		return array(
		    'id' => $this->data['id'],
		    'title' => $this->data['title'],
		    'pub_time' => date('Y/m/d H:i:s', $this->data['pub_time']),
		    'link' => $this->data['id'],
		    'id_author' => $this->data['id_author'],
		    'path' => Config::need('www_path') . '/post/' . $this->data['id_author'] . '/' . $this->data['id'],
		    'visits24' => $this->data['visits24'],
		    'commenters24' => $this->data['commenters24'],
		    'comments24' => $this->data['comments24'],
		    'links24' => $this->data['links24'],
		    'text' => $this->data['text'],
		    'has_content' => ($this->data['has_content'] == 1),
		    'has_pic' => ($this->data['has_pic'] == 1),
		    'pic' => Config::need('www_path').'/static/upload/post_images/' . $this->data['id'] . '/' . $this->data['id'] . '_' . $this->data['id_author'] . '.jpg',
		);
	}

}