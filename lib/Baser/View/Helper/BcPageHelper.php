<?php
/**
 * ページヘルパー
 * 
 * baserCMS :  Based Website Development Project <http://basercms.net>
 * Copyright 2008 - 2015, baserCMS Users Community <http://sites.google.com/site/baserusers/>
 *
 * @copyright		Copyright 2008 - 2015, baserCMS Users Community
 * @link			http://basercms.net baserCMS Project
 * @package			Baser.View.Helper
 * @since			baserCMS v 0.1.0
 * @license			http://basercms.net/license/index.html
 */

App::uses('Helper', 'View');

/**
 * ページヘルパー
 *
 * @package Baser.View.Helper
 */
class BcPageHelper extends Helper {

/**
 * ページモデル
 * 
 * @var Page
 * @access public
 */
	public $Page = null;

/**
 * data
 * @var array
 * @access public
 */
	public $data = array();

/**
 * ヘルパー
 * 
 * @var array
 */
	public $helpers = array('BcBaser');

/**
 * construct
 * 
 * @param object $View
 * @return void
 */
	public function __construct(View $View) {

		parent::__construct($View);
		if (ClassRegistry::isKeySet('Page')) {
			$this->Page = ClassRegistry::getObject('Page');
		} else {
			$this->Page = ClassRegistry::init('Page', 'Model');
		}
	}

/**
 * beforeRender
 * 
 * @param string $viewFile (継承もとで利用中) The view file that is going to be rendered
 * @return void
 */
	public function beforeRender($viewFile) {
		//if ($this->request->params['controller'] == 'pages' && ($this->request->params['action'] == 'display' || $this->request->params['action'] == 'smartphone_display') && isset($this->request->params['pass'][0])) {
		if ($this->request->params['controller'] == 'pages' && preg_match('/(^|_)display$/', $this->request->params['action'])) {
			// @TODO ページ機能が.html拡張子なしに統合できたらコメントアウトされたものに切り替える
			//$this->request->data = $this->Page->findByUrl('/'.impload('/',$this->request->params['pass'][0]));
			$param = Configure::read('BcRequest.pureUrl');
			if ($param === '') {
				$param = 'index';
			} elseif ($param && preg_match('/\/$/is', $param)) {
				$param .= 'index';
			}
			
			if (Configure::read('BcRequest.agent')) {
				$agentPrefix = Configure::read('BcRequest.agentPrefix');
				if(empty($this->BcBaser->siteConfig['linked_pages_' . $agentPrefix])) {
					$param = $agentPrefix . '/' . $param;
				}
			}
			$param = preg_replace("/\.html$/", '', $param);
			$this->request->data = $this->Page->findByUrl('/' . $param);
		}
	}

/**
 * ページ機能用URLを取得する
 * 
 * @param array $page 固定ページデータ
 * @return string URL
 */
	public function getUrl($page) {
		if(isset($page['Page'])) {
			$page = $page['Page'];
		}
		if(!isset($page['url'])) {
			return '';
		}
		return $this->Page->convertViewUrl($page['url']);
	}

/**
 * 現在のページが所属するカテゴリデータを取得する
 * 
 * @return array 失敗すると getCategory() は FALSE を返します。
 */
	public function getCategory() {

		if (!empty($this->request->data['PageCategory']['id'])) {
			return $this->request->data['PageCategory'];
		} else {
			return false;
		}
	}

/**
 * 現在のページが所属する親のカテゴリを取得する
 *
 * @param boolean $top 親カテゴリが存在するかどうか、 オプションのパラメータ、初期値はオプションのパラメータ、初期値は false
 * @return array
 */
	public function getParentCategory($top = false) {

		$category = $this->getCategory();
		if (empty($category['id'])) {
			return false;
		}
		if ($top) {
			$path = $this->Page->PageCategory->getPath($category['id']);
			if ($path) {
				$parent = $path[0];
			} else {
				return false;
			}
		} else {
			$parent = $this->Page->PageCategory->getParentNode($category['id']);
		}
		return $parent;
	}

/**
 * ページリストを取得する
 * 
 * @param int $pageCategoryId
 * @param int $recursive
 * @return array
 */
	public function getPageList($pageCategoryId, $recursive = null) {

		return $this->requestAction('/contents/get_page_list_recursive', array('pass' => array($pageCategoryId, $recursive)));
	}

/**
 * カテゴリ名を取得する
 * 
 * @return mixed string / false
 */
	public function getCategoryName() {

		$category = $this->getCategory();
		if ($category['name']) {
			return $category['name'];
		} else {
			return false;
		}
	}

/**
 * 公開状態を取得する
 *
 * @param array データリスト
 * @return boolean 公開状態
 */
	public function allowPublish($data) {

		if (isset($data['Page'])) {
			$data = $data['Page'];
		}

		$allowPublish = (int) $data['status'];

		// 期限を設定している場合に条件に該当しない場合は強制的に非公開とする
		if (($data['publish_begin'] != 0 && $data['publish_begin'] >= date('Y-m-d H:i:s')) ||
			($data['publish_end'] != 0 && $data['publish_end'] <= date('Y-m-d H:i:s'))) {
			$allowPublish = false;
		}

		return $allowPublish;
	}

/**
 * ページカテゴリ間の次の記事へのリンクを取得する
 *
 * @param string $title
 * @param array $options オプション（初期値 : array()）
 *	- `class` : CSSのクラス名（初期値 : 'next-link'）
 *	- `arrow` : 表示文字列（初期値 : ' ≫'）
 *	- `overCategory` : 固定ページのカテゴリをまたいで次の記事のリンクを取得するかどうか（初期値 : false）
 *  - `prefix` : PC（初期値 : null） or 'mobile' or 'smartphone'
 * @return void コンテンツナビが無効かつオプションoverCategoryがtrueでない場合はfalseを返す
 */
	public function getNextLink($title = '', $options = array()) {
		
		$options = array_merge(array(
			'class'			=> 'next-link',
			'arrow'			=> ' ≫',
			'overCategory'	=> false,
			'prefix'		=> null
		), $options);
		
		if (!$this->contentsNaviAvailable() && $options['overCategory'] !== true) {
			return false;
		}

		if (ClassRegistry::isKeySet('Page')) {
			$PageClass = ClassRegistry::getObject('Page');
		} else {
			$PageClass = ClassRegistry::init('Page');
		}

		$arrow = $options['arrow'];
		unset($options['arrow']);
		$overCategory = $options['overCategory'];
		unset($options['overCategory']);
		$prefix = $options['prefix'];
		unset($options['prefix']);
		
		if ($overCategory === true) {
			// ページ情報を持っていない場合はリンクを表示しない
			if (!isset($this->request->data['Page']['sort'])) {
				return false;
			}
			$pageUrlConditions = $this->getPageUrlConditions($prefix);
			$conditions = am(array(
				'Page.sort >' => $this->request->data['Page']['sort'],
				'AND' => $pageUrlConditions,
			),$PageClass->getConditionAllowPublish());
		} else {
			$conditions = am(array(
				'Page.sort >' => $this->request->data['Page']['sort'],
				'Page.page_category_id' => $this->request->data['Page']['page_category_id']
			), $PageClass->getConditionAllowPublish());
		}

		$nextPost = $PageClass->find('first', array(
			'conditions' => $conditions,
			'fields' => array('title', 'url'),
			'order' => 'sort',
			'recursive' => -1,
			'cache' => false
		));
		if ($nextPost) {
			if (!$title) {
				$title = $nextPost['Page']['title'] . $arrow;
			}
			$prefixes = Configure::read('BcAgent');	
			return $this->BcBaser->getLink($title, preg_replace('/^\/' . $prefixes['mobile']['prefix'] . '/', '/' . $prefixes['mobile']['alias'], $nextPost['Page']['url']), $options);
		}
	}

/**
 * ページカテゴリ間の次の記事へのリンクを出力する
 *
 * @param string $title
 * @param array $options オプション（初期値 : array()）
 *	- `class` : CSSのクラス名（初期値 : 'next-link'）
 *	- `arrow` : 表示文字列（初期値 : ' ≫'）
 *	- `overCategory` : 固定ページのカテゴリをまたいで次の記事のリンクを取得するかどうか（初期値 : false）
 *  - `prefix` : PC（初期値 : null） or 'mobile' or 'smartphone'
 * @return @return void コンテンツナビが無効かつオプションoverCategoryがtrueでない場合はfalseを出力する
 */
	public function nextLink($title = '', $options = array()) {
		echo $this->getNextLink($title, $options);
	}

/**
 * ページカテゴリ間の前の記事へのリンクを取得する
 *
 * @param string $title
 * @param array $options オプション（初期値 : array()）
 *	- `class` : CSSのクラス名（初期値 : 'prev-link'）
 *	- `arrow` : 表示文字列（初期値 : ' ≫'）
 *	- `overCategory` : 固定ページのカテゴリをまたいで次の記事のリンクを取得するかどうか（初期値 : false）
 *  - `prefix` : PC（初期値 : null） or 'mobile' or 'smartphone'
 * @return void コンテンツナビが無効かつオプションoverCategoryがtrueでない場合はfalseを返す
 */
	public function getPrevLink($title = '', $options = array()) {
		
		$options = array_merge(array(
			'class'			=> 'prev-link',
			'arrow'			=> '≪ ',
			'overCategory'	=> false,
			'prefix'		=> null
		), $options);

		if (!$this->contentsNaviAvailable() && $options['overCategory'] !== true) {
			return false;
		}

		if (ClassRegistry::isKeySet('Page')) {
			$PageClass = ClassRegistry::getObject('Page');
		} else {
			$PageClass = ClassRegistry::init('Page');
		}

		$arrow = $options['arrow'];
		unset($options['arrow']);
		$overCategory = $options['overCategory'];
		unset($options['overCategory']);
		$prefix = $options['prefix'];
		unset($options['prefix']);
		
		if ($overCategory === true) {
			// ページ情報を持っていない場合はリンクを表示しない
			if (!isset($this->request->data['Page']['sort'])) {
				return false;
			}
			$pageUrlConditions = $this->getPageUrlConditions($prefix);
			$conditions = am(array(
				'Page.sort <' => $this->request->data['Page']['sort'],
				'AND' => $pageUrlConditions
			), $PageClass->getConditionAllowPublish());
		} else {
			$conditions = am(array(
				'Page.sort <' => $this->request->data['Page']['sort'],
				'Page.page_category_id' => $this->request->data['Page']['page_category_id']
			), $PageClass->getConditionAllowPublish());
		}
		
		$nextPost = $PageClass->find('first', array(
			'conditions' => $conditions,
			'fields' => array('title', 'url'),
			'order' => 'sort DESC',
			'recursive' => -1,
			'cache' => false
		));
		if ($nextPost) {
			if (!$title) {
				$title = $arrow . $nextPost['Page']['title'];
			}
			$prefixes = Configure::read('BcAgent');
			return $this->BcBaser->getLink($title, preg_replace('/^\/' . $prefixes['mobile']['prefix'] . '/', '/' . $prefixes['mobile']['alias'], $nextPost['Page']['url']), $options);
		}
	}

/**
 * ページカテゴリ間の前の記事へのリンクを出力する
 *
 * @param string $title
 * @param array $options オプション（初期値 : array()）
 *	- `class` : CSSのクラス名（初期値 : 'prev-link'）
 *	- `arrow` : 表示文字列（初期値 : ' ≫'）
 *	- `overCategory` : 固定ページのカテゴリをまたいで次の記事のリンクを取得するかどうか（初期値 : false）
 *  - `prefix` : PC（初期値 : null） or 'mobile' or 'smartphone'
 * @return void コンテンツナビが無効かつオプションoverCategoryがtrueでない場合はfalseを返す
 */
	public function prevLink($title = '', $options = array()) {
		echo $this->getPrevLink($title, $options);
	}
	
/**
 * Page.urlのConditionを取得する
 * 
 * @param string $prefix 
 * @return array 
 */
	protected function getPageUrlConditions($prefix = null) {
		$pageUrlConditions = array();
		
		if ($prefix) {
			$pageUrlConditions[] = array('Page.url LIKE' => '/' . $prefix . '/%');
		} else {
			// 指定がなければprefixが指定されたページを除外する
			$prefixes = Configure::read('BcAgent');
			foreach($prefixes as $prefix) {
				$pageUrlConditions[] = array('Page.url NOT LIKE' => '/' . $prefix['prefix'] . '/%');
			}
		}
		return $pageUrlConditions;
	}

/**
 * コンテンツナビ有効チェック
 *
 * @return boolean
 */
	public function contentsNaviAvailable() {

		if (empty($this->request->data['Page']['page_category_id']) || empty($this->request->data['PageCategory']['contents_navi'])) {
			return false;
		} else {
			return true;
		}
	}

/**
 * 固定ページのコンテンツを出力する
 * 
 * @return void
 */
	public function content() {

		$agent = '';
		if (Configure::read('BcRequest.agentPrefix')) {
			$agent = Configure::read('BcRequest.agentPrefix');
		}
		$path = $this->_View->getVar('pagePath');
		
		if ($agent) {
			$url = '/' . implode('/', $this->request->params['pass']);
			$linked = $this->Page->isLinked($agent, $url);
			if (!$linked) {
				$path = $agent . DS . $path;
			}
		}
		echo $this->_View->evaluate(getViewPath() . 'Pages' . DS . $path . '.php', $this->_View->viewVars);

	}

/**
 * テンプレートを取得
 * セレクトボックスのソースとして利用
 * 
 * @param string $type layout or content
 * @param string $agent '' or mobile or smartphone
 * @return array
 */
	public function getTemplates($type = 'layout', $agent = '') {

		$agentPrefix = '';
		if ($agent) {
			$agentPrefix = Configure::read('BcAgent.' . $agent . '.prefix');
		}

		$siteConfig = Configure::read('BcSite');
		$themePath = WWW_ROOT . 'theme' . DS . $siteConfig['theme'] . DS;
		$viewPaths = array_merge(array($themePath), App::path('View'));
		$ext = Configure::read('BcApp.templateExt');

		$templates = array();

		foreach ($viewPaths as $viewPath) {

			$templatePath = '';
			switch ($type) {
				case 'layout':
					if (!$agentPrefix) {
						$templatePath = $viewPath . 'Layouts' . DS;
					} else {
						$templatePath = $viewPath . 'Layouts' . DS . $agentPrefix . DS;
					}
					break;
				case 'content':
					if (!$agentPrefix) {
						$templatePath = $viewPath . 'Pages' . DS . 'templates' . DS;
					} else {
						$templatePath = $viewPath . 'Pages' . DS . $agentPrefix . DS . 'templates' . DS;
					}
					break;
			}

			if (!$templatePath) {
				continue;
			}

			$Folder = new Folder($templatePath);
			$files = $Folder->read(true, true);
			if ($files[1]) {
				foreach ($files[1] as $file) {
					if (preg_match('/(.+)' . preg_quote($ext) . '$/', $file, $matches)) {
						if (!in_array($matches[1], $templates)) {
							$templates[] = $matches[1];
						}
					}
				}
			}
		}

		if ($templates) {
			return array_combine($templates, $templates);
		} else {
			return array();
		}
	}

	public function treeList($datas, $recursive = 0) {
		return $this->BcBaser->getElement('pages/index_tree_list', array('datas' => $datas, 'recursive' => $recursive));
	}

}
