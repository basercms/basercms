<?php
/**
 * baserCMS :  Based Website Development Project <http://basercms.net>
 * Copyright (c) baserCMS Users Community <http://basercms.net/community/>
 *
 * @copyright		Copyright (c) baserCMS Users Community
 * @link			http://basercms.net baserCMS Project
 * @package			Baser.Test.Case.Model
 * @since			baserCMS v 3.0.0-beta
 * @license			http://basercms.net/license/index.html
 */
App::uses('BcApp', 'Model');
/**
 * BcAppTest class
 * 
 * @package Baser.Test.Case.Model
 * @property BcAppModel $BcApp
 * @property Page $Page
 * @property SiteConfig $SiteConfig
 */

class BcAppTest extends BaserTestCase {

	public $fixtures = [
		'baser.Default.Page',
		'baser.Default.Dblog',
		'baser.Default.SiteConfig',
		'baser.Default.User',
		'baser.Default.UserGroup',
		'baser.Default.Favorite',
		'baser.Default.Permission',
		'baser.Default.SearchIndex',
		'baser.Default.Content'
	];

/**
 * setUp
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();
		$this->BcApp = ClassRegistry::init('BcApp');
		$this->Page = ClassRegistry::init('Page');
		$this->SiteConfig = ClassRegistry::init('SiteConfig');
		$this->Dblog = ClassRegistry::init('Dblog');
		$this->User = ClassRegistry::init('User');
	}

/**
 * tearDown
 *
 * @return void
 */
	public function tearDown() {
		unset($this->BcApp);
		unset($this->Page);
		unset($this->SiteConfig);
		unset($this->Dblog);
		parent::tearDown();
	}

/**
 * beforeSave
 *
 * @return	boolean
 * @access	public
 */
	public function testBeforeSave() {
		$this->Page->save([
			'Page' => [
				'name' => 'test',
				'page_category_id' => null,
				'title' => '',
				'url' => '',
				'description' => '',
				'status' => 1,
				'modified' => '',
			]
		]);

		$result = $this->Page->find('first', [
				'conditions' => ['id' => $this->Page->getLastInsertID()],
				'fields' => ['created'],
				'recursive' => -1
			]
		);

		$expected = true;
		$this->assertEquals($expected, !empty($result));
	}

/**
 * Saves model data to the database. By default, validation occurs before save.
 *
 * @param	array	$data Data to save.
 * @param	boolean	$validate If set, validation will be done before the save
 * @param	array	$fieldList List of fields to allow to be written
 * @return	mixed	On success Model::$data if its not empty or true, false on failure
 */
	public function testSave($data = null, $validate = true, $fieldList = []) {

		$this->markTestIncomplete('このテストは、まだ実装されていません。');

		$this->Page->save([
		'Page' => [
				'name' => 'test',
				'page_category_id' => null,
				'title' => '',
				'url' => '',
				'description' => '',
				'status' => 1,
				'modified' => '',
			]
		]);
		$now = date('Y-m-d H');

		$LastID = $this->Page->getLastInsertID();
		$result = $this->Page->find('first', [
				'conditions' => ['id' => $LastID],
				'fields' => ['created','modified'],
				'recursive' => -1
			]
		);
		$created = date('Y-m-d H', strtotime($result['Page']['created']));
		$modified = date('Y-m-d H', strtotime($result['Page']['modified']));

		$message = 'created,modifiedを更新できません';
		$this->assertEquals($now, $created, $message);
		$this->assertEquals($now, $modified, $message);
	}

/**
 * 配列の文字コードを変換する
 *
 * @param	array	変換前のデータ
 * @param	string	変換後の文字コード
 * @param	string 	変換元の文字コード
 * @dataProvider convertEncodingByArrayDataProvider
 */
	public function testConvertEncodingByArray($data, $outenc, $inenc) {
		$result = $this->BcApp->convertEncodingByArray($data, $outenc, $inenc);
		foreach ($result as $key => $value) {
			$encode = mb_detect_encoding($value);
			$this->assertEquals($outenc, $encode);
		}
	}

