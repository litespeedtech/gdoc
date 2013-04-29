<?php
require_once('DocItem.php');
require_once('DocTable.php');
require_once('GenTool.php');
require_once('ItemBase.php');

class DocPage
{
	var $_id;
	var $_name;
	var $_prevNav;
	var $_nextNav;
	var $_topNav;
	var $_descr = '';
	var $_cont;
	var $_tables;
	var $_items;
	var $_seeAlso;

	function DocPage(&$buf)
	{
		$e = "\r\n";
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
				if ( strncmp($tag, $line, strlen($tag)) == 0 )
					$descrInd = false;
				else
					$this->_descr .= $line . $e;
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
					$this->_descr = substr($line, strlen($tag)) . $e;
					$descrInd = true;
					continue;
				}
				$tag = 'CONT:';
				if ( strncmp($tag, $line, strlen($tag)) == 0 )
				{
					$this->_cont = substr($line, strlen($tag)) . $e;
					$contInd = true;
					continue;
				}
			}
		}
		$this->_cont = preg_split( "/[\s,]+/", $this->_cont, -1, PREG_SPLIT_NO_EMPTY );
	}

	function setNav($prevPage, $topPage, $nextPage)
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

	function transNav(&$base)
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

	function idToLink($id, &$base)
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


	function genDoc(&$base)
	{
		$e = "\r\n";
		echo ("generating $this->_id  \t\t");
		
		$this->transNav($base);
		$nav = GenTool::getNavBar($this->_prevNav, $this->_topNav, $this->_nextNav);
		$buf = GenTool::getHeader($nav, $this->_name);
		if ( $this->_descr )
			$buf .= '<tr class="intro"><td>' . $this->_descr . '</td></tr>'.$e;
		$buf .= '<tr><td>&nbsp;</td></tr>' . $e;
		$helpList = array();
		if ( $this->_tables )
		{
			$buf .= '<tr><td class="tbl-content">Table of Contents</td></tr>'.$e;
			$buf .= '<tr><td class="tbl-content1"><table cellpadding="2" border="1" cellspacing="0">'.$e;
			foreach ( $this->_tables as $table )
			{
				if ( isset($table) )
					$buf .= $table->toTableOfContents();
			}
			$buf .= '</table></td></tr>'. $e;

		}
		$buf .= '<tr><td>&nbsp;</td></tr>' . $e;
		if ( $this->_items )
		{
			foreach ( $this->_items as $item )
			{
				if ( $item != NULL )
				{
					$buf .= '<tr><td>' .$e;
					$buf .= $item->toDoc();
					$buf .= '</td></tr>'.$e;
				}
			}
		}

		$buf .= GenTool::getFooter($nav);

		$buf1 = GenTool::translateTag($buf, $base);

		$docname = '../docs/' . $this->_id . '.html';
		GenTool::writePage($docname, $buf1);
	}

	function getGuiTips(&$tips_base)
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

?>
