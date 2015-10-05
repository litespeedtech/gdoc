<?php

class DocTable extends Item
{
	protected $_items;

    public function __construct($buf='', $src='')
    {
        $this->_type = HelpDocDef::TYPE_TBL;
        $this->_src = $src;
        $this->_items = array();

        if ( $buf != '' ) {
			$this->parseDoc($buf);
            if ($this->hasValue('DESCR') || $this->hasValue('EDITTIP')) {
                $selfItem = new DocItem();
                $selfItem->initFromTbl($this);
            	$this->_items[$this->getId()] = $selfItem;
            }
        }
    }

    public function getName()
    {
        return $this->getValueInLang('NAME');
    }

    public function getCont()
    {
        return $this->hasValue('CONT');
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
		if ($this->hasValue('DESCR') )
			$buf .= '<a href="#'. $this->getId() . '">' . $this->getValueInLang('NAME') . '</a>';
		else
			$buf .= $this->getValueInLang('NAME');
		$buf .= '</td>'."\n" . '<td>';

		if ( isset($this->_items) )
		{
			foreach( $this->_items as $itemkey => $item )
			{
				if ($item == NULL) {
                    $this->showErr("item $itemkey contained in table is empty");
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
		if ($this->hasValue('DESCR') )
			$buf .= '<a href="#'. $this->getId() . '">' . $this->getValueInLang('NAME') . '</a>';
		else
			$buf .= $this->getValueInLang('NAME');
		$buf .= "</header><p>\n";

        foreach( $this->_items as $itemkey => $item ) {
            if ( $itemkey != $this->getId() )
                $buf .= '<a href="#'.$itemkey.'">'.$item->getName().'</a>&nbsp;|&nbsp;';
        }
		$buf .= '</p></section>' . "\n";
		return $buf;
	}

}
