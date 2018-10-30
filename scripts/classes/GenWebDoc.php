<?php

class GenWebDoc
{

    public function Generate()
    {
        $config = Config::getInstance();
        $config->SetCurrentLang(LanguagePack::DEFAULT_LANG);

        $url_convert = $this->getUrlConvert();
        $this->convertLinks($url_convert);
    }

    private function getUrlConvert()
    {
        $docType = Config::DocType();

        if ( $docType == 'ws' ) {
            $dyn_map = array(
                'ServGeneral_Help' => 'config/general',
                'ServLog_Help' => 'config/slog',
                'ServTuning_Help' => 'config/tuning',
                'ServSecurity_Help' => 'config/security',
                'Cache_Help' => 'config/cache',
                'PageSpeed_Config' => 'config/pagespeed',
                'ExtApp_Help' => 'config/extapps',
                'External_FCGI' => 'config/extapps/fcgi',
                'External_FCGI_Auth' => 'config/extapps/fcgi-auth',
                'External_LSAPI' => 'config/extapps/laspi',
                'External_Servlet' => 'config/extapps/servlet',
                'External_WS' => 'config/extapps/webserver',
                'External_PL' => 'config/extapps/piped-logger',
                'External_LB' => 'config/extapps/load-balancer',
                'ScriptHandler_Help' => 'config/scripthandler',
                'PHP_Help' => 'config/php',
                'Rails_Help' => 'config/rails',
                'Listeners_General_Help' => 'config/listener-general',
                'Listeners_SSL_Help' => 'config/listener-ssl',
                'Templates_Help' => 'config/templates',
                'VirtualHosts_Help' => 'config/vhostbasic',
                'VHGeneral_Help' => 'config/vhostgeneral',
                'VHSecurity_Help' => 'config/vhostsecurity',
                'VHSSL_Help' => 'config/vhost-ssl',
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
                'VHPageSpeed_Config' => 'config/vhost-pagespeed',
                'VHAddOns_Help' => 'config/addons',
                'AdminGeneral_Help' => 'config/admingeneral',
                'AdminSecurity_Help' => 'config/adminsecurity',
                'AdminListeners_General_Help' => 'config/adminlistenergen',
                'AdminListeners_SSL_Help' => 'config/adminlistenerssl',
                'ServerStat_Help' => 'admin/service',
            );

            $static_map = array(
                'index' => '',
                'license' => 'license-enterprise',
                'intro' => 'introduction',
                'install' => 'install',
                'admin' => 'admin',
                'security' => 'security',
                'config' => 'config',
                'webconsole' => 'webconsole'
            );

            $urlPrefix = '/docs/webserver/';
        }
        elseif ( $docType == 'lb' ) {
            $dyn_map = array(
                'ServGeneral_Help' => 'config/general',
                'ServLog_Help' => 'config/slog',
                'ServTuning_Help' => 'config/tuning',
                'ServSecurity_Help' => 'config/security',
                'Cache_Help' => 'config/cache',
                'PageSpeed_Config' => 'config/pagespeed',
                'ZConf_Help' => '/config/zeroconf',
                'Listeners_General_Help' => 'config/listener-general',
                'Listeners_SSL_Help' => 'config/listener-ssl',
                'Clusters_General_Help' => 'config/cluster-general',
                'Clusters_WorkerGroup_Help' => 'config/cluster-wg',
                'Templates_Help' => 'config/templates',
                'VirtualHosts_Help' => 'config/vhostbasic',
                'VHGeneral_Help' => 'config/vhostgeneral',
                'VHSecurity_Help' => 'config/vhostsecurity',
                'VHSSL_Help' => 'config/vhost-ssl',
                'VHPageSpeed_Config' => 'config/vhost-pagespeed',
                'Rewrite_Help' => 'config/rewrite',
                'Context_Help' => 'config/context',
                'LB_Context' => 'config/context/load-balancer',
                'Redirect_Context' => 'config/context/redirect',
                'AdminGeneral_Help' => 'config/admingeneral',
                'AdminSecurity_Help' => 'config/adminsecurity',
                'AdminListeners_General_Help' => 'config/adminlistenergen',
                'AdminListeners_SSL_Help' => 'config/adminlistenerssl',
                'HA_Config' => 'config/ha',
                'ServerStat_Help' => 'admin/service',
            );

            $static_map = array(
                'index' => '',
                'license' => 'license',
                'intro' => 'introduction',
                'install' => 'install',
                'admin' => 'admin',
                'security' => 'security',
                'config' => 'config',
                'webconsole' => 'webconsole'
            );

            $urlPrefix = '/docs/litespeed-web-adc/';
        }
        else {
            error_log("Invalid entrance");
            exit;
        }

        $map = array_merge($dyn_map, $static_map);
        $url_convert = array();

        foreach ( $map as $id => $link ) {
            $url_convert['file'][] = $id . '.html';
            $url_convert['search'][] = $id . '.html';
            $url_convert['replace'][] = $urlPrefix . $link;
        }

        return $url_convert;
    }

    private function convertLinks( $urlconvert )
    {
        $config = Config::getInstance();
        $outdir = $config->getOutputDir('web');

        $search = $urlconvert['search'];
        $replace = $urlconvert['replace'];
        $files = $urlconvert['file'];

        foreach ( $files as $f ) {
            $fromfile = $outdir . $f;
            $tofile = $outdir . 'docs/' . $f;
            $buf = file_get_contents($fromfile);

            if ( $buf === FALSE ) {
                echo "ConvertLinks:fail to open $fromfile \n";
                continue;
            }

            $bufnew = str_replace($search, $replace, $buf);
            GenTool::writePage($tofile, $bufnew);
            unlink($fromfile);
        }
    }

}
