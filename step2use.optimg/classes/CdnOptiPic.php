<?
Class CdnOptiPic {
    
    const MODULE_ID = 'step2use.optimg';
    
    const DEFAULT_IMG_ATTRS = 'src,data-lazy';
    
    public static function getSiteSettins($siteId = null) {
        if(!$siteId) {
            $siteId = SITE_ID;
        }
        $optipicSiteID = COption::GetOptionString(self::MODULE_ID, "OPTIPIC_SITE_ID_".$siteId, "");
        $autoreplaceActive = COption::GetOptionString(self::MODULE_ID, "CDN_AUTOREPLACE_ACTIVE_".$siteId, "N");
        
        $imgAttrs = COption::GetOptionString(self::MODULE_ID, "CDN_AUTOREPLACE_IMG_ATTRS_{$siteId}", self::DEFAULT_IMG_ATTRS);
        
        $attrs = [];
        foreach(explode(",", $imgAttrs) as $attr) {
            $attr = trim($attr);
            if($attr) {
                $attrs[] = preg_quote($attr, '/');
            }
        }
        
        $domains = COption::GetOptionString(self::MODULE_ID, "OPTIPIC_DOMAINS_{$siteId}");
        $domainsSettings = [];
        foreach(explode("\n", $domains) as $domain) {
            $domain = trim($domain);
            if($domain) {
                $domainsSettings[] = $domain;
            }
        }
        
        $exclusionsUrl = COption::GetOptionString(self::MODULE_ID, "OPTIPIC_EXCLUSIONS_URL_{$siteId}");
        $exclusionsUrlSettings = [];
        foreach(explode("\n", $exclusionsUrl) as $url) {
            $url = trim($url);
            if($url && substr($url, 0, 1)=='/') {
                $exclusionsUrlSettings[] = $url;
            }
        }
        
        $whitelistImgUrls = COption::GetOptionString(self::MODULE_ID, "OPTIPIC_WHITELIST_IMG_URLS_{$siteId}");
        $whitelistImgUrlsSettings = [];
        foreach(explode("\n", $whitelistImgUrls) as $url) {
            $url = trim($url);
            if($url && substr($url, 0, 1)=='/') {
                $whitelistImgUrlsSettings[] = $url;
            }
        }
        
        $srcsetAttrs = COption::GetOptionString(self::MODULE_ID, "OPTIPIC_SRCSET_ATTRS_{$siteId}");
        $srcsetAttrsSettings = [];
        foreach(explode("\n", $srcsetAttrs) as $attr) {
            $attr = trim($attr);
            if($attr) {
                $srcsetAttrsSettings[] = $attr;
            }
        }
        
        return array(
            'site_id' => $optipicSiteID,
            'autoreplace_active' => ($autoreplaceActive=='Y'),
            'img_attrs' => $attrs,
            'domains' => $domainsSettings,
            'exclusions_url' => $exclusionsUrlSettings,
            'whitelist_img_urls' => $whitelistImgUrlsSettings,
            'srcset_attrs' => $srcsetAttrsSettings,
        );
    }
    
    // replaceImgsUrl
    function onEndBufferContent(&$content) {
        global $APPLICATION;
        $dir = $APPLICATION->GetCurDir();
        
        if (!defined('ADMIN_SECTION') && PHP_SAPI != "cli" && PHP_SAPI != "cli-server") {
        
            ///
            
            //preg_match_all('#(/[^"\'\s]+\.(png|jpg|jpeg)?)#simS', $content, $matches);
            //var_dump($matches);exit;
            
            //$content = preg_replace('#(/[^"\'\s]+\.(png|jpg|jpeg))#simS', 'xxxxxx', $content);
            //return;
            ///
        
            $settings = self::getSiteSettins();
            //var_dump($settings);
            
            if($settings['autoreplace_active'] && $settings['site_id']) {
                
                include_once __DIR__.'/optipic-cdn-php/ImgUrlConverter.php';
                
                //var_dump($settings);exit;
                
                $converterOptiPic = new \optipic\cdn\ImgUrlConverter($settings);

                $content = $converterOptiPic->convertHtml($content);
                
                //$content = preg_replace('#(/[^"\'\s]+\.(png|jpg|jpeg))#simS', '//cdn.optipic.io/site-'.$settings['site_id'].'${1}', $content);
                //$content = preg_replace('#("|\'|\()(/[^"\'\s]+\.(png|jpg|jpeg)?)("|\'|\))#simS', '${1}//cdn.optipic.io/site-'.$settings['site_id'].'${2}${4}', $content);
                // url должен начинаться с единственного слеша (двойные слеши и отсутствие слеша пока не обрабатываем)
                // $content = preg_replace('#("|\'|\()(/[^/"\'\s]{1}[^"\'\s]*\.(png|jpg|jpeg){1}?)("|\'|\))#simS', '${1}//cdn.optipic.io/site-'.$settings['site_id'].'${2}${4}', $content);
                
                
                //$content = preg_replace('#("|\'|\()(/[^/"\'\s]{1}[^"\']*\.(png|jpg|jpeg){1}?)("|\'|\))#simS', '${1}//cdn.optipic.io/site-'.$settings['site_id'].'${2}${4}', $content);
                
                //var_dump($settings);exit;
            
                //var_dump($APPLICATION->GetCurDir());exit;
                //var_dump("Z");exit;
                /*$pattern = '#<img.+?src="(.*?)".+?>#sim';
                $replacement = 'xx';*/
                //$pattern = '/<img(.+?)src=(["\']?)(.+?)("\'?)(["\']?)>/sim';
                //$replacement = '<img${1}src="//cdn.optipic.io/site-6856/${2}"${3}>'; // 
                //$replacement = 'replaceImgsUrlCallback';
                //$content = preg_replace($pattern, $replacement,$content);
                
                /*
                // Подменяем url в тегах <img>
                // -----------------------------------------------------------------
                $imgAttrs = $settings['img_attrs'];
                //var_dump($imgAttrs);exit;
                
                //var_dump($attrs);exit;
                if(false && count($imgAttrs)) {
                    
                    foreach($imgAttrs as $attr) {
                        
                        //$imgAttrs = implode("|", $imgAttrs);
                        
                        $patterns = array(
                            array(
                                'pattern' => '/<img([^>]+?)('.$attr.'?)="([^"]+\.(png|jpg|jpeg)?)"([^>]*?)>/simS',
                                'quote' => '"',
                            ),
                            array(
                                'pattern' => "/<img([^>]+?)(".$attr."?)='([^']+\.(png|jpg|jpeg)?)'([^>]*?)>/simS",
                                'quote' => "'",
                            ),
                        );
                        
                        foreach($patterns as $pattern) {
                            //var_dump($pattern);
                            $qoute = $pattern['quote'];
                            $content = preg_replace_callback(
                                $pattern['pattern'], 
                                function($matches) use ($dir, $settings, $qoute) {
                                    //var_dump($matches);exit;
                                    $quoteSymbol = '"';
                                    if($qoute) {
                                        $quoteSymbol = $qoute;
                                    }
                                    
                                    $url = $matches[3];
                                    //var_dump($matches);
                                    
                                    $optipicUrl = self::buildImgUrl($url, array('site_id'=>$settings['site_id']));
                                    //var_dump($optipicUrl);exit;
                                    
                                    if($optipicUrl==$url) {
                                        return $matches[0];
                                    }
                                    else {
                                        return '<img'.$matches[1].$matches[2].'='.$quoteSymbol.$optipicUrl.$quoteSymbol.$matches[5].'>';
                                    }
                                }, 
                                $content
                            );
                        }
                    }
                    //exit;
                }*/
                // -----------------------------------------------------------------
            }
            
            
        }
    }
    
    public static function buildImgUrl($localUrl, $params=array()) {
        global $APPLICATION;
        $dir = $APPLICATION->GetCurDir();
        
        $schema = "//";
        
        if(!isset($params['site_id'])) {
            $settings = self::getSiteSettins();
            $params['site_id'] = $settings['site_id'];
        }
        
        if(isset($params['url_schema'])) {
            if($params['url_schema']=='http') {
                $schema = "http://";
            }
            elseif($params['url_schema']=='https') {
                $schema = "https://";
            }
        }
        
            
        if($params['site_id']) {
            if(!strlen(trim($localUrl)) || stripos($localUrl, 'cdn.optipic.io')!==false) {
                return $localUrl;
            }
            elseif(stripos($localUrl, 'http://')===0) {
                return $localUrl;
            }
            elseif(stripos($localUrl, 'https://')===0) {
                return $localUrl;
            }
            elseif(stripos($localUrl, '//')===0) {
                return $localUrl;
            }
            else {
                
                // если URL не абсолютный - приводим его к абсолютному
                if(substr($localUrl, 0, 1)!='/') {
                    $localUrl = $dir.$localUrl;
                }
                
                $url = $schema.'cdn.optipic.io/site-'.$params['site_id'];
                if(isset($params['q'])) {
                    $url .= '/optipic-q='.$params['q'];
                }
                if(isset($params['maxw'])) {
                    $url .= '/optipic-maxw='.$params['maxw'];
                }
                if(isset($params['maxh'])) {
                    $url .= '/optipic-maxh='.$params['maxh'];
                }
                
                $url .= $localUrl;
                
                return $url;
                
                //return '<img'.$matches[1].'src='.$quoteSymbol.'//cdn.optipic.io/site-'.$settings['site_id'].$url.$quoteSymbol.$matches[3].'>';
            }
        }
        // Если URL 
        else {
            return $localUrl;
        }
        
        
    }
}

?>