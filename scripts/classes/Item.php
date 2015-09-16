<?php

class Item
{
	public $_id;
	public $_name;
	public $_type; // PAGE, QA, ITEM, TBL
    public $_ns = '';

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
                $tag = 'NS:';
				if ( strncmp($tag, $line, strlen($tag)) == 0 )
				{
					$this->_ns = trim(substr($line, strlen($tag)));
					break;
				}
			}
		}
	}
}
