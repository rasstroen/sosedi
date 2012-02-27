<?php

class posts_module extends BaseModule {

	function generateData() {
		switch ($this->action) {
			case 'list' :
				switch ($this->mode) {
					case 'main':
						$this->getSynthesis();
						break;
					case 'links':
						$this->getSynthesis('links');
						break;
					case 'visits':
						$this->getSynthesis('visits');
						break;
					case 'comments':
						$this->getSynthesis('comments');
						break;
				}
				break;
			case 'show' :
				switch ($this->mode) {
					default:
						$this->getOnePost();
						break;
				}
				break;
		}
	}

	function getOnePost() {
		$aid = (int) $this->params['user_id'];
		$pid = (int) $this->params['post_id'];
		$query = 'SELECT * FROM `posts_index` WHERE 
			`id_post`=' . $pid . ' AND
			`id_author`=' . $aid;
		$res = Database::sql2row($query);
		if ($res) {
			$res['m'] = str_pad($res['m'], 2, '0', STR_PAD_LEFT);
			$tblname = 'posts_data__' . $res['y'] . '_' . $res['m'];
			try {
				$query = 'SELECT * FROM `' . $tblname . '` WHERE
					`id`=' . $pid . ' AND
					`id_author`=' . $aid;
				$data = Database::sql2row($query);
				if ($data) {

					$post = new Post($data);
					$this->data['post'] = $post->getFull();
					$aid = $post->data['id_author'];
					$authors = Database::sql2array('SELECT * FROM `authors` WHERE `id` =' . $aid . '');
					foreach ($authors as $data) {
						$author = new Author($data);
						$this->data['authors'][] = $author->getShort();
					}

					return true;
				}
			} catch (Exception $e) {
				
			}
		}
		throw new Exception('Не можем найти такой записи', '404');
	}

	function getSynthesis($mode = 'synthesis') {
		$min_update_time = time() - 48 * 60 * 60;
		$query = 'SELECT COUNT(1) FROM `posts` WHERE `update_time`>' . $min_update_time;
		$count = min(200, Database::sql2single($query));

		$cond = new Conditions();
		$per_page = 20;
		$cond->setPaging($count, $per_page, 'p');
		$this->data['conditions'] = $cond->getConditions();

		$limit = $cond->getLimit();

		switch ($mode) {
			case 'synthesis':
				$order = 'rating';
				break;
			case 'links':
				$order = 'rating_links';
				break;
			case 'visits':
				$order = 'rating_visits';
				break;
			case 'comments':
				$order = 'rating_comments';
				break;
		}


		$posts = Database::sql2array('SELECT * FROM `posts` ORDER BY ' . $order . ' DESC LIMIT ' . $limit);
		$i = 0;
		foreach ($posts as $data) {
			$post = new Post($data);
			$this->data['posts'][$i] = $post->getShort();
			$this->data['posts'][$i]['num'] = ($cond->currentPage-1) * $per_page + $i + 1;
			$aids[$post->data['id_author']] = $post->data['id_author'];
			$i++;
		}

		if (count($aids)) {
			$authors = Database::sql2array('SELECT * FROM `authors` WHERE `id` IN(' . implode(',', $aids) . ')');
			foreach ($authors as $data) {
				$author = new Author($data);
				$this->data['authors'][] = $author->getShort();
			}
		}
	}

}