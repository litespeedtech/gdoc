<?php

abstract class Item
{
	protected $_id;
	protected $_name;
	protected $_type; // PAGE, QA, ITEM, TBL
    protected $_ns = ''; // OLE
    protected $_lang = array();

    protected $_src;

    const TYPE_PAGE = 'PAGE';
    const TYPE_TBL = 'TBL';
    const TYPE_ITEM = 'ITEM';
    const TYPE_QA = 'QA';
    const TYPE_NAV = 'NAV';


    public function inCurrentNameSpace()
    {
        if ( $this->_ns == '' )
            return true ;
        if ( DOC_TYPE == 'lb' && strpos($this->_ns, 'L') !== false ) {
            return true ;
        }
        if ( DOC_TYPE == 'ws' && strpos($this->_ns, 'E') !== false ) {
            return true ;
        }
        if ( DOC_TYPE == 'ows' && strpos($this->_ns, 'O') !== false ) {
            return true ;
        }
        return false ;
    }

    public function getType()
    {
        return $this->_type;
    }

    public function getId()
    {
        return $this->_id;
    }

    public function getName()
    {
        global $config;
        $lang = $config['CUR_LANG'];

        if (($lang != DEFAULT_LANG) && isset($this->_lang[$lang]['name']))
            return $this->_lang[$lang]['name'];
        else
            return $this->_name;
    }

    public function setSrc($curfile)
    {
        $this->_src = $curfile;
    }

    public function applyLanguagePack($item, $lang)
    {
        if ($this->_id != $item->_id || $this->_type != $item->_type) {
            $this->showErr('apply language ' . $lang . ' failed, item does not match');
            return false;
        }

        if (!isset($this->_lang[$lang]))
            $this->_lang[$lang] = array();

        $this->_lang[$lang]['name'] = $item->_name;

        return true;
    }

    public function showErr($errMesg, $errType='ERR')
    {
        echo '[' . $errType . '] ' . $this->_type . ' ID:' . $this->_id . ' in ' . $this->_src . ' : ' . $errMesg . "\n";
    }

    public function dumpDebug()
    {
        $this->showErr(print_r($this, true), 'DEBUG');
    }
}