	public function convertEncodingByArrayDataProvider() {
		return [
			[["テスト1"], "ASCII", "SJIS"],
			[["テスト1", "テスト2"], "UTF-8", "SJIS"],
			[["テスト1", "テスト2"], "SJIS-win", "UTF-8"],
		];
	}

/**
 * データベースログを記録する
 */
	public function testSaveDbLog() {

		// Dblogにログを追加
		$message = 'テストです';
		$this->BcApp->saveDblog($message);

		// 最後に追加したログを取得
		$LastID = $this->Dblog->getLastInsertID();
		$result = $this->Dblog->find('first', [
				'conditions' => ['Dblog.id' => $LastID],
				'fields' => 'name',
			]
		);
		$this->assertEquals($message, $result['Dblog']['name']);

	}

/**
 * 機種依存文字の変換処理
 *
 * @param string 変換対象文字列
 * @param string 変換後予想文字列
 * @dataProvider replaceTextDataProvider
 */
	public function testReplaceText($str, $expect) {
		$result = $this->BcApp->replaceText($str);
		$this->assertEquals($expect, $result);
	}

	public function replaceTextDataProvider() {
		return [
			["\xE2\x85\xA0", "I"],
			["\xE2\x91\xA0", "(1)"],
			["\xE3\x8D\x89", "ミリ"],
			["\xE3\x88\xB9", "(代)"],
		];
	}

/**
 * データベース初期化
 *
 * @param $pluginName
 * @param $options
 * @param $expected
 *
 * @dataProvider initDbDataProvider
 *
 * MEMO: pluginNameが実在する場合が未実装
 */
	public function testInitDb($pluginName, $options, $expected) {
		$this->markTestIncomplete('このテストは、まだ実装されていません。');
		$result = $this->BcApp->initDb($pluginName, $options);
		$this->assertEquals($expected, $result);
	}

	public function initDbDataProvider() {
		return [
			['', [], true],
			['hoge', ['dbDataPattern' => true], 1]
		];
	}

/**
 * スキーマファイルを利用してデータベース構造を変更する
 */
	public function testLoadSchema() {
		$this->markTestIncomplete('このテストは、まだ実装されていません。');
		$path = BASER_CONFIGS . 'Schema';
		$result = $this->BcApp->loadSchema('test', $path);
		$expected = true;
		var_dump($result);
		 $this->assertEquals($expected, $result);
	}

/**
 * CSVを読み込む
 */
	public function testLoadCsv() {
		$this->markTestIncomplete('このテストは、まだ実装されていません。');
		$result = $this->BcApp->loadCsv('test','test');
	}

/**
 * 最短の長さチェック
 *
 * @param mixed $check
 * @param int $min
 * @param boolean $expect
 * @dataProvider minLengthDataProvider
 */
	public function testMinLength($check, $min, $expect) {
		$result = $this->BcApp->minLength($check, $min);
		$this->assertEquals($expect, $result);
	}

	public function minLengthDataProvider() {
		return [
			["あいう", 4, false],
			["あいう", 3, true],
			[["あいう", "あいうえお"], 4, false],
		];
	}
/**
 * 最長の長さチェック
 *
 * @param mixed $check
 * @param int $min
 * @param boolean $expect
 * @dataProvider maxLengthDataProvider
 */
	public function testMaxLength($check, $min, $expect) {
		$result = $this->BcApp->maxLength($check, $min);
		$this->assertEquals($expect, $result);
	}

	public function maxLengthDataProvider() {
		return [
			["あいう", 4, true],
			["あいう", 3, true],
			["あいう", 2, false],
			[["あいう", "あいうえお"], 4, true],
		];
	}

/**
 * 最大のバイト数チェック
 *
 * @param mixed $check
 * @param int $min
 * @param boolean $expect
 * @dataProvider maxByteDataProvider
 */
	public function testMaxByte($check, $min, $expect) {
		$result = $this->BcApp->maxByte($check, $min);
		$this->assertEquals($expect, $result);
	}

