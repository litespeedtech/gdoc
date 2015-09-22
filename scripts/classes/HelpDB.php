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
            Item::TYPE_ITEM => array(),
            Item::TYPE_TBL => array(),
            Item::TYPE_PAGE => array(),
            Item::TYPE_QA => array(),
            Item::TYPE_NAV => array()
        );

        global $config ;

        define('CUR_LANG', DEFAULT_LANG);

        // generate english
        $helpDocs = $this->getFileList($config['base_dir'] . '/text' );

		foreach( $helpDocs as $doc ) {
			$this->parseHelpDoc($doc);
		}

        foreach ($config['lang'] as $lang) {
            if ($lang != DEFAULT_LANG) {
                define('CUR_LANG', $lang);

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
            define('CUR_LANG', $lang);

            if (CUR_LANG == DEFAULT_LANG) {
                $config['DOCS_DIR'] = $config['outdir']['docs'];
                $config['WEB_DIR'] = $config['outdir']['web'];
            }
            else {
                $config['DOCS_DIR'] = $config['outdir']['docs_lang'] . CUR_LANG . '/';
                $config['WEB_DIR'] = $config['outdir']['web_lang'] . CUR_LANG . '/';
            }

            if ( isset($config['doc_nav']) ) {
                $navchain = $this->_db[Item::TYPE_NAV][$config['doc_nav']]->getChildren();
                foreach($navchain as $nav) {
                    foreach($nav->getSeq() as $pid) {
                        $page = $this->getNavPage($pid);
                        $page->genDoc($this);
                    }
                }
            }

            if ( isset($config['tip_nav']) ) {
                if (CUR_LANG == DEFAULT_LANG) {
                    $tipfile = $config['outdir']['tips'] . DOC_TYPE . '_tips' ;
                }
                else {
                    $tipfile = $config['outdir']['tips_lang'] . CUR_LANG . '_tips' ;
                }
                GenTool::genTips($this, $tipfile) ;
            }

        }


        if ( $config['FOR_WEB'] ) {
            define('CUR_LANG', DEFAULT_LANG);
            $webdocs = new GenWebDoc() ;
            $webdocs->GenerateWebDocs(new MapLSWS()) ;
        }

    }


    public function getStaticContent( $filename )
    {
        $buf = '';
        if (isset($this->_static[$filename])) {
            if (isset($this->_static[$filename][CUR_LANG]))
                $file = $this->_static[$filename][CUR_LANG];
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
        $texts = array() ;
        $path = array('common', DOC_TYPE) ;
        foreach ($path as $p) {
            $dir = $textpath . '/' . $p;
            if ($pathfiles = scandir($dir)) {
                foreach ( $pathfiles as $f ) {
                    if ( strpos($f, '.hdoc') !== false ) {
                        $texts[] = $dir . '/' . $f ;
                    }
                    elseif (strpos($f, '.txt') !== false) {
                        $this->_static[$f][CUR_LANG] = $dir . '/' . $f ;
                    }
                }
            }
        }

        return $texts ;
    }

    private function addToDB($item, $curfile)
    {
        if (!$item->inCurrentNameSpace())
            return;

        $item->setSrc($curfile);
        $type = $item->getType();
        $id = $item->getId();
        if ($id == NULL) {
            $item->showErr('Missing ID field, ignore');
            return;
        }

        global $config;
        if (in_array($id, $config['DEBUG_TAG'])) {
            $item->dumpDebug();
        }

        if (CUR_LANG == DEFAULT_LANG) {
            if (isset($this->_index[$id])) {
                $this->_index[$id]->showErr('duplicated ID first');
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
                $this->_index[$id]->applyLanguagePack($item);
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
        /*echo "In GenHelpDoc parseHelpDoc $helpDoc\n";
        if ($helpDoc == 'text/lb/LBPages.txt')
            $debug = true;*/

		$startInd = false;
		$itemInd = false;
		$tableInd = false;
		$pageInd = false;
		$navInd = false;
		$buf = array();

		while ( !feof($fd) )
		{
			$tmp = fgets($fd, 4096);
			$tag = '[END_HELPDOC]';
			if ( strncmp( $tag, $tmp, strlen($tag) ) == 0 )
				break;

			if ( !$startInd )
			{
				$tag = '[HELPDOC]';
				if ( strncmp( $tag, $tmp, strlen($tag) ) == 0 )
					$startInd = true;
			}
			else if ( $itemInd )
			{
				$buf[] = $tmp;
				$tag = '[END_ITEM]';
				if ( strncmp( $tag, $tmp, strlen($tag) ) == 0 )
				{
					$itemInd = false;
					$item = new DocItem($buf);
                    $this->addToDB($item, $helpDoc);
				}
			}
			else if ( $tableInd )
			{
				$buf[] = $tmp;
				$tag = '[END_TBL]';
				if ( strncmp( $tag, $tmp, strlen($tag) ) == 0 )
				{
					$tableInd = false;
					$table = new DocTable($buf);
                    $this->addToDB($table, $helpDoc);
				}
			}
			else if ( $pageInd )
			{
				$buf[] = $tmp;
				$tag = '[END_PAGE]';
				if ( strncmp( $tag, $tmp, strlen($tag) ) == 0 )
				{
					$pageInd = false;
					$page = new DocPage($buf);
                    $this->addToDB($page, $helpDoc);
				}
			}
			else if ( $navInd )
			{
				$buf[] = $tmp;
				$tag = '[END_PAGENAV]';
				if ( strncmp( $tag, $tmp, strlen($tag) ) == 0 )
				{
					$navInd = false;
					$navchain = new NavChain($buf);
                    $this->addToDB($navchain, $helpDoc);
				}
			}
			else
			{
				$tag = '[ITEM]';
				if ( strncmp( $tag, $tmp, strlen($tag) ) == 0 )
				{
					$itemInd = true;
					$buf = array();
					$buf[] = $tmp;
					continue;
				}
				$tag = '[TBL]';
				if ( strncmp( $tag, $tmp, strlen($tag) ) == 0 )
				{
					$tableInd = true;
					$buf = array();
					$buf[] = $tmp;
					continue;
				}
				$tag = '[PAGE]';
				if ( strncmp( $tag, $tmp, strlen($tag) ) == 0 )
				{
					$pageInd = true;
					$buf = array();
					$buf[] = $tmp;
				}
				$tag = '[PAGENAV]';
				if ( strncmp( $tag, $tmp, strlen($tag) ) == 0 )
				{
					$navInd = true;
					$buf = array();
					$buf[] = $tmp;
				}
			}
		}
		fclose($fd);

	}

    private function getNavPage($id)
    {
        if ($id == NULL)
            return NULL;
        if (!isset($this->_db[Item::TYPE_PAGE][$id])) {
            die("fail to getNavPage seq $id\n");
        }
        return $this->_db[Item::TYPE_PAGE][$id];
    }

	private function populateData()
	{
        global $config;
		foreach( $this->_db[Item::TYPE_TBL] as $tbl ) {
            foreach ($tbl->getCont() as $id) {
                if (isset($this->_db[Item::TYPE_ITEM][$id])) {
                    $tbl->addContItem($this->_db[Item::TYPE_ITEM][$id]);
                }
                else {
                    $tbl->showErr('cannot find item definition for content tag ' . $id);
                }
            }
		}

		foreach( $this->_db[Item::TYPE_PAGE] as $page) {
            foreach ($page->getCont() as $tid) {
                if (isset($this->_db[Item::TYPE_TBL][$tid])) {
                    $page->addContTbl($this->_db[Item::TYPE_TBL][$tid]);
                }
                else {
                    $page->showErr('cannot find table definition for content tag ' . $tid);
                }
            }
		}

        if (isset($config['doc_nav'])) {
            //doc pages
            $navchain = $this->getNavChain($config['doc_nav']);
            $this->_db[Item::TYPE_NAV][$config['doc_nav']]->setChildren($navchain);

            foreach( $navchain as $nav )
            {
                $seq = $nav->getSeq();
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

        if (isset($config['tip_nav'])) {
            // tips
            $navchain = $this->getNavChain($config['tip_nav']);
            $this->_db[Item::TYPE_NAV][$config['tip_nav']]->setChildren($navchain);

            foreach( $navchain as $nav )
            {
                $seq = $nav->getSeq();
                foreach ($seq as $pid) {
                    $page = $this->getNavPage($pid);
                    $items = $page->getItems();
                    foreach ($items as $id => $item) {
                        if (!isset($this->_tips[$id]))
                            $this->_tips[$id] = $item;
                    }
                }
            }
            ksort($this->_tips);
        }

	}


    private function getNavChain($navId)
    {
        $chain = array();
        if (!isset($this->_db[Item::TYPE_NAV][$navId])) {
            echo "Wrong Nav ID $root\n";
        }
        else {
            $nav = $this->_db[Item::TYPE_NAV][$navId];
            $chain[] = $nav;
            foreach ($nav->getCont() as $childId) {
                $children = $this->getNavChain($childId);
                $chain = array_merge($chain, $children);
            }
        }

        return $chain;
    }

	public function translate($tag)
	{
        $types = array(
            Item::TYPE_ITEM => 'tagl',
            Item::TYPE_TBL => 'tagl',
            Item::TYPE_PAGE => 'tagP',
            Item::TYPE_QA => 'tagQ');

        foreach ($types as $type => $style) {
            if ( preg_match("/^\{\s*$type\s*=\s*(\S+)\s*\}/", $tag, $matches) ) {
                $id = $tag;
                $pid = "";
                if ( preg_match("/(.+)#(.+)/", $tag, $m2) ) {
                    $id = $m2[2];
                    $pid = $m2[1] . ".html";
                }
                if (isset($this->_db[$type][$id])) {
                    $name = $this->_db[$type][$id]->getName();
                    $buf = '<span class="' . $style . '"><a href="'
                            . $pageId . '#' . $item->_id . '">'
                            . $name . '</a></span>';
                }
                else {
                    echo "Reference none existing $type -- $id\n";
                    $buf = '<span class="error">'.$tag.'</span>';
                }
                return $buf;
            }
        }
		echo "unable to match tag $tag\n";
	}

/*
 * 	function get_fixed_tips()
	{
		$buf =<<<EOD
		\$_tipsdb['note'] = new DAttrHelp('Notes', 'Add a note for yourself.', '', '', '');
		\$_tipsdb['tpextAppName'] = new DAttrHelp('Name', 'A unique name for this external application. In other parts of the the configuration, you will refer to external applications by this name. For virtual host templates, the external application name must contain the \$VH_NAME variable to preserve the uniqueness of external application names on different virtual hosts.', '', '', '');

EOD;
		return $buf;
	}
 * 	function get_fixed_tips()
	{
		$buf =<<<EOD
		\$this->db['note'] = new DATTR_HELP_ITEM('Notes', 'Add a note for yourself.', '', '', '');
		\$this->db['c'] = new DATTR_HELP_ITEM('Name', 'A unique name for this external application. In other parts of the the configuration, you will refer to external applications by this name. For virtual host templates, the external application name must contain the \$VH_NAME variable to preserve the uniqueness of external application names on different virtual hosts.', '', '', '');

EOD;
		return $buf;
	}
 */
}
