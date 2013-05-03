<?php

class Item
{
	var $_id;
	var $_name;
	var $_type; // PAGE, QA, ITEM, TBL

	function Item($buf, $type)
	{
		$e = "\r\n";
		$this->_type = $type;
		$startInd = false;
		foreach( $buf as $line )
		{
			$tag = "[END_$type]";
			if ( strncmp($tag, $line, strlen($tag)) == 0 )
				break;

			if ( !$startInd )
			{
				$tag = "[$type]";
				if ( strncmp($tag, $line, strlen($tag)) == 0 )
					$startInd = true;
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
					break;
				}
				$tag = 'Q:';
				if ( strncmp($tag, $line, strlen($tag)) == 0 )
				{
					$this->_name = trim(substr($line, strlen($tag)));
					break;
				}
			}
		}
	}
}
