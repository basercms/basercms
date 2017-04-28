<?php
/**
 * baserCMS :  Based Website Development Project <http://basercms.net>
 * Copyright (c) baserCMS Users Community <http://basercms.net/community/>
 *
 * @copyright		Copyright (c) baserCMS Users Community
 * @link			http://basercms.net baserCMS Project
 * @package			Baser.Model.Behavior
 * @since			baserCMS v 1.5.3
 * @license			http://basercms.net/license/index.html
 */

App::uses('Imageresizer', 'Vendor');

/**
 * ファイルアップロードビヘイビア
 * 
 * 《設定例》
 * public $actsAs = array(
 *  'BcUpload' => array(
 *     'saveDir'  => "editor",
 *     'fields'  => array(
 *       'image'  => array(
 *         'type'      => 'image',
 *         'namefield'    => 'id',
 *         'nameadd'    => false,
 * 			'subdirDateFormat'	=> 'Y/m'	// Or false
 *         'imageresize'  => array('prefix' => 'template', 'width' => '100', 'height' => '100'),
 * 				'imagecopy'		=> array(
 * 					'thumb'			=> array('suffix' => 'template', 'width' => '150', 'height' => '150'),
 * 					'thumb_mobile'	=> array('suffix' => 'template', 'width' => '100', 'height' => '100')
 * 				)
 *       ),
 *       'pdf' => array(
 *         'type'      => 'pdf',
 *         'namefield'    => 'id',
 *         'nameformat'  => '%d',
 *         'nameadd'    => false
 *       )
 *     )
 *   )
 * );
 *  
 * @package Baser.Model.Behavior
 */
