<?php

class DocPage extends Item
{
    protected $_nav;

    public $_descr = '';
	public $_cont;
	public $_tables;
	public $_items;
	public $_seeAlso;
	public $_static;

    public function __construct($buf='')
    {
        $this->_type = Item::TYPE_PAGE;
		if ( $buf != '' ) {
			$this->parseDoc($buf);
        }
        $this->_tables = array();
        $this->_items = array();
    }

    public function applyLanguagePack($peer, $lang)
    {
        if (parent::applyLanguagePack($peer, $lang)) {
            $this->_lang[$lang]['descr'] = $peer->_descr;
            $this->_lang[$lang]['name'] = $peer->_name;
            return true;
        }
        return false;
    }

    public function getCont()
    {
        return $this->_cont;
    }

    public function getItems()
    {
        return $this->_items;
    }

    public function addContTbl($tbl)
    {
        $this->_tables[$tbl->getId()] = $tbl;
        $items = $tbl->getItems();
        foreach ($items as $item) {
            $id = $item->getId();
            if (!isset($this->_items[$id])) {
                $this->_items[$id] = $item;
            }
        }
    }

    public function getDescr()
    {
        global $config;
        $lang = $config['CUR_LANG'];

        if (($lang != DEFAULT_LANG) && isset($this->_lang[$lang]['descr']))
            return $this->_lang[$lang]['descr'];
        else
            return $this->_descr;
    }

	protected function parseDoc($buf)
	{
		$startInd = false;
		$descrInd = false;
		$contInd = false;
		foreach( $buf as $line )
		{
			$tag = '[END_PAGE]';
			if ( strncmp($tag, $line, strlen($tag)) == 0 )
				break;

			if ( !$startInd )
			{
				$tag = '[PAGE]';
				if ( strncmp($tag, $line, strlen($tag)) == 0 )
					$startInd = true;
			}
			else if ( $descrInd )
			{
				$tag = 'END_DESCR';
				if ( strncmp($tag, $line, strlen($tag)) == 0 ) {
					$descrInd = false;
					$this->_descr = trim($this->_descr);
				}
				else
					$this->_descr .= $line;
			}
			else if ( $contInd )
			{
				$tag = 'END_CONT';
				if ( strncmp($tag, $line, strlen($tag)) == 0 )
					$contInd = false;
				else
					$this->_cont .= ' '. $line ;
			}
			else
			{
				$tag = 'ID:';
				if ( strncmp($tag, $line, strlen($tag)) == 0 )
				{
					$this->_id = trim(substr($line, strlen($tag)));
					continue;
				}
				$tag = 'NAME:';
				if ( strncmp($tag, $line, strlen($tag)) == 0 )
				{
					$this->_name = trim(substr($line, strlen($tag)));
					continue;
				}
                $tag = 'NS:';
				if ( strncmp($tag, $line, strlen($tag)) == 0 )
				{
					$this->_ns = trim(substr($line, strlen($tag)));
					continue;
				}
				$tag = 'SEE_ALSO:';
				if ( strncmp($tag, $line, strlen($tag)) == 0 )
				{
					$this->_seeAlso = trim(substr($line, strlen($tag)));
					continue;
				}
				$tag = 'DESCR:';
				if ( strncmp($tag, $line, strlen($tag)) == 0 )
				{
					if ($this->_descr != NULL) {
						$this->showErr("Page error: duplicate description found");
					}
					$this->_descr = substr($line, strlen($tag));
					$descrInd = true;
					continue;
				}
				$tag = 'CONT:';
				if ( strncmp($tag, $line, strlen($tag)) == 0 )
				{
					$this->_cont = substr($line, strlen($tag));
					$contInd = true;
					continue;
				}
				$tag = 'STATIC:';
				if ( strncmp($tag, $line, strlen($tag)) == 0 )
				{
					$this->_static = trim(substr($line, strlen($tag)));
					continue;
				}
			}
		}
		if ($this->_cont != NULL) {
			$this->_cont = preg_split( "/[\s,]+/", $this->_cont, -1, PREG_SPLIT_NO_EMPTY );
		}
        else {
            $this->_cont = array();
        }
	}

	public function setNav($prevPage, $topPage, $nextPage)
	{
        if ($this->_nav != NULL) {
            $this->showErr('Already setNav for page');
        }
        else {
            $this->_nav = array('prev' => $prevPage, 'top' => $topPage, 'next' => $nextPage);
        }
	}

	public function genDoc($db)
	{
		echo ("generating $this->_id  \n");

        global $config;

        $gentool = new GenTool;

		$buf = $gentool->getHeader($this->_name);//header name still show english
		$buf .= $gentool->getSideTree("{$this->_id}.html");
		$buf .= '<div class="contentwrapper">' . GenTool::getNavBar($this->_nav);

		$webbuf = '';

		if ($this->_static) {
			$webbuf .= $db->getStaticContent($this->_static);
		}
		else {
			$webbuf .= '<h1>' . $this->getName() . '</h1>';
			if ( $this->_descr ) {
				$webbuf .= '<p>' . $this->getDescr() . "</p>\n";
			}

			$helpList = array();
			if ( $this->_tables )
			{
				$webbuf .= '<h4>Table of Contents</h4>';
				$webbuf .= '<section class="toc">';

				foreach ( $this->_tables as $table )
				{
					if ( isset($table) )
						$webbuf .= $table->toTableOfContents();
				}
				$webbuf .= "</section>\n" ;

			}
			$webbuf .= '<section>';
			if ( $this->_items )
			{
				foreach ( $this->_items as $item )
				{
					if ( $item != NULL )
					{
						$itembuf = $item->toDoc();
						$itembuf = GenTool::translateTag($itembuf, $db);
						$webbuf .= '<div class="helpitem">' . $itembuf . "</div>\n";
					}
				}
			}
			$webbuf .= "</section>\n";
			//$webbuf = $gentool->translateTag($webbuf, $base);
		}


		$buf .= $webbuf;

		$buf .= '</div>'; // contentwrapper
		$buf .= $gentool->getFooter();

        $docname = $config['DOCS_DIR'] . $this->_id . '.html';
		GenTool::writePage($docname, $buf);

        if ($config['FOR_WEB']) {
			$docname = $config['WEB_DIR'] . $this->_id . '.html';
			$webbuf = '<div class="lsdoc_content">' . $webbuf . '</div>';
			GenTool::writePage($docname, $webbuf);
		}
	}

	/*public function getGuiTips(&$tips_base)
	{
		if ( $this->_items )
		{
			foreach ( $this->_items as $item )
			{
				if ( $item != NULL )
				{
					$tips_base[$item->_id] = $item;
				}
			}
		}
		if ($this->_tables)
		{
			foreach($this->_tables as $tbl)
			{
				if ($tbl->_descr != "") {
					$item = new DocItem(NULL);
					$tblid = 'TABLE' . $tbl->_id ;
					$item->loadFromTbl($tblid, $tbl->_name, $tbl->_descr,
							   $tbl->_tips, $tbl->_example, $tbl->_seeAlso);
					$tips_base[$tblid] = $item;
				}

			}
		}

	}
*/
}