	public function maxByteDataProvider() {
		return [
			["あいう", 10, true],
			["あいう", 9, true],
			["あいう", 8, false]
		];
	}

/**
 * 範囲を指定しての長さチェック
 *
 * @param mixed $check
 * @param int $min
 * @param int $max
 * @param boolean $expect
 * @dataProvider betweenDataProvider
 */
	public function testBetween($check, $min, $max, $expect) {
		$result = $this->BcApp->between($check, $min, $max);
		$this->assertEquals($expect, $result);
	}

	public function betweenDataProvider() {
		return [
			["あいう", 2, 4, true],
			["あいう", 3, 3, true],
			["あいう", 4, 3, false],
			[["あいう", "あいうえお"], 2, 4, true],
		];
	}

/**
 * 指定フィールドのMAX値を取得する
 */
	public function testGetMax() {
		$result = $this->Page->getMax('Page\.id');
		$this->assertEquals(11, $result, '指定フィールドのMAX値を取得できません');
	}

/**
 * テーブルにフィールドを追加する
 */
	public function testAddField() {
		$options = [
			'field' => 'testField',
			'column' => [
				'type' => 'text',
				'null' => true,
				'default' => null,
			],
			'table' => 'pages',
		];
		$this->Page->addField($options);
		$columns = $this->Page->getColumnTypes();
		$this->assertEquals(isset($columns['testField']), true);
	}

/**
 * フィールド構造を変更する
 */
	public function testEditField() {
		$this->markTestIncomplete('このテストは、まだ実装されていません。');
		$options = [
			'field' => 'testField',
			'column' => [
				'name' => 'testColumn',
			],
		];
		$this->BcApp->editField($options);
		$columns = $this->Page->getColumnTypes();
	}

/**
 * フィールド名を変更する
 */
	public function testRenameField() {
		$this->markTestIncomplete('このテストは、まだ実装されていません。');
	}


/**
 * フィールドを削除する
 */
	public function testDelField() {
		$this->markTestIncomplete('このテストは、まだ実装されていません。');
	}

/**
 * テーブルの存在チェックを行う
 * 
 * @param string $tableName
 * @param boolean $expect
 * @dataProvider tableExistsDataProvider
 */
	public function testTableExists($tableName, $expect) {
		$db = ConnectionManager::getDataSource('default');
		$prefix = $db->config['prefix'];

		$result = $this->BcApp->tableExists($prefix . $tableName);
		$this->assertEquals($expect, $result);
	}

	public function tableExistsDataProvider() {
		return [
			["users", true],
			["notexist", false],
		];
	}

/**
 * 英数チェック
 *
 * @param string $check チェック対象文字列
 * @param boolean $expect 
 * @dataProvider alphaNumericDataProvider
 */
	public function testAlphaNumeric($check, $expect) {
		$result = $this->BcApp->alphaNumeric($check);
		$this->assertEquals($expect, $result);
	}

	public function alphaNumericDataProvider() {
		return [
			[["aiueo"], true],
			[["12345"], true],
			[["あいうえお"], false],
		];
	}

/**
 * データの重複チェックを行う
 */
	public function testDuplicate() {
		$check = ['id' => 1];
		$result = $this->Page->duplicate($check);
		$this->assertEquals(false, $result);

		$check = ['id' => 100];
		$result = $this->Page->duplicate($check);
		$this->assertEquals(true, $result);
	}

/**
 * ファイルサイズチェック
 * 
 * @param string $fileName チェック対象ファイル名
 * @param string $fileSize チェック対象ファイルサイズ
 * @param boolean $expect
 * @dataProvider fileSizeDataProvider
 */
	public function testFileSize($fileName, $fileSize, $expect) {
		$check = [[
				"name" => $fileName,
				"size" => $fileSize,
			]
		];
		$size = 1000;

		$result = $this->BcApp->fileSize($check, $size);
		$this->assertEquals($expect, $result);		
	}

