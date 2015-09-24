<?php

class NavChain extends Item
{
    protected $_children;

    public function __construct($buf='', $src='')
    {
        $this->_type = HelpDocDef::TYPE_NAV;
        $this->_src = $src;

		if ( $buf != '' ) {
			$this->parseDoc($buf);
        }
    }

    public function getCont()
    {
        return $this->hasValue('CONT');
    }

    public function getSeq()
    {
        return $this->hasValue('NAV_SEQ');
    }

    public function getTopNav()
    {
        return $this->hasValue('TOP_NAV');
    }

    public function setChildren($children)
    {
        $this->_children = $children;
    }

    public function getChildren()
    {
        return $this->_children;
    }

}

