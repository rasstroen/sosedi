<?php

class menu_module extends BaseModule {

	function generateData() {
		switch ($this->action) {
			case 'list' :
				switch ($this->mode) {
					case 'rating':
						$this->getRatingMenu();
						break;
				}
				break;
		}
	}

	function getRatingMenu() {
		$current_sf = Request::$structureFile;
		if($current_sf == 'main.xml')
			$current_sf = 'rating/synthesis.xml';
		
		$menu = array(
		    array(
			'name' => 'synthesis',
			'title' => 'Сводный рейтинг',
			'path' => Config::need('www_path') . '/synthesis/',
			'xml' => 'rating/synthesis.xml',
		    ),
		    array(
			'name' => 'visits',
			'title' => 'По посещаемости',
			'path' => Config::need('www_path') . '/visits/',
			'xml' => 'rating/visits.xml',
		    ),
		    array(
			'name' => 'comments',
			'title' => 'По комментариям',
			'path' => Config::need('www_path') . '/comments/',
			'xml' => 'rating/comments.xml',
		    ),
		    array(
			'name' => 'links',
			'title' => 'По ссылкам',
			'path' => Config::need('www_path') . '/links/',
			'xml' => 'rating/links.xml',
		    ),
		);
		foreach ($menu as &$item) {
			if ($current_sf == $item['xml'])
				$item['class'] = 'selected';
			else
				$item['class'] = '';
		}
		$this->data['menu'] = $menu;
	}

}