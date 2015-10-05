<?php

class DocItem extends Item
{
	protected $_width='100%';

    public function __construct($buf='', $src='')
    {
        $this->_type = HelpDocDef::TYPE_ITEM;
        $this->_src = $src;
		if ( $buf != '' ) {
			$this->parseDoc($buf);
        }
    }

	public function initFromTbl($tbl)
	{
		$this->_fields = $tbl->getFields();
        $this->_src = 'TBL';
	}

    public function getName()
    {
        return $this->getValueInLang('NAME');
    }

    public function getDescr()
    {
        return $this->getValueInLang('DESCR');
    }

	public function toDoc()
	{
		$end = '</td></tr>'."\n";
		$buf = '<a name="'. $this->getId() . '"></a>';
		$buf .= '<table width="'.$this->_width.'" class="ht" border="0" cellpadding="5" cellspacing="0">' . "\n";
		$buf .= '<tr class="ht-title"><td><div>'.$this->getDefaultValue('NAME');
		$buf .= '<span class="top"><a href="#top"><img border=0 height=13 width=13 alt="Go to top" src="img/top.gif"></a></span></div>'.$end;
		//$buf .= '<tr class="ht-title"><td><table width="100%" border="0" cellpadding="0" cellspacing="0">';
		//$buf .= '<tr><td class="ht-title">'.$this->_name.'</td><td class="top"><a href="#top"><img border=0 height=13 width=13 alt="Go to top" src="img/top.gif"></a></td></tr></table>'.$end;
		$buf .= '<tr><td><span class="ht-label">Description: </span>' . $this->getValueInLang('DESCR') . $end;

        if ( $this->hasValue('SYNTAX') )	{
            $gentool = new GenTool;
			$syntax = $gentool->translateSyntax($this->getValueInLang('SYNTAX'));
			if ( $syntax )
				$buf .= '<tr><td><span class="ht-label">Syntax: </span>'. $syntax . $end;
		}

        if ( ($apply = $this->hasValue('APPLY')) == 1 ) {
			$buf .= '<tr><td><span class="ht-label">Apply: </span>';
			/* if ( $this->_apply ==  3 )
				$buf .= 'On the fly with reload.';
			elseif ( $this->_apply == 2 )
				$buf .= 'Restart required.'; */
			$buf .= 'Reinstall required.';
			/* else
				$buf .= $this->_apply;
			$buf .= $end; */
		}

		if ( $this->hasValue('EXAMPLE')) {
			$buf .= '<tr><td><span class="ht-label">Example: </span>' . $this->getValueInLang('EXAMPLE') . $end;
        }

		if ( $this->hasValue('TIPS')) {
			$buf .= '<tr><td><span class="ht-label">Tips: </span>' . $this->getValueInLang('TIPS') . $end;
        }

		if ( $seeAlso = $this->hasValue('SEE_ALSO') )
			$buf .= '<tr><td><span class="ht-label">See Also: </span>'. $seeAlso . $end;

		$buf .= "</table>\n";
		return $buf;
	}

    public function toToolTip()
    {
		$search = array("\n\n\n", "\n\n", "\r\n", "\n", '"', "'", '{ext-href}', '{ext-href-end}', '{ext-href-end-a}');
		$replace = array('<br/><br/>', '<br/>',  ' ', ' ', '&quot;', '&#039;', '<a href="', '" target="_blank">', '</a>');

        $id = $this->getDefaultValue('ID');
        $name = $this->getValueInLang('NAME');

        $descr = $this->getValueInLang('DESCR');
		$desc1 = GenTool::translateTagForTips($descr);
		$desc = str_replace($search, $replace, $desc1);

		$tip = '';
        if ( $this->hasValue('TIPS') ) {
            $tip = GenTool::translateTagForTips($this->getValueInLang('TIPS'));
            $tip = str_replace($search, $replace, $tip);
        }

        $syntax = '';
        if ( $this->hasValue('SYNTAX') )	{
            $syntax = GenTool::translateSyntax(GenTool::translateTagForTips($this->getValueInLang('SYNTAX')));
            $syntax = str_replace($search, $replace, $syntax);
        }
        $example = '';
        if ( $this->hasValue('EXAMPLE')) {
            $example = GenTool::translateTagForTips($this->getValueInLang('EXAMPLE'));
            $example = str_replace($search, $replace, $example);
        }

        if (DOC_TYPE == 'ows') {
            $buf = "\$_tipsdb['$id'] = new DAttrHelp('$name', '$desc', '$tip', '$syntax', '$example');\n\n";
        }
        else {
            $buf = "\$this->db['$id'] = new DATTR_HELP_ITEM(\"$name\", '$desc', '$tip', '$syntax', '$example');\n";
        }

        global $config;
        if (DEBUG && in_array($id, $config['DEBUG_TAG'])) {
            $this->showErr('In toToolTip ' . $buf, 'DEBUG');

		}
        return $buf;
    }

    public function toEditTip()
    {
		$search = array("\n", '"', "'", '{ext-href}', '{ext-href-end}', '{ext-href-end-a}');
		$replace = array(' ', '&quot;', '&#039;', '<a href="', '" target="_blank">', '</a>');

        $buf = '';
        if ($this->hasValue('EDITTIP')) {
            $id = $this->getDefaultValue('ID');
            $tips = $this->getValueInLang('EDITTIP');
            $buf .= '$this->edb[\'' . $id . '\'] = array(';
            foreach ($tips as $tip) {
                $buf .= "'" . str_replace($search, $replace, $tip) . "',";
            }
            $buf = rtrim($buf, ',');
            $buf .= "); \n";
        }
        return $buf;
    }
}
