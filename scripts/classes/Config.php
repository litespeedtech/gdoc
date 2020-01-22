<?php


class Config
{
	// define supported doc type
	const DOC_TYPE_WS	 = 'ws'; // LiteSpeed Enterprise
	const DOC_TYPE_LB	 = 'lb'; // LiteSpeed Web ADC
	const DOC_TYPE_OLS = 'ows';	// OpenLiteSpeed

	private $_docType;
	private $_baseDir;
	private $_outDir;
	private $_lang;
	private $_curLang;
	private $_docNav;
	private $_tipNav;
	private $_forweb;
	private $_ws_lb_replace;
	private $_debugTags	 = array();
	private static $_instance;

	private function __construct($type)
	{
		$this->_init($type);
	}

	public static function getInstance()
	{
		if (!isset(self::$_instance)) {
			die('config not initialized');
		}
		return self::$_instance;
	}

	public static function CurrentLang()
	{
		return self::getInstance()->_curLang;
	}

	public static function DocType()
	{
		if (!isset(self::getInstance()->_docType)) {
			debug_print_backtrace();
			die("config doctype not initialized\n");
		}
		return self::getInstance()->_docType;
	}

	public static function GetImagePath()
	{
		return (self::getInstance()->_curLang == LanguagePack::DEFAULT_LANG) ? '' : '../';
	}

	public static function GetFooterYear()
	{
		switch (self::DocType()) {
			case self::DOC_TYPE_OLS:
				return '2013-2020';
			case self::DOC_TYPE_WS:
				return '2003-2020';
			case self::DOC_TYPE_LB:
				return '2007-2020';
		}
	}

	public function getLanguages()
	{
		return $this->_lang;
	}

	public function getBaseDir()
	{
		return $this->_baseDir;
	}

	public function getDocNav()
	{
		return $this->_docNav;
	}

	public function getTipNav()
	{
		return $this->_tipNav;
	}

	public function getForWeb()
	{
		return $this->_forweb;
	}

	public function getWsLbReplace()
	{
		return $this->_ws_lb_replace;
	}

	public function SetCurrentLang($lang)
	{
		$this->_curLang = $lang;
	}

	public function getOutputDir($type)
	{
		if (!in_array($type, array('docs', 'web', 'tips'))) {
			die('wrong type in getOutputDir');
		}

		if ($type == 'tips') {
			$path = ($this->_docType == self::DOC_TYPE_OLS) ?
					$this->_outDir['tips_lang']
					: $this->_outDir[$type];
		}
		elseif ($this->_curLang == LanguagePack::DEFAULT_LANG) {
			$path = $this->_outDir[$type];
		}
		else {
			$path = $this->_outDir["{$type}_lang"] . $this->_curLang . '/';
		}

		if (!file_exists($path)) {
			mkdir($path);
		}
		return $path;
	}

	public static function Init($type, $base_dir)
	{
		self::$_instance = new Config($type);
		self::$_instance->_initBaseDir($base_dir);
	}

	private function _init($type)
	{
		$this->_lang			 = array(LanguagePack::DEFAULT_LANG);
		$this->_docNav			 = 'HTMLDOC';
		$this->_tipNav			 = 'POPTIPS';
		$this->_ws_lb_replace	 = array('from'	 => array('{ent_version}'),
			'to'	 => array(''));

		switch ($type) {
			case self::DOC_TYPE_WS:
				$this->_forweb				 = true;
				$this->_ws_lb_replace['to']	 = array('{tag}Enterprise Edition Only{/}');
				break;

			case self::DOC_TYPE_LB:
				$this->_forweb = true;
				break;

			case self::DOC_TYPE_OLS:
				$this->_forweb	 = false;
				$this->_lang[]	 = LanguagePack::LANG_CHINESE;
				$this->_lang[]	 = LanguagePack::LANG_JAPANESE;
				break;

			default:
				die('illegal doc type');
		}

		$this->_docType = $type;
	}

	private function _initBaseDir($base_dir)
	{
		$this->_baseDir	 = $base_dir;
		$outbase		 = dirname($base_dir) . '/output/';
		$this->_outDir	 = array(
			'base'		 => $outbase,
			'docs'		 => $outbase . 'docs/',
			'tips'		 => $outbase . 'tips/',
			'web'		 => $outbase . 'forweb/',
			'docs_lang'	 => $outbase . 'docs/',
			'tips_lang'	 => $outbase . 'tips/lang/',
			'web_lang'	 => $outbase . 'forweb/lang/',
		);
	}

	public static function setDebugTags($tags)
	{
		self::getInstance()->_debugTags = $tags;
	}

	public static function showDebugDump($id)
	{
		return in_array($id, self::getInstance()->_debugTags);
	}

}
