<?php
/**
 * Add a odt export button into the toolbar
 */
namespace MarkNotes\Plugins\Buttons;

defined('_MARKNOTES') or die('No direct access allowed');

class ODT extends \MarkNotes\Plugins\Button\Plugin
{
	protected static $me = __CLASS__;
	protected static $json_settings = 'plugins.buttons.odt';
	protected static $json_linked = 'plugins.page.html.odt';

	private static $layout = 'odt';

	public static function add(&$buttons = array()) : bool
	{
		// Get the button HTML code
		$buttons['export'][] = self::button(
			array(
				'name' => 'odt',
				'title' => 'export_odt',
				'default' => 'Export the note as a ODT document',
				'id' => 'icon_odt',
				'icon' => 'file-text-o',
				'task' => 'fnPluginHTMLODT'
			)
		);

		return true;
	}

	protected static function canAdd() : bool
	{
		// Conversion requires that
		//		1. the .odt file already exists OR
		//		2. the pandoc utility is present to allow the conversion

		if ($bReturn = parent::canAdd()) {
			// We can continue
			$bReturn = false;

			// Check if the .odt file already exists
			$aeSession = \MarkNotes\Session::getInstance();
			$aeSettings = \MarkNotes\Settings::getInstance();

			if ($aeSession->get('filename', '')!=='') {
				$aeFiles = \MarkNotes\Files::getInstance();

				$filename = $aeSettings->getFolderDocs(true).$aeSession->get('filename');
				$filename  = $aeFiles->replaceExtension($filename, static::$layout);
				$filename = str_replace('/', DS, $filename);

				$bReturn = $aeFiles->exists($filename);
			}

			if (!$bReturn) {
				$aeConvert = \MarkNotes\Tasks\Convert::getInstance($aeSession->get('filename'), static::$layout, 'pandoc');

				if ($aeConvert->isValid()) {
					// Yes, correctly configured, we'll be able
					// to offer the conversion
					$bReturn = true;
				}
			} // if (!$bReturn)
		} // if ($bReturn = parent::canAdd())

		return $bReturn;
	}
}
