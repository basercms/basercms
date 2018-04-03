<?php
/**
 * baserCMS :  Based Website Development Project <http://basercms.net>
 * Copyright (c) baserCMS Users Community <http://basercms.net/community/>
 *
 * @copyright		Copyright (c) baserCMS Users Community
 * @link			http://basercms.net baserCMS Project
 * @package			Blog.View
 * @since			baserCMS v 0.1.0
 * @license			http://basercms.net/license/index.html
 */

/**
 * [PUBLISH] RSS
 */
?>
<?php
if($posts){
	echo $this->Rss->items($posts,'transformRSS');
}

function transformRSS($data) {
	$view = new View();
	$blogHelper = new BlogHelper($view);
	$bcBaserhelper = new BcBaserHelper($view);
	$url = $bcBaserhelper->getContentsUrl() . 'archives/' . $data['BlogPost']['no'];
	if (!empty($data['BlogPost']['eye_catch'])) {
		$data['BlogPost']['eye_catch'] = Router::url($blogHelper->getEyeCatch($data, array('imgsize' => '', 'output' => 'url')), true);
	} else {
		$data['BlogPost']['eye_catch'] = '';
	}
	return [
		'title' => $data['BlogPost']['name'],
		'link' => $url,
		'guid' => $url,
		'category' => $data['BlogCategory']['title'],
		'description' => $blogHelper->removeCtrlChars($data['BlogPost']['content'] . $data['BlogPost']['detail']),
		'pubDate' => $data['BlogPost']['posts_date'],
		'eye_catch' => $data['BlogPost']['eye_catch'],
	];
}
?>