class BcUploadBehavior extends ModelBehavior {

/**
 * 保存ディレクトリ
 * 
 * @var string
 */
	public $savePath = '';

/**
 * 設定
 * 
 * @var array
 */
	public $settings = null;

/**
 * 一時ID
 * 
 * @var string
 */
	public $tmpId = null;

/**
 * Session
 * 
 * @var Session
 */
	public $Session = null;

/**
 * 画像拡張子
 * 
 * @var array 
 */
	public $imgExts = array('gif', 'jpg', 'jpeg', 'jpe', 'jfif', 'png');

/**
 * アップロードしたかどうか
 *
 * afterSave のリネーム判定に利用
 *
 * @var bool
 */
	public $uploaded = false;

/**
 * セットアップ
 * 
 * @param Model	$Model
 * @param array	actsAsの設定
 * @return void
 */
	public function setup(Model $Model, $settings = array()) {
		$this->settings[$Model->alias] = Hash::merge(array(
				'saveDir' => '',
				'fields' => array()
				), $settings);
		
		$this->savePath[$Model->alias] = $this->getSaveDir($Model);

		if (!is_dir($this->savePath[$Model->alias])) {
			$Folder = new Folder();
			$Folder->create($this->savePath[$Model->alias]);
			$Folder->chmod($this->savePath[$Model->alias], 0777, true);
		}

		App::uses('SessionComponent', 'Controller/Component');
		$this->Session = new SessionComponent(new ComponentCollection());
	}

/**
 * Before save
 * 
 * @param Model $Model
 * @param Model $options
 * @return boolean
 */
	public function beforeSave(Model $Model, $options = array()) {
		if($Model->exists()) {
			$this->deleteExistingFiles($Model);	
		}
		return $this->saveFiles($Model);
	}

/**
 * After save
 * 
 * @param Model $Model
 * @param Model $created
 * @param Model $options
 * @return boolean
 */
	public function afterSave(Model $Model, $created, $options = array()) {
		if($this->uploaded) {
			$this->renameToFieldBasename($Model);
			$Model->data = $Model->save($Model->data, array('callbacks' => false, 'validate' => false));
			$this->uploaded = false;
		}
	}

/**
 * 一時ファイルとして保存する
 * 
 * @param Model $Model
 * @param array $data
 * @param string $tmpId
 * @return boolean
 */
	public function saveTmpFiles(Model $Model, $data, $tmpId) {
		$this->Session->delete('Upload');
		$Model->data = $data;
		$this->tmpId = $tmpId;
		if ($this->saveFiles($Model)) {
			return $Model->data;
		} else {
			return false;
		}
	}

/**
 * ファイル群を保存する
 * 
 * @param Model $Model
 * @return boolean
 */
	public function saveFiles(Model $Model) {
		$serverData = $Model->findById($Model->id);

		$this->uploaded = false;
		foreach ($this->settings[$Model->alias]['fields'] as $key => $field) {

			if (empty($field['name'])) {
				$field['name'] = $key;
			}

			if (!empty($Model->data[$Model->name][$field['name'] . '_delete'])) {
				$file = $serverData[$Model->name][$field['name']];
				if (!$this->tmpId) {
					$this->delFile($Model, $file, $field);
					$Model->data[$Model->name][$field['name']] = '';
				} else {
					$Model->data[$Model->name][$field['name']] = $file;
				}
				continue;
			}
			if (empty($Model->data[$Model->name][$field['name']]['name']) && !empty($Model->data[$Model->name][$field['name'] . '_'])) {
				// 新しいデータが送信されず、既存データを引き継ぐ場合は、元のフィールド名に戻す
				$Model->data[$Model->name][$field['name']] = $Model->data[$Model->name][$field['name'] . '_'];
				unset($Model->data[$Model->name][$field['name'] . '_']);
			} elseif (!empty($Model->data[$Model->name][$field['name'] . '_tmp'])) {
				// セッションに一時ファイルが保存されている場合は復元する
				$this->moveFileSessionToTmp($Model, $field['name']);
			} elseif (!isset($Model->data[$Model->name][$field['name']]) ||
				!is_array($Model->data[$Model->name][$field['name']])) {
				continue;
			}

			if (!empty($Model->data[$Model->name][$field['name']]) && is_array($Model->data[$Model->name][$field['name']])) {

				if ($Model->data[$Model->name][$field['name']]['size'] == 0) {
					unset($Model->data[$Model->name][$field['name']]);
					continue;
				}

				// 拡張子を取得
				$field['ext'] = decodeContent($Model->data[$Model->name][$field['name']]['type'], $Model->data[$Model->name][$field['name']]['name']);

				/* タイプ別除外 */
				if ($field['type'] == 'image') {
					if (!in_array($field['ext'], $this->imgExts)) {
						unset($Model->data[$Model->name][$field['name']]);
						continue;
					}
				} else {
					if (is_array($field['type'])) {
						if (!in_array($field['ext'], $field['type'])) {
							unset($Model->data[$Model->name][$field['name']]);
							continue;
						}
					} else {
						if ($field['type'] != 'all' && $field['type'] != $field['ext']) {
							unset($Model->data[$Model->name][$field['name']]);
							continue;
						}
					}
				}

				if (empty($Model->data[$Model->name][$field['name']]['name'])) {

					/* フィールドに値がない場合はスキップ */
					unset($Model->data[$Model->name][$field['name']]);
					continue;
				} else {

					/* アップロードしたファイルを保存する */
					// ファイル名が重複していた場合は変更する
					$Model->data[$Model->name][$field['name']]['name'] = $this->getUniqueFileName($Model, $field['name'], $Model->data[$Model->name][$field['name']]['name'], $field);

					// 画像を保存
					$fileName = $this->saveFile($Model, $field);
					if ($fileName) {
						if (!$this->tmpId && ($field['type'] == 'all' || $field['type'] == 'image') && !empty($field['imagecopy']) && in_array($field['ext'], $this->imgExts)) {
							/* 画像をコピーする */
							foreach ($field['imagecopy'] as $copy) {
								// コピー画像が元画像より大きい場合はスキップして作成しない
								$size = $this->getImageSize($this->savePath[$Model->alias] . $fileName);
								if ($size && $size['width'] < $copy['width'] && $size['height'] < $copy['height']) {
									if (isset($copy['smallskip']) && $copy['smallskip'] === false) {
										$copy['width'] = $size['width'];
										$copy['height'] = $size['height'];
									} else {
										continue;
									}
								}

								$copy['name'] = $field['name'];
								$copy['ext'] = $field['ext'];
								$ret = $this->copyImage($Model, $copy);
								if (!$ret) {
									// 失敗したら処理を中断してfalseを返す
									return false;
								}
							}
						}

						// ファイルをリサイズ
						if (!$this->tmpId && !empty($field['imageresize']) && in_array($field['ext'], $this->imgExts)) {
							if (!empty($field['imageresize']['thumb'])) {
								$thumb = $field['imageresize']['thumb'];
							} else {
								$thumb = false;
							}
							$filePath = $this->savePath[$Model->alias] . $fileName;
							$this->resizeImage($filePath, $filePath, $field['imageresize']['width'], $field['imageresize']['height'], $thumb);
						}

						// 一時ファイルを削除
						@unlink($Model->data[$Model->name][$field['name']]['tmp_name']);
						// フィールドの値をファイル名に更新
						if (!$this->tmpId) {
							$Model->data[$Model->name][$field['name']] = $fileName;
						} else {
							$Model->data[$Model->name][$field['name']]['session_key'] = $fileName;
						}
						$this->uploaded = true;
					} else {
						// 失敗したら処理を中断してfalseを返す
						return false;
					}
				}
			}
		}

		return true;
	}

/**
 * セッションに保存されたファイルデータをファイルとして保存する
 * 
 * @param Model $Model
 * @param string $fieldName
 * @return void
 */
	public function moveFileSessionToTmp(Model $Model, $fieldName) {
		$fileName = $Model->data[$Model->alias][$fieldName . '_tmp'];
		$sessionKey = str_replace(array('.', '/'), array('_', '_'), $fileName);
		$tmpName = $this->savePath[$Model->alias] . $sessionKey;
		$fileData = $this->Session->read('Upload.' . $sessionKey . '.data');
		$fileType = $this->Session->read('Upload.' . $sessionKey . '.type');
		$this->Session->delete('Upload.' . $sessionKey);

		// サイズを取得
		if (ini_get('mbstring.func_overload') & 2 && function_exists('mb_strlen')) {
			$fileSize = mb_strlen($fileData, 'ASCII');
		} else {
			$fileSize = strlen($fileData);
		}

		if ($fileSize == 0) {
			return false;
		}

		// ファイルを一時ファイルとして保存
		$file = new File($tmpName, true, 0666);
		$file->write($fileData);
		$file->close();

		// 元の名前を取得
		/*$pos = strpos($sessionKey, '_');
		$fileName = substr($sessionKey, $pos + 1, strlen($sessionKey));*/

		// アップロードされたデータとしてデータを復元する
		$uploadInfo['error'] = 0;
		$uploadInfo['name'] = $fileName;
		$uploadInfo['tmp_name'] = $tmpName;
		$uploadInfo['size'] = $fileSize;
		$uploadInfo['type'] = $fileType;
		$Model->data[$Model->alias][$fieldName] = $uploadInfo;
		unset($Model->data[$Model->alias][$fieldName . '_tmp']);
	}

/**
 * ファイルを保存する
 * 
 * @param Model $Model
 * @param array 画像保存対象フィールドの設定
 * @return ファイル名 Or false
 */
	public function saveFile(Model $Model, $field) {
		// データを取得
		$file = $Model->data[$Model->name][$field['name']];

		if (empty($file['tmp_name'])) {
			return false;
		}
		if (!empty($file['error']) && $file['error'] != 0) {
			return false;
		}

		$fileName = $this->getSaveFileName($Model, $field, $file['name']);
		$filePath = $this->savePath[$Model->alias] . $fileName;
		$this->rotateImage($file['tmp_name']);

		if (!$this->tmpId) {
			if (copy($file['tmp_name'], $filePath)) {
				chmod($filePath, 0666);
				$ret = $fileName;
			} else {
				$ret = false;
			}
		} else {
			$_fileName = str_replace(array('.', '/'), array('_', '_'), $fileName);
			$this->Session->write('Upload.' . $_fileName, $field);
			$this->Session->write('Upload.' . $_fileName . '.type', $file['type']);
			$this->Session->write('Upload.' . $_fileName . '.data', file_get_contents($file['tmp_name']));
			return $fileName;
		}

		return $ret;
	}

/**
 * 保存用ファイル名を取得する
 *
 * @param Model $Model
 * @param $field
 * @param $name
 * @return mixed|string
 */
	public function getSaveFileName(Model $Model, $field, $name) {
		// プレフィックス、サフィックスを取得
		$prefix = '';
		$suffix = '';
		if (!empty($field['prefix'])) {
			$prefix = $field['prefix'];
		}
		if (!empty($field['suffix'])) {
			$suffix = $field['suffix'];
		}
		// 保存ファイル名を生成
		$basename = preg_replace("/\." . $field['ext'] . "$/is", '', $name);
		if (!$this->tmpId) {
			$fileName = $prefix . $basename . $suffix . '.' . $field['ext'];
			if(file_exists($this->savePath[$Model->alias] . $fileName)) {
				if(preg_match('/(.+_)([0-9]+)$/', $basename, $matches)) {
					$basename = $matches[1] . ((int) $matches[2] + 1);
				} else {
					$basename = $basename . '_1';
				}
				$fileName = $this->getSaveFileName($Model, $field, $basename . '.' . $field['ext']);
			}
		} else {
			if (!empty($field['namefield'])) {
				$Model->data[$Model->alias][$field['namefield']] = $this->tmpId;
				$fileName = $this->getFieldBasename($Model, $field, $field['ext']);
			} else {
				$fileName = $this->tmpId . '_' . $field['name'] . '.' . $field['ext'];
			}
		}
		return $fileName;
	}

/**
 * 画像をExif情報を元に正しい確度に回転する
 * 
 * @param $file
 * @return bool
 */
	public function rotateImage($file) {
		if(!function_exists('exif_read_data')) {
			return false;
		}
		$exif = @exif_read_data($file);
		if(empty($exif) || empty($exif['Orientation'])) {
			return true;
		}
		switch($exif['Orientation']) {
			case 3:
				$angle = 180;
				break;
			case 6:
				$angle = 270;
				break;
			case 8:
				$angle = 90;
				break;
			default:
				return true;
		}
		$imgInfo = getimagesize($file);
		$imageType = $imgInfo[2];
		// 元となる画像のオブジェクトを生成
		switch($imageType) {
			case IMAGETYPE_GIF:
				$srcImage = imagecreatefromgif($file);
				break;
			case IMAGETYPE_JPEG:
				$srcImage = imagecreatefromjpeg($file);
				break;
			case IMAGETYPE_PNG:
				$srcImage = imagecreatefrompng($file);
				break;
			default:
				return false;
		}
		$rotate = imagerotate($srcImage, $angle, 0);
		switch($imageType) {
			case IMAGETYPE_GIF:
				imagegif($rotate, $file);
				break;
			case IMAGETYPE_JPEG:
				imagejpeg($rotate, $file, 100);
				break;
			case IMAGETYPE_PNG:
				imagepng($rotate, $file);
				break;
			default:
				return false;
		}
		imagedestroy($srcImage);
		imagedestroy($rotate);
		return true;
	}

/**
 * 画像をコピーする
 * 
 * @param Model $Model
 * @param array 画像保存対象フィールドの設定
 * @return boolean
 */
	public function copyImage(Model $Model, $field) {
		// データを取得
		$file = $Model->data[$Model->name][$field['name']];

		// プレフィックス、サフィックスを取得
		$prefix = '';
		$suffix = '';
		if (!empty($field['prefix'])) {
			$prefix = $field['prefix'];
		}
		if (!empty($field['suffix'])) {
			$suffix = $field['suffix'];
		}

		// 保存ファイル名を生成
		$basename = preg_replace("/\." . $field['ext'] . "$/is", '', $file['name']);
		$fileName = $prefix . $basename . $suffix . '.' . $field['ext'];

		$filePath = $this->savePath[$Model->alias] . $fileName;

		if (!empty($field['thumb'])) {
			$thumb = $field['thumb'];
		} else {
			$thumb = false;
		}

		return $this->resizeImage($Model->data[$Model->name][$field['name']]['tmp_name'], $filePath, $field['width'], $field['height'], $thumb);
	}

/**
 * 画像ファイルをコピーする
 * リサイズ可能
 * 
 * @param string $source コピー元のパス
 * @param string $distination コピー先のパス
 * @param int $width 横幅
 * @param int $height 高さ
 * @param boolean $$thumb サムネイルとしてコピーするか
 * @return boolean
 */
	public function resizeImage($source, $distination, $width = 0, $height = 0, $thumb = false) {
		if ($width > 0 || $height > 0) {
			$imageresizer = new Imageresizer();
			$ret = $imageresizer->resize($source, $distination, $width, $height, $thumb);
		} else {
			$ret = copy($source, $distination);
		}

		if ($ret) {
			chmod($distination, 0666);
		}

		return $ret;
	}

/**
 * 画像のサイズを取得
 * 
 * 指定したパスにある画像のサイズを配列(高さ、横幅)で返す
 * 
 * @param string $path 画像のパス
 * @return mixed array / false
 */
	public function getImageSize($path) {
		$imginfo = getimagesize($path);
		if ($imginfo) {
			return array('width' => $imginfo[0], 'height' => $imginfo[1]);
		}
		return false;
	}

/**
 * Before delete
 * 画像ファイルの削除を行う
 * 削除に失敗してもデータの削除は行う
 * 
 * @param Model $Model
 */
	public function beforeDelete(Model $Model, $cascade = true) {
		$Model->data = $Model->findById($Model->id);
		$this->delFiles($Model);
		return true;
	}

/**
 * 画像ファイル群を削除する
 * 
 * @param Model $Model
 * @param string $fieldName フィールド名
 * @return boolean
 */
	public function delFiles(Model $Model, $fieldName = null) {
		foreach ($this->settings[$Model->alias]['fields'] as $key => $field) {
			if (empty($field['name'])) {
				$field['name'] = $key;
			}
			if (!$fieldName || ($fieldName && $fieldName == $field['name'])) {
				if (!empty($Model->data[$Model->name][$field['name']])) {
					$file = $Model->data[$Model->name][$field['name']];
					$this->delFile($Model, $file, $field);
				}
			}
		}
	}

/**
 * ファイルを削除する
 * 
 * @param Model $Model
 * @param array $field 保存対象フィールドの設定
 * - ext 対象のファイル拡張子
 * - prefix 対象のファイルの接頭辞
 * - suffix 対象のファイルの接尾辞
 * @param boolean $delImagecopy 
 * @return boolean
 */
	public function delFile(Model $Model, $file, $field, $delImagecopy = true) {
		if (!$file) {
			return true;
		}

		if (empty($field['ext'])) {
			$pathinfo = pathinfo($file);
			$field['ext'] = $pathinfo['extension'];
		}

		// プレフィックス、サフィックスを取得
		$prefix = '';
		$suffix = '';
		if (!empty($field['prefix'])) {
			$prefix = $field['prefix'];
		}
		if (!empty($field['suffix'])) {
			$suffix = $field['suffix'];
		}

		// 保存ファイル名を生成
		$basename = preg_replace("/\." . $field['ext'] . "$/is", '', $file);
		$fileName = $prefix . $basename . $suffix . '.' . $field['ext'];
		$filePath = $this->savePath[$Model->alias] . $fileName;
		if (!empty($field['imagecopy']) && $delImagecopy) {
			foreach ($field['imagecopy'] as $copy) {
				$copy['name'] = $field['name'];
				$copy['ext'] = $field['ext'];
				$this->delFile($Model, $file, $copy, false);
			}
		}

		if (file_exists($filePath)) {
			return unlink($filePath);
		}

		return true;
	}

/**
 * ファイル名をフィールド値ベースのファイル名に変更する
 * 
 * @param Model $Model
 * @return boolean
 */
	public function renameToFieldBasename(Model $Model, $copy = false) {
		foreach ($this->settings[$Model->alias]['fields'] as $key => $setting) {

			if (empty($setting['name'])) {
				$setting['name'] = $key;
			}

			if (!empty($setting['namefield']) && !empty($Model->data[$Model->alias][$setting['name']])) {
				$oldName = $Model->data[$Model->alias][$setting['name']];
				$saveDir = $this->savePath[$Model->alias];
				$saveDirInTheme = $this->getSaveDir($Model, true);
				$oldSaveDir = '';
				if(file_exists($saveDir . $oldName)) {
					$oldSaveDir = $saveDir;
				} elseif(file_exists($saveDirInTheme . $oldName)) {
					$oldSaveDir = $saveDirInTheme;
				}
				if (file_exists($oldSaveDir . $oldName)) {

					$pathinfo = pathinfo($oldName);
					$newName = $this->getFieldBasename($Model, $setting, $pathinfo['extension']);

					if (!$newName) {
						return true;
					}
					if ($oldName != $newName) {

						if (!empty($setting['imageresize'])) {
							$newName = $this->getFileName($Model, $setting['imageresize'], $newName);
						} else {
							$newName = $this->getFileName($Model, null, $newName);
						}

						if (!$copy) {
							rename($oldSaveDir . $oldName, $saveDir . $newName);
						} else {
							copy($oldSaveDir . $oldName, $saveDir . $newName);
						}

						$Model->data[$Model->alias][$setting['name']] = str_replace(DS, '/', $newName);

						if (!empty($setting['imagecopy'])) {
							foreach ($setting['imagecopy'] as $copysetting) {
								$oldCopyname = $this->getFileName($Model, $copysetting, $oldName);
								if (file_exists($oldSaveDir . $oldCopyname)) {
									$newCopyname = $this->getFileName($Model, $copysetting, $newName);
									if (!$copy) {
										rename($oldSaveDir . $oldCopyname, $saveDir . $newCopyname);
									} else {
										copy($oldSaveDir . $oldCopyname, $saveDir . $newCopyname);
									}
								}
							}
						}
					}
				} else {
					$Model->data[$Model->alias][$setting['name']] = '';
				}
			}
		}
		return true;
	}

/**
 * フィールドベースのファイル名を取得する
 *
 * @param Model $Model
 * @param array $setting
 * - namefield 対象となるファイルのベースの名前が格納されたフィールド名
 * - nameformat ファイル名のフォーマット
 * - name ファイル名の後に追加する名前
 * - nameadd nameを追加しないか
 * @param string $ext ファイルの拡張子
 * @return mixed false / string
 */
	public function getFieldBasename(Model $Model, $setting, $ext) {
		if (empty($setting['namefield'])) {
			return false;
		}
		$data = $Model->data[$Model->alias];
		if (!isset($data[$setting['namefield']])) {
			if ($setting['namefield'] == 'id' && $Model->id) {
				$basename = $Model->id;
			} else {
				return false;
			}
		} else {
			$basename = $data[$setting['namefield']];
		}

		if (!empty($setting['nameformat'])) {
			$basename = sprintf($setting['nameformat'], $basename);
		}

		if (!isset($setting['nameadd']) || $setting['nameadd'] !== false) {
			$basename .= '_' . $setting['name'];
		}

		$subdir = '';
		if (!empty($this->settings[$Model->alias]['subdirDateFormat'])) {
			$subdir = date($this->settings[$Model->alias]['subdirDateFormat']);
			if (!preg_match('/\/$/', $subdir)) {
				$subdir .= '/';
			}
			$subdir = str_replace('/', DS, $subdir);
			$path = $this->savePath[$Model->alias] . $subdir;
			if (!is_dir($path)) {
				$Folder = new Folder();
				$Folder->create($path);
				$Folder->chmod($path, 0777);
			}
		}

		return $subdir . $basename . '.' . $ext;
	}

/**
 * ベースファイル名からプレフィックス付のファイル名を取得する
 * 
 * @param Model $Model
 * @param array $setting
 * @param string $filename
 * @return string
 */
	public function getFileName(Model $Model, $setting, $filename) {
		if (empty($setting)) {
			return $filename;
		}

		$pathinfo = pathinfo($filename);
		$ext = $pathinfo['extension'];
		// プレフィックス、サフィックスを取得
		$prefix = '';
		$suffix = '';
		if (!empty($setting['prefix'])) {
			$prefix = $setting['prefix'];
		}
		if (!empty($setting['suffix'])) {
			$suffix = $setting['suffix'];
		}

		$basename = preg_replace("/\." . $ext . "$/is", '', $filename);
		return $prefix . $basename . $suffix . '.' . $ext;
	}

/**
 * ファイル名からベースファイル名を取得する
 * 
 * @param Model $Model
 * @param array $setting
 * @param string $filename
 * @return string
 */
	public function getBasename(Model $Model, $setting, $filename) {
		$pattern = "/^" . $setting['prefix'] . "(.*?)" . $setting['suffix'] . "\.[a-zA-Z0-9]*$/is";
		if (preg_match($pattern, $filename, $maches)) {
			return $maches[1];
		} else {
			return '';
		}
	}

/**
 * 一意のファイル名を取得する
 * 
 * @param string $fieldName 一意の名前を取得する元となるフィールド名
 * @param string $fileName 対象のファイル名
 * @return string
 */
	public function getUniqueFileName(Model $Model, $fieldName, $fileName, $setting = null) {
		$pathinfo = pathinfo($fileName);
		$basename = preg_replace("/\." . $pathinfo['extension'] . "$/is", '', $fileName);

		$ext = $setting['ext'];

		// 先頭が同じ名前のリストを取得し、後方プレフィックス付きのフィールド名を取得する
		$conditions[$Model->name . '.' . $fieldName . ' LIKE'] = $basename . '%' . $ext;
		$datas = $Model->find('all', array('conditions' => $conditions, 'fields' => array($fieldName), 'order' => "{$Model->name}.{$fieldName}"));
		$datas = Hash::extract($datas, "{n}.{$Model->name}.{$fieldName}");
		$numbers = array();

		if ($datas) {
			foreach($datas as $data) {
				$_basename = preg_replace("/\." . $ext . "$/is", '', $data);
				$lastPrefix = preg_replace('/^' . preg_quote($basename, '/') . '/', '', $_basename);
				if(!$lastPrefix) {
					$numbers[1] = 1;
				} elseif (preg_match("/^__([0-9]+)$/s", $lastPrefix, $matches)) {
					$numbers[$matches[1]] = true;
				}
			}
			if($numbers) {
				$prefixNo = 1;
				while(true) { 
					if(!isset($numbers[$prefixNo])) {
						break;
					}
					$prefixNo++;
				}
				if($prefixNo == 1) {
					return $basename . '.' . $ext;
				} else {
					return $basename . '__' . ($prefixNo) . '.' . $ext;
				}
			} else {
				return $basename . '.' . $ext;
			}
		} else {
			return $basename . '.' . $ext;
		}
		
	}

/**
 * 保存先のフォルダを取得する
 * 
 * @param Model $Model
 * @param bool $isTheme
 * @return string $saveDir
 */
	public function getSaveDir(Model $Model, $isTheme = false) {
		if(!$isTheme) {
			$basePath = WWW_ROOT . 'files' . DS;
		} else {
			$siteConfig = Configure::read('BcSite');
			$theme = $siteConfig['theme'];
			if($theme) {
				$basePath = WWW_ROOT . 'theme' . DS . $theme . DS . 'files' . DS;
			} else {
				$basePath = getViewPath() . 'files' . DS;
			}
		}
		if ($this->settings[$Model->alias]['saveDir']) {
			$saveDir = $basePath . $this->settings[$Model->alias]['saveDir'] . DS;
		} else {
			$saveDir = $basePath;
		}
		return $saveDir;
	}

/**
 * 既に存在するデータのファイルを削除する
 * 
 * @param Model $Model
 */
	public function deleteExistingFiles(Model $Model) {
		$dataTmp = $Model->data[$Model->alias];
		$Model->set($Model->find('first', [
			'conditions' => [$Model->alias . '.id' => $Model->data[$Model->alias]['id']],
			'recursive' => -1
		]));
		$uploadFields = array_keys($this->settings[$Model->alias]['fields']);
		$targetFields = array_keys($dataTmp);
		foreach($targetFields as $field) {
			if(in_array($field, $uploadFields) && !empty($dataTmp[$field]['tmp_name'])) {
				$this->delFiles($Model, $field);
			}
		}
		$Model->set($dataTmp);
	}
	
}
