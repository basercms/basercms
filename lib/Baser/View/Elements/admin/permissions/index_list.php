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
 * [ADMIN] アクセス制限設定一覧
 * 
 * @var \BcAppView $this
 */
$this->BcListTable->setColumnNumber(5);
?>


<table cellpadding="0" cellspacing="0" class="list-table sort-table" id="ListTable">
	<thead>
		<tr>
			<th class="list-tool">
	<div>
		<?php $this->BcBaser->link($this->BcBaser->getImg('admin/btn_add.png', ['width' => 69, 'height' => 18, 'alt' => '新規追加', 'class' => 'btn']), ['action' => 'add', $this->request->params['pass'][0]]) ?>
		<?php if (!$sortmode): ?>
			<?php $this->BcBaser->link($this->BcBaser->getImg('admin/btn_sort.png', ['width' => 65, 'height' => 14, 'alt' => '並び替え', 'class' => 'btn']), ['sortmode' => 1, $this->request->params['pass'][0]]) ?>
		<?php else: ?>
			<?php $this->BcBaser->link($this->BcBaser->getImg('admin/btn_normal.png', ['width' => 65, 'height' => 14, 'alt' => 'ノーマル', 'class' => 'btn']), ['sortmode' => 0, $this->request->params['pass'][0]]) ?>
		<?php endif ?>
	</div>
	<?php if ($this->BcBaser->isAdminUser()): ?>
		<div>
			<?php echo $this->BcForm->checkbox('ListTool.checkall', ['title' => '一括選択']) ?>
			<?php echo $this->BcForm->input('ListTool.batch', ['type' => 'select', 'options' => ['publish' => '有効', 'unpublish' => '無効', 'del' => '削除'], 'empty' => '一括処理']) ?>
			<?php echo $this->BcForm->button('適用', ['id' => 'BtnApplyBatch', 'disabled' => 'disabled']) ?>
		</div>
	<?php endif ?>
</th>
<th>NO</th>
<th>ルール名<br />URL設定</th>
<th>アクセス</th>
<?php echo $this->BcListTable->dispatchShowHead() ?>
<th>登録日<br />更新日</th>
</tr>
</thead>
<tbody>
	<?php if (!empty($datas)): ?>
		<?php foreach ($datas as $data): ?>
			<?php $this->BcBaser->element('permissions/index_row', ['data' => $data]) ?>
		<?php endforeach; ?>
	<?php else: ?>
		<tr>
			<td colspan="<?php echo $this->BcListTable->getColumnNumber() ?>"><p class="no-data">データが見つかりませんでした。</p></td>
		</tr>
	<?php endif; ?>
</tbody>
</table>
