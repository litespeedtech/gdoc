<?php

class DocTable extends Item
{
	protected $_seeAlso;

	protected $_descr;
	protected $_example;
	protected $_tips;
	protected $_cont;
	protected $_items;

    public function __construct($buf='')
    {
        $this->_type = Item::TYPE_TBL;
        $this->_items = array();

        if ( $buf != '' ) {
			$this->parseDoc($buf);
            if ($this->_descr) {
                $selfItem = new DocItem();
                $selfItem->initFromTbl( 'TABLE' . $this->_id, $this->_name, $this->_descr,
							   $this->_tips, $this->_example, $this->_seeAlso);
            	$this->_items[$this->_id] = $selfItem;
            }
        }
    }

    public function applyLanguagePack($peer)
    {
        if (parent::applyLanguagePack($peer)) {
            $this->_lang[CUR_LANG]['descr'] = $peer->_descr;
            $this->_lang[CUR_LANG]['example'] = $peer->_example;
            $this->_lang[CUR_LANG]['tips'] = $peer->_tips;
            return true;
        }
        return false;
    }

	protected function parseDoc($buf)
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
                $tag = 'NS:';
				if ( strncmp($tag, $line, strlen($tag)) == 0 )
				{
					$this->_ns = trim(substr($line, strlen($tag)));
					continue;
				}
                $tag = 'DESCR:';
				if ( strncmp($tag, $line, strlen($tag)) == 0 )
				{
					if ($this->_descr != NULL) {
                        $this->showErr("Table error: duplicate description found current descr is {$this->_descr}");
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

    public function getCont()
    {
        return $this->_cont;
    }

    public function addContItem($item)
    {
        $this->_items[$item->getId()] = $item;
    }

    public function getItems()
    {
        return $this->_items;
    }

	public function toTableOfContents1()
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


	public function toTableOfContents()
	{
		$buf = '<section class="toc-row"><header>';
		if ( isset($this->_descr) )
			$buf .= '<a href="#'. $this->_id . '">' . $this->getName() . '</a>';
		else
			$buf .= $this->getName();
		$buf .= "</header><p>\n";

        foreach( $this->_items as $itemkey => $item ) {
            if ( $itemkey != $this->_id )
                $buf .= '<a href="#'.$itemkey.'">'.$item->getName().'</a>&nbsp;|&nbsp;';
        }
		$buf .= '</p></section>' . "\n";
		return $buf;
	}

}
