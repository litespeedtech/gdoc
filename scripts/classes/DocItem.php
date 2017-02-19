<?php

class DocItem extends Item
{
    public function __construct($buf='', $src='', $start=-1)
    {
        $this->_type = HelpDocDef::TYPE_ITEM;
        $this->_src = $src;
        $this->_startLine = $start;
		if ( $buf != '' ) {
			$this->parseDoc($buf);
        }
    }

}
