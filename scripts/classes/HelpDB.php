<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of HelpDB
 *
 * @author lsong
 */
class HelpDB
{
    private $_db;

    public $_static = array();
    public $_index = array();
    public $_tips = array();
	private $_terms = array();

	public $_nav = array();
	
	private static $_instance;

	public static function GenerateDocs()
	{
		self::$_instance = new HelpDB();
		self::$_instance->_generateDocs();
	}
	
	public static function getInstance()
	{
		if (!isset(self::$_instance)) {
			die('config not initialized');
		}
		return self::$_instance;
		
	}
			
	private function __construct()
	{
		$this->_buildDB();
	}
    
    private function _buildDB()
    {
        $this->_db = array(
            HelpDocDef::TYPE_ITEM => array(),
            HelpDocDef::TYPE_TBL => array(),
            HelpDocDef::TYPE_PAGE => array(),
            HelpDocDef::TYPE_QA => array(),
            HelpDocDef::TYPE_NAV => array()
        );

        $config = Config::getInstance();
		
		foreach($config->getLanguages() as $lang) {
			$config->SetCurrentLang($lang);
            $helpDocs = $this->_loadFileList($config->getBaseDir(), $lang);
			foreach( $helpDocs as $doc ) {
				$this->parseHelpDoc($doc);
			}
		}
		$this->_populateData();
    }

    private function _generateDocs()
    {
		$config = Config::getInstance();
		
		foreach($config->getLanguages() as $lang) {
			$config->SetCurrentLang($lang);

			$navchain = $this->_db[HelpDocDef::TYPE_NAV][$config->getDocNav()]->getChildren();
			foreach($navchain as $nav) {
				if ($seq = $nav->getSeq()) {
					foreach($seq as $pid) {
						$page = $this->getNavPage($pid);
						$page->genDoc($this);
					}
				}
			}

			$tipdir = $config->getOutputDir('tips');
			if (Config::DocType() == Config::DOC_TYPE_OLS) {
				$tipfile = $tipdir . $lang . '_tips';
			}
			else {
				$tipfile = $tipdir . Config::DocType() . '_tips'; 
			}
			$this->genTips($tipfile);
        }

		if ($config->getForWeb()) {
            $webdocs = new GenWebDoc() ;
            $webdocs->Generate() ;
        }

    }
	
	public function genTips($outfile)
	{
		$fd = fopen($outfile, 'w');
		if ( !$fd )	{
			echo "fail to open file $outfile\n";
			return false;
		}
		fwrite($fd, "<?php \n");

        if (Config::DocType() == 'ows') {
            fwrite($fd, "\nglobal \$_tipsdb;\n\n");
        }

		$search = array("\n\n\n", "\n\n", "\r\n", "\n", '"', "'", '{ext-href}', '{ext-href-end}', '{ext-href-end-a}');
		$replace = array('<br/><br/>', '<br/>',  ' ', ' ', '&quot;', '&#039;', '<a href="', '" target="_blank" rel="noopener noreferrer">', '</a>');

        foreach( $this->_tips as $item )
		{
            if ( $item->hasValue('DESCR') ) {
                $buf = $item->toToolTip();
                fwrite($fd, $buf);
            }
		}

        foreach( $this->_tips as $item )
		{
            if ( $item->hasValue('EDITTIP') ) {
    			if ( $buf = $item->toEditTip() )
                    fwrite($fd, $buf);
            }
		}

		fclose($fd);
		echo ("finish generate tips $outfile \n");
	}
	

    public function getStaticContent( $filename )
    {
        $buf = '';
        if (isset($this->_static[$filename])) {
			$lang = Config::CurrentLang();
            if (isset($this->_static[$filename][$lang]))
                $file = $this->_static[$filename][$lang];
            else
                $file = $this->_static[$filename][LanguagePack::DEFAULT_LANG];

            $buf = file_get_contents($file) ;
        }
        else {
            echo "Cannot find static file $filename, ignore\n";
        }

        return $buf ;
    }

