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
                        'ExtApp_Help' => 'config/extapps/fcgi',
                        'ExtApp_Help' => 'config/extapps/fcgi-auth',
                        'ExtApp_Help' => 'config/extapps/laspi',
                        'ExtApp_Help' => 'config/extapps/servlet',
                        'ExtApp_Help' => 'config/extapps/webserver',
                        'ExtApp_Help' => 'config/extapps/pipped-logger',
                        'ExtApp_Help' => 'config/extapps/load-balancer',
			'ScriptHandler_Help' => 'config/scripthandler',
			'Rails_Help' => 'config/rails',
			'Listeners_Help' => 'config/listeners',
			'Templates_Help' => 'config/templates',
			'VirtualHosts_Help' => 'config/vhostlist',
			'VHGeneral_Help' => 'config/vhostgeneral',
			'VHSecurity_Help' => 'config/vhostsecurity',
			'Rewrite_Help' => 'config/rewrite',
			'Context_Help' => 'config/context',
                        'Context_Help' => 'config/context/static',
                        'Context_Help' => 'config/context/javawebapp',
                        'Context_Help' => 'config/context/servlet',
                        'Context_Help' => 'config/context/fcgi',
                        'Context_Help' => 'config/context/lsapi',
                        'Context_Help' => 'config/context/proxy',
                        'Context_Help' => 'config/context/cgi',
                        'Context_Help' => 'config/context/load-balancer',
                        'Context_Help' => 'config/context/redirect',
                        'Context_Help' => 'config/context/rails',
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
