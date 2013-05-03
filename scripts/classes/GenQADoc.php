<?php



class GenQADoc
{
	var $_index = array();
	var $_items = array();
	var $_pages = array();

	function genAll($qaDoc, &$base)
	{
		$this->_index = array();
		$this->_items = array();
		$this->_pages = array();

		$this->parseQADoc($qaDoc);

		foreach( $this->_pages as $page )
			$page->genDoc($base);

	}

	function genSome($helpDoc, $genPages, &$base)
	{
		$this->parseQADoc($helpDoc);

		foreach( $genPages as $page )
			$this->_pages[$page]->genDoc($base);
	}

	function parseQADoc($qaDoc)
	{
		$fd = fopen($qaDoc, 'r');

		$startInd = false;
		$itemInd = false;
		$pageInd = false;
		$buf = array();

		while ( !feof($fd) )
		{
			$tmp = fgets($fd, 4096);
			$tag = '[END_QADOC]';
			if ( strncmp( $tag, $tmp, strlen($tag) ) == 0 )
				break;

			if ( !$startInd )
			{
				$tag = '[QADOC]';
				if ( strncmp( $tag, $tmp, strlen($tag) ) == 0 )
					$startInd = true;
			}
			else if ( $itemInd )
			{
				$buf[] = $tmp;
				$tag = '[END_QA]';
				if ( strncmp( $tag, $tmp, strlen($tag) ) == 0 )
				{
					$itemInd = false;
					$item = new QAItem($buf);
					$this->_items[$item->_id] = $item; 
				}
			}
			else if ( $pageInd )
			{
				$buf[] = $tmp;
				$tag = '[END_PAGE]';
				if ( strncmp( $tag, $tmp, strlen($tag) ) == 0 )
				{
					$pageInd = false;
					$page = new QAPage($buf);
					$id = $page->_id;
					if ( $id == NULL )
						$id = $page->_name;
					$this->_pages[$id] = $page;
				}
			}
			else
			{
				$tag = '[QA]';
				if ( strncmp( $tag, $tmp, strlen($tag) ) == 0 )
				{
					$itemInd = true;
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
		$this->populateData();
	}

	function populateData()
	{
		foreach( array_keys($this->_pages) as $p )
		{
			foreach( $this->_pages[$p]->_cont as $i )
			{
				$this->_pages[$p]->_items[$i] = $this->_items[$i];
			}

		}
	}

}

