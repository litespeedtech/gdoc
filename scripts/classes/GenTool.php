<?php

class GenTool
{

	public static function writePage($docname, &$content)
	{
		$fd = fopen( $docname, 'w' );
		if ( !$fd )
			return false;

		fwrite( $fd, $content);

		fclose($fd);

		echo " ... finish Page $docname \n" ;
	}


	public static function getHeader($name)
	{
		$title = $name;
		if (DOC_TYPE == 'ows') {
			$title = "Open LiteSpeed Web Server Users' Manual - $title";
		}
		elseif (DOC_TYPE == 'ws') {
			$title = "LiteSpeed Web Server Users' Manual - $title";
		}
		elseif (DOC_TYPE == 'lb') {
			$title = "LiteSpeed Load Balancer Users' Manual - $title";
		}
		else {
			echo "Error: wrong DOC_TYPE\n";
		}
		$e = "\r\n";

		$buf = <<<EOD
<!DOCTYPE html>
<head>
  <meta charset="utf-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
  <title>$title</title>
  <meta name="description" content="$title." />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link rel="shortcut icon" href="img/favicon.ico" />
  <link rel="stylesheet" type="text/css" href="css/hdoc.css">
</head>
<body>
<div class="pagewrapper">
EOD;
		return $buf;
	}

	public static function getSideTree($docname)
	{
		global $static_dir;
		$buf = file_get_contents("$static_dir/leftside_toc.txt");
		$pos = strpos($buf, "<a href=\"$docname\"" );
		if ($pos !== FALSE) {
			$buf2 = substr($buf, 0, $pos);
			$buf2 .= '<span class="current">';
			$pos2 = strpos($buf, '</a>', $pos);
			$buf2 .= substr($buf, $pos, $pos2 + 4 - $pos);
			$buf2 .= '</span>';
			$buf2 .= substr($buf, $pos2+4);
			return $buf2;
		}
		else
			return $buf;
	}

	public static function getStaticContent($static_file)
	{
		global $static_dir;
		$buf = file_get_contents("$static_dir/$static_file");
		return $buf;
	}

	public static function getFooter()
	{
		if (DOC_TYPE == 'ows') {
			return '<footer class="copyright">Copyright &copy; 2013-2015. <a href="https://www.litespeedtech.com">LiteSpeed Technologies Inc.</a> All rights reserved.</footer>
				</div>
				</body>
				</html>';
		}
		elseif (DOC_TYPE == 'ws') {
			return '<footer class="copyright">Copyright &copy; 2003-2015. <a href="https://www.litespeedtech.com">LiteSpeed Technologies Inc.</a> All rights reserved.</footer>
				</div>
				</body>
				</html>';
		}
		elseif (DOC_TYPE == 'lb') {
			return '<footer class="copyright">Copyright &copy; 2007-2015. <a href="https://www.litespeedtech.com">LiteSpeed Technologies Inc.</a> All rights reserved.</footer>
				</div>
				</body>
				</html>';
		}
	}

	public static function getNavBar($prev, $top, $next)
	{
		if ($prev == '' && $top == '' && $next == '')
			return '';
		$buf = '<div class="nav-bar"><div class="prev">';
		if ( isset( $prev ) && count($prev) == 2 ) {
			$buf .= '&#171 <a href="'. trim($prev[0]).'">'.trim($prev[1]).'</a>';
		}
		else
			$buf .= '&nbsp;';
		$buf .= '</div><div class="center">';
		if ( isset( $top ) && count($top) == 2 ) {
			$buf .= '<a href="'. trim($top[0]).'">'.trim($top[1]).'</a>';
		}
		else
			$buf .= '&nbsp;';

		$buf .= '</div><div class="next">';

		if ( isset( $next ) && count($next) == 2 ) {
			$buf .= '<a href="'. trim($next[0]) . '">'.trim($next[1]).'</a> &#187;';
		}
		else
			$buf .= '&nbsp;';

		$buf .= '</div></div>' . "\r\n";
		return $buf;

	}

	public static function translateTag(&$buf, $base)
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


		$from = array("\n\n\n", "\n\n", "\r\n", "\n", '{ext-href}', '{ext-href-end}', '{ext-href-end-a}');
		$to = array("<br/><br/>\n", "<br/>\n", ' ', ' ', '<a href="', '" target="_blank">', '</a>');

		$buf1 = str_replace( $from, $to, $buf1 );

		return $buf1;
	}


	public static function translateTagForTips($buf, $base)
	{
		$from = array('{tag}', '{cmd}', '{val}', '{/}');

		if ( isset ($GLOBALS['ws_lb']) )
			$buf = str_replace( $GLOBALS['ws_lb'], $GLOBALS['ws_lb_replace'], $buf );

		$pattern = "/\{\s*(ITEM|QA|TBL|PAGE)\s*=[^}]+\}/";
		$c = preg_match_all($pattern, $buf, $matches);
		if ( $c > 0 )
		{
			$from1 = $matches[0];
			$to1 = array();
			foreach( $from1 as $f )
			{
				$to1[] = '"' . strip_tags($base->translate($f)) . '"';
			}
			$buf = str_replace( $from1, $to1, $buf );
		}

		$buf1 = str_replace( $from, '', $buf );


		return $buf1;
	}


	public static function translateSyntax($syntax)
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
