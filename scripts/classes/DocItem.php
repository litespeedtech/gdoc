<?php

class DocItem extends Item
{
	protected $_required;
	protected $_apply;
	protected $_since;
	protected $_seeAlso;

	protected $_descr;
	protected $_syntax;
	protected $_example;
	protected $_tips;
	protected $_width='100%';

    public function __construct($buf='')
    {
        $this->_type = Item::TYPE_ITEM;
		if ( $buf != '' ) {
			$this->parseDoc($buf);
        }
    }

	public function initFromTbl($id, $name, $descr, $tips, $example, $seeAlso)
	{
		$this->_id = $id;
		$this->_name = $name;
		$this->_descr = $descr;
		$this->_tips = $tips;
		$this->_example = $example;
		$this->_seeAlso = $seeAlso;
	}

    public function applyLanguagePack($peer)
    {
        if (parent::applyLanguagePack($peer)) {
            $this->_lang[CUR_LANG]['descr'] = $peer->_descr;
            $this->_lang[CUR_LANG]['syntax'] = $peer->_syntax;
            $this->_lang[CUR_LANG]['example'] = $peer->_example;
            $this->_lang[CUR_LANG]['tips'] = $peer->_tips;
            return true;
        }
        return false;
    }

    public function getDescr()
    {
        if ((CUR_LANG != DEFAULT_LANG) && isset($this->_lang[CUR_LANG]['descr']))
            return $this->_lang[CUR_LANG]['descr'];
        else
            return $this->_descr;
    }

    public function getSyntax()
    {
        if ((CUR_LANG != DEFAULT_LANG) && isset($this->_lang[CUR_LANG]['syntax']))
            return $this->_lang[CUR_LANG]['syntax'];
        else
            return $this->syntax;
    }

    public function getExample()
    {
        if ((CUR_LANG != DEFAULT_LANG) && isset($this->_lang[CUR_LANG]['example']))
            return $this->_lang[CUR_LANG]['example'];
        else
            return $this->_example;
    }

    public function getTips()
    {
        if ((CUR_LANG != DEFAULT_LANG) && isset($this->_lang[CUR_LANG]['tips']))
            return $this->_lang[CUR_LANG]['tips'];
        else
            return $this->_tips;
    }

