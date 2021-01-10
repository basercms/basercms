<?php
/**
 * baserCMS :  Based Website Development Project <https://basercms.net>
 * Copyright (c) baserCMS Users Community <https://basercms.net/community/>
 *
 * @copyright		Copyright (c) baserCMS Users Community
 * @link			https://basercms.net baserCMS Project
 * @package			Feed.Test.Case
 * @since			baserCMS v 3.0.0
 * @license			https://basercms.net/license/index.html
 */

class FeedAllModelTest extends CakeTestSuite {

/**
 * Suite define the tests for this suite
 *
 * @return CakeTestSuite
 */
	public static function suite() {
		$suite = new CakeTestSuite('All Feed Model tests');
		$suite->addTestDirectory(dirname(__FILE__) . DS . 'Model' . DS);
		return $suite;
	}

}
