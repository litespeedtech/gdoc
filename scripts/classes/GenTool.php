<?php

class GenTool
{

	function writePage($docname, &$content)
	{
		$fd = fopen( $docname, 'w' );
		if ( !$fd )
			return false;

		fwrite( $fd, $content);

		fclose($fd);

		echo "finish Page $docname \n" ;
	}


	function getHeader($nav, $name)
	{
		$e = "\r\n";
		$buf = '<html>'.$e;
		$buf .= '<head>'.$e;
		$buf .= "<title>LiteSpeed Web Server User's Manual - $name</title>$e";
		$buf .= '<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">'.$e; 
		$buf .= '<link rel="stylesheet" type="text/css" href="css/hdoc.css">'.$e;
		$buf .= '</head>'.$e;
		$buf .= '<body bgcolor="#FFFFFF" text="#000000">'.$e;
		$buf .= '<a name="top"></a><table width="100%" border="0" cellpadding="0" cellspacing="10">'.$e;
		if ( isset($nav) )
			$buf .= '<tr><td>'.$nav.'</td></tr>'.$e;

		$buf .= '<tr class="label-title"><td>' . $name . '</td></tr>'.$e;
		return $buf;
	}


	function getFooter($nav)
	{
		$e = "\r\n";
		if ( isset($nav) )
		{
			$buf = '<tr rowspan="2"><td>&nbsp;</td></tr>' . $e;
			$buf .= '<tr><td>'.$nav.'</td></tr>'.$e;
		}
		$buf .= '<tr rowspan="3"><td>&nbsp;</td></tr>' . $e;
		$buf .= '<tr><td class="copyright">Copyright &copy; 2003-2013. <a href="http://www.litespeedtech.com">Lite Speed Technologies Inc.</a><br>All rights reserved.</td></tr>';
		$buf .= '</table>'.$e;
		$buf .= '</body>'.$e.'</html>'.$e;
		return $buf;
	}

	function getNavBar($prev, $top, $next)
	{
		$e = "\r\n";
		$buf = '<table class="nav-bar" width="100%" border="0">'.$e;
		$buf .= '<tr><td width="30%" align="left"> ';
		if ( isset( $prev ) && count($prev) == 2 )
			$buf .= '&#171 <a href="'. trim($prev[0]).'">'.trim($prev[1]).'</a>';
		
		$buf .= '</td><td width="40%" align="center"> ';
		
		if ( isset( $top ) && count($top) == 2 )
			$buf .= '<a href="'. trim($top[0]).'">'.trim($top[1]).'</a>';
			
		$buf .= '</td><td width="30%" align="right"> ';
		if ( isset( $next ) && count($next) == 2 )
			$buf .= '<a href="'. trim($next[0]) . '">'.trim($next[1]).'</a> &#187;';

		$buf .= '</td></tr></table>'.$e;
		return $buf;

	}

	function translateTag(&$buf, &$base)
	{
		$from = array('{tag}', '{cmd}', '{val}', '{/}');
		$to = array('<span class="tag">', '<span class="cmd">', '<span class="val">', '</span>');

		if ( isset ($GLOBALS['ws_lb']) )
			$buf = str_replace( $GLOBALS['ws_lb'], $GLOBALS['ws_lb_replace'], $buf );

		$buf1 = str_replace( $from, $to, $buf );

		$pattern = "/\{\s*(ITEM|QA|TBL|PAGE)\s*=[^}]+\}/";
		$c = preg_match_all($pattern, $buf1, $matches);
		if ( $c > 0 )
		{
			$from1 = $matches[0];
			$to1 = array();
			foreach( $from1 as $f )
			{
				$to1[] = $base->translate($f);
			}
			$buf1 = str_replace( $from1, $to1, $buf1 );
		}

		return $buf1;
	}


	function translateTagForTips(&$buf, &$base)
	{
		$from = array('{tag}', '{cmd}', '{val}', '{/}');

		if ( isset ($GLOBALS['ws_lb']) )
			$buf = str_replace( $GLOBALS['ws_lb'], $GLOBALS['ws_lb_replace'], $buf );

		$buf1 = strip_tags(str_replace( $from, '', $buf ), '<br>');

		$pattern = "/\{\s*(ITEM|QA|TBL|PAGE)\s*=[^}]+\}/";
		$c = preg_match_all($pattern, $buf1, $matches);
		if ( $c > 0 )
		{
			$from1 = $matches[0];
			$to1 = array();
			foreach( $from1 as $f )
			{
				$to1[] = strip_tags($base->translate($f), '<br>');
			}
			$buf1 = str_replace( $from1, $to1, $buf1 );
		}

		return $buf1;
	}

	
	function translateSyntax($syntax)
	{
		switch($syntax)
		{
		case 'bool': return 'Select from radio box';
		case 'text': return NULL;
		case 'path1': return 'Absolute path.';
		case 'path2': return 'An absolute path or a relative path to $SERVER_ROOT.'; 
		case 'file2': return 'File name which can be an absolute path or relative to $SERVER_ROOT.';
		case 'file3': return 'File name which can be absolute, or relative to $SERVER_ROOT, or relative to $VH_ROOT.'; 
		case 'path3': return 'A path which can be absolute, or relative to $SERVER_ROOT, or relative to $VH_ROOT.'; 
		case 'select': return 'Select from drop down list';
		case 'checkbox': return 'Select from checkbox';
		case 'uint': return 'Integer number';
		}
		return $syntax;

	}


}
?>