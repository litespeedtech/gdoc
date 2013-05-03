<?php


class GenHelpTips
{
	var $_index;
	var $_items;
	var $_tables;
	var $_pages;
	var $_nav;
	var $_tipsBase;

	function GenHelpTips(&$GenHelpDoc)
	{
		$this->_index = $GenHelpDoc->_index;
		$this->_items = $GenHelpDoc->_items;
		$this->_tables = $GenHelpDoc->_tables;
		$this->_pages = $GenHelpDoc->_pages;
		$this->_nav = $GenHelpDoc->_nav;
		$this->_tipsBase = array();
	}

	function init($navchain)
	{
		//get nav items
		foreach( $navchain as $navId )
		{
			if ( isset($this->_nav[$navId]) )
			{
				$pages = $this->_nav[$navId]->_pages;

				$c = count($pages);

				for ( $i = 0 ; $i < $c ; ++$i )
				{
					$pageId = $pages[$i];

					if ( isset($this->_pages[$pageId]) )
					{
						$this->_pages[$pageId]->getGuiTips($this->_tipsBase);
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

	function genTips($navchain, &$base, $outfile)
	{
		$this->init($navchain);

		$fd = fopen($outfile, 'w');
		if ( !$fd )
		{
			echo "fail to open file $outfile\n";
			return false;
		}


		$search = array("\r\n", "\n", '&lt;br&gt;', '&amp;lt;', '&amp;gt;');
		$replace = array(' ', ' ', '<br><br>' , '&lt;', '&gt;');
		ksort($this->_tipsBase);
		foreach( $this->_tipsBase as $item )
		{
			$id = $item->_id;
			$name = $item->_name;
			$is_table = (strpos($item->_id, 'TABLE') !== FALSE); 
			$desc = htmlspecialchars(str_replace($search, $replace, GenTool::translateTagForTips($item->_descr, $base)), ENT_QUOTES);
			$desc = str_replace($search, $replace, $desc);
			$tip = '';
			if ( $item->_tips != "" ) {
				$tip = htmlspecialchars(str_replace($search, $replace, GenTool::translateTagForTips($item->_tips, $base )), ENT_QUOTES);
				$tip = str_replace($search, $replace, $tip);
			}
			$syntax = '';
			if ( !$is_table && $item->_syntax != "") {
				$syntax = htmlspecialchars(str_replace($search, $replace, GenTool::translateSyntax(GenTool::translateTagForTips($item->_syntax, $base))), ENT_QUOTES);
				$syntax = str_replace($search, $replace, $syntax);
			}
			
			$buf = "\t\t\$this->db['$id'] = new DATTR_HELP_ITEM('$name', '$desc', '$tip', '$syntax');\n";
			fwrite($fd, $buf);
		}

		fclose($fd);
		echo ("finish generate tips $outfile \n");
	}
}
