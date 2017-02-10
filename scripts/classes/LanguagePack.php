<?php


/**
 * Description of LanguagePack
 *
 * @author lsong
 */
class LanguagePack
{
	// define supported langauage here
	const DEFAULT_LANG = 'en-US';
	const LANG_JAPANESE = 'ja-JP';
	const LANG_CHINESE = 'zh-CN';

	public static function IsValidLang($lang)
	{
		$supported = array(self::DEFAULT_LANG, self::LANG_CHINESE, self::LANG_JAPANESE);
		return in_array($lang, $supported);
	}
}
