<?php


abstract class Item
{
	protected $_type; // PAGE, QA, ITEM, TBL
    protected $_fields = array();
    protected $_lang = array();

    protected $_src;

    public function inCurrentNameSpace()
    {
        if ($ns = $this->hasValue('NS')) {

            if ( DOC_TYPE == 'lb') {
                return (strpos($ns, 'L') !== false);
            }
            elseif ( DOC_TYPE == 'ws') {
                return (strpos($ns, 'E') !== false );
            }
            elseif ( DOC_TYPE == 'ows') {
                return (strpos($ns, 'O') !== false );
            }
            else {
                $this->showErr("Unknown DOC_TYPE " . DOC_TYPE);
                return false ;
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

    public function getValueInLang($fieldId)
    {
        global $config;
        $lang = $config['CUR_LANG'];

        if (!isset($this->_fields[$fieldId])) {
            $this->showErr('getFieldValueInLang Invalid field ID ' . $fieldId);
            return '';
        }

        if (($lang != DEFAULT_LANG) && isset($this->_lang[$lang][$fieldId]))
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
            if ($def->isMultiLang) {
                if ($value = $peer->hasValue($tag)) {
                    $this->_lang[$lang][$tag] = $value;
                }
            }
        }
    }

    public function addValue($value, $tagdef)
    {
        $value = trim($value);
        $tag = $tagdef->tag;

        if ($tagdef->splitCont) {
            $value = preg_split( "/[\s,]+/", $value, -1, PREG_SPLIT_NO_EMPTY );
        }

        if ($tagdef->isMultiEntry) {
            if ($this->_fields[$tag] == NULL) {
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
                        if ($def->isMultiLine) {
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
            if ($def->isMultiLine) {
                $buf .= 'END_' . $id . $e . $e;
            }
        }
		$buf = '[END_' . $this->_type . ']' . $e;

        return $buf;
	}


    public function showErr($errMesg, $errType='ERR')
    {
        echo '[' . $errType . '] ' . $this->_type . ' ID:' . $this->getId() . ' in ' . $this->_src . ' : ' . $errMesg . "\n";
    }

    public function dumpDebug()
    {
        $this->showErr('DUMP', 'DEBUG');
    }
}
