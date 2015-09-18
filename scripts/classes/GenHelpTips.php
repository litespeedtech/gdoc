<?php


class GenHelpTips
{
	var $_index;
	var $_items;
	var $_tables;
	var $_pages;
	var $_nav;
	var $_tipsBase;

	function GenHelpTips($genHelpDoc)
	{
		$this->_index = $genHelpDoc->_index;
		$this->_items = $genHelpDoc->_items;
		$this->_tables = $genHelpDoc->_tables;
		$this->_pages = $genHelpDoc->_pages;
		$this->_nav = $genHelpDoc->_nav;
		$this->_tipsBase = array();
	}

    private function getNavChain($root)
    {
        $chain = array();
        if (!isset($this->_nav[$root])) {
            echo "Wrong Nav ID $root\n";
        }
        else {
            $chain[] = $root;
            $nav = $this->_nav[$root];
            if ($nav->_cont != NULL) {
                foreach ($nav->_cont as $child) {
                    $children = $this->getNavChain($child);
                    $chain = array_merge($chain, $children);
                }
            }

        }
        return $chain;

    }

	function init($navRootId)
	{
		//get nav items
        $navchain = $this->getNavChain($navRootId);

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

		// init hard-coded tips
		//		$this->db['authName'] = new DATTR_HELP_ITEM('Authentication Name', 'Specifies an alternative name for the authorization realm for current context.  If it is not specified, the original realm name will be used. The authentication name is  displayed on the browser&#039;s login pop-up.', '', '', '');

	}

	function get_fixed_tips()
	{
		$buf =<<<EOD
		\$this->db['note'] = new DATTR_HELP_ITEM('Notes', 'Add a note for yourself.', '', '', '');
		\$this->db['c'] = new DATTR_HELP_ITEM('Name', 'A unique name for this external application. In other parts of the the configuration, you will refer to external applications by this name. For virtual host templates, the external application name must contain the \$VH_NAME variable to preserve the uniqueness of external application names on different virtual hosts.', '', '', '');

EOD;
		return $buf;
	}

	function genTips($navchain, $base, $outfile)
	{
		$this->init($navchain);

		$fd = fopen($outfile, 'w');
		if ( !$fd )
		{
			echo "fail to open file $outfile\n";
			return false;
		}
		fwrite($fd, "<?php \n");

		$search = array("\n\n\n", "\n\n", "\r\n", "\n", '"', "'", '{ext-href}', '{ext-href-end}', '{ext-href-end-a}');
		$replace = array('<br/><br/>', '<br/>',  ' ', ' ', '&quot;', '&#039;', '<a href="', '" target="_blank">', '</a>');
		ksort($this->_tipsBase);
		foreach( $this->_tipsBase as $item )
		{
			$id = $item->_id;
			$name = $item->_name;
			$is_table = (strpos($item->_id, 'TABLE') !== FALSE);
			$desc1 = GenTool::translateTagForTips($item->_descr, $base);
			$desc = str_replace($search, $replace, $desc1);
			$tip = '';
			if ( $item->_tips != "" ) {
				$tip = GenTool::translateTagForTips($item->_tips, $base );
				$tip = str_replace($search, $replace, $tip);
			}
			$syntax = '';
			if ( !$is_table && $item->_syntax != "") {
				$syntax = GenTool::translateSyntax(GenTool::translateTagForTips($item->_syntax, $base));
				$syntax = str_replace($search, $replace, $syntax);
			}
			$example = '';
			if ( $item->_example != "" ) {
				$example = GenTool::translateTagForTips($item->_example, $base );
				$example = str_replace($search, $replace, $example);
			}


			$buf = "\t\t\$this->db['$id'] = new DATTR_HELP_ITEM(\"$name\", '$desc', '$tip', '$syntax', '$example');\n";
			fwrite($fd, $buf);

			if ($id == DEBUG_TAG) {
				echo "In GenHelpTips::genTips - tipitem $id \n";
				var_dump($item);

				echo "In GenHelpTips::genTips - tipbuf $id \n";
				var_dump($buf);
				echo "end of var_dump buf \n";

			}

		}
		fwrite($fd, $this->get_fixed_tips());

		fclose($fd);
		echo ("finish generate tips $outfile \n");
	}
}
