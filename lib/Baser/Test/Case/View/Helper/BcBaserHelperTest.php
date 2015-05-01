<?php
/**
 * test for BcBaserHelper
 *
 * baserCMS :  Based Website Development Project <http://basercms.net>
 * Copyright 2008 - 2015, baserCMS Users Community <http://sites.google.com/site/baserusers/>
 *
 * @copyright		Copyright 2008 - 2015, baserCMS Users Community
 * @link			http://basercms.net baserCMS Project
 * @package			Baser.Test.Case.View.Helper
 * @since	       baserCMS v 3.0.6
 * @license			http://basercms.net/license/index.html
 */

App::uses('BcAppView', 'View');
App::uses('BcBaserHelper', 'View/Helper');

/**
 * BcBaser helper library.
 *
 * @package       Baser.Test.Case
 * @property      BcBaserHelper $BcBaser
 */
class BcBaserHelperTest extends BaserTestCase {

/**
 * Fixtures
 * @var array 
 */
	public $fixtures = array(
		'baser.View.Helper.BcBaserHelper.MenuBcBaserHelper',
		'baser.View.Helper.BcBaserHelper.PageBcBaserHelper',
		'baser.Default.PluginContent',
		'baser.Default.Content',
		'baser.Default.SiteConfig',
		'baser.Default.User',
		'baser.Default.UserGroup',
		'baser.Default.Favorite',
		'baser.Default.Permission',
		'baser.Default.PageCategory',
		'baser.Default.ThemeConfig',
	);

/**
 * View
 * 
 * @var View
 */
	protected $_View;

/**
 * __construct
 * 
 * @param string $name
 * @param array $data
 * @param string $dataName
 */
	public function __construct($name = null, array $data = array(), $dataName = '') {
		parent::__construct($name, $data, $dataName);
	}

/**
 * setUp
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();
		$this->_View = new BcAppView();
		$SiteConfig = ClassRegistry::init('SiteConfig');
		$this->_View->set('siteConfig', $SiteConfig->findExpanded());
		$this->_View->helpers = array('BcBaser');
		$this->_View->loadHelpers();
		$this->BcBaser = $this->_View->BcBaser;
	}

/**
 * tearDown
 *
 * @return void
 */
	public function tearDown() {
		unset($this->BcBaser);
		Router::reload();
		parent::tearDown();
	}

/**
 * ログイン状態にする
 *
 * @return void
 */
	protected function _login() {
		$User = ClassRegistry::init('User');
		$user = $User->find('first', array('conditions' => array('User.id' => 1), 'recursive' => -1));
		unset($user['User']['password']);
		$this->BcBaser->set('user', $user['User']);
	}

/**
 * ログイン状態を解除する
 *
 * @return void
 */
	protected function _logout() {
		$this->BcBaser->set('user', '');
	}

/**
 * コンストラクタ
 *
 * @return void
 */
	public function testConstruct() {
		$this->markTestIncomplete('このテストは、まだ実装されていません。');
	}

/**
 * メニューを取得する
 *
 * @return void
 */
	public function testGetMenus() {
		$result = $this->BcBaser->getMenus();
		$this->assertEquals(7, count($result));
		$this->assertEquals(true, isset($result[0]['Menu']['id']));
	}

/**
 * タイトルを設定する
 *
 * @return void
 */
	public function testSetTitle() {
		$topTitle = '｜baserCMS inc. [デモ]';

		// カテゴリがない場合
		$this->BcBaser->setTitle('会社案内');
		$this->assertEquals("会社案内{$topTitle}", $this->BcBaser->getTitle());

		// カテゴリがある場合
		$this->BcBaser->_View->set('crumbs', array(
			array('name' => '会社案内', 'url' => '/company/index'),
			array('name' => '会社データ', 'url' => '/company/data')
		));
		$this->BcBaser->setTitle('会社沿革');
		$this->assertEquals("会社沿革｜会社データ｜会社案内{$topTitle}", $this->BcBaser->getTitle());

		// カテゴリは存在するが、カテゴリの表示をオフにした場合
		$this->BcBaser->setTitle('会社沿革', false);
		$this->assertEquals("会社沿革{$topTitle}", $this->BcBaser->getTitle());
	}

/**
 * meta タグのキーワードを設定する
 *
 * @return void
 */
	public function testSetKeywords() {
		$this->BcBaser->setKeywords('baserCMS,国産,オープンソース');
		$this->assertEquals('baserCMS,国産,オープンソース', $this->BcBaser->getKeywords());
	}

/**
 * meta タグの説明文を設定する
 *
 * @return void
 */
	public function testSetDescription() {
		$this->BcBaser->setDescription('国産オープンソースのホームページです');
		$this->assertEquals('国産オープンソースのホームページです', $this->BcBaser->getDescription());
	}

/**
 * レイアウトで利用する為の変数を設定する
 *
 * @return void
 */
	public function testSet() {
		$this->BcBaser->set('keywords', 'baserCMS,国産,オープンソース');
		$this->assertEquals('baserCMS,国産,オープンソース', $this->BcBaser->getKeywords());
	}

/**
 * タイトルへのカテゴリタイトルの出力有無を設定する
 *
 * @return void
 */
	public function testSetCategoryTitle() {
		$topTitle = '｜baserCMS inc. [デモ]';

		$this->BcBaser->_View->set('crumbs', array(
			array('name' => '会社案内', 'url' => '/company/index'),
			array('name' => '会社データ', 'url' => '/company/data')
		));
		$this->BcBaser->setTitle('会社沿革');

		// カテゴリをオフにした場合
		$this->BcBaser->setCategoryTitle(false);
		$this->assertEquals("会社沿革{$topTitle}", $this->BcBaser->getTitle());

		// カテゴリをオンにした場合
		$this->BcBaser->setCategoryTitle(true);
		$this->assertEquals("会社沿革｜会社データ｜会社案内{$topTitle}", $this->BcBaser->getTitle());

		// カテゴリを指定した場合
		$this->BcBaser->setCategoryTitle('店舗案内');
		$this->assertEquals("会社沿革｜店舗案内{$topTitle}", $this->BcBaser->getTitle());

		// パンくず用にリンクも指定した場合
		$this->BcBaser->setCategoryTitle(array(
			'name' => '店舗案内',
			'url' => '/shop/index'
		));
		$expected = array(
			array(
				'name'	=> '店舗案内',
				'url'	=> '/shop/index'
			),
			array(
				'name'	=> '会社沿革',
				'url'	=> ''
			)
		);
		$this->assertEquals($expected, $this->BcBaser->getCrumbs());
	}

/**
 * meta タグ用のキーワードを取得する
 *
 * @param string $expected 期待値
 * @param string|null $keyword 設定されるキーワードの文字列
 * @return void
 * @dataProvider getKeywordsDataProvider
 */
	public function testGetKeywords($expected, $keyword = null) {
		if ($keyword !== null) {
			$this->BcBaser->setKeywords($keyword);
		}
		$this->assertEquals($expected, $this->BcBaser->getKeywords());
	}

/**
 * getKeywords用データプロバイダ
 *
 * @return array
 */
	public function getKeywordsDataProvider() {
		return array(
			array('baser,CMS,コンテンツマネジメントシステム,開発支援'),
			array('baser,CMS,コンテンツマネジメントシステム,開発支援', ''),
			array('baserCMS,国産,オープンソース', 'baserCMS,国産,オープンソース'),
		);
	}

/**
 * meta タグ用のページ説明文を取得する
 *
 * @param string $expected 期待値
 * @param string|null $description 設定されるキーワードの文字列
 * @return void
 * @dataProvider getDescriptionDataProvider
 */
	public function testGetDescription($expected, $description = null) {
		if ($description !== null) {
			$this->BcBaser->setDescription($description);
		}
		$this->assertEquals($expected, $this->BcBaser->getDescription());
	}

/**
 * getDescription用データプロバイダ
 *
 * @return array
 */
	public function getDescriptionDataProvider() {
		return array(
			array('baserCMS は、CakePHPを利用し、環境準備の素早さに重点を置いた基本開発支援プロジェクトです。WEBサイトに最低限必要となるプラグイン、そしてそのプラグインを組み込みやすい管理画面、認証付きのメンバーマイページを最初から装備しています。'),
			array('baserCMS は、CakePHPを利用し、環境準備の素早さに重点を置いた基本開発支援プロジェクトです。WEBサイトに最低限必要となるプラグイン、そしてそのプラグインを組み込みやすい管理画面、認証付きのメンバーマイページを最初から装備しています。', ''),
			array('国産オープンソースのホームページです', '国産オープンソースのホームページです')
		);
	}

/**
 * タイトルタグを取得する
 *
 * @return void
 */
	public function testGetTitle() {
		$topTitle = 'baserCMS inc. [デモ]';

		// 通常
		$this->BcBaser->_View->set('crumbs', array(
			array('name' => '会社案内', 'url' => '/company/index'),
			array('name' => '会社データ', 'url' => '/company/data')
		));
		$this->BcBaser->setTitle('会社沿革');
		$this->assertEquals("会社沿革｜会社データ｜会社案内｜{$topTitle}", $this->BcBaser->getTitle());

		// 区切り文字を ≫ に変更
		$this->assertEquals("会社沿革≫会社データ≫会社案内≫{$topTitle}", $this->BcBaser->getTitle('≫'));

		// カテゴリタイトルを除外
		$this->assertEquals("会社沿革｜{$topTitle}", $this->BcBaser->getTitle('｜', false));

		// カテゴリが対象ページと同じ場合に省略する
		$this->BcBaser->setTitle('会社データ');
		$this->assertEquals("会社データ｜会社案内｜{$topTitle}", $this->BcBaser->getTitle('｜', true));
	}

/**
 * パンくずリストの配列を取得する
 *
 * @return void
 */
	public function testGetCrumbs() {
		// パンくずが設定されてない場合
		$result = $this->BcBaser->getCrumbs(true);
		$this->assertEmpty($result);

		// パンくずが設定されている場合
		$this->BcBaser->_View->set('crumbs', array(
			array('name' => '会社案内', 'url' => '/company/index'),
			array('name' => '会社データ', 'url' => '/company/data')
		));
		$this->BcBaser->setTitle('会社沿革');
		$expected = array(
			array('name' => '会社案内', 'url' => '/company/index'),
			array('name' => '会社データ', 'url' => '/company/data'),
			array('name' => '会社沿革', 'url' => '')
		);
		$this->assertEquals($expected, $this->BcBaser->getCrumbs(true));

		// パンくずは設定されているが、オプションでカテゴリをオフにした場合
		$expected = array(
			array('name' => '会社沿革', 'url' => '')
		);
		$this->assertEquals($expected, $this->BcBaser->getCrumbs(false));
	}

/**
 * コンテンツタイトルを取得する
 *
 * @return void
 */
	public function testGetContentsTitle() {
		// 設定なし
		$this->assertEmpty($this->BcBaser->getContentsTitle());

		// 設定あり
		$this->BcBaser->setTitle('会社データ');
		$this->assertEquals('会社データ', $this->BcBaser->getContentsTitle());
	}

/**
 * コンテンツタイトルを出力する
 *
 * @return void
 */
	public function testContentsTitle() {
		$this->expectOutputString('会社データ');
		$this->BcBaser->setTitle('会社データ');
		$this->BcBaser->contentsTitle();
	}

/**
 * タイトルタグを出力する
 *
 * @return void
 */
	public function testTitle() {
		$topTitle = 'baserCMS inc. [デモ]';
		$title = '会社データ';
		$this->expectOutputString('<title>' . $title . '｜' . $topTitle . '</title>' . PHP_EOL);
		$this->BcBaser->setTitle($title);
		$this->BcBaser->title();
	}

/**
 * キーワード用のメタタグを出力する
 *
 * @return void
 */
	public function testMetaKeywords() {
		$this->BcBaser->setKeywords('baserCMS,国産,オープンソース');
		ob_start();
		$this->BcBaser->metaKeywords();
		$result = ob_get_clean();
		$excepted = array(
			'meta' => array(
				'name'		=> 'keywords',
				'content'	=> 'baserCMS,国産,オープンソース'
			)
		);

		$this->assertTags($result, $excepted);
	}

/**
 * ページ説明文用のメタタグを出力する
 *
 * @return void
 */
	public function testMetaDescription() {
		$this->BcBaser->setDescription('国産オープンソースのホームページです');
		ob_start();
		$this->BcBaser->metaDescription();
		$result = ob_get_clean();
		$excepted = array(
			'meta' => array(
				'name'		=> 'description',
				'content'	=> '国産オープンソースのホームページです'
			)
		);
		$this->assertTags($result, $excepted);
	}

/**
 * RSSフィードのリンクタグを出力する
 *
 * @return void
 */
	public function testRss() {
		ob_start();
		$this->BcBaser->rss('ブログ', 'http://localhost/blog/');
		$result = ob_get_clean();
		$excepted = array(
			'link' => array(
				'href'	=> 'http://localhost/blog/',
				'type'	=> 'application/rss+xml',
				'rel'	=> 'alternate',
				'title'	=> 'ブログ'
			)
		);
		$this->assertTags($result, $excepted);
	}

/**
 * 現在のページがトップページかどうかを判定する
 *
 * @param bool $expected 期待値
 * @param string $url リクエストURL
 * @param string $agent ユーザーエージェント
 *
 * @return void
 * @dataProvider isHomeDataProvider
 */
	public function testIsHome($expected, $url, $agent = null) {
		$this->_unsetAgent();
		if ($agent !== null) {
			$this->_setAgent($agent);
		}
		$this->BcBaser->request = $this->_getRequest($url);
		$this->assertEquals($expected, $this->BcBaser->isHome());
	}

/**
 * isHome用データプロバイダ
 *
 * @return array
 */
	public function isHomeDataProvider() {
		return array(
			//PC
			array(true, '/'),
			array(true, '/index'),
			array(false, '/news/index'),

			// モバイルページ
			array(false, '/', 'mobile'),
			array(false, '/s/', 'mobile'),
			array(true, '/m/', 'mobile'),
			array(true, '/m/index', 'mobile'),
			array(false, '/m/news/index', 'mobile'),

			// スマートフォンページ
			array(false, '/', 'smartphone'),
			array(false, '/m/', 'smartphone'),
			array(true, '/s/', 'smartphone'),
			array(true, '/s/index', 'smartphone'),
			array(false, '/s/news/index', 'smartphone')
		);
	}

/**
 * baserCMSが設置されているパスを出力する
 *
 * @param string $expected 期待値
 * @param string $baseUrl App.baseUrl
 * @return void
 * @dataProvider rootDataProvider
 */
	public function testRoot($expected, $baseUrl) {
		$this->expectOutputString($expected);
		Configure::write('App.baseUrl', $baseUrl);
		$this->BcBaser->request = $this->_getRequest('/');
		$this->BcBaser->root();
	}

/**
 * root用データプロバイダ
 *
 * @return array
 */
	public function rootDataProvider() {
		return array(
			array('/', ''),
			array('/index.php/', 'index.php'),
			array('/basercms/index.php/', 'basercms/index.php')
		);
	}

/**
 * baserCMSが設置されているパスを取得する
 *
 * @param string $expected 期待値
 * @param string $baseUrl App.baseUrl
 * @return void
 * @dataProvider rootDataProvider
 */
	public function getRoot($expected, $baseUrl) {
		Configure::write('App.baseUrl', $baseUrl);
		$this->BcBaser->request = $this->_getRequest('/');
		$this->assertEquals($expected, $this->BcBaser->getRoot());
	}

/**
 * baserCMSの設置フォルダを考慮したURLを出力する
 * 
 * BcBaserHelper::getUrl() をラッピングしているだけなので、最低限のテストのみ
 *
 * @return void
 */
	public function testUrl() {
		$this->expectOutputString('/basercms/index.php/about');
		Configure::write('App.baseUrl', '/basercms/index.php');
		$this->BcBaser->request = $this->_getRequest('/');
		$this->BcBaser->url('/about');
	}

/**
 * baserCMSの設置フォルダを考慮したURLを取得する
 *
 * @return void
 */
	public function testGetUrl() {
		// ノーマル
		$result = $this->BcBaser->getUrl('/about');
		$this->assertEquals('/about', $result);

		// 省略した場合
		$result = $this->BcBaser->getUrl();
		$this->assertEquals('/', $result);

		// フルURL
		$result = $this->BcBaser->getUrl('/about', true);
		$this->assertEquals(Configure::read('App.fullBaseUrl') . '/about', $result);

		// 配列URL
		$result = $this->BcBaser->getUrl(array(
			'admin'			=> true,
			'plugin'		=> 'blog',
			'controller'	=> 'blog_posts',
			'action'		=> 'edit',
			1
		));
		$this->assertEquals('/admin/blog/blog_posts/edit/1', $result);

		// セッションIDを付加する場合
		// TODO セッションIDを付加する場合、session.use_trans_sid の値が0である必要が
		// があるが、上記の値はセッションがスタートした後では書込不可の為見送り
		/*Configure::write('BcRequest.agent', 'mobile');
		Configure::write('BcAgent.mobile.sessionId', true);
		ini_set('session.use_trans_sid', 0);*/

		// --- サブフォルダ+スマートURLオフ ---
		Configure::write('App.baseUrl', '/basercms/index.php');
		$this->BcBaser->request = $this->_getRequest('/');

		// ノーマル
		$result = $this->BcBaser->getUrl('/about');
		$this->assertEquals('/basercms/index.php/about', $result);

		// 省略した場合
		$result = $this->BcBaser->getUrl();

		$this->assertEquals('/basercms/index.php/', $result);

		// フルURL
		$result = $this->BcBaser->getUrl('/about', true);
		$this->assertEquals(Configure::read('App.fullBaseUrl') . '/basercms/index.php/about', $result);

		// 配列URL
		$result = $this->BcBaser->getUrl(array(
			'admin'			=> true,
			'plugin'		=> 'blog',
			'controller'	=> 'blog_posts',
			'action'		=> 'edit',
			1
		));
		$this->assertEquals('/basercms/index.php/admin/blog/blog_posts/edit/1', $result);
	}

/**
 * エレメントテンプレートのレンダリング結果を取得する
 *
 * @return void
 */
	public function testGetElement() {
		// フロント
		$result = $this->BcBaser->getElement(('global_menu'));
		$this->assertTextContains('<ul class="global-menu clearfix">', $result);

		// ### 管理画面
		$View = new BcAppView();
		$View->subDir = 'admin';
		$this->BcBaser = new BcBaserHelper($View);
		// 管理画面用のテンプレートがなくフロントのテンプレートがある場合
		// ※ フロントが存在する場合にはフロントのテンプレートを利用する
		$result = $this->BcBaser->getElement(('global_menu'));
		$this->assertTextContains('<ul class="global-menu clearfix">', $result);
		// 強制的にフロントのテンプレートに切り替えた場合
		$result = $this->BcBaser->getElement('crumbs', array(), array('subDir' => false));
		$this->assertEquals('<strong>ホーム</strong>', $result);
	}

/**
 * エレメントテンプレートを出力する
 * 
 * BcBaserHelper::getElement() をラッピングしているだけなので、最低限のテストのみ
 *
 * @return void
 */
	public function testElement() {
		$this->expectOutputRegex('/<ul class="global-menu clearfix">/');
		$this->BcBaser->element(('global_menu'));
	}

/**
 * ヘッダーテンプレートを出力する
 *
 * @return void
 */
	public function testHeader() {
		$this->expectOutputRegex('/<div id="Header">/');
		$this->BcBaser->header();
	}

/**
 * フッターテンプレートを出力する
 *
 * @return void
 */
	public function testFooter() {
		$this->expectOutputRegex('/<div id="Footer">/');
		$this->BcBaser->footer();
	}

/**
 * ページネーションを出力する
 *
 * @return void
 */
	public function testPagination() {
		$this->expectOutputRegex('/<div class="pagination">/');
		$this->BcBaser->request->params['paging']['Model'] = array(
			'count'		=> 100,
			'pageCount'	=> 3,
			'page'		=> 2,
			'limit'		=> 10,
			'current'	=> null,
			'prevPage'	=> 1,
			'nextPage'	=> 3,
			'options'	=> array(),
			'paramType'	=> 'named'
		);
		$this->BcBaser->pagination();
	}

/**
 * コンテンツ本体を出力する
 *
 * @return void
 */
	public function testContent() {
		$this->expectOutputString('コンテンツ本体');
		$this->_View->assign('content', 'コンテンツ本体');
		$this->BcBaser->content();
	}

/**
 * セッションメッセージを出力する
 *
 * @return void
 */
	public function testFlash() {
		// TODO コンソールからのセッションのテストをどうするか？そもそもするか？ ryuring
		if (isConsole()) {
			return;
		}

		$message = 'エラーが発生しました。';
		$this->expectOutputString('<div id="MessageBox"><div id="flashMessage" class="message">' . $message . '</div></div>');
		App::uses('SessionComponent', 'Controller/Component');
		App::uses('ComponentCollection', 'Controller/Component');
		$Session = new SessionComponent(new ComponentCollection());
		$Session->setFlash($message);
		$this->BcBaser->flash();
	}

/**
 * コンテンツ内で設定した CSS や javascript をレイアウトテンプレートに出力する
 *
 * @return void
 */
	public function testScripts() {
		$themeConfigTag = '<link rel="stylesheet" type="text/css" href="/files/theme_configs/config.css" />';
		// CSS
		$expected = '<link rel="stylesheet" type="text/css" href="/css/admin/layout.css" />';
		$this->BcBaser->css('admin/layout', array('inline' => false));
		ob_start();
		$this->BcBaser->scripts();
		$result = ob_get_clean();
		$result = str_replace($themeConfigTag, '', $result);
		$this->assertEquals($expected, $result);
		$this->_View->assign('css', '');
		// Javascript
		$expected = '<script type="text/javascript" src="/js/admin/startup.js"></script>';
		$this->BcBaser->js('admin/startup', false);
		ob_start();
		$this->BcBaser->scripts();
		$result = ob_get_clean();
		$result = str_replace($themeConfigTag, '', $result);
		$this->assertEquals($expected, $result);
		$this->_View->assign('script', '');
		// meta
		$expected = '<meta name="description" content="説明文" />';
		App::uses('BcHtmlHelper', 'View/Helper');
		$BcHtml = new BcHtmlHelper($this->_View);
		$BcHtml->meta('description', '説明文', array('inline' => false));
		ob_start();
		$this->BcBaser->scripts();
		$result = ob_get_clean();
		$result = str_replace($themeConfigTag, '', $result);
		$this->assertEquals($expected, $result);
		$this->_View->assign('meta', '');
		// ツールバー
		$expected = '<link rel="stylesheet" type="text/css" href="/css/admin/toolbar.css" />';
		$this->BcBaser->set('user', array('User'));
		ob_start();
		$this->BcBaser->scripts();
		$result = ob_get_clean();
		$result = str_replace($themeConfigTag, '', $result);
		$this->assertEquals($expected, $result);
	}

/**
 * ツールバーエレメントや CakePHP のデバッグ出力を表示
 *
 * @return void
 */
	public function testFunc() {
		Configure::write('debug', 0);

		// 未ログイン
		ob_start();
		$this->BcBaser->func();
		$result = ob_get_clean();
		$this->assertEquals('', $result);

		// ログイン中
		$expects = '<div id="ToolBar">';
		$this->_login();
		$this->BcBaser->set('currentPrefix', 'admin');
		$this->BcBaser->set('currentUserAuthPrefixes', array('admin'));
		ob_start();
		$this->BcBaser->func();
		$result = ob_get_clean();
		$this->assertTextContains($expects, $result);
		$this->_logout();

		// デバッグモード２
		$expects = '<table class="cake-sql-log"';
		Configure::write('debug', 2);
		ob_start();
		$this->BcBaser->func();
		$result = ob_get_clean();
		$this->assertTextContains($expects, $result);
	}

/**
 * サブメニューを設定する
 * 
 * @param array $elements サブメニューエレメント名を配列で指定
 * @param array $expects サブメニュータイトル
 * @return void
 * @dataProvider setSubMenusDataProvider
 */
	public function testSetSubMenus($elements, $expects) {
		$this->_View->subDir = 'admin';
		$this->BcBaser->setSubMenus($elements);
		ob_start();
		$this->BcBaser->subMenu();
		$result = ob_get_clean();
		foreach ($expects as $expect) {
			$this->assertTextContains($expect, $result);
		}
	}

/**
 * setSubMenus 用のデータプロバイダ
 *
 * @return array
 */
	public function setSubMenusDataProvider() {
		return array(
			array(array('contents'), array('<th>検索インデックスメニュー</th>')),
			array(array('editor_templates', 'site_configs'), array('<th>エディタテンプレートメニュー</th>', '<th>システム設定共通メニュー</th>')),
			array(array('menus', 'tools'), array('<th>メニュー管理メニュー</th>', '<th>ツールメニュー</th>')),
			array(array('plugins', 'themes'), array('<th>プラグイン管理メニュー</th>', '<th>テーマ管理メニュー</th>')),
			array(array('users'), array('<th>ユーザー管理メニュー</th>')),
			array(array('widget_areas'), array('<th>ウィジェットエリア管理メニュー</th>')),
		);
	}

/**
 * XMLヘッダタグを出力する
 *
 * @param string $expected 期待値
 * @param string $agent ユーザーエージェント
 * @return void
 * @dataProvider xmlDataProvider
 */
	public function testXmlHeader($expected, $agent = null) {
		$this->expectOutputString($expected);

		if ($agent !== null) {
			$this->_setAgent($agent);
		}
		$this->BcBaser->xmlHeader();
	}

/**
 * xmlHeader用データプロバイダ
 *
 * @return array
 */
	public function xmlDataProvider() {
		return array(
			array('<?xml version="1.0" encoding="UTF-8" ?>' . "\n"),
			array('<?xml version="1.0" encoding="Shift-JIS" ?>' . "\n", 'mobile')
		);
	}

/**
 * アイコン（favicon）タグを出力する
 *
 * @return void
 */
	public function testIcon() {
		$this->expectOutputString('<link href="/favicon.ico" type="image/x-icon" rel="icon" /><link href="/favicon.ico" type="image/x-icon" rel="shortcut icon" />' . "\n");
		$this->BcBaser->icon();
	}

/**
 * ドキュメントタイプを指定するタグを出力する
 * 
 * @param string $docType ドキュメントタイプ
 * @param string $expected ドキュメントタイプを指定するタグ
 * @return void
 * @dataProvider docTypeDataProvider
 */
	public function testDocType($docType, $expected) {
		$this->expectOutputString($expected . "\n");
		$this->BcBaser->docType($docType);
	}

/**
 * docType 用のデータプロバイダ
 * 
 * @return array
 */
	public function docTypeDataProvider() {
		return array(
			array('xhtml-trans', '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">'),
			array('html5', '<!DOCTYPE html>')
		);
	}

/**
 * CSSの読み込みタグを出力する
 *
 * @return void
 */
	public function testCss() {
		// ノーマル
		ob_start();
		$this->BcBaser->css('admin/import');
		$result = ob_get_clean();
		$expected = '<link rel="stylesheet" type="text/css" href="/css/admin/import.css" />';
		$this->assertEquals($expected, $result);
		// 拡張子あり
		ob_start();
		$this->BcBaser->css('admin/import.css');
		$result = ob_get_clean();
		$expected = '<link rel="stylesheet" type="text/css" href="/css/admin/import.css" />';
		$this->assertEquals($expected, $result);
		// インラインオフ（array）
		$this->BcBaser->css('admin/import.css', array('inline' => false));
		$expected = '<link rel="stylesheet" type="text/css" href="/css/admin/import.css" />';
		$result = $this->_View->Blocks->get('css');
		$this->assertEquals($expected, $result);
		$this->_View->Blocks->end();
		// インラインオフ（boolean）
		$this->BcBaser->css('admin/import.css', false);
		$expected = '<link rel="stylesheet" type="text/css" href="/css/admin/import.css" />';
		$this->_View->assign('css', '');
		$this->assertEquals($expected, $result);
	}

/**
 * JSの読み込みタグを出力する
 *
 * @param string $expected 期待値
 * @param string $url URL
 * @return void
 * @dataProvider jsDataProvider
 */
	public function testJs($expected, $url) {
		$this->expectOutputString($expected);
		$this->BcBaser->js($url);
	}

/**
 * js 用データプロバイダ
 *
 * @return array
 */
	public function jsDataProvider() {
		return array(
			array('<script type="text/javascript" src="/js/admin/startup.js"></script>', 'admin/startup'),
			array('<script type="text/javascript" src="/js/admin/startup.js"></script>', 'admin/startup.js')
		);
	}

/**
 * JSの読み込みタグを出力する（インラインオフ）
 *
 * @return void
 */
	public function testJsNonInline() {
		// インラインオフ（boolean）
		$this->BcBaser->js('admin/function', false);
		$expected = '<script type="text/javascript" src="/js/admin/function.js"></script>';
		$result = $this->_View->fetch('script');
		$this->assertEquals($expected, $result);
	}

/**
 * 画像読み込みタグを出力する
 *
 * @return void
 */
	public function testImg() {
		$this->expectOutputString('<img src="/img/baser.power.gif" alt="" />');
		$this->BcBaser->img('baser.power.gif');
	}

/**
 * 画像タグを取得する
 * 
 * @param string $path 画像のパス
 * @param array $options オプション
 * @param string $expected 結果
 * @return void
 * @dataProvider getImgDataProvider
 */
	public function testGetImg($path, $options, $expected) {
		$result = $this->BcBaser->getImg($path, $options);
		$this->assertEquals($expected, $result);
	}

/**
 * getImg 用データプロバイダ
 * 
 * @return array
 */
	public function getImgDataProvider() {
		return array(
			array('baser.power.gif', array('alt' => "baserCMSロゴ"), '<img src="/img/baser.power.gif" alt="baserCMSロゴ" />'),
			array('baser.power.gif', array('title' => "baserCMSロゴ"), '<img src="/img/baser.power.gif" title="baserCMSロゴ" alt="" />')
		);
	}

/**
 * アンカータグを出力する
 *
 * @return void
 */
	public function testLink() {
		$this->expectOutputString('<a href="/about">会社案内</a>');
		$this->BcBaser->link('会社案内', '/about');
	}

/**
 * アンカータグを取得する
 * 
 * @param string $title タイトル
 * @param string $url URL
 * @param array $option オプション
 * @param string $expected 結果
 * @return void
 * @dataProvider getLinkDataProvider
 */
	public function testGetLink($title, $url, $option, $expected) {
		if (!empty($option['prefix'])) {
			$this->_getRequest('/admin');
		}
		if (!empty($option['forceTitle'])) {
			$this->_View->viewVars['user']['user_group_id'] = 2;
		}
		if (!empty($option['ssl'])) {
			Configure::write('BcEnv.sslUrl', 'https://localhost/');
		}
		$result = $this->BcBaser->getLink($title, $url, $option);
		$this->assertEquals($expected, $result);
		Configure::write('BcEnv.sslUrl', '');
	}

/**
 * getLink 用の データプロバイダ
 * 
 * @return array
 */
	public function getLinkDataProvider() {
		return array(
			array('', '/', array(), '<a href="/"></a>'),
			array('会社案内', '/about', array(), '<a href="/about">会社案内</a>'),
			array('会社案内 & 会社データ', '/about', array('escape' => true), '<a href="/about">会社案内 &amp; 会社データ</a>'),	// エスケープ
			array('固定ページ管理', array('controller' => 'pages', 'action' => 'index'), array('prefix' => true), '<a href="/admin/pages/index">固定ページ管理</a>'),	// プレフィックス
			array('システム設定', array('admin' => true, 'controller' => 'site_configs', 'action' => 'form'), array('forceTitle' => true), '<span>システム設定</span>'),	// 強制タイトル
			array('会社案内', '/about', array('ssl' => true), '<a href="https://localhost/about">会社案内</a>'), // SSL
			array('テーマファイル管理', array('controller' => 'themes', 'action' => 'manage', 'jsa'), array('ssl' => true), '<a href="https://localhost/themes/manage/jsa">テーマファイル管理</a>'), // SSL
			array('画像', '/img/test.jpg', array('ssl' => true), '<a href="https://localhost/img/test.jpg">画像</a>'), // SSL
		);
	}

/**
 * SSL通信かどうか判定する
 *
 * @return void
 */
	public function testIsSSL() {
		$_SERVER['HTTPS'] = true;
		$this->BcBaser->request = $this->_getRequest('https://localhost/');
		$this->assertEquals(true, $this->BcBaser->isSSL());
	}

/**
 * charset メタタグを出力する
 *
 * @param string $expected 期待値
 * @param string $encoding エンコード
 * @param string $agent ユーザーエージェント
 * @return void
 * @dataProvider charsetDataProvider
 */
	public function testCharset($expected, $encoding, $agent = null) {
		$this->expectOutputString($expected);

		$this->_unsetAgent();

		if ($agent !== null) {
			$this->_setAgentSetting($agent, true);
			$this->_setAgent($agent);
		}

		if ($encoding !== null) {
			$this->BcBaser->charset($encoding);
		} else {
			$this->BcBaser->charset();
		}
	}

/**
 * charset用データプロバイダ
 *
 * @return array
 */
	public function charsetDataProvider() {
		return array(
			array('<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />', 'UTF-8'),
			array('<meta http-equiv="Content-Type" content="text/html; charset=Shift-JIS" />', null, 'mobile')
		);
	}

/**
 * コピーライト用の年を出力する
 *
 * @param string $expected 期待値
 * @param mixed $begin 開始年
 * @return void
 * @dataProvider copyYearDataProvider
 */
	public function testCopyYear($expected, $begin) {
		$this->expectOutputString($expected);
		$this->BcBaser->copyYear($begin);
	}

/**
 * copyYear用データプロバイダ
 *
 * @return array
 */
	public function copyYearDataProvider() {
		$year = date('Y');
		return array(
			array("2000 - {$year}", 2000),
			array($year, 'はーい')
		);
	}

/**
 * 編集画面へのリンクを出力する
 * 
 * setPageEditLink のテストも兼ねる
 *
 * @return void
 */
	public function testEditLink() {
		// リンクなし
		$expected = '';
		$this->BcBaser->setPageEditLink(1);
		ob_start();
		$this->BcBaser->editLink();
		$result = ob_get_clean();
		$this->assertEquals($expected, $result);
		// リンクあり
		$expected = '<a href="/admin/pages/edit/1" class="tool-menu">編集する</a>';
		$this->_View->viewVars['user'] = array('User' => array('id' => 1));
		$this->_View->viewVars['currentUserAuthPrefixes'] = array(Configure::read('Routing.prefixes.0'));
		$this->BcBaser->setPageEditLink(1);
		ob_start();
		$this->BcBaser->editLink();
		$result = ob_get_clean();
		$this->assertEquals($expected, $result);
	}

/**
 * 編集画面へのリンクが存在するかチェックする
 *
 * @return void
 */
	public function testExistsEditLink() {
		// 存在しない
		$this->BcBaser->setPageEditLink(1);
		$this->assertEquals(false, $this->BcBaser->existsEditLink());
		// 存在する
		$this->_View->viewVars['user'] = array('User' => array('id' => 1));
		$this->_View->viewVars['currentUserAuthPrefixes'] = array(Configure::read('Routing.prefixes.0'));
		$this->BcBaser->setPageEditLink(1);
		$this->assertEquals(true, $this->BcBaser->existsEditLink());
	}

/**
 * 公開ページへのリンクを出力する
 *
 * @return void
 */
	public function testPublishLink() {
		// リンクなし
		$expected = '';
		ob_start();
		$this->BcBaser->publishLink();
		$result = ob_get_clean();
		$this->assertEquals($expected, $result);
		// リンクあり
		$expected = '<a href="/" class="tool-menu">公開ページ</a>';
		$this->_View->viewVars['currentUserAuthPrefixes'] = array(Configure::read('Routing.prefixes.0'));
		$this->_View->viewVars['publishLink'] = '/';
		ob_start();
		$this->BcBaser->publishLink();
		$result = ob_get_clean();
		$this->assertEquals($expected, $result);
	}

/**
 * 公開ページへのリンクが存在するかチェックする
 *
 * @return void
 */
	public function testExistsPublishLink() {
		// 存在しない
		$this->assertEquals(false, $this->BcBaser->existsPublishLink());
		// 存在する
		$this->_View->viewVars['currentUserAuthPrefixes'] = array(Configure::read('Routing.prefixes.0'));
		$this->_View->viewVars['publishLink'] = '/';
		$this->assertEquals(true, $this->BcBaser->existsPublishLink());
	}

/**
 * アップデート処理が必要かチェックする
 * 
 * @param string $baserVersion baserCMSのバージョン
 * @param string $dbVersion データベースのバージョン
 * @param bool $expected 結果
 * @return void
 * @dataProvider checkUpdateDataProvider
 */
	public function testCheckUpdate($baserVersion, $dbVersion, $expected) {
		$this->BcBaser->siteConfig['version'] = $dbVersion;
		$this->_View->viewVars['baserVersion'] = $baserVersion;
		$this->assertEquals($expected, $this->BcBaser->checkUpdate());
	}

/**
 * checkUpdate のデータプロバイダ
 * 
 * @return array
 */
	public function checkUpdateDataProvider() {
		return array(
			array('1.0.0', '1.0.0', false),
			array('1.0.1', '1.0.0', true),
			array('1.0.1-beta', '1.0.0', false),
			array('1.0.1', '1.0.0-beta', false)
		);
	}

/**
 * コンテンツを特定するIDを出力する
 *
 * @return void
 */
	public function testContentsName() {
		$this->markTestIncomplete('このテストは、まだ実装されていません。');
	}

/**
 * コンテンツを特定するIDを取得する
 * ・キャメルケースで取得
 * ・URLのコントローラー名までを取得
 * ・ページの場合は、カテゴリ名（カテゴリがない場合は Default）
 * ・トップページは、Home
 *
 * @param string $url URL
 * @param string $expects コンテンツ名
 * @param string $ua リクエストのユーザーエージェント
 * @param array $agents 対応する設定のエージェントのリスト
 * @param array $linkedAgents 連動する設定のエージェントのリスト
 * @return void*
 * @dataProvider getContentsNameDataProvider
 * 
 * http://192.168.33.10/test.php?case=View%2FHelper%2FBcBaserHelper&baser=true&filter=testGetContentsName
 */
	public function testGetContentsName($url, $expects, $ua = null, array $agents = array(), array $linkedAgents = array()) {
		//Configure周りの設定を全てOFF状態に
		$this->_unsetAgent();
		$this->_unsetAgentLinks();

		if (!empty($ua) && !empty($agents) && in_array($ua, $agents)) {
			$this->_setAgentSetting($ua, true);
			$this->_setAgent($ua);
		}

		//連携を設定
		foreach ($linkedAgents as $linked) {
			$this->_setAgentLink($linked);
		}

		$this->BcBaser->request = $this->_getRequest($url);
		$this->assertEquals($expects, $this->BcBaser->getContentsName());
	}

/**
 * getContentsName用のデータプロバイダ
 *
 * @return array
 */
	public function getContentsNameDataProvider() {
		return array(
			//PC
			array('/', 'Home'),
			array('/news', 'News'),
			array('/contact', 'Contact'),
			array('/company', 'Default'),

			//モバイル　対応OFF 連動OFF

			//スマートフォン 対応OFF　連動OFF

			//モバイル　対応ON 連動OFF
			array('/m/', 'Home', 'mobile', array('mobile')),
			array('/m/news', 'News', 'mobile', array('mobile')),
			array('/m/contact', 'Contact', 'mobile', array('mobile')),
			array('/m/company', 'M', 'mobile', array('mobile')),	// 存在しないページ

			//スマートフォン 対応ON　連動OFF
			array('/s/', 'Home', 'smartphone', array('smartphone')),
			array('/s/news', 'News', 'smartphone', array('smartphone')),
			array('/s/contact', 'Contact', 'smartphone', array('smartphone')),
			array('/s/company', 'S', 'smartphone', array('smartphone')),	// 存在しないページ

			//モバイル　対応ON 連動ON
			array('/m/', 'Home', 'mobile', array('mobile'), array('mobile')),
			array('/m/news', 'News', 'mobile', array('mobile'), array('mobile')),
			array('/m/contact', 'Contact', 'mobile', array('mobile'), array('mobile')),
			array('/m/company', 'Default', 'mobile', array('mobile'), array('mobile')),	// 存在しないページ

			//スマートフォン 対応ON　連動ON
			array('/s/', 'Home', 'smartphone', array('smartphone'), array('smartphone')),
			array('/s/news', 'News', 'smartphone', array('smartphone'), array('smartphone')),
			array('/s/contact', 'Contact', 'smartphone', array('smartphone'), array('smartphone')),
			array('/s/company', 'Default', 'smartphone', array('smartphone'), array('smartphone'))	// 存在しないページ
		);
	}

/**
 * パンくずリストのHTMLレンダリング結果を表示する
 *
 * @return void
 */
	public function testCrumbs() {
		// パンくずが設定されてない場合
		$result = $this->BcBaser->crumbs();
		$this->assertEmpty($result);

		// パンくずが設定されている場合
		$crumbs = array(
			array('name' => '会社案内', 'url' => '/company/index'),
			array('name' => '会社データ', 'url' => '/company/data'),
			array('name' => '会社沿革', 'url' => '')
		);
		foreach ($crumbs as $crumb) {
			$this->BcBaser->addCrumb($crumb['name'], $crumb['url']);
		}
		ob_start();
		$this->BcBaser->crumbs();
		$result = ob_get_clean();
		$expected = array(
			array('a' => array('href' => '/company/index')),
			'会社案内',
			'/a',
			'&raquo;',
			array('a' => array('href' => '/company/data')),
			'会社データ',
			'/a',
			'&raquo;会社沿革'
		);
		$this->assertTags($result, $expected);

		// 区切り文字を変更、先頭にホームを追加
		ob_start();
		$this->BcBaser->crumbs(' | ', 'ホーム');
		$result = ob_get_clean();
		$expected = array(
			array('a' => array('href' => '/')),
			'ホーム',
			'/a',
			' | ',
			array('a' => array('href' => '/company/index')),
			'会社案内',
			'/a',
			' | ',
			array('a' => array('href' => '/company/data')),
			'会社データ',
			'/a',
			' | 会社沿革'
		);
		$this->assertTags($result, $expected);
	}

/**
 * パンくずリストの要素を追加する
 *
 * @return void
 */
	public function testAddCrumbs() {
		$this->BcBaser->addCrumb('会社案内', '/company/index');
		ob_start();
		$this->BcBaser->crumbs();
		$result = ob_get_clean();
		$expected = array(
			array('a' => array('href' => '/company/index')),
			'会社案内',
			'/a'
		);
		$this->assertTags($result, $expected);
	}

/**
 * ページ機能で作成したページの一覧データを取得する
 * 
 * @param int $pageCategoryId 固定ページカテゴリ
 * @param array $options オプション
 * @param array $expected ページリストデータ
 * @return void
 * @dataProvider getPageListDataProvider
 */
	public function testGetPageList($pageCategoryId, $options, $expected) {
		$this->_setAgentSetting('mobile', true);
		$this->_setAgentSetting('smartphone', true);
		$result = $this->BcBaser->getPageList($pageCategoryId, $options);
		$this->assertEquals($expected, $result);
	}

/**
 * getPageList 用のデータプロバイダ
 *
 * @return array
 */
	public function getPageListDataProvider() {
		return array(
			array(null, array(), array(
				array('title' => 'PCトップページ', 'url' => '/'),
				array('title' => 'サービス', 'url' => '/service'),
				array('title' => '会社案内', 'url' => '/company'),
				array('title' => '採用情報', 'url' => '/recruit'),
				array('title' => 'モバイルトップページ', 'url' => '/m/'),
				array('title' => 'スマートフォントップページ', 'url' => '/s/'),
				array('title' => 'スマートフォン採用情報', 'url' => '/s/recruit'),
				array('title' => 'モバイルサービス', 'url' => '/m/service')
			)),
			array(1, null, array(
				array('title' => 'モバイルトップページ', 'url' => '/m/'),
				array('title' => 'モバイルサービス', 'url' => '/m/service')
			)),
			array(null, array('order' => 'Page.sort DESC'), array(
				array('title' => 'モバイルサービス', 'url' => '/m/service'),
				array('title' => 'スマートフォン採用情報', 'url' => '/s/recruit'),
				array('title' => 'スマートフォントップページ', 'url' => '/s/'),
				array('title' => 'モバイルトップページ', 'url' => '/m/'),
				array('title' => '採用情報', 'url' => '/recruit'),
				array('title' => '会社案内', 'url' => '/company'),
				array('title' => 'サービス', 'url' => '/service'),
				array('title' => 'PCトップページ', 'url' => '/')
			))
		);
	}
/**
 * ブラウザにキャッシュさせる為のヘッダーを出力する
 *
 * @return void
 */
	public function testCacheHeader() {
		$this->markTestIncomplete('このテストは、まだ実装されていません。');
	}

/**
 * httpから始まるURLを取得する
 *
 * @return void
 */
	public function testGetUri() {
		$this->markTestIncomplete('このテストは、まだ実装されていません。');
	}

/**
 * 文字列を検索しマークとしてタグをつける
 *
 * @return void
 */
	public function testMark() {
		$this->markTestIncomplete('このテストは、まだ実装されていません。');
	}

/**
 * サイトマップを出力する
 *
 * @return void
 */
	public function testSitemap() {
		$this->markTestIncomplete('このテストは、まだ実装されていません。');
	}

/**
 * Flashを表示する
 *
 * @return void
 */
	public function testSwf() {
		$this->markTestIncomplete('このテストは、まだ実装されていません。');
	}

/**
 * URLをリンクとして利用可能なURLに変換する
 *
 * @return void
 */
	public function testChangePrefixToAlias() {
		$this->markTestIncomplete('このテストは、まだ実装されていません。');
	}

/**
 * 現在のログインユーザーが管理者グループかどうかチェックする
 *
 * @return void
 */
	public function testIsAdminUser() {
		$this->markTestIncomplete('このテストは、まだ実装されていません。');
	}

/**
 * 現在のページが固定ページかどうかを判定する
 *
 * @return void
 */
	public function testIsPage() {
		$this->markTestIncomplete('このテストは、まだ実装されていません。');
	}

/**
 * 現在のページの純粋なURLを取得する
 * 
 * @param string $agent エージェント
 * @param string $url 現在のURL
 * @param string $expected 期待値
 * @return void
 * @dataProvider getHereDataProvider
 */
	public function testGetHere($agent, $url, $expected) {
		$this->_setAgent($agent);
		$this->BcBaser->request = $this->_getRequest($url);
		$this->assertEquals($expected, $this->BcBaser->getHere());
	}

/**
 * getHere用のデータプロバイダ
 *
 * @return array
 */
	public function getHereDataProvider() {
		return array(
			// PCページ
			array('', '/', '/'),
			array('', '/index', '/index'),
			array('', '/contact/index', '/contact/index'),
			array('', '/blog/blog/index', '/blog/blog/index'),
			// モバイルページ
			array('mobile', '/', '/'),
			array('mobile', '/index', '/index'),
			array('mobile', '/contact/index', '/contact/index'),
			array('mobile', '/blog/blog/index', '/blog/blog/index'),
			// スマートフォンページ
			array('smartphone', '/', '/'),
			array('smartphone', '/index', '/index'),
			array('smartphone', '/contact/index', '/contact/index'),
			array('smartphone', '/blog/blog/index', '/blog/blog/index')
		);
	}

/**
 * 現在のページがページカテゴリのトップかどうかを判定する
 *
 * @return void
 */
	public function testIsCategoryTop() {
		$this->markTestIncomplete('このテストは、まだ実装されていません。');
	}

/**
 * ページをエレメントとして読み込む
 *
 * @return void
 */
	public function testPage() {
		$this->markTestIncomplete('このテストは、まだ実装されていません。');
	}

/**
 * ウィジェットエリアを出力する
 *
 * @return void
 */
	public function testWidgetArea() {
		$this->markTestIncomplete('このテストは、まだ実装されていません。');
	}

/**
 * 指定したURLが現在のURLかどうか判定する
 *
 * @param string $currentUrl 現在のURL
 * @param string $url 引数として与えられるURL
 * @param bool $expects メソッドの返り値
 * @return void
 *
 * @dataProvider isCurrentUrlDataProvider
 */
	public function testIsCurrentUrl($currentUrl, $url, $expects) {
		$this->BcBaser->request = $this->_getRequest($currentUrl);
		$this->assertEquals($expects, $this->BcBaser->isCurrentUrl($url));
		// --- サブフォルダ+スマートURLオフ ---
		Configure::write('App.baseUrl', '/basercms/index.php');
		$this->BcBaser->request = $this->_getRequest($currentUrl);
		$this->assertEquals($expects, $this->BcBaser->isCurrentUrl($url));
	}

/**
 * isCurrentUrl用のデータプロバイダ
 *
 * @return array
 */
	public function isCurrentUrlDataProvider() {
		return array(
			array('/', '/', true),
			array('/index', '/', true),
			array('/', '/index', true),
			array('/company', '/company', true),
			array('/news', '/news', true),
			array('/news/', '/news', false),
			array('/news/index', '/news', false),
			array('/news', '/news/', false),
			array('/news/', '/news/', true),
			array('/news/index', '/news/', true),
			array('/news', '/news/index', false),
			array('/news/', '/news/index', true),
			array('/news/index', '/news/index', true),
			array('/', '/company', false),
			array('/company', '/', false),
			array('/news', '/', false)
		);
	}

/**
 * ユーザー名を整形して表示する
 *
 * @return void
 */
	public function testGetUserName() {
		$this->markTestIncomplete('このテストは、まだ実装されていません。');
	}

/**
 * コアテンプレートを読み込む
 *
 * @return void
 */
	public function testIncludeCore() {
		$this->markTestIncomplete('このテストは、まだ実装されていません。');
	}

/**
 * ロゴを出力する
 *
 * @return void
 */
	public function testLogo() {
		$this->markTestIncomplete('このテストは、まだ実装されていません。');
	}

/**
 * メインイメージを出力する
 *
 * @return void
 */
	public function testMainImage() {
		$this->markTestIncomplete('このテストは、まだ実装されていません。');
	}

/**
 * メインイメージの取得でidやclassを指定するオプション
 *
 * @return void
 */
	public function testMainImageIdClass() {
		$num = 2;
		$idName = 'testIdName';
		$className = 'testClassName';

		//getMainImageを叩いてULを入手(default)
		ob_start();
		$this->BcBaser->mainImage(array('all' => true, 'num' => $num));
		$tags = ob_get_clean();
		$check = preg_match('|<ul id="MainImage">|', $tags) === 1;
		$this->assertTrue($check);

		//getMainImageを叩いてULを入手(id指定)
		ob_start();
		$this->BcBaser->mainImage(array('all' => true, 'num' => $num, 'id' => $idName));
		$tags = ob_get_clean();
		$check = preg_match('|<ul id="' . $idName . '">|', $tags) === 1;
		$this->assertTrue($check);

		//getMainImageを叩いてULを入手(class指定・id非表示)
		ob_start();
		$this->BcBaser->mainImage(array('all' => true, 'num' => $num, 'id' => false, 'class' => $className));
		$tags = ob_get_clean();
		$check = preg_match('|<ul class="' . $className . '">|', $tags) === 1;
		$this->assertTrue($check);
		//getMainImageを叩いてULを入手(全てなし)
		ob_start();
		$this->BcBaser->mainImage(array('all' => true, 'num' => $num, 'id' => false, 'class' => false));
		$tags = ob_get_clean();
		$check = preg_match('|<ul>|', $tags) === 1;
		$this->assertTrue($check);
	}

/**
 * テーマのURLを取得する
 *
 * @return void
 */
	public function testGetThemeUrl() {
		$this->BcBaser->request = $this->_getRequest('/');
		$this->BcBaser->request->webroot = '/';
		$this->siteConfig['theme'] = 'm-single';
		$expects = $this->BcBaser->request->webroot . 'theme' . '/' . $this->siteConfig['theme'] . '/';
		$this->assertEquals($expects, $this->BcBaser->getThemeUrl());
	}

/**
 * テーマのURLを出力する
 *
 * @return void
 */
	public function testThemeUrl() {
		$this->BcBaser->request = $this->_getRequest('/');
		$this->BcBaser->request->webroot = '/';
		$this->siteConfig['theme'] = 'm-single';
		$expects = $this->BcBaser->request->webroot . 'theme' . '/' . $this->siteConfig['theme'] . '/';
		ob_start();
		$this->BcBaser->themeUrl();
		$result = ob_get_clean();
		$this->assertEquals($expects, $result);
	}

/**
 * ベースとなるURLを取得する
 *
 * @param string $baseUrl サブディレクトリ配置
 * @param string $url アクセスした時のURL
 * @param string $expects 期待値
 * @return void
 * 
 * @dataProvider getBaseUrlDataProvider
 */
	public function testGetBaseUrl($baseUrl, $url, $expects) {
		Configure::write('App.baseUrl', $baseUrl);
		$this->BcBaser->request = $this->_getRequest($url);
		$this->assertEquals($expects, $this->BcBaser->getBaseUrl());
	}

/**
 * testGetBaseUrl用のデータプロバイダ
 *
 * @return array
 */
	public function getBaseUrlDataProvider() {
		return array(
			// ノーマル
			array('', '/', '/'),
			array('', '/index', '/'),
			array('', '/contact/index', '/'),
			array('', '/blog/blog/index', '/'),
			// サブフォルダ
			array('/basercms', '/', '/basercms/'),
			array('/basercms', '/index', '/basercms/'),
			array('/basercms', '/contact/index', '/basercms/'),
			array('/basercms', '/blog/blog/index', '/basercms/'),
		);
	}

/**
 * ベースとなるURLを出力する
 *
 * @param string $smartUrl スマートURLのオン・オフ、サブディレクトリ配置のスマートURLのオン・オフ
 * @param string $url アクセスした時のURL
 * @param string $expects 期待値
 * @return void
 * 
 * @dataProvider baseUrlDataProvider
 */
	public function testBaseUrl($smartUrl, $url, $expects) {
		Configure::write('App.baseUrl', $smartUrl);
		$this->BcBaser->request = $this->_getRequest($url);
		ob_start();
		$this->BcBaser->baseUrl();
		$result = ob_get_clean();
		$this->assertEquals($expects, $result);
	}

/**
 * testGetBaseUrl用のデータプロバイダ
 *
 * @return array
 */
	public function baseUrlDataProvider() {
		return array(
			// ノーマル
			array('', '/', '/'),
			array('', '/index', '/'),
			array('', '/contact/index', '/'),
			array('', '/blog/blog/index', '/'),
			// スマートURLオフ
			array('index.php', '/', '/index.php/'),
			array('index.php', '/index', '/index.php/'),
			array('index.php', '/contact/index', '/index.php/'),
			array('index.php', '/blog/blog/index', '/index.php/'),
			// サブフォルダ+スマートURLオン
			array('/basercms', '/', '/basercms/'),
			array('/basercms', '/index', '/basercms/'),
			array('/basercms', '/contact/index', '/basercms/'),
			array('/basercms', '/blog/blog/index', '/basercms/'),
			// サブフォルダ+スマートURLオフ
			array('/basercms/index.php', '/', '/basercms/index.php/'),
			array('/basercms/index.php', '/index', '/basercms/index.php/'),
			array('/basercms/index.php', '/contact/index', '/basercms/index.php/'),
			array('/basercms/index.php', '/blog/blog/index', '/basercms/index.php/')
		);
	}

/**
 * サブメニューを出力する
 *
 * @return void
 */
	public function testSubMenu() {
		$this->markTestIncomplete('このテストは、まだ実装されていません。');
	}

/**
 * コンテンツナビを出力する
 *
 * @return void
 */
	public function testContentsNavi() {
		$this->markTestIncomplete('このテストは、まだ実装されていません。');
	}

/**
 * パンくずリストを出力する
 *
 * @return void
 */
	public function testCrumbsList() {
		$this->markTestIncomplete('このテストは、まだ実装されていません。');
	}

/**
 * グローバルメニューを出力する
 *
 * @return void
 */
	public function testGlobalMenu() {
		$this->markTestIncomplete('このテストは、まだ実装されていません。');
	}

/**
 * Google Analytics のトラッキングコードを出力する
 *
 * @return void
 */
	public function testGoogleAnalytics() {
		$this->markTestIncomplete('このテストは、まだ実装されていません。');
	}

/**
 * Google Maps を出力する
 *
 * @return void
 */
	public function testGoogleMaps() {
		$this->markTestIncomplete('このテストは、まだ実装されていません。');
	}

/**
 * 表示件数設定機能を出力する
 *
 * @return void
 */
	public function testListNum() {
		$this->markTestIncomplete('このテストは、まだ実装されていません。');
	}

/**
 * サイト内検索フォームを出力
 *
 * @return void
 */
	public function testSiteSearchForm() {
		$this->markTestIncomplete('このテストは、まだ実装されていません。');
	}

}