	public function fileSizeDataProvider() {
		return [
			["test.jpg", 1000, true],
			["test.jpg", 1001, false],
			["", 1000, true],
			["test.jpg", null, false],
		];
	}

/**
 * ファイルの拡張子チェック
 * 
 * @param string $fileName チェック対象ファイル名
 * @param string $fileSize チェック対象ファイルタイプ
 * @param boolean $expect
 * @dataProvider fileExtDataProvider
 */
	public function testFileExt($fileName, $fileType, $expect) {
		$check = [[
				"name" => $fileName,
				"type" => $fileType,
			]
		];
		$ext = "jpg,png";

		$result = $this->BcApp->fileExt($check, $ext);
		$this->assertEquals($expect, $result);		
	}

	public function fileExtDataProvider() {
		return [
			["test.jpg", "image/jpeg", true],
			["test.png", "image/png", true],
			["test.gif", "image/gif", false],
			["test", "image/png", true],
		];
	}

/**
 * 半角チェック
 * 
 * @param array $check
 * @param boolean $expect
 * @dataProvider halfTextDataProvider
 */
	public function testHalfText($check, $expect) {
		$result = $this->BcApp->halfText($check);
		$this->assertEquals($expect, $result);
	}

	public function halfTextDataProvider() {
		return [
			[["test"], true],
			[["テスト"], false],
			[["test", "テスト"], true],
			[["テスト", "test"], false],
		];
	}

/**
 * Modelキャッシュを削除する
 */
	public function testDeleteModelCache() {
		$path = CACHE . 'models' . DS . 'dummy';

		// ダミーファイルをModelキャッシュフォルダに作成
		if (touch($path)) {
			$this->BcApp->deleteModelCache();
			$result = !file_exists($path);
			$this->assertTrue($result, 'Modelキャッシュを削除できません');

		} else {
			$this->markTestIncomplete('ダミーのキャッシュファイルの作成に失敗しました。');

		}
	}

/**
 * Key Value 形式のテーブルよりデータを取得して
 * １レコードとしてデータを展開する
 */
	public function testFindExpanded() {
		$result = $this->SiteConfig->findExpanded();

		$message = 'Key Value 形式のテーブルよりデータを取得して１レコードとしてデータを展開することができません';
		$this->assertEquals('baserCMS inc. [デモ]', $result['name'], $message);
		$this->assertEquals('baser,CMS,コンテンツマネジメントシステム,開発支援', $result['keyword'], $message);
	}

/**
 * Key Value 形式のテーブルにデータを保存する
 */
	public function testSaveKeyValue() {
		$data = [
			'SiteConfig' => [
				'test1' => 'テストです1',
				'test2' => 'テストです2',
			]
		];
		$this->SiteConfig->saveKeyValue($data);
		$result = $this->SiteConfig->findExpanded();

		$message = 'Key Value 形式のテーブルにデータを保存することができません';
		$this->assertEquals('テストです1', $result['test1'], $message);
		$this->assertEquals('テストです2', $result['test2'], $message);

	}

/**
 * リストチェック
 * 対象となる値がリストに含まれる場合はエラー
 * 
 * @param string $check 対象となる値
 * @param array $list リスト
 * @param boolean $expect
 * @dataProvider notInListDataProvider
 */
	public function testNotInList($check, $list, $expect) {
		$result = $this->BcApp->notInList($check, $list);
		$this->assertEquals($expect, $result);
	}