    private function _loadFileList($base_dir, $lang)
    {
		if ($lang == LanguagePack::DEFAULT_LANG) {
			$textpath = $base_dir . '/text';
		}
		else {
			$textpath = $base_dir . '/text_lang/' . $lang;
		}
		
		global $docterms;

		$texts = array() ;
		$termsfile = "$textpath/common/BasicTerm.php";

		if (file_exists($termsfile)) {
			if (!isset($docterms)) {
				$docterms = array();
			}
			$docterms[$lang] = array();
			$ref = &$docterms[$lang];
			include $termsfile;
		}
        $path = array('common', Config::DocType()) ;
        foreach ($path as $p) {
            $dir = $textpath . '/' . $p;
            if (is_dir($dir) && ($pathfiles = scandir($dir))) {
                foreach ( $pathfiles as $f ) {
                    if ( strpos($f, '.hdoc') !== false ) {
                        $texts[] = $dir . '/' . $f ;
                    }
                    elseif (strpos($f, '.txt') !== false) {
                        $this->_static[$f][$lang] = $dir . '/' . $f ;
                    }
                }
            }
        }

        return $texts ;
    }

    private function addToDB($item)
    {
        if (!$item->inCurrentNameSpace())
            return;

        $type = $item->getType();
        $id = $item->getId();
        if ($id == NULL) {
            $item->showErr('Missing ID field, ignore');
            return;
        }

        $lang = Config::CurrentLang();

        if (DEBUG) {
            $item->showErr('addToDB', 'DEBUG');

            if (Config::showDebugDump($id)) {
                $item->dumpDebug();
            }
        }

        if ($lang == LanguagePack::DEFAULT_LANG) {
            if (isset($this->_index[$id])) {
                $this->_index[$id]->showErr('duplicated ID first ' . $lang . ' defaultlang ' . LanguagePack::DEFAULT_LANG);
                $item->showErr('duplicated ID current, ignore');
            }
            else {
                $this->_index[$id] = $item;
                $this->_db[$type][$id] = $item;
            }
        }
        else {
            if (!isset($this->_index[$id])) {
                $item->showErr('In language file, but not in English, ignore');
            }
            else {
                $this->_index[$id]->applyLanguagePack($lang, $item);
            }

        }
    }

	private function parseHelpDoc($helpDoc)
	{
        $debug = false;
		$fd = fopen($helpDoc, 'r');
		if (!$fd) {
			echo "Failed to open helpdoc: $helpDoc\n";
			return;
		}
        if (DEBUG) {
            echo " ... parsing $helpDoc\n";
        }

        $root = HelpDocDef::TYPE_HELPDOC;
        $def = HelpDocDef::Get($root);
		$startInd = false;
        $curTag = '';
        $curBuf = NULL;
        $curLine = 0;

		while ( !feof($fd) )
		{
			$tmp = fgets($fd, 4096);
            $curLine ++;

            $tag = '[END_' . $root . ']';
			if ( strncmp( $tag, $tmp, strlen($tag) ) == 0 )
				break;

			if ( !$startInd )
			{
                $tag = '[' . $root . ']';
				if ( strncmp( $tag, $tmp, strlen($tag) ) == 0 ) {
                    $startInd = true;
                }
			}
			elseif ( $curTag )
			{
				$tag = '[END_' . $curTag . ']';
				if ( strncmp( $tag, $tmp, strlen($tag) ) == 0 )
				{
					$itemClass = $def[$curTag];
                    $item = new $itemClass($curBuf, $helpDoc, $curLine);
                    $this->addToDB($item);
                    $curTag = '';
                    $curBuf = NULL;
				}
                else {
                    $curBuf[] = $tmp;
                }
			}
			else
			{
                foreach ($def as $id => $itemClass) {
                    $tag = '[' . $id . ']';
                    if ( strncmp( $tag, $tmp, strlen($tag) ) == 0 )
                    {
                        $curTag = $id;
                        $curBuf = array();
                        break;
                    }
                }
			}
		}
		fclose($fd);

	}

    private function getNavPage($id)
    {
        if ($id == NULL)
            return NULL;
        if (!isset($this->_db[HelpDocDef::TYPE_PAGE][$id])) {
            die("fail to getNavPage seq $id\n");
        }
        return $this->_db[HelpDocDef::TYPE_PAGE][$id];
    }

