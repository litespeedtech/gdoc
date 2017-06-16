<?php


abstract class Item
{
	protected $_type; // PAGE, QA, ITEM, TBL
    protected $_fields = array();
    protected $_lang = array();
	protected $_width='100%';

    protected $_src;
    protected $_startLine = -1;

    public function inCurrentNameSpace()
    {
        if ($ns = $this->hasValue('NS')) {
			switch (Config::DocType()) {
				case Config::DOC_TYPE_LB:
					return (strpos($ns, 'L') !== false);
					
				case Config::DOC_TYPE_WS:
					return (strpos($ns, 'E') !== false);
					
				case Config::DOC_TYPE_OLS:
					return (strpos($ns, 'O') !== false);

            }

        }
        else
            return true ;
    }

    public function getType()
    {
        return $this->_type;
    }

    public function getId()
    {
        if (isset($this->_fields['ID']))
            return $this->_fields['ID'];
        else {
            echo "Fail to get ID for item\n";
        }
    }
	
    public function getName()
    {
        return $this->getValueInLang('NAME');
    }

    public function getDescr()
    {
        return $this->getValueInLang('DESCR');
    }

	public function toDoc_old()
	{
		$end = '</p></td></tr>'."\n";
		$imgpath = Config::GetImagePath();
		$buf = '<a name="'. $this->getId() . '"></a>';
		$buf .= '<table width="'.$this->_width.'" class="ht" border="0" cellpadding="5" cellspacing="0">' . "\n";
		$buf .= '<tr class="ht-title"><td><div>'.$this->getName('NAME');
		$buf .= '<span class="top"><a href="#top"><img border=0 height=13 width=13 alt="Go to top" src="' . $imgpath . 'img/top.gif"></a></span></div>'.$end;
		$buf .= '<tr><td><span class="ht-label">' . GenTool::getTerm('label_description') 
				. '</span><p>' . $this->getValueInLang('DESCR') . $end;

        if ( $this->hasValue('SYNTAX') )	{
			$syntax = GenTool::translateSyntax($this->getValueInLang('SYNTAX'));
			if ( $syntax )
				$buf .= '<tr><td><span class="ht-label">' 
					. GenTool::getTerm('label_syntax') . '</span><p>'. $syntax . $end;
		}

        if ( ($apply = $this->hasValue('APPLY')) == 1 ) {
			$buf .= '<tr><td><span class="ht-label">' 
					. GenTool::getTerm('label_apply')
					. '</span><p>';
			$buf .= GenTool::getTerm('apply_1');
			$buf .= $end;
		}

		if ( $this->hasValue('EXAMPLE')) {
			$buf .= '<tr><td><span class="ht-label">' 
					. GenTool::getTerm('label_example')
					. '</span><p>' . $this->getValueInLang('EXAMPLE') . $end;
        }

		if ( $this->hasValue('TIPS')) {
			$buf .= '<tr><td><span class="ht-label">'
					. GenTool::getTerm('label_tips') . '</span><p>' . $this->getValueInLang('TIPS') . $end;
        }

		if ( $seeAlso = $this->hasValue('SEE_ALSO') )
			$buf .= '<tr><td><span class="ht-label">' 
				. GenTool::getTerm ('label_seealso')
				. '</span><p>'. $seeAlso . $end;

		$buf .= "</table>\n";
		return $buf;
	}
	

	public function toDoc()
	{
		$end = "</p>\n";
		$imgpath = Config::GetImagePath();
        
		$buf = '<article class="ls-helpitem">';
		$buf .= '<div><header id="' . $this->getId() . '"><h3>'.$this->getName('NAME') ;
		$buf .= '<span class="top"><a href="#top">&#8657;</a></span></h3></header></div>';
        
		$buf .= '<h4>' . GenTool::getTerm('label_description') 
				. '</h4><p>' . $this->getValueInLang('DESCR') . $end;

        if ( $this->hasValue('SYNTAX') )	{
			$syntax = GenTool::translateSyntax($this->getValueInLang('SYNTAX'));
			if ( $syntax )
				$buf .= '<h4>' 
					. GenTool::getTerm('label_syntax') . '</h4><p>'. $syntax . $end;
		}

        if ( ($apply = $this->hasValue('APPLY')) == 1 ) {
			$buf .= '<h4>' 
					. GenTool::getTerm('label_apply')
					. '</h4><p>';
			$buf .= GenTool::getTerm('apply_1');
			$buf .= $end;
		}

		if ( $this->hasValue('EXAMPLE')) {
			$buf .= '<h4>' 
					. GenTool::getTerm('label_example')
					. '</h4><div class="ls-example">' . $this->getValueInLang('EXAMPLE') . '</div>';
        }

		if ( $this->hasValue('TIPS')) {
			$buf .= '<h4>'
					. GenTool::getTerm('label_tips') . '</h4><p>' 
                    . GenTool::translateTipMarker($this->getValueInLang('TIPS')) . $end;
        }

		if ( $seeAlso = $this->hasValue('SEE_ALSO') )
			$buf .= '<h4>' 
				. GenTool::getTerm ('label_seealso')
				. '</h4><p class="ls-text-small">'. $seeAlso . $end;

		$buf .= "</article>\n";
		return $buf;
	}
	

    public function getValueInLang($fieldId)
    {
        $lang = Config::CurrentLang();

        if (!isset($this->_fields[$fieldId])) {
            $this->showErr('getFieldValueInLang Invalid field ID ' . $fieldId);
            return '';
        }

        if (($lang != LanguagePack::DEFAULT_LANG) && isset($this->_lang[$lang][$fieldId]))
            return $this->_lang[$lang][$fieldId];
        else
            return $this->_fields[$fieldId];
    }

