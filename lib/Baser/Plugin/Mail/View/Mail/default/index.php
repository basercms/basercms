<?php
/* SVN FILE: $Id$ */
/**
 * [PUBLISH] メールフォーム
 * 
 * PHP versions 5
 *
 * baserCMS :  Based Website Development Project <http://basercms.net>
 * Copyright 2008 - 2012, baserCMS Users Community <http://sites.google.com/site/baserusers/>
 *
 * @copyright		Copyright 2008 - 2012, baserCMS Users Community
 * @link			http://basercms.net baserCMS Project
 * @package			baser.plugins.mail.views
 * @since			baserCMS v 0.1.0
 * @version			$Revision$
 * @modifiedby		$LastChangedBy$
 * @lastmodified	$Date$
 * @license			http://basercms.net/license/index.html
 */
$this->bcBaser->css(array('/mail/css/style', 'jquery-ui/ui.all'), array('inline' => true));
$this->bcBaser->js(array('jquery-ui-1.8.14.custom.min','i18n/ui.datepicker-ja'), false);
?>

<h2 class="contents-head">
	<?php $this->bcBaser->contentsTitle() ?>
</h2>

<h3 class="contents-head">入力フォーム</h3>

<?php if($this->mail->descriptionExists()): ?>
<div class="section mail-description">
	<?php $this->mail->description() ?>
</div>
<?php endif ?>

<div class="section">
	<?php $this->bcBaser->flash() ?>
	<?php $this->bcBaser->element('mail_form') ?>
</div>
