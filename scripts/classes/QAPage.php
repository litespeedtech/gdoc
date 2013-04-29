<?php
require_once('QAItem.php');
require_once('GenTool.php');

class QAPage
{
	var $_id;
	var $_name;
	var $_prevNav;
	var $_nextNav;
	var $_topNav;
	var $_descr = '';
	var $_cont;
	var $_items;
	var $_seeAlso;

	function QAPage(&$buf)
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
				$tag = 'PREV_NAV:';
				if ( strncmp($tag, $line, strlen($tag)) == 0 )
				{
					$this->_prevNav = preg_split("/=/", 
												 trim(substr($line, strlen($tag))),
												 -1, PREG_SPLIT_NO_EMPTY);
					continue;
				}
				$tag = 'NEXT_NAV:';
				if ( strncmp($tag, $line, strlen($tag)) == 0 )
				{
					$this->_nextNav = preg_split("/=/", 
												 trim(substr($line, strlen($tag))),
												 -1, PREG_SPLIT_NO_EMPTY);
					continue;
				}
				$tag = 'TOP_NAV:';
				if ( strncmp($tag, $line, strlen($tag)) == 0 )
				{
					$this->_topNav = preg_split("/=/", 
												 trim(substr($line, strlen($tag))),
												 -1, PREG_SPLIT_NO_EMPTY);
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

	function genDoc(&$base)
	{
		$e = "\r\n";
		$nav = GenTool::getNavBar($this->_prevNav, $this->_topNav, $this->_nextNav);
		$buf = GenTool::getHeader($nav, $this->_name);
		if ( $this->_descr )
			$buf .= '<tr class="intro"><td>' . $this->_descr . '</td></tr>'.$e;
		$buf .= '<tr><td>&nbsp;</td></tr>' . $e;
		$helpList = array();

		$buf .= '<tr><td class="tbl-content">Table of Contents</td></tr>'.$e;
		$buf .= '<tr><td><table class="tbl-content2" cellpadding="0" border="0" cellspacing="5"><tr><td>'.$e;
		$buf .= '<ul>';
		foreach ( $this->_cont as $c )
		{
			//echo "cont $c \n";
			$buf .= $this->_items[$c]->toTableOfContents();
		}
		$buf .= '</ul></td></tr></table></td></tr>'. $e;

		foreach ( $this->_cont as $c )
		{
			$buf .= '<tr><td>' .$e;
			$buf .= $this->_items[$c]->toDoc();
			$buf .= '</td></tr>'.$e;
		}

		$buf .= GenTool::getFooter($nav);

		$buf1 = GenTool::translateTag($buf, $base);

		$docname = '../docs/' . $this->_id . '.html';
		GenTool::writePage($docname, $buf1);
	}


}

?>
