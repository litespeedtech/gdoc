<?php

class QAItem
{
	var $_id;
	var $_question;
	var $_answer;
	var $_seeAlso;
	var $_width='90%';

	function QAItem($buf)
	{
		$startInd = false;
		$answerInd = false; 
		foreach( $buf as $line )
		{
			$tag = '[END_QA]';
			if ( strncmp($tag, $line, strlen($tag)) == 0 )
				break;

			if ( !$startInd )
			{
				$tag = '[QA]';
				if ( strncmp($tag, $line, strlen($tag)) == 0 )
					$startInd = true;
			}
			else if ( $answerInd )
			{
				$tag = 'END_A';
				if ( strncmp($tag, $line, strlen($tag)) == 0 )
					$answerInd = false;
				else
					$this->_answer .= $line ;
			}
			else
			{
				$tag = 'ID:';
				if ( strncmp($tag, $line, strlen($tag)) == 0 )
				{
					$this->_id = trim(substr($line, strlen($tag)));
					continue;
				}
				$tag = 'Q:';
				if ( strncmp($tag, $line, strlen($tag)) == 0 )
				{
					$this->_question = trim(substr($line, strlen($tag)));
					continue;
				}
				$tag = 'SEE_ALSO:';
				if ( strncmp($tag, $line, strlen($tag)) == 0 )
				{
					$this->_seeAlso = trim(substr($line, strlen($tag)));
					continue;
				}
				$tag = 'A:';
				if ( strncmp($tag, $line, strlen($tag)) == 0 )
				{
					$this->_answer = substr($line, strlen($tag)) ;
					$answerInd = true;
					continue;
				}
			}
		}
	}

	function toDoc()
	{
		$e = "\r\n";
		$buf = '<a name="'. $this->_id . '"></a>';
		$buf .= '<table width="'.$this->_width.'" border="0" cellpadding="3" cellspacing="0">'.$e;
		$buf .= '<tr class="qa-title"><td>&#149;&nbsp;'.$this->_question;
		$buf .= '&nbsp;&nbsp;&nbsp;&nbsp;<span class="qa-top"><a href="#top">top</a></td></tr>'.$e;
		$buf .= '<tr><td class="qa">'.$this->_answer.'</td></tr>'.$e;

		if ( $this->_seeAlso != NULL )
			$buf .= '<tr><td><span class="ht-label">See Also: </span>' . $this->_seeAlso . '</td></tr>'.$e;

		$buf .= '</table>'.$e;
		return $buf;
	}

	function toText()
	{
		$e = "\r\n";
		$buf = '[QA]' . $e;
		$buf .= 'ID: ' . $this->_id . $e;
		$buf .= 'Q: ' . $this->_question . $e;
		$buf .= 'A: ' . $this->_answer . $e . 'END_A' . $e . $e;
		$buf .= 'SEE_ALSO: ' . $this->_seeAlso . $e . $e;
		$buf .= '[END_QA]' . $e;
		return $buf;
	}

	function toTableOfContents()
	{
		$buf = '<li><a href="#' . $this->_id . '">' ;
		$buf .= $this->_question."</a></li>\r\n";
		return $buf;
	}
}
?>
