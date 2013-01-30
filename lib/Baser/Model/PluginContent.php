<?php
/* SVN FILE: $Id$ */
/**
 * プラグインコンテンツモデル
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
 * メニューモデル
 *
 * @package baser.models
 */
class PluginContent extends AppModel {
/**
 * クラス名
 *
 * @var string
 * @access public
 */
	public $name = 'PluginContent';
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
					'message'	=> 'コンテンツ名は半角英数字、ハイフン、アンダースコアのみで入力してください。'),
			array(	'rule'		=> array('isUnique'),
					'on'		=> 'create',
					'message'	=>	'入力されたコンテンツ名は既に使用されています。'),
			array(	'rule'		=> array('maxLength', 50),
					'message'	=> 'コンテンツ名は50文字以内で入力してください。')
		),
		'content_id' => array(
			array(	'rule'		=> array('notEmpty'),
					'message'	=> 'コンテンツIDを入力してください。',
					'on'		=> 'update')
		),
		'plugin' => array(
			array(	'rule'		=> array('alphaNumericPlus'),
					'message'	=> 'プラグイン名は半角英数字、ハイフン、アンダースコアのみで入力してください。'),
			array(	'rule'		=> array('notEmpty'),
					'message'	=> 'プラグイン名を入力してください。'),
			array(	'rule'		=> array('maxLength', 20),
					'message'	=> 'プラグイン名は20文字以内で入力してください。')
		)
	);
/**
 * プラグイン名の書き換え
 * DBに登録したデータを元にURLのプラグイン名部分を書き換える。
 * 
 * @param $url
 * @return array Or false
 * @access public
 */
	public function currentPluginContent($url) {

		if(!$url) {
			return false;
		}

		$url = preg_replace('/^\//','',$url);
		if(strpos($url, '/') !== false) {
			list($name) = split('/',$url);
		}else {
			$name = $url;
		}

		// @toto basercamp 暫定処理、routes.phpのコメント読むこと
		if( $name === 'feed' ){
			return  array('PluginContent' => array('name'=>'feed','plugin'=>'feed'));
		}

		return $pluginContent = $this->find('first', array(
			'fields' => array('name', 'plugin'),
			'conditions'=> array('PluginContent.name' => $name)
		));

	}

}
