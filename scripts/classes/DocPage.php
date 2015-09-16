<?php

class DocPage
{
	public $_id;
	public $_name;
    public $_ns;
	public $_prevNav;
	public $_nextNav;
	public $_topNav;
	public $_descr = '';
	public $_cont;
	public $_tables;
	public $_items;
	public $_seeAlso;
	public $_static;

	public function DocPage(&$buf)
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
						echo "Page error: duplicate description found $this->_id\n";
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
	}

	public function setNav($prevPage, $topPage, $nextPage)
	{
		if ( $prevPage == NULL )
			$this->_prevNav = NULL;
		else
			$this->_prevNav = array($prevPage);

		if ( $topPage == NULL )
			$this->_topNav = NULL;
		else
		{
			$this->_topNav = preg_split("/=/",
										 trim($topPage),
										 -1, PREG_SPLIT_NO_EMPTY);
		}

		if ( $nextPage == NULL )
			$this->_nextNav = NULL;
		else
			$this->_nextNav = array($nextPage);
	}

	public function transNav(&$base)
	{
		if ( $this->_prevNav != NULL && count($this->_prevNav) == 1 )
		{
			$this->_prevNav = $this->idToLink($this->_prevNav[0], $base);
		}
		if ( $this->_topNav != NULL && count($this->_topNav) == 1 )
		{
			$this->_topNav = $this->idToLink($this->_topNav[0], $base);
		}
		if ( $this->_nextNav != NULL && count($this->_nextNav) == 1 )
		{
			$this->_nextNav = $this->idToLink($this->_nextNav[0], $base);
		}
	}

	public function idToLink($id, &$base)
	{
		$nav = array();
		$nav[0] = $id . '.html';
		$item = $base->_items[$id];
		if ( $item->_type != 'PAGE' )
		{
			echo "wrong idToLink -- not PAGE -- $id $item->_type \n";
		}
		else
		{
			$nav[1] = $item->_name;
		}
		return $nav;
	}


	public function genDoc(&$base)
	{
		echo ("generating $this->_id  \n");

        $gentool = new GenTool;

		$this->transNav($base);
		$nav = $gentool->getNavBar($this->_prevNav, $this->_topNav, $this->_nextNav);
		$buf = $gentool->getHeader($this->_name);
		$buf .= $gentool->getSideTree("{$this->_id}.html");
		$buf .= '<div class="contentwrapper">' . $nav;

		$webbuf = '';

		if ($this->_static) {
			$webbuf .= $gentool->getStaticContent($this->_static);
		}
		else {
			$webbuf .= '<h1>' . $this->_name . '</h1>';
			if ( $this->_descr ) {
				$webbuf .= '<p>' . $this->_descr . "</p>\n";
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
						$itembuf = $gentool->translateTag($itembuf, $base);
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

		$docname = '../docs/' . $this->_id . '.html';
		$gentool->writePage($docname, $buf);

		if (FOR_WEB == 1) {
			$docname = '../forweb/' . $this->_id . '.html';
			$webbuf = '<div class="lsdoc_content">' . $webbuf . '</div>';
			$gentool->writePage($docname, $webbuf);
		}
	}

	public function getGuiTips(&$tips_base)
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

}

