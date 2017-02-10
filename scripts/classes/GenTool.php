<?php

class GenTool
{

    public static function writePage( $docname, $content )
    {
        $fd = fopen($docname, 'w') ;
        if ( ! $fd )
            return false ;

        fwrite($fd, $content) ;

        fclose($fd) ;

        echo "\t ... finish Page $docname \n" ;
    }

	public static function getTerm($id)
	{
		global $docterms;
		$lang = Config::CurrentLang();
		if (isset($docterms[$lang][$id])) {
			return $docterms[$lang][$id];
		}
		elseif ($lang != LanguagePack::DEFAULT_LANG) {
			if (isset($docterms[$lang][LanguagePack::DEFAULT_LANG])) {
				return $docterms[$lang][LanguagePack::DEFAULT_LANG];
			}
			else {
				die('wrong ref of term id = '. $id);
			}
		}
		else {
			die('wrong ref of term id = '. $id);
		}
		
	}
	
    public static function getHeader( $name )
    {
        $title = $name ;
        if ( DOC_TYPE == 'ows' ) {
            $title = self::getTerm('ows_user_manual') . " - $title" ;
        }
        elseif ( DOC_TYPE == 'ws' ) {
			$title = self::getTerm('ws_user_manual') . " - $title" ;
        }
        elseif ( DOC_TYPE == 'lb' ) {
			$title = self::getTerm('lb_user_manual') . " - $title" ;
        }
        else {
            echo "Error: wrong DOC_TYPE\n" ;
        }
        $e = "\r\n" ;

        $buf =<<<EOD
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

        return $buf ;
    }

    public static function getSideTree( $docname )
    {
        $db = HelpDB::getInstance();
        $buf = $db->getStaticContent('leftside_toc.txt') ;
        $pos = strpos($buf, "<a href=\"$docname\"") ;
        if ( $pos !== FALSE ) {
            $buf2 = substr($buf, 0, $pos) ;
            $buf2 .= '<span class="current">' ;
            $pos2 = strpos($buf, '</a>', $pos) ;
            $buf2 .= substr($buf, $pos, $pos2 + 4 - $pos) ;
            $buf2 .= '</span>' ;
            $buf2 .= substr($buf, $pos2 + 4) ;
            return $buf2 ;
        }
        else
            return $buf ;
    }

    public static function getFooter()
    {
        $copyyear = array('ows' => '2013-2017', 'ws' => '2003-2017', 'lb' => '2007-2017');
        $footer =  '<footer class="copyright">' . self::getTerm('copyright') . ' &copy; ' . $copyyear[DOC_TYPE]
                . '. <a href="https://www.litespeedtech.com">LiteSpeed Technologies Inc.</a> ' . self::getTerm('all_rights_reserved') . '</footer>'
                . "\n</div>\n</body>\n</html>" ;
        return $footer;
    }

    public static function getNavBar( $nav)
    {
        if ( $nav['prev'] == NULL && $nav['top'] == NULL && $nav['next'] == NULL )
            return '' ;

        $buf = '<div class="nav-bar"><div class="prev">' ;
        if ( $nav['prev'] ) {
            $buf .= '&#171 <a href="' . $nav['prev']->getId() . '.html">' . $nav['prev']->getName() . '</a>' ;
        }
        else
            $buf .= '&nbsp;' ;
        $buf .= '</div><div class="center">' ;
        if ( $nav['top']) {
            $buf .= '<a href="' . $nav['top']->getId() . '.html">' . $nav['top']->getName() . '</a>' ;
        }
        else
            $buf .= '&nbsp;' ;

        $buf .= '</div><div class="next">' ;

        if ( $nav['next'] ) {
            $buf .= '<a href="' . $nav['next']->getId() . '.html">' . $nav['next']->getName() . '</a> &#187;' ;
        }
        else
            $buf .= '&nbsp;' ;

        $buf .= '</div></div>' . "\r\n" ;
        return $buf ;
    }

    public static function translateTag( &$buf)
    {
		$config = Config::getInstance();
		$db = HelpDB::getInstance();
		$wslb_replace = $config->getWsLbReplace();
        $buf = str_replace($wslb_replace['from'], $wslb_replace['to'], $buf) ;

        $from = array( '{tag}', '{cmd}', '{val}', '{/}' ) ;
        $to = array( '<span class="tag">', '<span class="cmd">', '<span class="val">', '</span>' ) ;


        $buf1 = str_replace($from, $to, $buf) ;

        $pattern = "/\{\s*(ITEM|QA|TBL|PAGE)\s*=[^}]+\}/" ;
        $c = preg_match_all($pattern, $buf1, $matches) ;
        if ( $c > 0 ) {
            $from1 = $matches[0] ;
            $to1 = array() ;
            foreach ( $from1 as $f ) {
                $to1[] = $db->Translate($f) ;
            }
            $buf1 = str_replace($from1, $to1, $buf1) ;
        }

        $from = array( "\n\n\n", "\n\n", "\r\n", "\n", '{ext-href}', '{ext-href-end}', '{ext-href-end-a}' ) ;
        $to = array( "<br/><br/>\n", "<br/>\n", ' ', ' ', '<a href="', '" target="_blank" rel="noopener noreferrer">', '</a>' ) ;

        $buf1 = str_replace($from, $to, $buf1) ;

        return $buf1 ;
    }

    public static function translateTagForTips( $buf)
    {
		$config = Config::getInstance();
		$db = HelpDB::getInstance();
		$wslb_replace = $config->getWsLbReplace();

        $buf = str_replace($wslb_replace['from'], $wslb_replace['to'], $buf) ;

        $from = array( '{tag}', '{cmd}', '{val}', '{/}' ) ;

        $pattern = "/\{\s*(ITEM|QA|TBL|PAGE)\s*=[^}]+\}/" ;
        $c = preg_match_all($pattern, $buf, $matches) ;
        if ( $c > 0 ) {
            $from1 = $matches[0] ;
            $to1 = array() ;
            foreach ( $from1 as $f ) {
                $to1[] = '"' . strip_tags($db->translate($f)) . '"' ;
            }
            $buf = str_replace($from1, $to1, $buf) ;
        }

        $buf1 = str_replace($from, '', $buf) ;

        return $buf1 ;
    }

    public static function translateSyntax( $syntax )
    {
        switch ( $syntax ) {
            case 'bool': return self::getTerm('syntax_bool');
            case 'text': return null ;
            case 'path1': return self::getTerm('syntax_path1') ;
            case 'path2': return self::getTerm('syntax_path2') ;
            case 'file2': return self::getTerm('syntax_file2') ;
            case 'file3': return self::getTerm('syntax_file3') ;
            case 'path3': return self::getTerm('syntax_path3');
			case 'select': return self::getTerm('syntax_select') ;
            case 'checkbox': return self::getTerm('syntax_checkbox') ;
            case 'uint': return self::getTerm('syntax_uint') ;
        }
        return $syntax ;
    }




}
