<?php

class DocItem
{
	var $_id;
	var $_name;
	var $_required;
	var $_apply;
	var $_since;
	var $_seeAlso;

	var $_descr;
	var $_syntax;
	var $_example;
	var $_tips;
	var $_width='100%';

	function loadFromTbl($id, $name, $descr, $tips, $example, $seeAlso)
	{
		$this->_id = $id;
		$this->_name = $name;
		$this->_descr = $descr;
		$this->_tips = $tips;
		$this->_example = $example;
		$this->_seeAlso = $seeAlso;
	}

	function DocItem($buf)
	{
		if ( $buf == NULL )
			return;
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
						echo "Item error: duplicate description found $this->_id\n";
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

	function toDoc()
	{
		$end = '</td></tr>'."\n";
		$buf = '<a name="'. $this->_id . '"></a>';
		$buf .= '<table width="'.$this->_width.'" class="ht" border="0" cellpadding="5" cellspacing="0">' . "\n";
		$buf .= '<tr class="ht-title"><td><div>'.$this->_name;
		$buf .= '<span class="top"><a href="#top"><img border=0 height=13 width=13 alt="Go to top" src="img/top.gif"></a></span></div>'.$end;
		//$buf .= '<tr class="ht-title"><td><table width="100%" border="0" cellpadding="0" cellspacing="0">';
		//$buf .= '<tr><td class="ht-title">'.$this->_name.'</td><td class="top"><a href="#top"><img border=0 height=13 width=13 alt="Go to top" src="img/top.gif"></a></td></tr></table>'.$end;
		$buf .= '<tr><td><span class="ht-label">Description: </span>' . $this->_descr . $end;
		if ( $this->_syntax != NULL )
		{
            $gentool = new GenTool;
			$syntax = $gentool->translateSyntax($this->_syntax);
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
			$buf .= '<tr><td><span class="ht-label">Example: </span>' . $this->_example . $end;

		if ( $this->_tips != NULL )
			$buf .= '<tr><td><span class="ht-label">Tips: </span>' . $this->_tips . $end;

		if ( $this->_seeAlso != NULL )
			$buf .= '<tr><td><span class="ht-label">See Also: </span>'. $this->_seeAlso . $end;

		$buf .= "</table>\n";
		return $buf;
	}

	function toText()
	{
		$e = "\n";
		$buf = '[ITEM]' . $e;
		$buf .= 'ID: ' . $this->_id . $e;
		$buf .= 'NAME: ' . $this->_name . $e;
		$buf .= 'REQUIRED: ' . $this->_required . $e;
		$buf .= 'APPLY: ' . $this->_apply . $e;
		$buf .= 'SINCE: ' . $this->_since . $e;
		$buf .= 'SEE_ALSO: ' . $this->_seeAlso . $e . $e;
		$buf .= 'DESCR: ' . $this->_descr . $e . 'END_DESCR' . $e . $e;
		$buf .= 'SYNTAX: ' . $this->_syntax . $e . 'END_SYNTAX'. $e . $e;
		$buf .= 'EXAMPLE: ' . $this->_example . 'END_EXAMPLE' . $e . $e;
		$buf .= 'TIPS: ' . $this->_tips . $e . 'END_TIPS'. $e . $e;
		$buf .= '[END_ITEM]' . $e;
		return $buf;
	}
}
