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
    public $_db = array();

    public $_static = array();
    public $_index = array();
    public $_tips = array();

	public $_nav = array();


    //put your code here
    public function buildDB()
    {
        $this->_db = array(
            HelpDocDef::TYPE_ITEM => array(),
            HelpDocDef::TYPE_TBL => array(),
            HelpDocDef::TYPE_PAGE => array(),
            HelpDocDef::TYPE_QA => array(),
            HelpDocDef::TYPE_NAV => array()
        );

        global $config ;

        $config['CUR_LANG'] = DEFAULT_LANG;

        // generate english
        $helpDocs = $this->getFileList($config['base_dir'] . '/text' );

		foreach( $helpDocs as $doc ) {
			$this->parseHelpDoc($doc);
		}

        foreach ($config['lang'] as $lang) {
            if ($lang != DEFAULT_LANG) {
                $config['CUR_LANG'] = $lang;

                $helpDocs = $this->getFileList($config['base_dir'] . '/text_lang/' . $lang);
                foreach( $helpDocs as $doc ) {
                    $this->parseHelpDoc($doc);
                }
            }
        }

		$this->populateData();
    }

    public function generateDocs()
    {
        global $config;

        foreach ($config['lang'] as $lang) {
            $config['CUR_LANG'] = $lang;

            if ($lang == DEFAULT_LANG) {
                $config['DOCS_DIR'] = $config['outdir']['docs'];
                $config['WEB_DIR'] = $config['outdir']['web'];
            }
            else {
                $config['DOCS_DIR'] = $config['outdir']['docs_lang'] . $lang . '/';
                $config['WEB_DIR'] = $config['outdir']['web_lang'] . $lang . '/';
            }

            if (!file_exists($config['DOCS_DIR'])) {
                mkdir($config['DOCS_DIR']);
            }
            if (!file_exists($config['WEB_DIR'])) {
                mkdir($config['WEB_DIR']);
            }

            if ( isset($config['doc_nav']) ) {
                $navchain = $this->_db[HelpDocDef::TYPE_NAV][$config['doc_nav']]->getChildren();
                foreach($navchain as $nav) {
                    if ($seq = $nav->getSeq()) {
                        foreach($seq as $pid) {
                            $page = $this->getNavPage($pid);
                            $page->genDoc($this);
                        }
                    }
                }
            }

            if ( isset($config['tip_nav']) ) {
                if ($lang == DEFAULT_LANG && count($config['lang']) == 1) {
                    $tipfile = $config['outdir']['tips'] . DOC_TYPE . '_tips' ;
                }
                else {
                    $tipfile = $config['outdir']['tips_lang'] . $lang . '_tips' ;
                }
                GenTool::genTips($this, $tipfile) ;
            }

        }


        if ( $config['FOR_WEB'] ) {
            $webdocs = new GenWebDoc() ;
            $webdocs->Generate() ;
        }

    }


    public function getStaticContent( $filename )
    {
        global $config;
        $buf = '';
        if (isset($this->_static[$filename])) {
            if (isset($this->_static[$filename][$config['CUR_LANG']]))
                $file = $this->_static[$filename][$config['CUR_LANG']];
            else
                $file = $this->_static[$filename][DEFAULT_LANG];

            $buf = file_get_contents($file) ;
        }
        else {
            echo "Cannot find static file $filename, ignore\n";
        }

        return $buf ;
    }

    private function getFileList( $textpath)
    {
        global $config;
        $texts = array() ;
        $path = array('common', DOC_TYPE) ;
        foreach ($path as $p) {
            $dir = $textpath . '/' . $p;
            if (is_dir($dir) && ($pathfiles = scandir($dir))) {
                foreach ( $pathfiles as $f ) {
                    if ( strpos($f, '.hdoc') !== false ) {
                        $texts[] = $dir . '/' . $f ;
                    }
                    elseif (strpos($f, '.txt') !== false) {
                        $this->_static[$f][$config['CUR_LANG']] = $dir . '/' . $f ;
                    }
                }
            }
        }

        return $texts ;
    }

    private function addToDB($item)
    {
        global $config;

        if (!$item->inCurrentNameSpace())
            return;

        $type = $item->getType();
        $id = $item->getId();
        if ($id == NULL) {
            $item->showErr('Missing ID field, ignore');
            return;
        }

        $lang = $config['CUR_LANG'];

        if (DEBUG) {
            $item->showErr('addToDB', 'DEBUG');

            if (in_array($id, $config['DEBUG_TAG'])) {
                $item->dumpDebug();
            }
        }

        if ($lang == DEFAULT_LANG) {
            if (isset($this->_index[$id])) {
                $this->_index[$id]->showErr('duplicated ID first ' . $lang . ' defaultlang ' . DEFAULT_LANG);
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

	private function populateData()
	{
        global $config;
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

        if (isset($config['doc_nav'])) {
            //doc pages
            $navId = $config['doc_nav'];
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

        }

        if (isset($config['tip_nav'])) {
            // tips
            $navchain = $this->getNavChain($config['tip_nav']);
            $this->_db[HelpDocDef::TYPE_NAV][$config['tip_nav']]->setChildren($navchain);

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

	public function translate($tag)
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
