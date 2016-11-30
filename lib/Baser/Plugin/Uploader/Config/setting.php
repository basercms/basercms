<?php
/**
 * baserCMS :  Based Website Development Project <http://basercms.net>
 * Copyright (c) baserCMS Users Community <http://basercms.net/community/>
 *
 * @copyright		Copyright (c) baserCMS Users Community
 * @link			http://basercms.net baserCMS Project
 * @package			Uploader.Config
 * @since			baserCMS v 3.0.10
 * @license			http://basercms.net/license/index.html
 */

/**
 * システムナビ
 */
$config['BcApp.adminNavi'] = [
	'Plugin' => [
		'menus' => [
			'Uploader' => ['title' => 'アップロードファイル一覧', 'url' => ['admin' => true, 'plugin' => 'uploader', 'controller' => 'uploader_files', 'action' => 'index']],
		]
	]
];
