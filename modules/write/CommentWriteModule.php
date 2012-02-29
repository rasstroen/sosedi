<?php

class CommentWriteModule extends BaseWriteModule {

	function write() {
		$id = Request::post('entry_id');
		$title = Request::post('title');
		$body = Request::post('body');
		$id_parent = Request::post('answer_to');
		global $current_user;

		$query = 'SELECT * FROM `blog_entries` WHERE `id`=' . $id;
		$data = Database::sql2row($query);
		$entry = new Entrie($data);


		if (!$current_user->authorized)
			throw new Exception('must be autorized');
		if (!$body)
			throw new Exception('body missed');
		if (!$title)
			throw new Exception('title missed');

		if ($id_parent) {
			// answer
			$query = 'SELECT * FROM `blog_entries_comments` WHERE `id`=' . $id_parent;
			$parent_comment = Database::sql2row($query);
			if ($parent_comment['id_parent'] > 0) {
				$answer_to = $id_parent;
				$id_parent = $parent_comment['id_parent'];
			}else{
				$answer_to = $id_parent;
				$id_parent = $parent_comment['id'];
			}
		} else {
			$answer_to = 0;
			$id_parent = 0;
		}

		$query = 'INSERT INTO `blog_entries_comments` SET
			`id_entry`=' . $id . ',
			`id_user`=' . $current_user->id . ',
			`id_parent`=' . $id_parent . ',
			`time`=' . time() . ',
			`title`=' . Database::escape($title) . ',
			`comment`=' . Database::escape($body) . ',
			`answer_to`=' . $answer_to;
		Database::query($query);

		$comment_id = Database::lastInsertId();


		$entry->updateCommentsCount();

		header('Location: ' . '/blog/' . $entry->user->getNickName() . '/' . $entry->id . '#comment-' . $comment_id);
		exit(0);
	}

}