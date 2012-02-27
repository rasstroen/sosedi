<pre><?php
chdir('../');
ini_set('memory_limit', '1024M');
ini_set('dispay_errors', 1);
error_reporting(1);
set_time_limit(0);
include 'config.php';
if (file_exists('localconfig.php'))
	require_once 'localconfig.php';
else
	$local_config = array();
// переписываем конфиг
Config::init($local_config);
require_once 'include.php';

$update_time = floor(time() / 60 / 60) * 60 * 60;
// округляем время до получаса
$fetched_count = 0;

$tables = Database::sql2array('SHOW TABLES LIKE \'posts_data%\'');

$exists_table = array();
foreach ($tables as $trash => $name) {
	$exists_table[$name] = $name;
}


if (!is_running_process(basename(__FILE__))) {
	$page = 0;
	$posts_processed = true;

	while ($posts_processed) {
		$page++;
		$url = 'http://blogs.yandex.ru/entriesapi/?p=' . $page;
		$data = grab($url);
		$rss = new DOMDocument();
		$rss->loadXML($data);


		$i = 0;
		$posts = array();
		foreach ($rss->getElementsByTagName('item') as $item) {
			$i++;
			foreach ($item->getElementsByTagName('*') as $field) {
				$nn = str_replace('yablogs:', '', $field->nodeName);
				$posts[$i][$nn] = $field->nodeValue;
			}
		}
		$posts_processed = count($posts) > 0;

		$aids = array();
		$anames = array();
		foreach ($posts as $post) {
			if (strpos($post['author'], 'livejournal.com') !== false)
				$anames[$post['ppb_username']] = $post['ppb_username'];
		}

		if (count($anames)) {
			$query = 'SELECT `id`,`username` FROM `authors` WHERE `username` IN(\'' . implode('\',\'', $anames) . '\')';
			$anames_fetched = Database::sql2array($query, 'username');
			foreach ($anames_fetched as $aname) {
				$aids[$aname['username']] = $aname['id'];
				unset($anames[$aname['username']]);
			}
		}

		// остались неопознанные
		if (count($anames)) {
			foreach ($anames as $aname) {
				Database::query('INSERT INTO `authors` SET
					`username`=\'' . $aname . '\'
					ON DUPLICATE KEY UPDATE 
					`username`=\'' . $aname . '\'');
				$aids[$aname] = Database::lastInsertId();
			}
		}


		foreach ($posts as $post) {
			if (strpos($post['author'], 'livejournal.com') !== false) {
				$fetched_count++;
				$id_author = $aids[$post['ppb_username']];
				$id_post = explode('.', $post['link']);
				$id_post = $id_post[count($id_post) - 2];
				$id_post = explode('/', $id_post);
				$id_post = $id_post[count($id_post) - 1];
				// insert into updates
				$query = 'INSERT INTO `posts_updates` 
					SET 
					`update_time`=' . $update_time . ',
					`id_author`=' . $id_author . ',
					`id_post`=' . $id_post . ',
					`commenters`=' . $post['commenters'] . ',
					`commenters24`=' . $post['commenters24'] . ',
					`comments`=' . $post['comments'] . ',
					`comments24`=' . $post['comments24'] . ',
					`links`=' . $post['links'] . ',
					`links24`=' . $post['links24'] . ',
					`links24weight`=' . $post['links24weight'] . ',
					`linksweight`=' . $post['linksweight'] . ',
					`visits24`=' . $post['visits24'] . '					
					ON DUPLICATE KEY UPDATE
					`update_time`=' . $update_time . ',
					`id_author`=' . $id_author . ',
					`id_post`=' . $id_post . ',
					`commenters`=' . $post['commenters'] . ',
					`commenters24`=' . $post['commenters24'] . ',
					`comments`=' . $post['comments'] . ',
					`comments24`=' . $post['comments24'] . ',
					`links`=' . $post['links'] . ',
					`links24`=' . $post['links24'] . ',
					`links24weight`=' . $post['links24weight'] . ',
					`linksweight`=' . $post['linksweight'] . ',
					`visits24`=' . $post['visits24'] . '
					';
				Database::query($query);

				$pubtime = strtotime($post['pubDate']);

				// insert into index
				$y = date('Y');
				$m = date('m');

				$query = 'INSERT INTO `posts_index` SET 
					`id_post` = ' . $id_post . ',
					`id_author` = ' . $id_author . ',
					`y` = ' . $y . ',
					`m` = ' . $m . '
						ON DUPLICATE KEY UPDATE
					`y` = ' . $y . ',
					`m` = ' . $m . '
					';
				Database::query($query);
				$tblname = 'posts_data__' . $y . '_' . $m;
				if (!isset($exists_table[$tblname])) {
					Database::query('
CREATE TABLE IF NOT EXISTS `' . $tblname . '` (
  `id` int(10) unsigned NOT NULL,
  `pub_time` int(10) unsigned NOT NULL,
  `title` varchar(255) NOT NULL,
  `id_author` int(10) unsigned NOT NULL,
  `update_time` int(10) unsigned NOT NULL,
  `commenters` int(10) unsigned NOT NULL,
  `commenters24` int(10) unsigned NOT NULL,
  `comments` int(10) unsigned NOT NULL,
  `comments24` int(10) unsigned NOT NULL,
  `links` int(10) unsigned NOT NULL,
  `links24` int(10) unsigned NOT NULL,
  `links24weight` decimal(10,4) unsigned NOT NULL,
  `linksweight` decimal(10,4) unsigned NOT NULL,
  `visits24` int(10) unsigned NOT NULL,
  `rating_comments` int(10) unsigned NOT NULL,
  `rating_links` int(10) unsigned NOT NULL,
  `rating_visits` int(10) unsigned NOT NULL,
  `rating` int(10) unsigned NOT NULL,
  `has_pic` tinyint(3) unsigned NOT NULL,
  `has_video` tinyint(3) unsigned NOT NULL,
  `has_content` tinyint(3) unsigned NOT NULL,
  `short` text NOT NULL,
  `text` longtext NOT NULL,
  PRIMARY KEY  (`id`,`id_author`),
  KEY `rating` (`rating`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;');
					$exists_table[$tblname] = $tblname;
				}


				// insert for backup
				$query = 'INSERT INTO `' . $tblname . '` SET
					`update_time`=' . $update_time . ',
					`pub_time`=' . $pubtime . ',
					`id_author`=' . $id_author . ',
					`id`=' . $id_post . ',
					`title`=' . Database::escape($post['title']) . ',
					`commenters`=' . $post['commenters'] . ',
					`commenters24`=' . $post['commenters24'] . ',
					`comments`=' . $post['comments'] . ',
					`comments24`=' . $post['comments24'] . ',
					`links`=' . $post['links'] . ',
					`links24`=' . $post['links24'] . ',
					`links24weight`=' . $post['links24weight'] . ',
					`linksweight`=' . $post['linksweight'] . ',
					`visits24`=' . $post['visits24'] . '
					ON DUPLICATE KEY UPDATE
					`update_time`=' . $update_time . ',
					`pub_time`=' . $pubtime . ',
					`id_author`=' . $id_author . ',
					`id`=' . $id_post . ',
					`title`=' . Database::escape($post['title']) . ',
					`commenters`=' . $post['commenters'] . ',
					`commenters24`=' . $post['commenters24'] . ',
					`comments`=' . $post['comments'] . ',
					`comments24`=' . $post['comments24'] . ',
					`links`=' . $post['links'] . ',
					`links24`=' . $post['links24'] . ',
					`links24weight`=' . $post['links24weight'] . ',
					`visits24`=' . $post['visits24'] . ',
					`linksweight`=' . $post['linksweight'] . '';
				Database::query($query);
				// insert into posts
				$query = 'INSERT INTO `posts` SET
					`update_time`=' . $update_time . ',
					`pub_time`=' . $pubtime . ',
					`id_author`=' . $id_author . ',
					`id`=' . $id_post . ',
					`title`=' . Database::escape($post['title']) . ',
					`commenters`=' . $post['commenters'] . ',
					`commenters24`=' . $post['commenters24'] . ',
					`comments`=' . $post['comments'] . ',
					`comments24`=' . $post['comments24'] . ',
					`links`=' . $post['links'] . ',
					`links24`=' . $post['links24'] . ',
					`links24weight`=' . $post['links24weight'] . ',
					`linksweight`=' . $post['linksweight'] . ',
					`visits24`=' . $post['visits24'] . '
					ON DUPLICATE KEY UPDATE
					`update_time`=' . $update_time . ',
					`pub_time`=' . $pubtime . ',
					`id_author`=' . $id_author . ',
					`id`=' . $id_post . ',
					`title`=' . Database::escape($post['title']) . ',
					`commenters`=' . $post['commenters'] . ',
					`commenters24`=' . $post['commenters24'] . ',
					`comments`=' . $post['comments'] . ',
					`comments24`=' . $post['comments24'] . ',
					`links`=' . $post['links'] . ',
					`links24`=' . $post['links24'] . ',
					`links24weight`=' . $post['links24weight'] . ',
					`linksweight`=' . $post['linksweight'] . ',
					`visits24`=' . $post['visits24'] . ',
					`prev_visits24`=`visits24`,
					`prev_commenters`=`commenters`,
					`prev_commenters24`=`commenters24`,
					`prev_comments`=`comments`,
					`prev_comments24`=`comments24`,
					`prev_links`=`links`,
					`prev_links24`=`links24`,
					`prev_visits24`=`visits24`';
				Database::query($query);
			}
		}
	}
	echo 'range' . "\n";
	// range posts
	// by comments, by visits, by links, total
	$posts = array(1);
	$last = 0;
	Database::query('START TRANSACTION');
	while (count($posts) > 0) {
		// берём кучку постов
		$query = 'SELECT id,id_author,commenters,commenters24,comments,comments24,links,links24,links24weight,linksweight,visits24 FROM `posts` ORDER BY `id` LIMIT ' . $last . ',100';
		$last+=100;
		$posts = Database::sql2array($query);
		foreach ($posts as $post) {
			$query = 'UPDATE `posts` SET 
				`rating_comments`=' . setRateComments($post) . ',
				`rating_links`=' . setRateLinks($post) . ',
				`rating_visits`=' . setRateVisits($post) . ',
				`rating`=' . setRate($post) . '
					WHERE 
				`id`=' . $post['id'] . ' AND `id_author`=' . $post['id_author'];
			Database::query($query);
		}
		// оцениваем по 4м критериям
		// апдейтим
	}
	Database::query('COMMIT');
	echo 'update statistics' . "\n";
	Database::query('UPDATE `posts_updates_last` SET `time`=' . time() . ',`posts_fetched`=' . $fetched_count);

	// truncate old
	$old_time = time() - 12 * 24 * 60 * 60; // 3 дня не обновлялся пост? убить!
	$query = 'DELETE FROM `posts` WHERE `update_time`<' . $old_time;
	Database::query($query);
	// truncate updates
	$old_time = time() - 25 * 60 * 60; // храним статистику за сутки
	$query = 'DELETE FROM `posts_updates` WHERE `update_time`<' . $old_time;
	Database::query($query);
	
	
	$old_time = time() - 6 * 24 * 60 * 60; // 3 дня не обновлялся пост? убить!
	// old posts
	$query = 'UPDATE `posts` SET `rating`=0 WHERE `update_time`<' . $old_time;
	Database::query($query);
}else
	echo 'already running ' . basename(__FILE__) . "\n";

function setRate($post) {
	// visits 200  comments 30 commenters 10 links 2
	$rate = 1;
	$rate += $post['commenters'] * 0.02;
	$rate += $post['commenters24'] * 5;
	$rate += $post['comments'] * 0.02;
	$rate += $post['comments24'] * 2.6;
	$rate += sqrt($post['links']) * 0.1;
	$rate += $post['links24'] * 0.4;
	$rate += sqrt($post['links24weight']) * 0.5;
	$rate += $post['visits24'] * 0.09;
	if (!$post['visits24'])
		$rate = $rate / 4;
	if ($post['comments24'] && !$post['commenters24'])
		$rate -= 3 * $post['comments24'];

	if ($post['comments24'] > 100 && !$post['visits24'] < 100)
		$rate -= 3 * $post['comments24'];

	return round(max(0, $rate) * 10);
}

function setRateComments($post) {
	$rate = 1;
	$rate += $post['commenters'] / 100;
	$rate += $post['commenters24'] / 10;
	$rate += $post['comments'] / 300;
	$rate += $post['comments24'] / 30;
	return round($rate * 10);
}

function setRateLinks($post) {
	$rate = 1;
	$rate += $post['links'] / 8000;
	$rate += $post['links24'] / 5;
	$rate += $post['links24weight'] / 17;
	return round($rate * 10);
}

function setRateVisits($post) {
	$rate = 1;
	$rate += $post['visits24'] / 3000;
	return $rate;
}

function grab($url) {
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_FAILONERROR, 1);
	curl_setopt($ch, CURLOPT_TIMEOUT, 10);
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
	ob_start();
	$result = curl_exec($ch);
	ob_end_clean();
	return $result;
}