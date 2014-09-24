<?php


class GenHelpDoc
{
	var $_index;
	var $_items;
	var $_tables;
	var $_pages;
	var $_nav;

	function GenHelpDoc($helpDocs)
	{
		$this->_index = array();
		$this->_items = array();
		$this->_tables = array();
		$this->_pages = array();

		foreach( $helpDocs as $doc )
		{
			$this->parseHelpDoc( $doc );
		}

		$this->populateData();
	}

	function genPages($navchain, $base)
	{
		foreach( $navchain as $navId )
		{
			if ( isset($this->_nav[$navId]) )
			{
				$pages = $this->_nav[$navId]->_pages;
				$topPage = $this->_nav[$navId]->_topNav;

				$c = count($pages);
				$prevPage = NULL;
				$nextPage = NULL;

				for ( $i = 0 ; $i < $c ; ++$i )
				{
					$pageId = $pages[$i];
					if ( $i > 0 )
						$prevPage = $pages[$i-1];
					else
						$prevPage = NULL;

					if ( $i < $c -1 )
						$nextPage = $pages[$i+1];
					else
						$nextPage = NULL;

					if ( isset($this->_pages[$pageId]) )
					{
						$this->_pages[$pageId]->setNav( $prevPage, $topPage, $nextPage);
						$this->_pages[$pageId]->genDoc($base);
					}
					else
						echo "Wrong page ID = $pageId \n";
				}

			}
			else
			{
				echo "Wrong Nav Chain ID = $navId \n";
			}
		}

	}

	function parseHelpDoc($helpDoc)
	{
		$fd = fopen($helpDoc, 'r');
		if (!$fd) {
			echo "Failed to open helpdoc: $helpDoc\n";
			return;
		}

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
					$this->_items[$item->_id] = $item;
					if ($item->_id == DEBUG_TAG) {
						echo "In GenHelpDoc::parseHelpDoc - item \n";
						var_dump($item);
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
					$table = new DocTable($buf);
					$id = $table->_id;
					if ( $id == NULL )
						$id = $table->_name;
					$this->_tables[$id] = $table;
					if ($id == DEBUG_TAG) {
						echo "In GenHelpDoc::parseHelpDoc - table \n";
						var_dump($table);
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
					$page = new DocPage($buf);
					$id = $page->_id;
					if ( $id == NULL )
						$id = $page->_name;
					$this->_pages[$id] = $page;
					if ($id == DEBUG_TAG) {
						echo "In GenHelpDoc::parseHelpDoc - page \n";
						var_dump($page);
					}
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
					$id = $navchain->_id;
					$this->_nav[$id] = $navchain;
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

	function populateData()
	{
		$tables = array_keys($this->_tables);
		foreach( $tables as $t )
		{
			$this->_tables[$t]->insertItems($this->_items);
		}
		foreach( array_keys($this->_pages) as $p )
		{
			if ($this->_pages[$p]->_cont != NULL) {
				$items = array();
				foreach( $this->_pages[$p]->_cont as $i )
				{
					if (!isset( $this->_tables[$i])) {
						echo "no index on table $i for page = $p\n";
						var_dump($this->_pages[$p]);
					}
					$this->_pages[$p]->_tables[$i] = $this->_tables[$i];
					if (!is_array($items)) {
						echo "not array 1 !\n";
						var_dump($items);
					}
					if (!is_array($this->_tables[$i]->_items)) {
						echo "not array 2!\n";
						var_dump($this->_tables[$i]->_items);
					}
					$items = array_merge($items, $this->_tables[$i]->_items);
				}
				$this->_pages[$p]->_items = $items;
			}
		}
	}


}
