<?php

class NavChain
{
	var $_id;
//	var $_name;
    var $_ns;
	var $_topNav;
	var $_pages;
    public $_cont;

	function NavChain(&$buf)
	{
		$e = "\r\n";
		$startInd = false;
		$seqInd = false;
        $contInd = false;
		$seqDef = '';

		foreach( $buf as $line )
		{
			$tag = '[END_PAGENAV]';
			if ( strncmp($tag, $line, strlen($tag)) == 0 )
				break;

			if ( !$startInd )
			{
				$tag = '[PAGENAV]';
				if ( strncmp($tag, $line, strlen($tag)) == 0 )
					$startInd = true;
			}
			else if ( $seqInd )
			{
				$tag = 'END_NAV_SEQ';
				if ( strncmp($tag, $line, strlen($tag)) == 0 )
					$seqInd = false;
				else
					$seqDef .= $line . $e;
			}
			else if ( $contInd )
			{
				$tag = 'END_CONT';
				if ( strncmp($tag, $line, strlen($tag)) == 0 )
					$contInd = false;
				else
					$this->_cont .= ' '. $line ;
			}
			else
			{
				$tag = 'ID:';
				if ( strncmp($tag, $line, strlen($tag)) == 0 )
				{
					$this->_id = trim(substr($line, strlen($tag)));
					continue;
				}
                $tag = 'NS:';
				if ( strncmp($tag, $line, strlen($tag)) == 0 )
				{
					$this->_ns = trim(substr($line, strlen($tag)));
					continue;
				}

				/*$tag = 'NAME:';
				if ( strncmp($tag, $line, strlen($tag)) == 0 )
				{
					$this->_name = trim(substr($line, strlen($tag)));
					continue;
				}*/
				$tag = 'TOP_NAV:';
				if ( strncmp($tag, $line, strlen($tag)) == 0 )
				{
					$this->_topNav = trim(substr($line, strlen($tag)));
					continue;
				}
				$tag = 'NAV_SEQ:';
				if ( strncmp($tag, $line, strlen($tag)) == 0 )
				{
					$seqDef = substr($line, strlen($tag)) . $e;
					$seqInd = true;
					continue;
				}
				$tag = 'CONT:';
				if ( strncmp($tag, $line, strlen($tag)) == 0 )
				{
					$this->_cont = substr($line, strlen($tag));
					$contInd = true;
					continue;
				}
			}
		}
		$this->_pages = preg_split( "/[\s,]+/", $seqDef, -1, PREG_SPLIT_NO_EMPTY );
		if ($this->_cont != NULL) {
			$this->_cont = preg_split( "/[\s,]+/", $this->_cont, -1, PREG_SPLIT_NO_EMPTY );
		}

	}



}

