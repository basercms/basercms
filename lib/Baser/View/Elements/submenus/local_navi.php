<?php
/**
 * baserCMS :  Based Website Development Project <https://basercms.net>
 * Copyright (c) baserCMS Users Community <https://basercms.net/community/>
 *
 * @copyright       Copyright (c) baserCMS Users Community
 * @link            https://basercms.net baserCMS Project
 * @package         Baser.View
 * @since           baserCMS v 0.1.0
 * @license         https://basercms.net/license/index.html
 */

/**
 * [PUBLISH] ローカルナビゲーション
 */
if (empty($this->request->params['Content'])) {
	return;
}
if ($this->request->params['Content']['type'] == 'ContentFolder') {
	$parentId = $this->request->params['Content']['id'];
	$title = $this->request->params['Content']['title'];
} else {
	$parent = $this->BcContents->getParent($this->request->params['Content']['id']);
	$parentId = $parent['Content']['id'];
	$title = $parent['Content']['title'];
}
if ($parent['Content']['site_root']) {
	return;
}
?>


<div class="local-navi">
	<h2><?php echo h($title) ?></h2>
	<?php $this->BcBaser->contentsMenu($parentId, 1, $this->request->params['Content']['id']) ?>
</div>
