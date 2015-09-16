<?php



class ItemBase
{
	var $_items = array();

	function ItemBase($docs)
	{
		foreach( $docs as $doc )
			$this->parseDoc($doc);

	}

	function parseDoc($doc)
	{
		$fd = fopen($doc, 'r');
		if (!$fd) {
			echo "failed to open file: $doc \n";
			return;
		}

		$qaInd = false;
		$itemInd = false;
		$tableInd = false;
		$pageInd = false;
		$buf = array();

		while ( !feof($fd) )
		{
			$tmp = fgets($fd, 4096);

			if ( $itemInd )
			{
				$buf[] = $tmp;
				$tag = '[END_ITEM]';
				if ( strncmp( $tag, $tmp, strlen($tag) ) == 0 )
				{
					$itemInd = false;
					$item = new Item($buf, 'ITEM');
                    if (GenTool::inCurrentNameSpace($item->_ns)) {
                        if ( isset($this->_items[$item->_id]) )
                            echo "duplicate item $item->_id in $doc \n";
                        else
                            $this->_items[$item->_id] = $item;
                    }
				}
			}
			else if ( $qaInd )
			{
				$buf[] = $tmp;
				$tag = '[END_QA]';
				if ( strncmp( $tag, $tmp, strlen($tag) ) == 0 )
				{
					$qaInd = false;
					$item = new Item($buf, 'QA');
                    if (GenTool::inCurrentNameSpace($item->_ns)) {
                        if ( isset($this->_items[$item->_id]) )
                            echo "duplicate qa $item->_id in $doc \n";
                        else
                            $this->_items[$item->_id] = $item;
                    }
				}
			}
			else if ( $tableInd )
			{
				$buf[] = $tmp;
				$tag = '[END_TBL]';
				if ( strncmp( $tag, $tmp, strlen($tag) ) == 0 )
				{
					$tableInd = false;
					$item = new Item($buf, 'TBL');
                    if (GenTool::inCurrentNameSpace($item->_ns)) {
                        if ( isset($this->_items[$item->_id]) && $this->_items[$item->_id]->_type == 'TBL' )
                            echo "duplicate tbl $item->_id in $doc \n";
                        else
                            $this->_items[$item->_id] = $item;
                    }
				}
			}
			else if ( $pageInd )
			{
				$buf[] = $tmp;
				$tag = '[END_PAGE]';
				if ( strncmp( $tag, $tmp, strlen($tag) ) == 0 )
				{
					$pageInd = false;
					$item = new Item($buf, 'PAGE');
                    if (GenTool::inCurrentNameSpace($item->_ns)) {
                        if ( isset($this->_items[$item->_id]) )
                            echo "duplicate page $item->_id in $doc \n";
                        else
                            $this->_items[$item->_id] = $item;
                    }
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
				$tag = '[QA]';
				if ( strncmp( $tag, $tmp, strlen($tag) ) == 0 )
				{
					$qaInd = true;
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
			}
		}
		fclose($fd);

	}

	function translate($tag)
	{
		if ( preg_match("/^\{\s*ITEM\s*=\s*(\S+)\s*\}/", $tag, $matches) )
		{
			return $this->transItem($matches[1]);
		}
		else if ( preg_match("/^\{\s*TBL\s*=\s*(\S+)\s*\}/", $tag, $matches) )
		{
			return $this->transTbl($matches[1]);
		}
		else if ( preg_match("/^\{\s*QA\s*=\s*(\S+)\s*\}/", $tag, $matches) )
		{
			return $this->transQA($matches[1]);
		}
		else if ( preg_match("/^\{\s*PAGE\s*=\s*(\S+)\s*\}/", $tag, $matches) )
		{
			return $this->transPage($matches[1]);
		}
		else
		{
			echo "unable to match tag $tag\n";
		}
	}

	function transItem($tag)
	{
		$itemId = $tag;
		$pageId = "";
		if ( preg_match("/(.+)#(.+)/", $tag, $matches) )
		{
			$itemId = $matches[2];
			$pageId = $matches[1] . ".html";
		}

		$item = $this->_items[$itemId];
		if ( $item == NULL )
		{
			echo "none existing item -- $itemId\n";
			$buf = '<span class="error">'.$tag.'</span>';
		}
		else if ( $item->_type != 'ITEM' )
		{
			echo "wrong tag type -- not ITEM -- $tag $item->_type \n";
			$buf = '<span class="error">'.$tag.'</span>';
		}
		else
		{
			$buf = '<span class="tagI"><a href="'. $pageId . '#' . $item->_id . '">';
			$buf .= $item->_name;
			$buf .= '</a></span>';
		}

		return $buf;
	}

	function transTbl($tag)
	{
		$tblId = $tag;
		$pageId = "";
		if ( preg_match("/(.+)#(.+)/", $tag, $matches) )
		{
			$tblId = $matches[2];
			$pageId = $matches[1] . ".html";
		}

		$item = $this->_items[$tblId];
		if ( $item == NULL )
		{
			echo "none existing table -- $tblId\n";
			$buf = '<span class="error">'.$tag.'</span>';
		}
		else if ( $item->_type != 'TBL' )
		{
			echo "wrong tag type -- not TBL -- $tag $item->_type \n";
			$buf = '<span class="error">'.$tag.'</span>';
		}
		else
		{
			$buf = '<span class="tagI"><a href="'. $pageId . '#' . $item->_id . '">';
			$buf .= $item->_name;
			$buf .= '</a></span>';
		}

		return $buf;
	}


	function transQA($tag)
	{
		$itemId = $tag;
		$pageId = "";
		if ( preg_match("/(.+)#(.+)/", $tag, $matches) )
		{
			$itemId = $matches[2];
			$pageId = $matches[1] . ".html";
		}

		$item = $this->_items[$itemId];
		if ( $item == NULL )
		{
			echo "none existing qa item -- $itemId\n";
			$buf = '<span class="error">'.$tag.'</span>';
		}
		else if ( $item->_type != 'QA' )
		{
			echo "wrong tag type -- not QA -- $tag $item->_type \n";
			$buf = '<span class="error">'.$tag.'</span>';
		}
		else
		{
			$buf = '<span class="tagQ"><a href="'. $pageId . '#' . $item->_id . '">';
			$buf .= $item->_name;
			$buf .= '</a></span>';
		}

		return $buf;
	}


	function transPage($tag)
	{
		$itemId = $tag;
		$item = $this->_items[$itemId];
		if ( $item == NULL )
		{
			echo "none existing page item -- $itemId\n";
			$buf = '<span class="error">'.$tag.'</span>';
		}
		else if ( $item->_type != 'PAGE' )
		{
			echo "wrong tag type -- not PAGE -- $tag $item->_type \n";
			$buf = '<span class="error">'.$tag.'</span>';
		}
		else
		{
			$buf = '<span class="tagP"><a href="'. $itemId . '.html">';
			$buf .= $item->_name;
			$buf .= '</a></span>';
		}

		return $buf;
	}

}