	public function notInListDataProvider() {
		return [
			[["test1"], ["test1", "test2"], false],
			[["test3"], ["test1", "test2"], true],
		];
	}

/**
 * Deconstructs a complex data type (array or object) into a single field value.
 */
	public function testDeconstruct() {
		$field = 'Page.contents';
		$data = [
			'wareki' => true,
			'year' => 'h-27',
		];
		$result = $this->Page->deconstruct($field, $data);

		$expected = [
			'wareki' => true,
			'year' => 2015
		];

		$this->assertEquals($expected, $result, 'deconstruct が 和暦に対応していません');
	}

/**
 * ２つのフィールド値を確認する
 * 
 * @param mixed $check 対象となる値
 * @param	mixed	$fields フィールド名
 * @param	mixed	$data 値データ
 * @param boolean $expected 期待値
 * @param boolean $message テストが失敗した場合に表示されるメッセージ
 * @dataProvider confirmDataProvider
 */
	public function testConfirm($check, $fields, $data, $expected, $message = null) {
		$this->BcApp->data['BcApp'] = $data;
		$result = $this->BcApp->confirm($check, $fields);
		$this->assertEquals($expected, $result, $message);

	}

	public function confirmDataProvider() {
		return [
			['', ['test1', 'test2'], ['test1' => 'value','test2' => 'value'], true, '2つのフィールドが同じ値の場合の判定が正しくありません'],
			['', ['test1', 'test2'], ['test1' => 'value','test2' => 'other_value'], false, '2つのフィールドが異なる値の場合の判定が正しくありません'],
			[['value'=>'value'], 'test', ['test' => 'value'], true, 'フィールド名が一つで同じ値の場合の判定が正しくありません'],
			[['value'=>'value'], 'test', ['test' => 'other_value'], false, 'フィールド名が一つで異なる値の場合の判定が正しくありません'],
		];
	}

/**
 * 指定したモデル以外のアソシエーションを除外する
 *
 * @param array $auguments アソシエーションを除外しないモデル
 * @param array $expectedHasKey 期待する存在するキー
 * @param array $expectedNotHasKey 期待する存在しないキー
 * @dataProvider expectsDataProvider
 */
	public function testExpects($arguments, $expectedHasKeys, $expectedNotHasKeys) {
		$this->User->expects($arguments);
		$result = $this->User->find('first', ['recursive' => 1]);

		// 存在するキー
		foreach ($expectedHasKeys as $key) {
			$this->assertArrayHasKey($key, $result, '指定したモデル以外のアソシエーションを除外できません');
		}

		// 存在しないキー
		foreach ($expectedNotHasKeys as $key) {
			$this->assertArrayNotHasKey($key, $result, '指定したモデル以外のアソシエーションを除外できません');
		}
	}

	public function expectsDataProvider() {
		return [
			[[], ['User'], ['UserGroup', 'Favorite']],
			[['UserGroup'], ['User', 'UserGroup'], ['Favorite']],
		];
	}

/**
 * 複数のEメールチェック（カンマ区切り）
 * 
 * @param array $check 複数のメールアドレス
 * @param boolean $expect
 * @dataProvider emailsDataProvider
 */
	public function testEmails($check, $expect) {
		$message = '複数のEメールのバリデーションチェックができません';
		$result = $this->BcApp->emails($check);
		$this->assertEquals($expect, $result, $message);
	}

	public function emailsDataProvider() {
		return [
			[["test1@co.jp"], true],
			[["test1@co.jp,test2@cp.jp"], true],
			[["test1@cojp,test2@cp.jp"], false],
			[["test1@co.jp,test2@cpjp"], false],
		];
	}

/**
 * Used to report user friendly errors.
 * If there is a file app/error.php or app/app_error.php this file will be loaded
 * error.php is the AppError class it should extend ErrorHandler class.
 */
	public function testCakeError() {
		$this->markTestIncomplete('このテストは、まだ実装されていません。');
	}

/**
 * Queries the datasource and returns a result set array.
 */
	public function testFind() {
		$this->markTestIncomplete('このテストは、まだ実装されていません。');
	}

/**
 * イベントを発火
 */
	public function testDispatchEvent() {
		$this->markTestIncomplete('このテストは、まだ実装されていません。');
	}

/**
 * データが公開済みかどうかチェックする
 *
 * @param boolean $status 公開ステータス
 * @param string $publishBegin 公開開始日時
 * @param string $publishEnd 公開終了日時
 * @param boolean $expect
 * @dataProvider isPublishDataProvider
 */
	public function testIsPublish($status, $publishBegin, $publishEnd, $expect) {
		$result = $this->BcApp->isPublish($status, $publishBegin, $publishEnd);
		$this->assertEquals($expect, $result);
	}

