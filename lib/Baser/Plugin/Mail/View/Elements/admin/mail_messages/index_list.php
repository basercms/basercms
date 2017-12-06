<?php
/**
 * baserCMS :  Based Website Development Project <http://basercms.net>
 * Copyright (c) baserCMS Users Community <http://basercms.net/community/>
 *
 * @copyright		Copyright (c) baserCMS Users Community
 * @link			http://basercms.net baserCMS Project
 * @package			Baser.View
 * @since			baserCMS v 0.1.0
 * @license			http://basercms.net/license/index.html
 */

/**
 * [ADMIN] 受信メール一覧　テーブル
 * @var \BcAppView $this
 */
$this->BcListTable->setColumnNumber(6);
?>


<!-- pagination -->
<?php $this->BcBaser->element('pagination') ?>

<!-- list -->
<table cellpadding="0" cellspacing="0" class="list-table sort-table" id="ListTable">
<thead>
	<tr>
		<th style="white-space: nowrap" class="list-tool">
			<?php if ($this->BcBaser->isAdminUser()): ?>
			<div>
				<?php echo $this->BcForm->checkbox('ListTool.checkall', ['title' => '一括選択']) ?>
				<?php echo $this->BcForm->input('ListTool.batch', ['type' => 'select', 'options' => ['del' => '削除'], 'empty' => '一括処理']) ?>
				<?php echo $this->BcForm->button('適用', ['id' => 'BtnApplyBatch', 'disabled' => 'disabled']) ?>
			</div>
			<?php endif; ?>
		</th>
		<th style="white-space: nowrap"><?php echo $this->Paginator->sort('id', ['asc' => $this->BcBaser->getImg('admin/blt_list_down.png', ['alt' => '昇順', 'title' => '昇順']) . ' NO', 'desc' => $this->BcBaser->getImg('admin/blt_list_up.png', ['alt' => '降順', 'title' => '降順']) . ' NO'], ['escape' => false, 'class' => 'btn-direction']) ?></th>
		<th style="white-space: nowrap" colspan="2"><?php echo $this->Paginator->sort('created', ['asc' => $this->BcBaser->getImg('admin/blt_list_down.png', ['alt' => '昇順', 'title' => '昇順']) . ' 受信日時', 'desc' => $this->BcBaser->getImg('admin/blt_list_up.png', ['alt' => '降順', 'title' => '降順']) . ' 受信日時'], ['escape' => false, 'class' => 'btn-direction']) ?></th>
		<th style="white-space: nowrap">受信内容</th>
		<th style="white-space: nowrap">添付</th>
		<?php echo $this->BcListTable->dispatchShowHead() ?>
	</tr>
</thead>
<tbody>
<?php if ($messages): ?>
		<?php $count = 0; ?>
		<?php foreach ($messages as $data): ?>
			<?php $this->BcBaser->element('mail_messages/index_row', ['data' => $data, 'count' => $count]) ?>
			<?php $count++; ?>
		<?php endforeach; ?>
	<?php else: ?>
		<tr><td colspan="<?php echo $this->BcListTable->getColumnNumber() ?>"><p class="no-data">データが見つかりませんでした。</p></td></tr>
<?php endif ?>
</tbody>
</table>

<!-- list-num -->
<?php $this->BcBaser->element('list_num') ?>
