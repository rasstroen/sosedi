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

	$time_update = time() - 31 * 24 * 60 * 60;
	$cover_sizes = array(
		array(100, 100, false),
		array(50, 50, false),
	);

	if (!is_running_process(basename(__FILE__))) {
		$authors_processed = true;
		while ($authors_processed) {
			$authors_processed = false;
			$query = 'SELECT * FROM `authors` WHERE `pic_update`<' . $time_update . ' ORDER BY `pic_update` LIMIT 10';
			$authors = Database::sql2array($query, 'id');
			$authors_processed = count($authors) > 0;
			foreach ($authors as $author) {
				$url = 'http://' . $author['username'] . '.livejournal.com/data/foaf';
				$foaf = grab($url);

				if ($foaf) {
					$rss = new DOMDocument();
					$rss->loadXML($foaf);

					preg_match_all('/lj\:dateCreated\=\"(.*)\"/isU', $foaf, $created);
					if (isset($created[1][0]))
						$created = strtotime($created[1][0]);
					else
						$created = 0;
					preg_match_all('/lj\:dateLastUpdated\=\"(.*)\"/isU', $foaf, $updated);
					if (isset($updated[1][0]))
						$updated = strtotime($updated[1][0]);
					else
						$updated = 0;

					$posts = array();
					$bio = false;
					foreach ($rss->getElementsByTagName('Person') as $item) {
						foreach ($item->getElementsByTagName('*') as $field) {
							if($field->nodeName == 'foaf:knows')
								break;
								
							if(!isset($bio[$field->nodeName]))
							$bio[$field->nodeName] = $field->nodeValue;
							if($field->nodeName == 'foaf:img')
							$bio[$field->nodeName] = $field->attributes->item(0)->value;
						}
						break;
					}
					

					if ($bio['foaf:img']) {
						$pic = 0;
						$imgdata = grab($bio['foaf:img']);
						$tmp_name = '/tmp/aimage.jpg';
						file_put_contents($tmp_name, $imgdata);
						$size = getimagesize($tmp_name);
						if ($size) {
							if ($size[0] >= 10)
								if ($size[1] >= 10) {
									$folder = Config::need('static_path') . 'upload/author_images/' . ceil($author['id'] / 500);
									$filename = $folder . '/' . $author['id'] . '.jpg';
									$filename1 = $folder . '/' . $author['id'] . '_small.jpg';
									mkdir($folder);
									$thumb = new Thumb();
									try {
										$thumb->createThumbnails($tmp_name, array($filename,$filename1), $cover_sizes);
										$pic = 1;
									} catch (Exception $e) {

										continue;
									}
									unlink($tmp_name);
								}
						}else
							unlink($tmp_name);
					}else {
						$pic = 0;
					}

					$query = 'UPDATE `authors` SET
					`journaltitle`=' . Database::escape($bio['lj:journaltitle']) . ', 
					`journalsubtitle`=' . Database::escape($bio['lj:journalsubtitle']) . ', 
					`posted`=' . Database::escape((int) $bio['ya:posted']) . ', 
					`has_pic`=' . $pic . ',
					`created`=' . $created . ',
					`updated`=' . $updated . '
					WHERE `id`=' . $author['id'];
					Database::query($query);
				}
			}
			if ($authors_processed) {
				$query = 'UPDATE `authors` SET `pic_update`=' . time() . ' WHERE `id` IN(' . implode(',', array_keys($authors)) . ')';
				Database::query($query);
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