	public function isPublishDataProvider() {
		return [
			[true, null, null, true],
			[false, null, null, false],
			[true, '2015-01-01 00:00:00', null, true],
			[true, '3000-01-01 00:00:00', null, false],
			[true, null, '2015-01-01 00:00:00', false],
			[true, null, '3000-01-01 00:00:00', true],
			[true, '2015-01-01 00:00:00', '3000-01-01 00:00:00', true],
			[true, '2015-01-01 00:00:00', '2015-01-02 00:00:00', false],
		];
	}

/**
 * 日付の正当性チェック
 * 
 * @param array $check 確認する値
 * @param boolean $expect
 * @dataProvider checkDateDataProvider
 */
	public function testCheckDate($check, $expect) {
		$result = $this->BcApp->checkDate($check);
		$this->assertEquals($expect, $result);	
	}

	public function checkDateDataProvider() {
		return [
			[['2015-01-01'], true],
			[['201511'], false],
			[['2015-01-01 00:00:00'], true],
			[['2015-0101 00:00:00'], false],
			[['1970-01-01 09:00:00'], false],
		];
	}


/**
 * ツリーより再帰的に削除する
 */
	public function testRemoveFromTreeRecursive() {
		$this->markTestIncomplete('このテストは、まだ実装されていません。');
	}

/**
 * ファイルが送信されたかチェックするバリデーション
 * 
 * @param array $check ファイルのデータ
 * @param boolean $expect　
 * @dataProvider notFileEmptyDataProvider
 */
	public function testNotFileEmpty($check,$expect) {
		$file = [$check];
		$result = $this->BcApp->notFileEmpty($file);
		$this->assertEquals($expect, $result);
	}

	public function notFileEmptyDataProvider() {
		return [
			[['size' => 0], false],
			[['size' => 100], true],
			[[], false],
		];
	}

/**
 * BcContentsRoute::getUrlPattern
 *
 * @param string $url URL文字列
 * @param string $expect 期待値
 * @return void
 * @dataProvider getUrlPatternDataProvider
 */
	public function testGetUrlPattern($url, $expects) {
		$this->assertEquals($expects, $this->BcApp->getUrlPattern($url));
	}

	public function getUrlPatternDataProvider() {
		return [
			['/news', ['/news']],
			['/news/', ['/news/', '/news/index']],
			['/news/index', ['/news/index', '/news/']],
			['/news/archives/1', ['/news/archives/1']],
			['/news/archives/index', ['/news/archives/index', '/news/archives/']]
		];
	}

/**
 * 単体データをサニタイズ処理する関数
 *
 * @param $data
 * @param $expect
 * @dataProvider sanitizeDataProvider
 */
	public function testSanitize($data, $expect) {
		$result = $this->BcApp->sanitize($data);
		$this->assertEquals($expect, $result);
	}

	public function sanitizeDataProvider() {
		return[
			['<', '&lt;'],
			['>', '&gt;'],
			['"', '&quot;'],
			['\\', '\\']
		];
	}

/**
 * レコードデータをサニタイズ処理する関数
 * @param $data
 * @param $expect
 * @dataProvider sanitizeRecordDataProvider
 */
	public function testSanitizeRecord($data, $expect) {
		$result = $this->BcApp->sanitizeRecord($data);
		$this->assertEquals($expect, $result);
	}

	public function sanitizeRecordDataProvider() {
		return[
			[['aa', '\"', '<', '>'], ['aa', '\&quot;', '&lt;', '&gt;']],
			[["aa", "\"", "<", ">"], ['aa', '&quot;', '&lt;', '&gt;']],
			[[["aa", "\"", "<", ">"], '\"', '<', '>'], [['aa', '"', '<', '>'], '\&quot;', '&lt;', '&gt;']],
		];
	}

}