	private function _populateData()
	{
        $config = Config::getInstance();
		foreach( $this->_db[HelpDocDef::TYPE_TBL] as $tbl ) {
            if ($cont = $tbl->getCont()) {
                foreach ($cont as $id) {
                    if (isset($this->_db[HelpDocDef::TYPE_ITEM][$id])) {
                        $tbl->addContItem($this->_db[HelpDocDef::TYPE_ITEM][$id]);
                    }
                    else {
                        $tbl->showErr('cannot find item definition for content tag ' . $id);
                    }
                }
            }
		}

		foreach( $this->_db[HelpDocDef::TYPE_PAGE] as $page) {
            if ($cont = $page->getCont()) {
                foreach ($cont as $tid) {
                    if (isset($this->_db[HelpDocDef::TYPE_TBL][$tid])) {
                        $page->addContTbl($this->_db[HelpDocDef::TYPE_TBL][$tid]);
                    }
                    else {
                        $page->showErr('cannot find table definition for content tag ' . $tid);
                    }
                }
            }
		}

		$navId = $config->getDocNav();
		$navchain = $this->getNavChain($navId);
		$navroot = $this->_db[HelpDocDef::TYPE_NAV][$navId];
		$navroot->setChildren($navchain);

		foreach( $navchain as $nav )
		{
			if ($seq = $nav->getSeq()) {
				$topPage = $this->getNavPage($nav->getTopNav());

				$c = count($seq);

				for ( $i = 0 ; $i < $c ; ++$i )
				{
					$curId = $seq[$i];
					$prevId = ($i > 0) ? $seq[$i-1] : '';
					$nextId = ($i < ($c-1)) ? $seq[$i+1] : '';

					$curPage = $this->getNavPage($curId);
					$prevPage = $this->getNavPage($prevId);
					$nextPage = $this->getNavPage($nextId);
					$curPage->setNav( $prevPage, $topPage, $nextPage);
				}
			}
		}

		$tipnav = $config->getTipNav();
		// tips
		$navchain = $this->getNavChain($tipnav);
		$this->_db[HelpDocDef::TYPE_NAV][$tipnav]->setChildren($navchain);

		foreach( $navchain as $nav )
		{
			if ($seq = $nav->getSeq()) {
				foreach ($seq as $pid) {
					$page = $this->getNavPage($pid);
					$items = $page->getItems();
					foreach ($items as $id => $item) {
						if (!isset($this->_tips[$id]))
							$this->_tips[$id] = $item;
					}
				}
			}
		}
		ksort($this->_tips);

	}


    private function getNavChain($navId)
    {
        $chain = array();
        if (!isset($this->_db[HelpDocDef::TYPE_NAV][$navId])) {
            echo "Wrong Nav ID $root\n";
        }
        else {
            $nav = $this->_db[HelpDocDef::TYPE_NAV][$navId];
            $chain[] = $nav;
            if ($cont = $nav->getCont()) {
                foreach ($cont as $childId) {
                    $children = $this->getNavChain($childId);
                    $chain = array_merge($chain, $children);
                }
            }
        }

        return $chain;
    }

	public function Translate($tag)
	{
        $types = array(
            HelpDocDef::TYPE_ITEM => 'tagl',
            HelpDocDef::TYPE_TBL => 'tagl',
            HelpDocDef::TYPE_PAGE => 'tagP',
            HelpDocDef::TYPE_QA => 'tagQ');

        foreach ($types as $type => $style) {
            if ( preg_match("/^\{\s*$type\s*=\s*(\S+)\s*\}/", $tag, $matches) ) {
                $id = $matches[1];
                $pid = "";
                if ( preg_match("/(.+)#(.+)/", $id, $m2) ) {
                    $id = $m2[2];
                    $pid = $m2[1] . ".html";
                }
                if (isset($this->_db[$type][$id])) {
                    $name = $this->_db[$type][$id]->getName();
                    $buf = '<span class="' . $style . '"><a href="'
                            . $pid . '#' . $id . '">'
                            . $name . '</a></span>';
                }
                else {
                    echo "Reference non existing $type : $id -- $tag \n";
                    $buf = '<span class="error">'.$tag.'</span>';
                }
                return $buf;
            }
        }
		echo "unable to match tag $tag\n";
	}

}
