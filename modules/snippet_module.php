<?php

class snippet_module extends BaseModule {

	function generateData() {
		switch ($this->action) {
			case 'show' :
				switch ($this->mode) {
					case 'posts_updates_last':
						$this->getPostsUpdatesLast();
						break;
				}
				break;
		}
	}

	function getPostsUpdatesLast() {
		$query = 'SELECT * FROM `posts_updates_last`';
		$data = Database::sql2row($query);
		$data['time'] = date('Y/m/d H:i:s',$data['time']);
		$this->data['snippet'] = $data;
	}

}