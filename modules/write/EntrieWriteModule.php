<?php

class EntrieWriteModule extends BaseWriteModule {

	function write() {
		$id = Request::post('id');
		$title = Request::post('title');
		$body = Request::post('body');
		global $current_user;
		if (!$current_user->authorized)
			throw new Exception('must be autorized');
		if (!$body)
			throw new Exception('body missed');
		if (!$title)
			throw new Exception('title missed');

		if ($id) {
			$Blog = new Blog($current_user);
			$params = array('unixtime' => true);
			$rawData = array(
			    'id' => 'NULL',
			    'title' => $title,
			    'body' => $body,
			    'id_user' => $current_user->id,
			    'comment_count' => 0,
			    'time' => time()
			);
			$oldData = $Blog->getEntrie($id, $params);
			$rawData['id'] = $oldData['id'];
			$rawData['changed'] = time();
		}else
			$rawData = array(
			    'id' => 'NULL',
			    'title' => $title,
			    'body' => $body,
			    'id_user' => $current_user->id,
			    'comment_count' => 0,
			    'time' => time()
			);
		/* @var $current_user CurrentUser */
		$id = $id ? $id : 'NULL';
		if (!Request::post('delete')) {
			$entry = new Entrie($rawData);
			$lastId = $entry->upsert();
			$post_id = ($id && ($id != 'NULL')) ? $id : $lastId;
			header('Location: ' . '/blog/' . $current_user->getNickName() . '/' . $post_id);
			exit(0);
		} else {
			$entry = new Entrie($rawData);
			$entry->delete();
			header('Location: ' . '/blog/' . $current_user->getNickName());
			exit(0);
		}
	}

}