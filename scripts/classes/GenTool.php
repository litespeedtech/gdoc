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

        echo " ... finish Page $docname \n" ;
    }

    public static function getHeader( $name )
    {
        $title = $name ;
        if ( DOC_TYPE == 'ows' ) {
            $title = "Open LiteSpeed Web Server Users' Manual - $title" ;
        }
        elseif ( DOC_TYPE == 'ws' ) {
            $title = "LiteSpeed Web Server Users' Manual - $title" ;
        }
        elseif ( DOC_TYPE == 'lb' ) {
            $title = "LiteSpeed Load Balancer Users' Manual - $title" ;
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
        global $db ;
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
        $copyyear = array('ows' => '2013-2015', 'ws' => '2003-2015', 'lb' => '2007-2015');
        $footer =  '<footer class="copyright">Copyright &copy; ' . $copyyear[DOC_TYPE]
                . '. <a href="https://www.litespeedtech.com">LiteSpeed Technologies Inc.</a> All rights reserved.</footer>'
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

        if ( isset($next) && count($next) == 2 ) {
            $buf .= '<a href="' . $nav['next']->getId() . '.html">' . $nav['next']->getName() . '</a> &#187;' ;
        }
        else
            $buf .= '&nbsp;' ;

        $buf .= '</div></div>' . "\r\n" ;
        return $buf ;
    }

    public static function translateTag( &$buf, $db )
    {
        global $config ;
        if ( isset($config['ws_lb']) ) {
            $buf = str_replace($config['ws_lb'], $config['ws_lb_replace'], $buf) ;
        }

        $from = array( '{tag}', '{cmd}', '{val}', '{/}' ) ;
        $to = array( '<span class="tag">', '<span class="cmd">', '<span class="val">', '</span>' ) ;


        $buf1 = str_replace($from, $to, $buf) ;

        $pattern = "/\{\s*(ITEM|QA|TBL|PAGE)\s*=[^}]+\}/" ;
        $c = preg_match_all($pattern, $buf1, $matches) ;
        if ( $c > 0 ) {
            $from1 = $matches[0] ;
            $to1 = array() ;
            foreach ( $from1 as $f ) {
                $to1[] = $db->translate($f) ;
            }
            $buf1 = str_replace($from1, $to1, $buf1) ;
        }

        $from = array( "\n\n\n", "\n\n", "\r\n", "\n", '{ext-href}', '{ext-href-end}', '{ext-href-end-a}' ) ;
        $to = array( "<br/><br/>\n", "<br/>\n", ' ', ' ', '<a href="', '" target="_blank">', '</a>' ) ;

        $buf1 = str_replace($from, $to, $buf1) ;

        return $buf1 ;
    }

    public static function translateTagForTips( $buf, $db)
    {
        global $config ;
        if ( isset($config['ws_lb']) )
            $buf = str_replace($config['ws_lb'], $config['ws_lb_replace'], $buf) ;

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
            case 'bool': return 'Select from radio box' ;
            case 'text': return NULL ;
            case 'path1': return 'Absolute path.' ;
            case 'path2': return 'An absolute path or a relative path to $SERVER_ROOT.' ;
            case 'file2': return 'File name which can be an absolute path or relative to $SERVER_ROOT.' ;
            case 'file3': return 'File name which can be absolute, or relative to $SERVER_ROOT, or relative to $VH_ROOT.' ;
            case 'path3': return 'A path which can be absolute, or relative to $SERVER_ROOT, or relative to $VH_ROOT.' ;
            case 'select': return 'Select from drop down list' ;
            case 'checkbox': return 'Select from checkbox' ;
            case 'uint': return 'Integer number' ;
        }
        return $syntax ;
    }


	public static function genTips($db, $outfile)
	{
        global $config;
		$fd = fopen($outfile, 'w');
		if ( !$fd )	{
			echo "fail to open file $outfile\n";
			return false;
		}
		fwrite($fd, "<?php \n");

        if (DOC_TYPE == 'ows') {
            fwrite($fd, "\nglobal \$_tipsdb;\n\n");
        }

		$search = array("\n\n\n", "\n\n", "\r\n", "\n", '"', "'", '{ext-href}', '{ext-href-end}', '{ext-href-end-a}');
		$replace = array('<br/><br/>', '<br/>',  ' ', ' ', '&quot;', '&#039;', '<a href="', '" target="_blank">', '</a>');

        foreach( $db->_tips as $item )
		{
			$id = $item->getId();
			$name = $item->getName();
			$is_table = (strpos($id, 'TABLE') !== FALSE);
			$desc1 = GenTool::translateTagForTips($item->getDescr(), $db);
			$desc = str_replace($search, $replace, $desc1);
			$tip = '';
			if ( $tips = $item->getTips() ) {
				$tip = GenTool::translateTagForTips($tips, $db );
				$tip = str_replace($search, $replace, $tip);
			}
			$syntax = '';
			if ( !$is_table && $item->getSyntax()) {
				$syntax = GenTool::translateSyntax(GenTool::translateTagForTips($item->getSyntax(), $base));
				$syntax = str_replace($search, $replace, $syntax);
			}
			$example = '';
			if ( $item->getExample() ) {
				$example = GenTool::translateTagForTips($item->getExample(), $base );
				$example = str_replace($search, $replace, $example);
			}

            if (DOC_TYPE == 'ows') {
                $buf = "\$_tipsdb['$id'] = new DAttrHelp('$name', '$desc', '$tip', '$syntax', '$example');\n\n";
            }
            else {
                $buf = "\t\t\$this->db['$id'] = new DATTR_HELP_ITEM(\"$name\", '$desc', '$tip', '$syntax', '$example');\n";
            }
			fwrite($fd, $buf);

			if (in_array($id, $config['DEBUG_TAG'])) {
				echo "In GenHelpTips::genTips - tipitem $id \n";
				var_dump($item);

				echo "In GenHelpTips::genTips - tipbuf $id \n";
				var_dump($buf);
				echo "end of var_dump buf \n";

			}

		}
		//fwrite($fd, $this->get_fixed_tips());

		fclose($fd);
		echo ("finish generate tips $outfile \n");
	}


}
