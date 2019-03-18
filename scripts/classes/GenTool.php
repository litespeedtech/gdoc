<?php

class GenTool
{

    public static function writePage( $docname, $content )
    {
        file_put_contents($docname, $content);
        echo "\t ... finish Page $docname \n" ;
    }

	public static function getTerm($id)
	{
		global $docterms;
		$lang = Config::CurrentLang();
		if (isset($docterms[$lang][$id])) {
			return $docterms[$lang][$id];
		}
		elseif (isset($docterms[LanguagePack::DEFAULT_LANG][$id])) {
			return $docterms[LanguagePack::DEFAULT_LANG][$id];
		}
		else {
			die('wrong ref of term id = '. $id);
		}

	}

    public static function getHeader( $name )
    {
		$title = '';
		switch (Config::DocType()) {
			case Config::DOC_TYPE_OLS:
				$title = self::getTerm('ows_user_manual');
				break;
			case Config::DOC_TYPE_WS:
				$title = self::getTerm('ws_user_manual');
				break;
			case Config::DOC_TYPE_LB:
				$title = self::getTerm('lb_user_manual');
				break;
			default:
				die('should not come here');
		}
		$title .= ' - ' . $name;
        $e = "\r\n" ;

		$imgpath = Config::GetImagePath();

        $buf =<<<EOD
<!DOCTYPE html>
<head>
  <meta charset="utf-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
  <title>$title</title>
  <meta name="description" content="$title." />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="robots" content="noindex">
  <link rel="shortcut icon" href="{$imgpath}img/favicon.ico" />
  <link rel="stylesheet" type="text/css" href="{$imgpath}css/hdoc.css">
</head>
<body>
<div class="pagewrapper clearfix">
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
        $footer =  '<div  class="ls-col-1-1"><footer class="copyright">'
				. self::getTerm('copyright') . ' &copy; ' . Config::GetFooterYear()
                . '. <a href="https://www.litespeedtech.com">LiteSpeed Technologies Inc.</a> '
				. self::getTerm('all_rights_reserved') . '</footer>'
                . "\n</div></div>\n</body>\n</html>\n" ;

        return $footer;
    }

    public static function getNavBar( $nav)
    {
        if ( $nav['prev'] == NULL && $nav['top'] == NULL && $nav['next'] == NULL )
            return '' ;

        $buf = '<div class="nav-bar ls-spacer-micro-top"><div class="prev">' ;
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

    public static function translateTipMarker($tip, $keepMarker = true)
    {
        $search = array('[P]', '[S]', '[I]', '[A]');
        $replace = array('', '', '', '');
        if ($keepMarker) {
            $replace = array(
                '<span title="Performance" class="ls-icon-performance"></span>',
                '<span title="Security" class="ls-icon-security"></span>',
                '<span title="Information" class="ls-icon-info"></span>',
                '<span title="Attention" class="ls-icon-attention"></span>'
            );
            if ($tip[0] != '[') {
                $tip = '[I] ' . $tip;
            }
        }
        $new_tip = str_replace($search, $replace, $tip);
        return $new_tip;
    }


}
