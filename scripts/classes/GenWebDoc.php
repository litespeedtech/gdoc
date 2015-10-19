<?php

class MapLSWS
{
	var $static_map;
	var $dyn_map;
	var $url_convert;

	function __construct()
	{
		$this->dyn_map = array(
			'ServGeneral_Help' => 'config/general',
			'ServTuning_Help' => 'config/tuning',
			'ServSecurity_Help' => 'config/security',
			'RequestFilter_Help' => 'config/reqfilter',
			'Cache_Help' => 'config/cache',
			'ExtApp_Help' => 'config/extapps',
                        'External_FCGI' => 'config/extapps/fcgi',
                        'External_FCGI_Auth' => 'config/extapps/fcgi-auth',
                        'External_LSAPI' => 'config/extapps/laspi',
                        'External_Servlet' => 'config/extapps/servlet',
                        'External_WS' => 'config/extapps/webserver',
                        'External_PL' => 'config/extapps/pipped-logger',
                        'External_LB' => 'config/extapps/load-balancer',
			'ScriptHandler_Help' => 'config/scripthandler',
			'Rails_Help' => 'config/rails',
			'Listeners_Help' => 'config/listeners',
			'Templates_Help' => 'config/templates',
			'VirtualHosts_Help' => 'config/vhostlist',
			'VHGeneral_Help' => 'config/vhostgeneral',
			'VHSecurity_Help' => 'config/vhostsecurity',
			'Rewrite_Help' => 'config/rewrite',
			'Context_Help' => 'config/context',
                        'Static_Context' => 'config/context/static',
                        'Java_Web_App_Context' => 'config/context/javawebapp',
                        'Servlet_Context' => 'config/context/servlet',
                        'FCGI_Context' => 'config/context/fcgi',
                        'LSAPI_Context' => 'config/context/lsapi',
                        'Proxy_Context' => 'config/context/proxy',
                        'CGI_Context' => 'config/context/cgi',
                        'LB_Context' => 'config/context/load-balancer',
                        'Redirect_Context' => 'config/context/redirect',
                        'Rails_Context' => 'config/context/rails',
                        'VHWebSocket_Help' => 'config/web-socket-proxy',
			'VHAddOns_Help' => 'config/addons',
			'AdminGeneral_Help' => 'config/adminserver',
			'AdminListener_Help' => 'config/adminlistener',
			'ServerStat_Help' => 'admin/service',
		);

		$this->static_map = array(
			'index' => '',
			'license_std' => 'license-standard',
			'license' => 'license-enterprise',
			'intro' => 'introduction',
			'install' => 'install',
			'admin' => 'admin',
			'security' => 'security',
			'config' => 'config'
		);

		$map = array_merge($this->dyn_map, $this->static_map);
		foreach($map as $id => $link) {
			$this->url_convert['file'][] = $id . '.html';
			$this->url_convert['search'][] = $id . '.html';
			$this->url_convert['replace'][] = '/docs/webserver/' . $link;
		}
		$this->url_convert['search'][] = '<h1>';
		$this->url_convert['replace'][] = '<h2>';
		$this->url_convert['search'][] = '</h1>';
		$this->url_convert['replace'][] = '</h2>';
		$this->url_convert['search'][] = 'img/top.gif';
		$this->url_convert['replace'][] = '/static/image/top.gif';
	}
}

class GenWebDoc
{
	function GenerateWebDocs($Map)
	{
		$this->convertLinks($Map->url_convert);
	}

	function convertLinks($urlconvert)
	{
        global $config;
		$search = $urlconvert['search'];
		$replace = $urlconvert['replace'];
		$files = $urlconvert['file'];

		foreach( $files as $f) {
			$fromfile = $config['outdir']['web'] . $f;
			$tofile = $config['outdir']['web'] . 'docs/' . $f;
			$buf = file_get_contents($fromfile) ;
			if ($buf === FALSE) {
				echo "ConvertLinks:fail to open $fromfile \n";
				continue;
			}
			$bufnew = str_replace($search, $replace, $buf);
			GenTool::writePage($tofile, $bufnew);
            unlink($fromfile);
		}
	}
}