    protected function parseDoc($buf)
	{
		$startInd = false;
		$descrInd = false;
		$syntaxInd = false;
		$exampleInd = false;
		$tipsInd = false;
		foreach( $buf as $line )
		{
			$tag = '[END_ITEM]';
			if ( strncmp($tag, $line, strlen($tag)) == 0 )
				break;

			if ( !$startInd )
			{
				$tag = '[ITEM]';
				if ( strncmp($tag, $line, strlen($tag)) == 0 )
					$startInd = true;
			}
			else if ( $descrInd )
			{
				$tag = 'END_DESCR';
				if ( strncmp($tag, $line, strlen($tag)) == 0 ) {
					$descrInd = false;
					$this->_descr = trim($this->_descr);
				}
				else
					$this->_descr .= $line;
			}
			else if ( $syntaxInd )
			{
				$tag = 'END_SYNTAX';
				if ( strncmp($tag, $line, strlen($tag)) == 0 )
				{
					$syntaxInd = false;
					$this->_syntax = trim($this->_syntax);
				}
				else
					$this->_syntax .= $line;
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
				$tag = 'REQUIRED:';
				if ( strncmp($tag, $line, strlen($tag)) == 0 )
				{
					$this->_required = trim(substr($line, strlen($tag)));
					continue;
				}
				$tag = 'APPLY:';
				if ( strncmp($tag, $line, strlen($tag)) == 0 )
				{
					$this->_apply = trim(substr($line, strlen($tag)));
					continue;
				}
				$tag = 'SINCE:';
				if ( strncmp($tag, $line, strlen($tag)) == 0 )
				{
					$this->_since = trim(substr($line, strlen($tag)));
					continue;
				}
				$tag = 'SEE_ALSO:';
				if ( strncmp($tag, $line, strlen($tag)) == 0 )
				{
					$this->_seeAlso = trim(substr($line, strlen($tag)));
					continue;
				}
				$tag = 'DESCR:';
				if ( strncmp($tag, $line, strlen($tag)) == 0 )
				{
					if ($this->_descr != NULL) {
						$this->showErr("Item error: duplicate description found \n Existing descr = " . $this->_descr . "\n New descr = " . substr($line, strlen($tag)));
					}
					$this->_descr = substr($line, strlen($tag));
					$descrInd = true;
					continue;
				}
				$tag = 'SYNTAX:';
				if ( strncmp($tag, $line, strlen($tag)) == 0 )
				{
					$this->_syntax = substr($line, strlen($tag));
					$syntaxInd = true;
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
			}
		}
	}

	public function toDoc()
	{
		$end = '</td></tr>'."\n";
		$buf = '<a name="'. $this->_id . '"></a>';
		$buf .= '<table width="'.$this->_width.'" class="ht" border="0" cellpadding="5" cellspacing="0">' . "\n";
		$buf .= '<tr class="ht-title"><td><div>'.$this->getName();
		$buf .= '<span class="top"><a href="#top"><img border=0 height=13 width=13 alt="Go to top" src="img/top.gif"></a></span></div>'.$end;
		//$buf .= '<tr class="ht-title"><td><table width="100%" border="0" cellpadding="0" cellspacing="0">';
		//$buf .= '<tr><td class="ht-title">'.$this->_name.'</td><td class="top"><a href="#top"><img border=0 height=13 width=13 alt="Go to top" src="img/top.gif"></a></td></tr></table>'.$end;
		$buf .= '<tr><td><span class="ht-label">Description: </span>' . $this->getDescr . $end;
		if ( $this->_syntax != NULL )
		{
            $gentool = new GenTool;
			$syntax = $gentool->translateSyntax($this->getSyntax());
			if ( $syntax )
				$buf .= '<tr><td><span class="ht-label">Syntax: </span>'. $syntax . $end;
		}
		if ( $this->_apply != NULL && $this->_apply == 1)
		{
			$buf .= '<tr><td><span class="ht-label">Apply: </span>';
			/* if ( $this->_apply ==  3 )
				$buf .= 'On the fly with reload.';
			elseif ( $this->_apply == 2 )
				$buf .= 'Restart required.'; */
			if ( $this->_apply == 1 )
				$buf .= 'Reinstall required.';
			/* else
				$buf .= $this->_apply;
			$buf .= $end; */
		}
		if ( $this->_example != NULL )
			$buf .= '<tr><td><span class="ht-label">Example: </span>' . $this->getExample() . $end;

		if ( $this->_tips != NULL )
			$buf .= '<tr><td><span class="ht-label">Tips: </span>' . $this->getTips() . $end;

		if ( $this->_seeAlso != NULL )
			$buf .= '<tr><td><span class="ht-label">See Also: </span>'. $this->_seeAlso . $end;

		$buf .= "</table>\n";
		return $buf;
	}

	public function toText()
	{
		$e = "\n";
		$buf = '[ITEM]' . $e;
		$buf .= 'ID: ' . $this->_id . $e;
		$buf .= 'NAME: ' . $this->getName() . $e;
		$buf .= 'REQUIRED: ' . $this->_required . $e;
		$buf .= 'APPLY: ' . $this->_apply . $e;
		$buf .= 'SINCE: ' . $this->_since . $e;
		$buf .= 'SEE_ALSO: ' . $this->_seeAlso . $e . $e;
		$buf .= 'DESCR: ' . $this->getDescr() . $e . 'END_DESCR' . $e . $e;
		$buf .= 'SYNTAX: ' . $this->getSyntax() . $e . 'END_SYNTAX'. $e . $e;
		$buf .= 'EXAMPLE: ' . $this->getExample() . 'END_EXAMPLE' . $e . $e;
		$buf .= 'TIPS: ' . $this->getTips() . $e . 'END_TIPS'. $e . $e;
		$buf .= '[END_ITEM]' . $e;
		return $buf;
	}
}
