<?php
/**
 * Intern plugin, don't answer to URL
 */
namespace MarkNotes\Plugins\Task\Encrypt;

defined('_MARKNOTES') or die('No direct access allowed');

use starekrow\Lockbox\CryptoKey;
use starekrow\Lockbox\Secret;

class Unencrypt
{
	/**
	 * Generate the code for the upload form
	 */
	public static function run(&$params = null)
	{
		$aeSettings = \MarkNotes\Settings::getInstance();

		$arrSettings = $aeSettings->getPlugins('plugins.options.markdown.encrypt');

		// Only if we've a password for the encryption
		if (trim($arrSettings['password'])!=='') {
			// Get the password
			$password = $arrSettings['password'];
			$method = 'AES-256-ECB';

			// The info to encrypt is stored in $params['data']
			$info = $params['data'];

			// We can go further, load lockbox and crypt the info
			$lib = __DIR__.DS.'libs/lockbox'.DS;

			// Include Lockbox
			require_once $lib."CryptoCore.php";
			require_once $lib."CryptoCoreLoader.php";
			require_once $lib."CryptoCoreFailed.php";
			require_once $lib."CryptoCoreBuiltin.php";
			require_once $lib."CryptoCoreOpenssl.php";
			require_once $lib."Crypto.php";
			require_once $lib."CryptoKey.php";
			require_once $lib."Secret.php";

			$key = new CryptoKey($password, null, $method);
			$unencrypted = $key->unlock($info);

			// Something goes wrong
			// The return value is false when, f.i., the password
			// used for the encryption isn't the one used for the
			// decryption so, please check your password.

			if ($unencrypted === false) {
				$aeSession = \MarkNotes\Session::getInstance();
				$task = $aeSession->get('task','');

				$sMsg=$aeSettings->getText('encrypt_decrypt_error');

				/*<!-- build:debug -->*/
				if ($aeSettings->getDebugMode()) {
					$aeDebug = \MarkNotes\Debug::getInstance();
					if ($aeDebug->getDevMode()) {
						$sMsg.=" - ".DEV_MODE_PREFIX."Current password=".$arrSettings['password'];
					}
				}
				/*<!-- endbuild -->*/

				if (in_array($task, array('task.export.html', 'task.export.reveal'))) {
					$sMsg="<span class='encrypt_error'>".$sMsg."</span>";
				}

				$unencrypted = $sMsg;
			}

			$params['data'] = $unencrypted;
		}
		return true;
	}

	/**
	 * Attach the function and responds to events
	 */
	public function bind(string $task)
	{
		$aeEvents = \MarkNotes\Events::getInstance();
		$aeEvents->bind('unencrypt', __CLASS__.'::run', $task);
		return true;
	}
}
