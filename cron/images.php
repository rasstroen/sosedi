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
if (!is_running_process(basename(__FILE__))) {
	$posts_processed = true;
	while ($posts_processed) {
		$posts_processed = false;
		$query = 'SELECT `id`,`id_author` as id_author, update_time as update_time
			FROM `posts` P
			WHERE `has_pic`=0 AND `has_content`>0
			ORDER BY `rating` DESC LIMIT 10';
		$ids = Database::sql2array($query);

		foreach ($ids as &$pd) {
			$y = date('Y', $pd['update_time']);
			$m = date('m', $pd['update_time']);
			$tblname = 'posts_data__' . $y . '_' . $m;
			$query = 'SELECT `text` FROM ' . $tblname . ' WHERE `id`=' . $pd['id'] . ' AND `id_author`=' . $pd['id_author'] . '';
			$pd['text'] = Database::sql2single($query);
		}

		if (count($ids) > 0)
			$posts_processed = true;


		foreach ($ids as $data) {
			$found = false;
			if ($data['text']) {
				$found = upload_post_main_image($data);
			}

			$y = date('Y', $pd['update_time']);
			$m = date('m', $pd['update_time']);
			$tblname = 'posts_data__' . $y . '_' . $m;

			if ($found) {
				Database::query('UPDATE `' . $tblname . '` SET `has_pic`=1 WHERE `id`=' . $data['id'] . ' AND `id_author`=' . $data['id_author'] . '');
				Database::query('UPDATE `posts` SET `has_pic`=1 WHERE `id`=' . $data['id'] . ' AND `id_author`=' . $data['id_author'] . '');
			} else {
				Database::query('UPDATE `' . $tblname . '` SET `has_pic`=2 WHERE `id`=' . $data['id'] . ' AND `id_author`=' . $data['id_author'] . '');
				Database::query('UPDATE `posts` SET `has_pic`=2 WHERE `id`=' . $data['id'] . ' AND `id_author`=' . $data['id_author'] . '');
			}
		}
	}
}

function upload_post_main_image($data) {
	try {
		$cover_sizes = array(
		    array(150, 150, false),
		    array(400, 400, true),
		);

		$content = $data['text'];
		$urls = array();
		preg_match_all("/(<img )(.+?)( \/)?(>)/", $content, $images);
		foreach ($images[2] as $val) {
			if (preg_match("/(src=)('|\")(.+?)('|\")/", $val, $matches) == 1)
				$urls[$matches[3]] = $matches[3];
		}

		if (count($urls)) {
			foreach ($urls as $url) {
				$imgdata = grab($url);
				$tmp_name = '/tmp/image.jpg';
				file_put_contents($tmp_name, $imgdata);
				$size = getimagesize($tmp_name);
				if ($size) {
					if ($size[0] >= 150)
						if ($size[1] >= 150) {
							$folder = Config::need('static_path') . 'upload/post_images/' . $data['id'];
							$filename = $folder . '/' . $data['id'] . '_' . $data['id_author'] . '.jpg';
							$filename_o = $folder . '/' . $data['id'] . '_' . $data['id_author'] . '_big.jpg';
							mkdir($folder);
							$thumb = new Thumb();
							try {
								$thumb->createThumbnails($tmp_name, array($filename, $filename_o), $cover_sizes);
							} catch (Exception $e) {
								return false;
							}
							unlink($tmp_name);
							return true;
						}
				}else
					unlink($tmp_name);
			}
		}
		return false;
	} catch (Exception $e) {
		return false;
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