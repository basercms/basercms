<?php
/* SVN FILE: $Id$ */
/**
 * プラグインモデル
 *
 * PHP versions 5
 *
 * baserCMS :  Based Website Development Project <http://basercms.net>
 * Copyright 2008 - 2012, baserCMS Users Community <http://sites.google.com/site/baserusers/>
 *
 * @copyright		Copyright 2008 - 2012, baserCMS Users Community
 * @link			http://basercms.net baserCMS Project
 * @package			baser.models
 * @since			baserCMS v 0.1.0
 * @version			$Revision$
 * @modifiedby		$LastChangedBy$
 * @lastmodified	$Date$
 * @license			http://basercms.net/license/index.html
 */
/**
 * Include files
 */
/**
 * プラグインモデル
 *
 * @package baser.models
 */
class Plugin extends AppModel {
/**
 * クラス名
 *
 * @var string
 * @access public
 */
	public $name = 'Plugin';
/**
 * ビヘイビア
 * 
 * @var array
 * @access public
 */
	public $actsAs = array('BcCache');
/**
 * データベース接続
 *
 * @var string
 * @access public
 */
	public $useDbConfig = 'baser';
/**
 * バリデーション
 *
 * @var array
 * @access public
 */
	public $validate = array(
		'name' => array(
			array(	'rule'		=> array('alphaNumericPlus'),
					'message'	=> 'プラグイン名は半角英数字、ハイフン、アンダースコアのみが利用可能です。',
					'reqquired'	=> true),
			array(	'rule'		=> array('isUnique'),
					'on'		=> 'create',
					'message'	=>	'指定のプラグインは既に使用されています。'),
			array(	'rule'		=> array('maxLength', 50),
					'message'	=> 'プラグイン名は50文字以内としてください。')
		),
		'title' => array(
			array(	'rule'		=> array('maxLength', 50),
					'message'	=> 'プラグインタイトルは50文字以内とします。')
		)
	);
/**
 * データベースを初期化する
 * 既存のテーブルは上書きしない
 *
 * @param string $plugin
 * @return boolean
 * @access public
 */
	public function initDb($dbConfigName, $pluginName = '', $loadCsv = true, $filterTable = '', $filterType = '') {
		
		return parent::initDb('plugin', $pluginName, true, $filterTable, 'create');
		
	}
/**
 * データベースの構造を変更する
 * 
 * @param string $plugin
 * @return boolean
 * @access public
 */
	public function alterDb($plugin, $dbConfigName = 'baser', $filterTable = '') {
		
		return parent::initDb($dbConfigName, $plugin, false, $filterTable, 'alter');
		
	}
	
}