    public function getDefaultValue($fieldId)
    {
        if (isset($this->_fields[$fieldId])) {
            return $this->_fields[$fieldId];
        }
        else {
            $this->showErr('getDefaultValue Invalid field ID ' . $fieldId);
            return '';
        }
    }

    public function hasValue($fieldId)
    {
        if (!empty($this->_fields[$fieldId])) {
            return $this->_fields[$fieldId];
        }
        else {
            return false;
        }
    }

    public function getFields()
    {
        return $this->_fields;
    }

    public function applyLanguagePack($lang, $peer)
    {
        $tagsDef = HelpDocDef::Get($peer->getType());

        if (!isset($this->_lang[$lang])) {
            $this->_lang[$lang] = array();
        }

        foreach ($tagsDef as $tag => $def) {
            if ($def->isMultiLang()) {
                if ($value = $peer->hasValue($tag)) {
                    $this->_lang[$lang][$tag] = $value;
                }
            }
        }
    }

    public function addValue($value, $tagdef)
    {
        $value = trim($value);
        $tag = $tagdef->getTag();

        if ($tagdef->splitCont()) {
            $value = preg_split( "/[\s,]+/", $value, -1, PREG_SPLIT_NO_EMPTY );
        }

        if ($tagdef->isMultiEntry()) {
            if (!isset($this->_fields[$tag])) {
                $this->_fields[$tag] = array();
            }
            $this->_fields[$tag][] = $value;
        }
        elseif (!isset($this->_fields[$tag])) {
            $this->_fields[$tag] = $value;
        }
        else {
            $this->showErr("Parse error: duplicate attribute $tag found");
        }
    }

	protected function parseDoc($buf)
	{
        $tagsDef = HelpDocDef::Get($this->_type);

        $curMultiLineTag = '';
        $curMultiLineVal = '';

        foreach( $buf as $line )
		{
			if ( $curMultiLineTag )
			{
				$tag = 'END_' . $curMultiLineTag;
				if ( strncmp($tag, $line, strlen($tag)) == 0 ) {
                    $this->addValue($curMultiLineVal, $tagsDef[$curMultiLineTag]);
					$curMultiLineTag = '';
                    $curMultiLineVal = '';
				}
				else
					$curMultiLineVal .= $line;
			}
			else
			{
                foreach ($tagsDef as $id => $def) {
                    $tag = $id . ':';
                    if ( strncmp($tag, $line, strlen($tag)) == 0 )
                    {
                        $value = substr($line, strlen($tag));
                        if ($def->isMultiLine()) {
                            $curMultiLineTag = $id;
                            $curMultiLineVal = $value;
                        }
                        else {
                            $this->addValue($value, $def);
                        }
                        break;
                    }
                }
			}
		}
	}

	public function toText()
	{
        $tagsDef = HelpDocDef::Get($this->_type);
		$e = "\n";
		$buf = '[' . $this->_type . ']' . $e;

        foreach ($tagsDef as $id => $def) {
            $buf .= $id . ': ' . $this->getDefaultValue($id) . $e;
            if ($def->isMultiLine()) {
                $buf .= 'END_' . $id . $e . $e;
            }
        }
		$buf = '[END_' . $this->_type . ']' . $e;

        return $buf;
	}

    public function toToolTip()
    {
		$search = array("\n\n\n", "\n\n", "\r\n", "\n", '"', "'", '{ext-href}', '{ext-href-end}', '{ext-href-end-a}');
		$replace = array('<br/><br/>', '<br/>',  ' ', ' ', '&quot;', '&#039;', '<a href="', '" target="_blank" rel="noopener noreferrer">', '</a>');

        $id = $this->getDefaultValue('ID');
        $name = $this->getValueInLang('NAME');

        $descr = $this->getValueInLang('DESCR');
		$desc1 = GenTool::translateTagForTips($descr);
		$desc = str_replace($search, $replace, $desc1);

		$tip = '';
        if ( $this->hasValue('TIPS') ) {
            $tip = GenTool::translateTagForTips(GenTool::translateTipMarker($this->getValueInLang('TIPS'), false));
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

        if (Config::DocType() == 'ows') {
            $buf = "\$_tipsdb['$id'] = new DAttrHelp(\"$name\", '$desc', '$tip', '$syntax', '$example');\n\n";
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
		$replace = array(' ', '&quot;', '&#039;', '<a href="', '" target="_blank" rel="noopener noreferrer">', '</a>');

        $buf = '';
        if ($this->hasValue('EDITTIP')) {
            $id = $this->getDefaultValue('ID');
            $tips = $this->getValueInLang('EDITTIP');
            if (Config::DocType() == 'ows') {
                $buf .= "\n\$_tipsdb['EDTP:$id']";
            }
            else {
                $buf .= '$this->edb[\'' . $id . "']";
            }
            $buf .= ' = array(';

            foreach ($tips as $tip) {
                $buf .= "'" . str_replace($search, $replace, $tip) . "',";
            }
            $buf = rtrim($buf, ',');
            $buf .= "); \n";
        }
        return $buf;
    }

    public function showErr($errMesg, $errType='ERR')
    {
        echo '[' . $errType . '] ' . $this->_type . ' ID:' . $this->getId() . ' in ' . $this->_src . ' [' . $this->_startLine . ']  : ' . $errMesg . "\n";
    }

    public function dumpDebug()
    {
        $this->showErr('DUMP', 'DEBUG');
    }
}
