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

$tables = Database::sql2array('SHOW TABLES LIKE \'posts_data%\'');

$exists_table = array();
foreach ($tables as $trash => $name) {
	$exists_table[$name] = $name;
}



$update_time = floor(time() / 60 / 60) * 60 * 60;
// округляем время до получаса
$fetched_count = 0;
if (!is_running_process(basename(__FILE__))) {
	$posts_processed = true;
	while ($posts_processed) {
		$posts_processed = false;
		$query = 'SELECT `id`,`id_author`,`pub_time`,update_time FROM `posts` WHERE `has_content`=0 ORDER BY `rating` DESC LIMIT 10';
		$ids = Database::sql2array($query);
		if (count($ids) > 0)
			$posts_processed = true;
		$aids = array();
		foreach ($ids as $data) {
			$aids[$data['id_author']] = $data['id_author'];
		}
		$query = 'SELECT * FROM `authors` WHERE id IN(' . implode(',', $aids) . ')';
		$authors = Database::sql2array($query, 'id');
		foreach ($ids as $data) {
			$url = 'http://';
			$url .= $authors[$data['id_author']]['username'];
			$url .= '.livejournal.com';
			$url .= '/data/rss';
			$raw = grab($url);
			$rss = new DOMDocument();
			$rss->loadXML($raw);

			$posts = array();

			$found = false;
			$fulltext = '';

			foreach ($rss->getElementsByTagName('item') as $item) {
				foreach ($item->getElementsByTagName('*') as $field) {
					if ($field->nodeName == 'link')
						if (strpos($field->nodeValue, '/' . $data['id'] . '.html') !== false) {
							$found = true;
							foreach ($item->getElementsByTagName('description') as $f)
								$fulltext = $f->nodeValue;
						} else {
							// not our post
							continue;
						}
				}
			}
			$pubtime = $data['update_time'];
			$y = date('Y', $pubtime);
			$m = date('m', $pubtime);
			$tblname = 'posts_data__' . $y . '_' . $m;

			if ($found) {
				$fulltext = preg_replace('/\<script(.*)\/script\>/isU', '', $fulltext);
				$fulltext = preg_replace('/\<form(.*)\/form>/isU', '', $fulltext);
				$fulltext = preg_replace('/\<iframe(.*)\/iframe>/isU', '', $fulltext);
				$short = close_dangling_tags(_substr(prepare_review($fulltext, ''), 211));

				$query = 'INSERT INTO `' . $tblname . '` SET
				`id`=' . $data['id'] . ',
				`id_author`=' . $data['id_author'] . ',
				`text`=' . Database::escape($fulltext) . ',
				`short`=' . Database::escape($short) . ',
				`has_content`=1
				ON DUPLICATE KEY UPDATE
				`has_content`=1,
				`short`=' . Database::escape($short) . ',
				`text`=' . Database::escape($fulltext);
				Database::query($query);
				Database::query('UPDATE `posts` SET `short`=' . Database::escape($short) . ', `has_content`=1 WHERE `id`=' . $data['id'] . ' AND `id_author`=' . $data['id_author'] . '');
			} else {
				Database::query('UPDATE `posts` SET `has_content`=2 WHERE `id`=' . $data['id'] . ' AND `id_author`=' . $data['id_author'] . '');
			}
		}
	}
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