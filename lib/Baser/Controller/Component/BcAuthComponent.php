<?php
/**
 * baserCMS :  Based Website Development Project <http://basercms.net>
 * Copyright (c) baserCMS Users Community <http://basercms.net/community/>
 *
 * @copyright		Copyright (c) baserCMS Users Community
 * @link			http://basercms.net baserCMS Project
 * @package			Baser.Controller.Component
 * @since			baserCMS v 0.1.0
 * @license			http://basercms.net/license/index.html
 */

App::uses('AuthComponent', 'Controller/Component');

/**
 * Authentication control component class （baserCMS拡張）
 *
 * Binds access control with user authentication and session management.
 *
 * @package Baser.Controller.Component
 */
class BcAuthComponent extends AuthComponent {

/**
 * 個体識別ID
 * @var string
 * CUSTOMIZE ADD 2011/09/25 ryuring
 */
	public $serial = '';

/**
 * Log a user in. If a $user is provided that data will be stored as the logged in user.  If `$user` is empty or not
 * specified, the request will be used to identify a user. If the identification was successful,
 * the user record is written to the session key specified in AuthComponent::$sessionKey. Logging in
 * will also change the session id in order to help mitigate session replays.
 *
 * @param array $user Either an array of user data, or null to identify a user using the current request.
 * @return boolean True on login success, false on failure
 * @link http://book.cakephp.org/2.0/en/core-libraries/components/authentication.html#identifying-users-and-logging-them-in
 */
	public function login($user = null) {
		// CUSTOMIZE ADD 2011/09/25 ryuring
		// 簡単ログイン
		// >>>
		if (!empty($this->fields['serial']) && !$user) {
			$serial = $this->getSerial();
			$Model = $this->getModel();
			if ($serial) {
				$user = $Model->find(
					'first',
					[
						'conditions' => [
							sprintf('%s.%s', $Model->alias, $this->fields['serial']) => $serial
						],
						'recursive' => -1
					]
				);
			}
		}
		// <<<
		// CUSTOMIZE ADD 2011/09/25 ryuring
		// ログイン時点でもモデルを保存しておく Session::user() のキーとして利用する
		// >>>
		return parent::login($user);
		// <<<
	}

/**
 * Logs a user out, and returns the login action to redirect to.
 * Triggers the logout() method of all the authenticate objects, so they can perform
 * custom logout logic.  AuthComponent will remove the session data, so
 * there is no need to do that in an authentication object.  Logging out
 * will also renew the session id.  This helps mitigate issues with session replays.
 *
 * @return string AuthComponent::$logoutRedirect
 * @see AuthComponent::$logoutRedirect
 * @link http://book.cakephp.org/2.0/en/core-libraries/components/authentication.html#logging-users-out
 */
	public function logout() {
		if (!empty($this->fields['serial'])) {
			$this->deleteSerial();
		}
		return parent::logout();
	}

/**
 * 個体識別IDを保存する
 *
 * @return boolean
 */
	public function saveSerial() {
		$user = self::user();
		if (!empty($this->fields['serial']) && $user) {
			$serial = $this->getSerial();
			$Model = $model = $this->getModel();
			if ($serial) {
				$user[$this->userModel][$this->fields['serial']] = $serial;
				$Model->set($user);
				return $Model->save();
			}
		}
	}

/**
 * 個体識別IDを削除する
 *
 * @return boolean
 */
	public function deleteSerial() {
		$user = self::user();
		if (!empty($this->fields['serial']) && $user) {
			$Model = $model = $this->getModel();
			$user[$this->userModel][$this->fields['serial']] = '';
			$Model->set($user);
			return $Model->save();
		}
	}

/**
 * 個体識別IDを取得
 *
 * @return string
 */
	public function getSerial() {
		if (!empty($_SERVER['HTTP_X_DCMGUID'])) {
			return $_SERVER['HTTP_X_DCMGUID'];
		}

		if (!empty($_SERVER['HTTP_X_UP_SUBNO'])) {
			return $_SERVER['HTTP_X_UP_SUBNO'];
		}

		if (!empty($_SERVER['HTTP_X_JPHONE_UID'])) {
			return $_SERVER['HTTP_X_JPHONE_UID'];
		}
		return '';
	}

/**
 * セッションキーをセットする
 *
 * @param string $sessionKey
 */
	public function setSessionKey($sessionKey) {
		self::$sessionKey = $sessionKey;
	}

/**
 * 再ログインを実行する
 *
 * return boolean
 */
	public function relogin () {

		$UserModel = ClassRegistry::init($this->authenticate['Form']['userModel']);
		$user = self::user();
		$Db = $UserModel->getDataSource();
		$Db->flushMethodCache();
		$UserModel->schema(true);
		$user = $UserModel->find('first', ['conditions' => ['User.id' => $user['id']], 'recursive' => -1]);
		$this->authenticate['Form']['passwordHasher'] = 'BcNo';
		$this->request->data['User'] = $user['User'];
		return $this->login();
	}
}
