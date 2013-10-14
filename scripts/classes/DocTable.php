<?php

class DocTable
{
	var $_id;
	var $_name;
	var $_seeAlso;

	var $_descr;
	var $_example;
	var $_tips;
	var $_cont;
	var $_items;

	function DocTable($buf)
	{
		$startInd = false;
		$descrInd = false;
		$exampleInd = false;
		$tipsInd = false;
		$contInd = false;
		foreach( $buf as $line )
		{
			$tag = '[END_TBL]';
			if ( strncmp($tag, $line, strlen($tag)) == 0 )
				break;

			if ( !$startInd )
			{
				$tag = '[TBL]';
				if ( strncmp($tag, $line, strlen($tag)) == 0 )
					$startInd = true;
			}
			else if ( $descrInd )
			{
				$tag = 'END_DESCR';
				if ( strncmp($tag, $line, strlen($tag)) == 0 )
				{
					$descrInd = false;
					$this->_descr = trim($this->_descr);
					if ( strlen($this->_descr) < 2 )
						$this->_descr = NULL;
				}
				else
					$this->_descr .= $line;
			}
			else if ( $exampleInd )
			{
				$tag = 'END_EXAMPLE';
				if ( strncmp($tag, $line, strlen($tag)) == 0 )
				{
					$exampleInd = false;
					$this->_example = trim($this->_example);
				}
				else
					$this->_example .= $line;

			}
			else if ( $tipsInd )
			{
				$tag = 'END_TIPS';
				if ( strncmp($tag, $line, strlen($tag)) == 0 )
				{
					$tipsInd = false;
					$this->_tips = trim($this->_tips);
				}
				else
					$this->_tips .= $line;
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
				$tag = 'DESCR:';
				if ( strncmp($tag, $line, strlen($tag)) == 0 )
				{
					if ($this->_descr != NULL) {
						echo "Table error: duplicate description found $this->_id\n";
					}
					
					$this->_descr = substr($line, strlen($tag));
					$descrInd = true;
					continue;
				}
				$tag = 'EXAMPLE:';
				if ( strncmp($tag, $line, strlen($tag)) == 0 )
				{
					$this->_example = substr($line, strlen($tag));
					$exampleInd = true;
					continue;
				}
				$tag = 'TIPS:';
				if ( strncmp($tag, $line, strlen($tag)) == 0 )
				{
					$this->_tips = substr($line, strlen($tag));
					$tipsInd = true;
					continue;
				}
				$tag = 'CONT:';
				if ( strncmp($tag, $line, strlen($tag)) == 0 )
				{
					$this->_cont = substr($line, strlen($tag));
					$contInd = true;
					continue;
				}
				$tag = 'SEE_ALSO:';
				if ( strncmp($tag, $line, strlen($tag)) == 0 )
				{
					$this->_seeAlso = trim(substr($line, strlen($tag)));
					continue;
				}
			}
		}
		$this->_cont = preg_split( "/[\s,]+/", $this->_cont, -1, PREG_SPLIT_NO_EMPTY );
	}

	function insertItems(&$itemBase)
	{
		$this->_items = array();
		if ( isset($this->_descr) )
		{
			$item = new DocItem(NULL);
			$item->loadFromTbl($this->_id, $this->_name, $this->_descr, 
							   $this->_tips, $this->_example, $this->_seeAlso);
			$this->_items[$this->_id] = $item;
		}

		foreach( $this->_cont as $i )
		{
			if (isset($itemBase[$i]))
				$this->_items[$i] = $itemBase[$i];
			else 
				echo "Err: item $i is not found for tbl def $this->_id \n";
		}
	}

	function toTableOfContents1()
	{
		$buf = '<tr><td class="tbl-content-lead">';
		if ( isset($this->_descr) )
			$buf .= '<a href="#'. $this->_id . '">' . $this->_name . '</a>';
		else
			$buf .= $this->_name;
		$buf .= '</td>'."\n" . '<td>';
		
		if ( isset($this->_items) )
		{
			foreach( $this->_items as $itemkey => $item )
			{
				if ($item == NULL) {
					echo "Err: item is empty, table ID = $this->_id , item ID = $itemkey\n";
				}
				else if ( $item->_id != $this->_id )
					$buf .= '<a href="#'.$item->_id.'">'.$item->_name.'</a>&nbsp;&nbsp;&nbsp;';
			}
		}
		$buf .= '</td></tr>' . "\n";
		return $buf;
	}


	function toTableOfContents()
	{
		$buf = '<section class="toc-row"><header>';
		if ( isset($this->_descr) )
			$buf .= '<a href="#'. $this->_id . '">' . $this->_name . '</a>';
		else
			$buf .= $this->_name;
		$buf .= "</header><p>\n";
		
		if ( isset($this->_items) )
		{
			foreach( $this->_items as $itemkey => $item )
			{
				if ($item == NULL) {
					echo "Err: item is empty, table ID = $this->_id , item ID = $itemkey\n";
				}
				else if ( $item->_id != $this->_id )
					$buf .= '<a href="#'.$item->_id.'">'.$item->_name.'</a>&nbsp;|&nbsp;';
			}
		}
		$buf .= '</p></section>' . "\n";
		return $buf;
	}	
	
}
