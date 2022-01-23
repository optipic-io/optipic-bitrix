<?

IncludeModuleLangFile(__FILE__);

global $DB, $APPLICATION, $MESS, $DBType;
CModule::AddAutoloadClasses(
    "step2use.optimg",
    array(
        "S2uIblockTpl" => "classes/$DBType/S2uIblockTpl.php",
        "CdnOptiPic" => "classes/CdnOptiPic.php", 
    )
);

Class CStepUseOptimg
{

    const API_URL = "https://api.optipic.io/api/";
	const TEST_API_URL = "https://optipic.io/api/";

    public static function fromUtf($string) {
        if(defined('BX_UTF')) {
            return $string;
        }
        else {
            return mb_convert_encoding($string, 'windows-1251', 'utf-8');
        }
    }

	function OnBuildGlobalMenu(&$aGlobalMenu, &$aModuleMenu)
	{
		if($GLOBALS['APPLICATION']->GetGroupRight("main") < "R")
			return;

		$MODULE_ID = basename(dirname(__FILE__));
		

        $items = [];
        $items[] = array(
            'text' => GetMessage('S2U_SETTING_MENU_TITLE'),
            'url' => "settings.php?mid={$MODULE_ID}&mid_menu=1",
            'module_id' => $MODULE_ID,
            "title" => $MODULE_ID . ": " . GetMessage('S2U_SETTING_MENU_TITLE'),
        );

		if (file_exists($path = dirname(__FILE__).'/admin'))
		{
			if ($dir = opendir($path))
			{
				$arFiles = array();

				while(false !== $item = readdir($dir))
				{
					if (in_array($item,array('.','..','menu.php')))
						continue;

					if (!file_exists($file = $_SERVER['DOCUMENT_ROOT'].'/bitrix/admin/'.$MODULE_ID.'_'.$item))
						file_put_contents($file,'<'.'? require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/'.$MODULE_ID.'/admin/'.$item.'");?'.'>');

					$arFiles[] = $item;
				}

				sort($arFiles);

				foreach($arFiles as $item){
                    if($item != 's2u_iblock_template_edit.php' /*&& $item != 's2u_iblock_templates.php'*/){
                        $itemParams = array(
                            'text' => GetMessage($item),
                            'url' => $MODULE_ID.'_'.$item,
                            'module_id' => $MODULE_ID,
                            "title" => GetMessage($item),
                        );

                        if($item == 's2u_iblock_templates.php'){
                            $itemParams['more_url'] = array("step2use.optimg_s2u_iblock_template_edit.php");
                        }

                        $items[] = $itemParams;
                    }
                }

			}
		}
        
        $aMenu = array(
			//"parent_menu" => "global_menu_services",
			"parent_menu" => "global_menu_settings",
			"section" => $MODULE_ID,
			"sort" => 50,
			"text" => 'OptiPic',
			"title" => 'OptiPic',
			"icon" => "s2u_optipic_icon",
			"page_icon" => "",
			"items_id" => $MODULE_ID."_items",
			"more_url" => array(),
			"items" => array(
                array(
                    'text' => GetMessage('S2U_CDN_MODE'),
                    'url' => "settings.php?mid={$MODULE_ID}&cdn=1&mid_menu=1",
                    'module_id' => $MODULE_ID,
                    "title" => $MODULE_ID . ": " . GetMessage('S2U_SETTING_MENU_TITLE'),
                ),
                array(
                    'text' => GetMessage('S2U_CLASSIC_MODE'),
                    'url' => "settings.php?mid={$MODULE_ID}&mid_menu=1",
                    'module_id' => $MODULE_ID,
                    "title" => $MODULE_ID . ": " . GetMessage('S2U_SETTING_MENU_TITLE'),
                    "items" => $items,
                ),
                array(
                    'text' => GetMessage('S2U_MODE_DIFF'),
                    'url' => "https://optipic.io/ru/cdn/diff/",
                    'module_id' => $MODULE_ID,
                    "title" => $MODULE_ID . ": " . GetMessage('S2U_MODE_DIFF_MENU_TITLE'),
                ),
                
            )
		);
        
		$aModuleMenu[] = $aMenu;
	}

    public static function getRecommendedTariff($bytesNeeded) {
        
        $url = self::getApiUrl()."recommendmetariff?bytes=" . $bytesNeeded;
        
        $login = self::getLogin();
        $password = self::getPassword();
        
        $recommendData = '{}';
        $info  = array();
        
        if (function_exists('curl_init')) {
            $ch = curl_init();

            // ��������� URL � ������ ����������� ����������
            curl_setopt($ch, CURLOPT_URL, $url);

            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_POSTREDIR, 3); // ����� POST-������ ������������ � ��� ���������

            curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
            curl_setopt($ch, CURLOPT_USERPWD, $login.":".$password);

            // �������� �������� � ������ � ��������
            $recommendData = curl_exec($ch);

            $info = curl_getinfo($ch);
            
        }
        else {
            $httpClient = new \Bitrix\Main\Web\HttpClient();
            $httpClient->setAuthorization($login, $password);
            $recommendData = $httpClient->post($url);
            $info["http_code"] = $httpClient->getStatus();
        }
        
        return json_decode($recommendData, true);
    }

	public static function ClearindexFileBase() {
	    global $DB;
	    $DB->Query("TRUNCATE TABLE atl_optimg_files");
	}

	public static function ReindexFileBase($byStep=false, $lastProcessed=false) {
	    global $DB;

        $arSites = array();
        $dbSites = CSite::GetList($by="sort", $order="desc", Array("ACTIVE" => "Y"));
        while($dbSite = $dbSites->GetNext()){
            $arSites[] = $dbSite;
        }

        //echo "<pre>"; print_r($arSites); echo "</pre>";
	    $path       = $_SERVER['DOCUMENT_ROOT']; // ���� � ���������� � �������������
        //echo "<pre>"; print_r($path); echo "</pre>";
        //die();
        //$extensions = array('png', 'jpg', 'jpeg'); // ���������� ���������� // self::ARR_EXTENSIONS

        $extensionsStr = COption::GetOptionString("step2use.optimg", "EXTENSIONS_WHITE_LIST");
        $extensions = explode(',', $extensionsStr);

        if(!$extensions){
            $extensions = array('png', 'jpg', 'jpeg');
        }

        $directoryIterator = new RecursiveDirectoryIterator($path, RecursiveDirectoryIterator::SKIP_DOTS | RecursiveDirectoryIterator::FOLLOW_SYMLINKS);
        $iteratorIterator  = new RecursiveIteratorIterator($directoryIterator, RecursiveIteratorIterator::LEAVES_ONLY, RecursiveIteratorIterator::CATCH_GET_CHILD);

        //$start = microtime(true); 
        $start = time();

        if(isset($lastProcessed) && $lastProcessed!==false) {
            $alreadySkippedLastProcessedFile = false;
        }
        else $alreadySkippedLastProcessedFile = true;
        //var_dump($alreadySkippedLastProcessedFile); 

        // ���������
        $ignorePaths = self::getIgnorelist();
        // ������������� ������
        $indexOnlyPaths = self::getIndexOnlyPaths();

        $i=0;
        foreach ($iteratorIterator as $file) {

            $fileExt = pathinfo($file->getFilename(), PATHINFO_EXTENSION);
            $fileExt = ToLower($fileExt); // ��������������������� ���������� (PNG --> png)
            if (in_array($fileExt, $extensions)) {

                $path = str_replace($_SERVER['DOCUMENT_ROOT'], "", $file->getPathname());

                $pathMD5 = $DB->ForSql(md5($path));

                if($byStep && !$alreadySkippedLastProcessedFile && $DB->ForSql($path)==$lastProcessed) {
                    $alreadySkippedLastProcessedFile = true;
                }

                if($alreadySkippedLastProcessedFile===false) {
                    continue;
                }
                
                // ���������� ����, ���� �� �� �������� � ������
                /*if(self::isFileInIndexScope($path)===false) {
                    continue;
                }*/
                if(self::getCompressOptionsByFilepath($path)===false) {
                    continue;
                }
                
                // ���������� ����, ���� � ���� ��� ������� ���� �� ������
                if(!is_readable($_SERVER['DOCUMENT_ROOT']. $path)){
                    continue;
                }



                $pathSQL = $DB->ForSql($path);

                $size = $DB->ForSql($file->getSize());
                //$mtime = $DB->ForSql(date("Y-m-d H:i:s", $file->getMTime()));
                $mtime = $DB->ForSql($file->getMTime());
                //var_dump($mtime);exit;


                $alreadyCompressed = 'N';
                $sizeCompressed = 0;
                $compressTime = 0;
                if(file_exists($file->getPathname().".optipic-orig")) {
                    $alreadyCompressed = 'Y';
                    $sizeCompressed = $size;
                    $size = filesize($file->getPathname().".optipic-orig");
                    $compressTime = date("Y-m-d H:i:s", $mtime);
                }
				// ������ ���������� ��� �������������
				if(file_exists($file->getPathname().".optimg-orig")) {
                    $alreadyCompressed = 'Y';
                    $sizeCompressed = $size;
                    $size = filesize($file->getPathname().".optimg-orig");
                    $compressTime = date("Y-m-d H:i:s", $mtime);
                }

                /*
                 * this is for debug purposes temp
                 *
                 * if($pathMD5 == '81d06e2cfc2ca68a65e6c8799efbce94'){
                    $sizeCompressed = $size;
                    $compressTime = date("Y-m-d H:i:s", strtotime("-5 day"));
                }*/



                $DB->Query("INSERT IGNORE INTO atl_optimg_files
                    (PATH_MD5, PATH, SIZE_ORIGINAL, MTIME, ALREADY_PROCESSED_TODAY, SIZE_COMPRESSED, LAST_COMPRESSED_DATE)
                    VALUES ('$pathMD5', '$pathSQL', '$size', '$mtime', '$alreadyCompressed', '$sizeCompressed', '$compressTime')");

                // ���������, ���� ����������� � ����� �� ��� �����
                $timePass = time()-$start;
                //var_dump($timePass);
                $stepLimit = COption::GetOptionInt("step2use.optimg", "REINDEX_STEP_LIMIT_IN_SECONDS", 20);
                if($byStep && ($timePass > $stepLimit)) {
                    return $path;

                    /*array(
                        'result' => 'success',
                        'path' => $path
                    );*/
                }

                //echo '<img src="' . $file->getPathname() . '">';
                //$i++;
                //optimg($file->getPathname());
                //echo $file->getPathname()."<br/>\n";
                //if($i>100) break;
            }
        }

        /*
         * ����� �� �������� MTIME ��� ��� ������, ������� �� ���������� �����
         */

        $DB->Query("UPDATE atl_optimg_files SET MTIME=0, ALREADY_PROCESSED_TODAY='N' WHERE (SIZE_ORIGINAL = SIZE_COMPRESSED OR SIZE_COMPRESSED/SIZE_ORIGINAL > 0.9) AND (LAST_COMPRESSED_DATE < NOW() - INTERVAL 5 DAY OR LAST_COMPRESSED_DATE = 0)");

        //echo "NUM: $i<br/>\n";
        //echo "����� ���������� �������: ".(microtime(true) - $start);
        return true;
	}

	public static function GetHumanFilesize($bytes, $decimals = 2) {
        $sz = 'BKMGTP';
        $factor = floor((strlen($bytes) - 1) / 3);
        return sprintf("%.{$decimals}f ", $bytes / pow(1024, $factor)) . @$sz[$factor];
    }

	public static function GetFilesCount() {
	    global $DB;
	    $res = $DB->Query("SELECT COUNT(*) as CNT FROM atl_optimg_files");
	    $res = $res->Fetch();
	    //var_dump($res);
	    return $res["CNT"];
	}

	public static function GetSumOriginSize() {
	    global $DB;
	    $res = $DB->Query("SELECT SUM(SIZE_ORIGINAL) as CNT FROM atl_optimg_files", false, $err_mess.__LINE__);
	    $res = $res->Fetch();
	    //var_dump($res);
	    return $res["CNT"];
	}

	public static function GetSumCompressedSize() {
	    global $DB;
	    $res = $DB->Query("SELECT SUM(SIZE_COMPRESSED) as CNT FROM atl_optimg_files", false, $err_mess.__LINE__);
        // WHERE ALREADY_PROCESSED_TODAY='Y'
	    $res = $res->Fetch();
	    return $res["CNT"];
	}

    public function GetSizeLeftToCompress(){
        global $DB;
        $res = $DB->Query("SELECT SUM(SIZE_ORIGINAL) as CNT FROM atl_optimg_files WHERE SIZE_COMPRESSED=0", false, $err_mess.__LINE__);
        // ALREADY_PROCESSED_TODAY='N'
        $res = $res->Fetch();
        //var_dump($res);
        return $res["CNT"];
    }

	public static function GetFilesProcessedCount() {
	    global $DB;
	    $res = $DB->Query("SELECT COUNT(*) as CNT FROM atl_optimg_files WHERE SIZE_COMPRESSED!=0");
        // ALREADY_PROCESSED_TODAY='Y'
	    $res = $res->Fetch();
	    //var_dump($res);
	    return $res["CNT"];
	}

    public static function GetUncompressedFilesCount() {
        global $DB;
        $res = $DB->Query("SELECT COUNT(*) as CNT FROM atl_optimg_files WHERE SIZE_COMPRESSED=0", false, $err_mess.__LINE__);
        // ALREADY_PROCESSED_TODAY='N'
        $res = $res->Fetch();
        //var_dump($res);
        return $res["CNT"];
    }

	public static function GetCompressedPercent() {
	    global $DB;
	    $res = $DB->Query("SELECT (SUM(SIZE_ORIGINAL)-SUM(SIZE_COMPRESSED))/SUM(SIZE_ORIGINAL)*100 as CNT FROM atl_optimg_files WHERE SIZE_COMPRESSED>0;", false, $err_mess.__LINE__);
	    $res = $res->Fetch();
	    //var_dump($res);
	    return (float) $res["CNT"];
	}

	public static function GetApiEfficiency() {
        
        $url = self::getApiUrl()."geteffective";
        
        $login = self::getLogin();
        $password = self::getPassword();
        
        $efficiencyData = '{}';
        $info  = array();
        
        if (function_exists('curl_init')) {
            $ch = curl_init();

            // ��������� URL � ������ ����������� ����������
            curl_setopt($ch, CURLOPT_URL, $url);

            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_POSTREDIR, 3); // ����� POST-������ ������������ � ��� ���������

            curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
            curl_setopt($ch, CURLOPT_USERPWD, $login.":".$password);



            // �������� �������� � ������ � ��������
            $efficiencyData = curl_exec($ch);

            $info = curl_getinfo($ch);
        }
        else {
            $httpClient = new \Bitrix\Main\Web\HttpClient();
            $httpClient->setAuthorization($login, $password);
            $efficiencyData = $httpClient->post($url);
            $info["http_code"] = $httpClient->getStatus();
        }
        
        
        return json_decode($efficiencyData, true);
    }

	public static function GetOrigFilesCount() {
        $directoryIterator = new RecursiveDirectoryIterator($_SERVER['DOCUMENT_ROOT'], RecursiveDirectoryIterator::SKIP_DOTS | RecursiveDirectoryIterator::FOLLOW_SYMLINKS);
        $iteratorIterator  = new RecursiveIteratorIterator($directoryIterator, RecursiveIteratorIterator::LEAVES_ONLY);

        $extensions = array('optipic-orig', 'optimg-orig'); // ���������� ����������
        $count = 0;
        foreach ($iteratorIterator as $file) {
            $fileExt = pathinfo($file->getFilename(), PATHINFO_EXTENSION);
            if (in_array($fileExt, $extensions)) {
                $count++;
            }
        }
        return $count;

        /*
        global $DB;
	    $res = $DB->Query("SELECT SUM(SIZE_ORIGINAL) as CNT FROM atl_optimg_files WHERE SIZE_ORIGINAL>0", false, $err_mess.__LINE__);
	    $res = $res->Fetch();
	    //var_dump($res);
	    return $res["CNT"];*/
    }

	public static function ReturnOrigFiles($byStep=false) {
        global $DB;
        
        // �� ������� ���������� ���������� �� 1 ���
        $stepLimit = COption::GetOptionInt("step2use.optimg", "RETURN_ORIGS_BY_STEP", 10);
        

	    $start = time();

        $directoryIterator = new RecursiveDirectoryIterator($_SERVER['DOCUMENT_ROOT'], RecursiveDirectoryIterator::SKIP_DOTS | RecursiveDirectoryIterator::FOLLOW_SYMLINKS);
        $iteratorIterator  = new RecursiveIteratorIterator($directoryIterator, RecursiveIteratorIterator::LEAVES_ONLY);

        $extensions = array('optipic-orig', 'optimg-orig'); // ���������� ����������

        foreach ($iteratorIterator as $file) {
            
            // ������� �������� ���� - ���� �������, /bitrix/cache/ � �.�.
            $internalIgnorelist = self::getInternalIgnorelist();
            $localPathDir = pathinfo($file->getFilename(), PATHINFO_DIRNAME);
            $ignoreThis = false;
            foreach($internalIgnorelist as $ignorePath) {
                if($ignorePath && strpos($localPathDir, $ignorePath)===0) {
                    $ignoreThis = true;
                }
            }
            if($ignoreThis) {
                continue;
            }
            
            $fileExt = pathinfo($file->getFilename(), PATHINFO_EXTENSION);
            if(in_array($fileExt, $extensions)) {
				$renamed = false;
				
				// ���� � ������ �����, � ������� ����� ������������ ��������
				$newPath = str_replace(array('.optipic-orig', '.optimg-orig'), '', $file->getPathname());

                //echo '<img src="' . $file->getPathname() . '">';
                $i++;
                //optimg($file->getPathname());
                //echo $file->getPathname()."<br/>\n";
                //if($i>100) break;
                if(file_exists($newPath) && is_writable($newPath) && is_writable($file->getPathname())){
                    
                    $filePerms = fileperms($file->getPathname());
                    $fileOwner = fileowner($file->getPathname());
                    $fileGroup = filegroup($file->getPathname());
                    $renamed = rename($file->getPathname(), $newPath);

                    @chmod($newPath, $filePerms); // �� �������� ������� ���������� ����� �� ����� ����
                    @chown($newPath, $fileOwner); // ������ ��������� ����� ��, ��� � ��������� �����
                    @chgrp($newPath, $fileGroup); // ������ ������ ����� ��, ��� � ��������� �����
                }
                // ���������� ������, ����� ����� ���������� ��� ������ ��� ������ ��� �� ���������� - ����� �������� ������������ ��������
                else {
                    continue;
                }


                if($renamed) {
                    $path = str_replace($_SERVER['DOCUMENT_ROOT'], "", $newPath);
                    clearstatcache(true, $newPath); // ������ ��� ���������� �� ����� - ����� php ������ ������ mtime � size

                    $pathMD5 = $DB->ForSql(md5($path));
                    $newSize = filesize($newPath);
                    $newMtime = filemtime($newPath);

                    $DB->Query("UPDATE atl_optimg_files set SIZE_COMPRESSED=0, SIZE_ORIGINAL='$newSize', MTIME='$newMtime', ALREADY_PROCESSED_TODAY='N', LAST_COMPRESSED_DATE=0 WHERE PATH_MD5='".$pathMD5."'");
                }

                //unlink($file->getPathname());

                $timePass = time()-$start;
                // $timePass>1
                if($byStep && $i>$stepLimit) {
                    return str_replace($_SERVER["DOCUMENT_ROOT"], "", $file->getPathname());
                }
            }
        }

        return true;
    }

    // delete orig files

    public static function DeleteOrigFiles($byStep=false) {
        global $DB;


        $start = time();

        $directoryIterator = new RecursiveDirectoryIterator($_SERVER['DOCUMENT_ROOT'], RecursiveDirectoryIterator::SKIP_DOTS | RecursiveDirectoryIterator::FOLLOW_SYMLINKS);
        $iteratorIterator  = new RecursiveIteratorIterator($directoryIterator, RecursiveIteratorIterator::LEAVES_ONLY);

        $extensions = array('optipic-orig', 'optimg-orig'); // ���������� ����������

        $i = 0;


        foreach ($iteratorIterator as $file) {

            $fileExt = pathinfo($file->getFilename(), PATHINFO_EXTENSION);



            if (in_array($fileExt, $extensions)) {
                

				$filepath = $file->getPathname();

				if(file_exists($filepath) && is_writable($filepath)) {
					unlink($filepath);
					$i++;
				}

                $timePass = time()-$start;
                // $timePass>1
                if($byStep && $i>10) {
                    return str_replace($_SERVER["DOCUMENT_ROOT"], "", $file->getPathname());
                }
            }
        }

        return true;
    }

	public static function OptimizeAllImgs($nolimit=false, $agent=true) {
	    global $DB;

	    // ����� ���� ����� ������������� � ���������� ������?
	    if($agent && COption::GetOptionString("step2use.optimg", "AGENT_ACTIVE", "N")=="N") {
	        return "CStepUseOptimg::OptimizeAllImgs();";
	    }
        
        // ������ �� ������� �������-������
        // ���� ������ ������ ����������� � �������� 40 ������ ����� - �� ������� ��� �����-�� ����� ��� ������� � �� ��� ��� �� ��������� �� �����
        $lastCompressDone = COption::GetOptionString("step2use.optimg", "last_compress_done");
        if($agent && date('Y-m-d H:i:s', time()-40) <= $lastCompressDone) {
            return "CStepUseOptimg::OptimizeAllImgs();";
        }

        //AddMessage2Log(date("d.m.Y H:i:s").' /START/', "step2use.optimg");
        /////////////////////////
        /*AddMessage2Log(date("d.m.Y H:i:s").' /START/', "step2use.optimg");
        //sleep(60*10);
        
        for($jj=0;$jj<20;$jj++) {
            COption::SetOptionString("step2use.optimg", "last_compress_done", date('Y-m-d H:i:s'));
            sleep(30);
        }
        
        AddMessage2Log(date("d.m.Y H:i:s").' /END/', "step2use.optimg");
        */
        /////////////////////////
        
        // ����� - ������� ������� ����� ������ �� 1 ������ �������
        $limit = COption::GetOptionInt("step2use.optimg", "COMPRESS_LIMIT_BY_STEP");
        $limit = intval($limit);
        
	    $i = 0;
        
        $sql = "SELECT * FROM atl_optimg_files WHERE ALREADY_PROCESSED_TODAY='N'";
        // ���� ������� �������� � ����� ���, �� ����������� �� �� ������ $limit, ������������ �� � ��������� �������
        if(!$nolimit && $limit) {
            $sql .= " LIMIT $limit";
        }
        // ���� ������� ��� ����������� (�.�. �� 1 ���), �� ����������� �� 100 ������� �� �� �� ������������ �����, ���� �� ��������� �������� ������� � ��
        else {
            $sql .= " LIMIT 100";
        }
        
        
        
        while(true) {
            
            // ����� ���� ����� ��� ������������� � ���������� ������?
            if($agent && COption::GetOptionString("step2use.optimg", "AGENT_ACTIVE", "N")=="N") {
                return "CStepUseOptimg::OptimizeAllImgs();";
            }
            
            // ��� �� ��������� ���� ���� ���� �� ��������� ���
            $hadFilesByLastStep = false;
            
            //AddMessage2Log($sql, "step2use.optimg");
            
            //AddMessage2Log(date("d.m.Y H:i:s").' /LOOP 1/', "step2use.optimg");
            $res = $DB->Query($sql, false, $err_mess.__LINE__);
            while($fileDB = $res->Fetch()) {
                $hadFilesByLastStep = true;
                
                //AddMessage2Log(date("d.m.Y H:i:s").' /LOOP 2/', "step2use.optimg");
                $fileFS = array();
                $fileFS["PATH"] = $_SERVER['DOCUMENT_ROOT'].$fileDB["PATH"];
                $fileFS["MTIME"] = filemtime($fileFS["PATH"]);
                $fileFS["SIZE"] = filesize($fileFS["PATH"]);

                if(file_exists($fileFS["PATH"]) && ($fileDB["SIZE_COMPRESSED"]!=$fileFS["SIZE"] || $fileDB["MTIME"]!=$fileFS["MTIME"])) {
                    $successCompressed = self::Optimg($fileFS["PATH"]);
                    
                    // ��������� ����� ���������� ������
                    COption::SetOptionString("step2use.optimg", "last_compress_done", date('Y-m-d H:i:s'));
                    
                    if($successCompressed) {
                        // ���� ���� ������� - ��������� ���������� � ������� �������
                        clearstatcache(true, $fileFS["PATH"]); // ������ ��� ���������� �� ����� - ����� php ������ ������ mtime � size
                        $newMtime = $DB->ForSql(filemtime($fileFS["PATH"]));
                        $newSize = $DB->ForSql(filesize($fileFS["PATH"]));
                        //var_dump($fileFS["SIZE"]);
                        //var_dump($newSize);

                        //// �����-�� ��������� ������, ����� ������ ���� ��������� ������, ��� ��������
                        //if($newSize>$fileFS["SIZE"]) {
                        //    copy($fileFS["PATH"].".optimg-orig", $fileFS["PATH"]); // ���������� �������� ���� ����
                        //    $newSize =  $DB->ForSql(filesize($fileFS["PATH"].".optimg-orig")); // � ������ ����� �� ����-�����
                        //    var_dump("BAD");
                        //}

                        // ��������� ���������� � ����� � ��
                        $DB->Query("UPDATE atl_optimg_files set SIZE_COMPRESSED='$newSize', MTIME='$newMtime',WRITE_ERROR=0, ALREADY_PROCESSED_TODAY='Y', LAST_COMPRESSED_DATE=UTC_TIMESTAMP() WHERE PATH_MD5='".$DB->ForSql($fileDB["PATH_MD5"])."'");
                    }
                    else {
                        // ���� �� ��� ���� ������� - ��������� ���������� � ������� �������, ����� ���� �� ������� ���������� ��� ��������������
                        $DB->Query("UPDATE atl_optimg_files set SIZE_COMPRESSED='".$fileFS["SIZE"]."', MTIME='".$fileFS["MTIME"]."', WRITE_ERROR=1, ALREADY_PROCESSED_TODAY='Y' WHERE PATH_MD5='".$DB->ForSql($fileDB["PATH_MD5"])."'");
                    }
                    $i++;

                    // ����� ���� ���� �������� � API? ����� ����� ����� 1�� ��������� � API ��������� �������
                    if(COption::GetOptionString("step2use.optimg", "API_ERROR")=="Y") {
                        return "CStepUseOptimg::OptimizeAllImgs();";
                    }
                    
                    if(!$agent && !$nolimit && $limit && $i>$limit) {
                        return "CStepUseOptimg::OptimizeAllImgs();";
                    }
                }
                elseif(file_exists($fileFS["PATH"]) && $fileDB["SIZE_COMPRESSED"]==$fileFS["SIZE"] && $fileDB["MTIME"]==$fileFS["MTIME"]) {
                    // ��������� ���������� � ����� � ��
                    $DB->Query("UPDATE atl_optimg_files set ALREADY_PROCESSED_TODAY='Y' WHERE PATH_MD5='".$DB->ForSql($fileDB["PATH_MD5"])."'");
                    //$i++;
                }

                // ���� ��� �� ���������� ����� ���� � �� - ������� ��� �� �������
                if(file_exists($fileFS["PATH"])==false) {
                    $DB->Query("DELETE FROM atl_optimg_files WHERE PATH_MD5='".$DB->ForSql($fileDB["PATH_MD5"])."'");
                    //var_dump("DEL");
                }

                //var_dump($fileDB);var_dump($fileFS);exit;
            }
            
            //AddMessage2Log(date("d.m.Y H:i:s").' /LOOP 1 pre end/', "step2use.optimg");
            
            // ���� � �������� ������ - �� ������� �� ������������ ����� ����� ����� 1�� ����
            if(!$nolimit && $limit) {
                //AddMessage2Log(date("d.m.Y H:i:s").' /LOOP 1 break/', "step2use.optimg");
                break;
            }
            
            // ���� � ����������� ������ �� ��������� ��� �� ���� ���������� �� 1 �����, ������ ��� ����� ��� ���������� � ����� �������� ����������� ����
            if(!$hadFilesByLastStep) {
                break;
            }
        }
	/*
        $path       = $_SERVER['DOCUMENT_ROOT']; // ���� � ���������� � �������������
        $extensions = array('png', 'jpg', 'jpeg'); // ���������� ����������

        $directoryIterator = new RecursiveDirectoryIterator($path, RecursiveDirectoryIterator::SKIP_DOTS | RecursiveDirectoryIterator::FOLLOW_SYMLINKS);
        $iteratorIterator  = new RecursiveIteratorIterator($directoryIterator, RecursiveIteratorIterator::LEAVES_ONLY);

        $start = microtime(true);

        $dirs = array();

        $i=0;
        foreach ($iteratorIterator as $file) {
            if (in_array($file->getExtension(), $extensions)) {
                //echo '<img src="' . $file->getPathname() . '">';
                $i++;
                optimg($file->getPathname());
                //echo $file->getPathname()."<br/>\n";
                //if($i>100) break;
            }
        }
*/
        //echo "NUM: $i<br/>\n";
        //echo "����� ���������� �������: ".(microtime(true) - $start);
        return "CStepUseOptimg::OptimizeAllImgs();";
    }
    
    public static function getInternalIgnorelist() {
        return array(
            "/bitrix/wizards/",
            "/bitrix/modules/",
            "/bitrix/themes/",
            "/bitrix/components/bitrix/",
            "/bitrix/panel/",
            "/bitrix/images/",
            "/bitrix/js/",
            "/bitrix/cache/"
        );
    }
    
    /**
     * ���������� ������ ������������ �����
     */
    public static function getIgnorelist() {
        // �����
        // ----------------------------------
        // ��� �������� ����
        $ignorePathsInternal = self::getInternalIgnorelist();

        $strIgnorePath = COption::GetOptionString("step2use.optimg", "IGNORE_PATH");
        $ignorePaths = ($strIgnorePath)? explode("\n", $strIgnorePath): array();
        // ���������� ��������� ���� � ����, ��� ������ � ���������� ������
        $ignorePaths = array_merge($ignorePathsInternal, $ignorePaths);
        foreach($ignorePaths as $pK=>$pV) {
            $ignorePaths[$pK] = trim($pV);
        }
        // ----------------------------------
        
        return $ignorePaths;
    }
    
    /**
     * ���������� ������ ����� "������������� ������"
     */
    public static function getIndexOnlyPaths() {

        $strIndexOnlyPaths = COption::GetOptionString("step2use.optimg", "INDEX_ONLY");
        $indexOnlyPaths = ($strIndexOnlyPaths)? explode("\n", $strIndexOnlyPaths): array();
        if($indexOnlyPaths){
            foreach($indexOnlyPaths as $iK=>$iV) {
                $indexOnlyPaths[$iK] = trim($iV);
            }
        }
        
        return $indexOnlyPaths;
    }
    
    /**
     * ������ ��������� ���� �� ������� ���� - �������� DOCUMENT_ROOT
     */
    public static function getLocalPath($path) {
        if(strpos($path, $_SERVER['DOCUMENT_ROOT'])===0) {
            $path = str_replace($_SERVER['DOCUMENT_ROOT'], "", $path);
        }
        return $path;
    }
    
    /**
     * �������� �� ���� � ������ ������?
     * ����������� ��������� ����� � ��������� � � "����������� ������"
     */
    public static function isFileInIndexScope($path, $deleteOutOfScope=true) {
        global $DB;
        
        // ��������� ����������
        $fileExt = pathinfo($path, PATHINFO_EXTENSION);
        $fileExt = ToLower($fileExt); // ��������������������� ���������� (PNG --> png)
        $extensionsStr = COption::GetOptionString("step2use.optimg", "EXTENSIONS_WHITE_LIST");
        $extensions = explode(',', $extensionsStr);
        if(!$extensions){
            $extensions = array('png', 'jpg', 'jpeg');
        }
        
        
        // ������ ��������� ���� �� ������� ���� - �������� DOCUMENT_ROOT
        $path = self::getLocalPath($path);
        
        $pathMD5 = $DB->ForSql(md5($path));
        
        
        // ���������� �����, �� ��������������� ��������� �����������
        if(!in_array($fileExt, $extensions)) {
            if($deleteOutOfScope) {
                //$DB->Query("DELETE from atl_optimg_files WHERE PATH_MD5 = '$pathMD5'");
                self::deleteFileFromIndex($path);
            }
            return false;
        }
        
        $ignorePaths = self::getIgnorelist();
        $indexOnlyPaths = self::getIndexOnlyPaths();
        
        // ���������� �����, ������� � ������
        $ignoreThisPath = false;
        foreach($ignorePaths as $ignorePath) {
            if($ignorePath && strpos($path, $ignorePath)===0) {
                $ignoreThisPath = true;
            }
        }
        if($ignoreThisPath) {
            if($deleteOutOfScope) {
                //$DB->Query("DELETE from atl_optimg_files WHERE PATH_MD5 = '$pathMD5'");
                self::deleteFileFromIndex($path);
            }
            return false;
        }

        if($indexOnlyPaths){
            $indexThisPath = false;

            foreach($indexOnlyPaths as $indexPath) {

                if($indexPath && strpos($path, $indexPath)===0) {
                    $indexThisPath = true;
                }
            }

            if(!$indexThisPath) {
                if($deleteOutOfScope) {
                    //$DB->Query("DELETE from atl_optimg_files WHERE PATH_MD5 = '$pathMD5'");
                    self::deleteFileFromIndex($path);
                }
                return false;
            }
        }
        
        
        
        return true;
    }
    
    /**
     * ������� ���� �� �������
     */
    public static function deleteFileFromIndex($filepath) {
        global $DB;
        
        $path = self::getLocalPath($filepath);
        
        $pathMD5 = $DB->ForSql(md5($path));
        
        $DB->Query("DELETE from atl_optimg_files WHERE PATH_MD5 = '$pathMD5'");
        
        return true;
    }
    
    /**
     * �������� ��������� ������ �����
     * ���� ������������ false ������ ���� ��������� � ������ ������������ � ��� ������� �� �����
     */
    public static function getCompressOptionsByFilepath($filepath) { //  isFileInIndexScopeByIblockTpl
        // ������ ��������� ���� �� ������� ���� - �������� DOCUMENT_ROOT
        $localPath = self::getLocalPath($filepath);
        
        // ���������, ������ �� ���� ������ ���� � �������
        if(self::isFileInIndexScope($filepath)===false) {
            return false;
        }
        
        $quality = COption::GetOptionInt("step2use.optimg", "COMPRESS_QUALITY");
        $maxwidth = COption::GetOptionInt("step2use.optimg", "MAX_WIDTH");
        $maxheight = COption::GetOptionInt("step2use.optimg", "MAX_HEIGHT");
        
        $ignoreThis = false;
        
        if(strpos($localPath, '/upload/')===0 && strpos($localPath, '/upload/resize_cache/')===false && S2uIblockTpl::GetCount()>0) {
            
            
            $fileIblockInfo = S2uIblockTpl::getFileIblock($filepath);
            //file_put_contents($_SERVER['DOCUMENT_ROOT'].'/optipic.txt', var_export($localPath, true)."\n", FILE_APPEND);
            //file_put_contents($_SERVER['DOCUMENT_ROOT'].'/optipic.txt', var_export($fileIblockInfo, true)."\n-----------\n", FILE_APPEND);
            if($fileIblockInfo['IBLOCK']){
                $fileTemplateParams = S2uIblockTpl::GetIblockTemplateParams($fileIblockInfo['IBLOCK']);
                //file_put_contents($_SERVER['DOCUMENT_ROOT'].'/optipic.txt', var_export($fileTemplateParams, true), FILE_APPEND);
                if($fileTemplateParams){
                    $singleTplConfig = $fileTemplateParams[0];

                    if(!$singleTplConfig['IGNORE_THIS_IBLOCK']) {
                        if ($singleTplConfig['COMPRESS_QUALITY']) {
                            $quality = $singleTplConfig['COMPRESS_QUALITY'];
                        }
                        if ($singleTplConfig['MAX_WIDTH']) {
                            $maxwidth = $singleTplConfig['MAX_WIDTH'];
                        }
                        if ($singleTplConfig['MAX_HEIGHT']) {
                            $maxheight = $singleTplConfig['MAX_HEIGHT'];
                        }

                        switch ($fileIblockInfo['TYPE']):
                            case 'preview':
                                if (!$singleTplConfig['IGNORE_PREVIEW']) {
                                    if ($singleTplConfig['COMPRESS_QUALITY_PREVIEW']) {
                                        $quality = $singleTplConfig['COMPRESS_QUALITY_PREVIEW'];
                                    }
                                    if ($singleTplConfig['MAX_WIDTH_PREVIEW']) {
                                        $maxwidth = $singleTplConfig['MAX_WIDTH_PREVIEW'];
                                    }
                                    if ($singleTplConfig['MAX_HEIGHT_PREVIEW']) {
                                        $maxheight = $singleTplConfig['MAX_HEIGHT_PREVIEW'];
                                    }
                                }
                                else {
                                    //return false; // �������� ������������� ��������-������
                                    $ignoreThis = true;
                                }
                                break;
                            case 'detail':
                                if (!$singleTplConfig['IGNORE_DETAIL']) {
                                    if ($singleTplConfig['COMPRESS_QUALITY_DETAIL']) {
                                        $quality = $singleTplConfig['COMPRESS_QUALITY_DETAIL'];
                                    }
                                    if ($singleTplConfig['MAX_WIDTH_DETAIL']) {
                                        $maxwidth = $singleTplConfig['MAX_WIDTH_DETAIL'];
                                    }
                                    if ($singleTplConfig['MAX_HEIGHT_PREVIEW']) {
                                        $maxheight = $singleTplConfig['MAX_HEIGHT_DETAIL'];
                                    }
                                }
                                else {
                                    //return false; // �������� ������������� ��������� ��������
                                    $ignoreThis = true;
                                }
                                break;
                            case 'prop':
                                if (!$singleTplConfig['IGNORE_PROP']) {
                                    if ($singleTplConfig['COMPRESS_QUALITY_PROP']) {
                                        $quality = $singleTplConfig['COMPRESS_QUALITY_PROP'];
                                    }
                                    if ($singleTplConfig['MAX_WIDTH_PROP']) {
                                        $maxwidth = $singleTplConfig['MAX_WIDTH_PROP'];
                                    }
                                    if ($singleTplConfig['MAX_HEIGHT_PROP']) {
                                        $maxheight = $singleTplConfig['MAX_HEIGHT_PROP'];
                                    }
                                }
                                else {
                                    //return false; // �������� ������������� �������
                                    $ignoreThis = true;
                                }
                                break;

                        endswitch;
                        
                        
                    }
                    else {
                        //return false; // �������� ������������� ����� ���������
                        $ignoreThis = true;
                    }
                }
            }
        }
        
        // ���������� ����?
        if($ignoreThis) {
            self::deleteFileFromIndex($localPath); // ������� �� �������
            return false;
        }
        
        //return true;
        return array(
            'quality' => $quality,
            'maxwidth' => $maxwidth,
            'maxheight' => $maxheight,
        );
    }
    
    public static function Optimg($filepath) {

        if(!is_writable($filepath)){
            return false;
        }
        
        // ���������, ������ �� ���� ������ ���� � �������
        /*if(self::isFileInIndexScope($filepath)===false) {
            return false;
        }*/
        $options = self::getCompressOptionsByFilepath($filepath);
        
        $dump = "[".date("d.m.Y H:i:s")."] ".$filepath."\n";
        $dump .= var_export($options, true)."\n--------------------------\n";
        //file_put_contents($_SERVER['DOCUMENT_ROOT'].'/optipic.txt', $dump, FILE_APPEND);
        //file_put_contents(__DIR__ . '/optipic.txt', $dump, FILE_APPEND);
        
        if(!$options) {
            return false;
        }
        
        $quality = $options['quality']; //COption::GetOptionInt("step2use.optimg", "COMPRESS_QUALITY");
        $maxwidth = $options['maxwidth']; //COption::GetOptionInt("step2use.optimg", "MAX_WIDTH");
        $maxheight = $options['maxwidth']; //COption::GetOptionInt("step2use.optimg", "MAX_HEIGHT");
        
        
		$localPath = self::getLocalPath($filepath);
        
        //var_dump($filepath);
        

        

        // ��������� � ������ ��������� ����������� ����
        // �� ������ ��� ������ �� /upload/ (�� �� �� /upload/resize_cache/)
        /*
        if(strpos($localPath, '/upload/')===0 && strpos($localPath, '/upload/resize_cache/')===false && S2uIblockTpl::GetCount()>0) {
            $fileIblockInfo = S2uIblockTpl::getFileIblock($filepath);
            if($fileIblockInfo['IBLOCK']){
                $fileTemplateParams = S2uIblockTpl::GetIblockTemplateParams($fileIblockInfo['IBLOCK']);
                if($fileTemplateParams){
                    $singleTplConfig = $fileTemplateParams[0];

                    if(!$singleTplConfig['IGNORE_THIS_IBLOCK']) {
                        if ($singleTplConfig['COMPRESS_QUALITY']) {
                            $quality = $singleTplConfig['COMPRESS_QUALITY'];
                        }
                        if ($singleTplConfig['MAX_WIDTH']) {
                            $maxwidth = $singleTplConfig['MAX_WIDTH'];
                        }
                        if ($singleTplConfig['MAX_HEIGHT']) {
                            $maxheight = $singleTplConfig['MAX_HEIGHT'];
                        }

                        switch ($fileIblockInfo['TYPE']):
                            case 'preview':
                                if (!$singleTplConfig['IGNORE_PREVIEW']) {
                                    if ($singleTplConfig['COMPRESS_QUALITY_PREVIEW']) {
                                        $quality = $singleTplConfig['COMPRESS_QUALITY_PREVIEW'];
                                    }
                                    if ($singleTplConfig['MAX_WIDTH_PREVIEW']) {
                                        $maxwidth = $singleTplConfig['MAX_WIDTH_PREVIEW'];
                                    }
                                    if ($singleTplConfig['MAX_HEIGHT_PREVIEW']) {
                                        $maxheight = $singleTplConfig['MAX_HEIGHT_PREVIEW'];
                                    }
                                }
                                break;
                            case 'detail':
                                if (!$singleTplConfig['IGNORE_DETAIL']) {
                                    if ($singleTplConfig['COMPRESS_QUALITY_DETAIL']) {
                                        $quality = $singleTplConfig['COMPRESS_QUALITY_DETAIL'];
                                    }
                                    if ($singleTplConfig['MAX_WIDTH_DETAIL']) {
                                        $maxwidth = $singleTplConfig['MAX_WIDTH_DETAIL'];
                                    }
                                    if ($singleTplConfig['MAX_HEIGHT_PREVIEW']) {
                                        $maxheight = $singleTplConfig['MAX_HEIGHT_DETAIL'];
                                    }
                                }
                                break;
                            case 'prop':
                                if (!$singleTplConfig['IGNORE_PROP']) {
                                    if ($singleTplConfig['COMPRESS_QUALITY_PROP']) {
                                        $quality = $singleTplConfig['COMPRESS_QUALITY_PROP'];
                                    }
                                    if ($singleTplConfig['MAX_WIDTH_PROP']) {
                                        $maxwidth = $singleTplConfig['MAX_WIDTH_PROP'];
                                    }
                                    if ($singleTplConfig['MAX_HEIGHT_PROP']) {
                                        $maxheight = $singleTplConfig['MAX_HEIGHT_PROP'];
                                    }
                                }
                                break;

                        endswitch;
                    }
                }
            }
        }
        */

        $chUrl = self::getApiUrl()."compress?quality=".$quality."&maxwidth=".$maxwidth."&maxheight=".$maxheight;
        
        // ��� ��������� � GET
        $addGetOpts = array();
        // ������������� jpeg
        if('Y'==COption::GetOptionString("step2use.optimg", "OPT_PROGRESSIVE_JPEG", 'Y')) {
            $addGetOpts['jpegprogressive'] = 1;
        }
        else {
            $addGetOpts['jpegprogressive'] = 0;
        }
        // Striptags
        if('N'==COption::GetOptionString("step2use.optimg", "OPT_STRIPTAGS", 'Y')) {
            $addGetOpts['nostriptags'] = 1;
        }
        else {
            $addGetOpts['nostriptags'] = 0;
        }
        // ��������� ��� get-��������� � url
        if(count($chUrl)) {
            $chUrl .= '&'.http_build_query($addGetOpts);
        }
        
        $optiImgData = false;
        $info = array();
        
        if (function_exists('curl_init')) {
            // �������� ������ ������� cURL
            $ch = curl_init();
            // ��������� URL � ������ ����������� ����������
            curl_setopt($ch, CURLOPT_URL, $chUrl);
            
            $cfile = curl_file_create($filepath);
            //$imgData = file_get_contents(__DIR__.'/tmp/e52014dd32838a3de07669cd27feda93.png');
            $imgData = array('file' => $cfile, 'filepath' => $localPath);

            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $imgData);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_POSTREDIR, 3); // ����� POST-������ ������������ � ��� ���������

            curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
            curl_setopt($ch, CURLOPT_USERPWD, self::getLogin().":".self::getPassword());

            // �������� �������� � ������ � ��������
            $optiImgData = curl_exec($ch);

            $info = curl_getinfo($ch);
            
            // ���������� ������ � ������������ ��������
            curl_close($ch);
        }
        else {
            $httpClient = new \Bitrix\Main\Web\HttpClient();
            $httpClient->setAuthorization(self::getLogin(), self::getPassword());
            $imgData = array('file' => fopen($filepath, 'r'), 'filepath' => $localPath);
            $optiImgData = $httpClient->post($chUrl, $imgData, true);
            $info["http_code"] = $httpClient->getStatus();
        }
        
        //var_dump($info);exit;

        // ������������ ������� �� �����
        //if($info["http_code"]==402 && (COption::GetOptionString("step2use.optimg", "API_ERROR")=="N" || COption::GetOptionString("step2use.optimg", "LAST_API_ERROR")!=GetMessage("ATL_API_ERROR_PAYMENT"))) {
        if($info["http_code"]==402 && (COption::GetOptionString("step2use.optimg", "API_ERROR")=="N" || COption::GetOptionString("step2use.optimg", "LAST_API_ERROR")!=402)) {
            COption::SetOptionString("step2use.optimg", "API_ERROR", "Y");
            //COption::SetOptionString("step2use.optimg", "LAST_API_ERROR", GetMessage("ATL_API_ERROR_PAYMENT"));
            COption::SetOptionString("step2use.optimg", "LAST_API_ERROR", 402);
            return false;
        }

        // IP �� �������������
        //if($info["http_code"]==403 && (COption::GetOptionString("step2use.optimg", "API_ERROR")=="N" || COption::GetOptionString("step2use.optimg", "LAST_API_ERROR")!=GetMessage("ATL_API_ERROR_WRONG_IP"))) {
        if($info["http_code"]==403 && (COption::GetOptionString("step2use.optimg", "API_ERROR")=="N" || COption::GetOptionString("step2use.optimg", "LAST_API_ERROR")!=403)) {
            COption::SetOptionString("step2use.optimg", "API_ERROR", "Y");
            //COption::SetOptionString("step2use.optimg", "LAST_API_ERROR", GetMessage("ATL_API_ERROR_WRONG_IP"));
            COption::SetOptionString("step2use.optimg", "LAST_API_ERROR", 403);
            return false;
        }

        // ������ �����������
        //if($info["http_code"]==401 && (COption::GetOptionString("step2use.optimg", "API_ERROR")=="N" || COption::GetOptionString("step2use.optimg", "LAST_API_ERROR")!=GetMessage("ATL_API_ERROR_AUTH"))) {
        if(($info["http_code"]==401 && (COption::GetOptionString("step2use.optimg", "API_ERROR")=="N" || COption::GetOptionString("step2use.optimg", "LAST_API_ERROR")!=401) || (!COption::GetOptionString("step2use.optimg", "PASSWORD") || !COption::GetOptionString("step2use.optimg", "LOGIN"))) ) {
            COption::SetOptionString("step2use.optimg", "API_ERROR", "Y");
            //COption::SetOptionString("step2use.optimg", "LAST_API_ERROR", GetMessage("ATL_API_ERROR_AUTH"));
            COption::SetOptionString("step2use.optimg", "LAST_API_ERROR", 401);
            return false;
        }

        // ������� ������ API, ���� ������ ��� ���������
        if($info["http_code"]==200 && COption::GetOptionString("step2use.optimg", "API_ERROR")=="Y") {
            COption::SetOptionString("step2use.optimg", "API_ERROR", "N");
            COption::SetOptionString("step2use.optimg", "LAST_API_ERROR", "");
        }

        // �� ������ ������ - ���� �� 200, �� ��������� ������
        if($info["http_code"]!=200) {
            //COption::SetOptionString("step2use.optimg", "API_ERROR", "Y");
            return false;
        }

        //COption::SetOptionString("step2use.optimg", "API_ERROR", "Y");

        //if(strpos($filepath, "/bitrix/templates/eshop_bootstrap_green/lang/en/big.png")!==false) {

            // ��������� ������ ������ �� ��������� �����
            $tmppath = $filepath.".tmp"; // ��������� ���� ������� ����� � ����������
            file_put_contents($tmppath, $optiImgData);

            $newSize = filesize($tmppath);
            $oldSize = filesize($filepath);

            //var_dump(filesize($tmppath));
            //var_dump($tmppath);
            //unlink($tmppath);

            //var_dump($filepath);
            //var_dump($info['size_download']);
            //var_dump(filesize($filepath));
        //}

        // ���� ���� �������� �������
        if(!$newSize)
        {
            @unlink($tmppath);
            return false;
        }

        // orig-���� ��������� ������ ��� 1� ������
        // ����� ��? ���� ������� - � orig ���������� ��������� �� ������� �����.
        // � ���� ����� ����� ��������� ������ orig-��, �� � ������ ����� ���� ������������� �� ������ ������ �����
        if(COption::GetOptionString("step2use.optimg", "SAVE_ORIG")=="Y" && file_exists($filepath.".optipic-orig")==false && file_exists($filepath.".optimg-orig")==false) {
            $successCopied = copy($filepath, $filepath.".optipic-orig");
            if(!$successCopied){
                return false;
            }
            if(is_readable($filepath)){
                $filePerms = fileperms($filepath);
                $fileOwner = fileowner($filepath);
                $fileGroup = filegroup($filepath);
                @chmod($filepath.".optipic-orig", $filePerms); // �� �������� ������� ���������� ����� �� ����� ����
                @chown($filepath, $fileOwner); // ������ ��������� ����� ��, ��� � ��������� �����
                @chgrp($filepath, $fileGroup); // ������ ������ ����� ��, ��� � ��������� �����
            }

        }

        // ���� ������� ������, ����� ������ ���� ��������� ������, ��� ��������
	            if($newSize < $oldSize) {
                    //copy($fileFS["PATH"].".optimg-orig", $fileFS["PATH"]); // ���������� �������� ���� ����
                    //$newSize =  $DB->ForSql(filesize($fileFS["PATH"].".optimg-orig")); // � ������ ����� �� ����-�����
                    //var_dump("BAD");
                    if(is_readable($filepath)){
                        $filePerms = fileperms($filepath);
                        $fileOwner = fileowner($filepath);
                        $fileGroup = filegroup($filepath);
                        $successWritten = rename($tmppath, $filepath);
                        if(!$successWritten){
                            return false;
                        }
                        @chmod($filepath, $filePerms); // �� �������� ������� ���������� ����� �� ����� ����
                        @chown($filepath, $fileOwner); // ������ ��������� ����� ��, ��� � ��������� �����
                        @chgrp($filepath, $fileGroup); // ������ ������ ����� ��, ��� � ��������� �����
                    }

	            }
	            else {
	                @unlink($tmppath);
	            }

        //file_put_contents($filepath, $optiImgData);

        

        return true;
    }
    
    
    public static function getLogin() {
        return COption::GetOptionString("step2use.optimg", "LOGIN");
    }
    
    
    public static function getPassword() {
        return COption::GetOptionString("step2use.optimg", "PASSWORD");
    }
    

    public static function GetActiveBytes($cdn=false) {
        $url = self::getApiUrl()."getactivebytes";
        
        $login = self::getLogin();
        $password = self::getPassword();
        
        $info = array();
        
        if (function_exists('curl_init')) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_POSTREDIR, 3); // ����� POST-������ ������������ � ��� ���������
            curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
            curl_setopt($ch, CURLOPT_USERPWD, $login.":".$password);

            $apiResult = curl_exec($ch);
            $info = curl_getinfo($ch);
            //var_dump($info);
            //var_dump($apiResult);
        }
        else {
            $httpClient = new \Bitrix\Main\Web\HttpClient();
            $httpClient->setAuthorization($login, $password);
            $apiResult = $httpClient->post($url);
            $info["http_code"] = $httpClient->getStatus();
        }

        // ������ �����������
        if($info["http_code"]==401) {
            COption::SetOptionString("step2use.optimg", "API_ERROR", "Y");
            //COption::SetOptionString("step2use.optimg", "LAST_API_ERROR", GetMessage("ATL_API_ERROR_AUTH", array("#EMAIL#"=>COption::GetOptionString("step2use.optimg", "LOGIN"))));
            COption::SetOptionString("step2use.optimg", "LAST_API_ERROR", 401);
            return false;
        }

        // ������� ������ API, ���� ������ ��� ���������
        if($info["http_code"]==200 && COption::GetOptionString("step2use.optimg", "API_ERROR")=="Y") {
            COption::SetOptionString("step2use.optimg", "API_ERROR", "N");
            COption::SetOptionString("step2use.optimg", "LAST_API_ERROR", "");
        }

        $apiResult = json_decode($apiResult, true);
        if($cdn) {
            return $apiResult["cdn_views"];
        }
        else {
            return $apiResult["bytes"];
        }
    }

    public static function getApiUrl() {
        return (COption::GetOptionString("step2use.optimg", "USE_TEST_API")=="Y")? self::TEST_API_URL: self::API_URL;
    }


    /**
     *
     * ����� - ��������� ����� ����� � ���� �������.
     * ����� ����, �������� ��� ����� ��� ��������������
     *
     */
    public static function RefreshIndexBase() {
        global $DB;
        
        // ����� ���� ����� ������������� � ���������� ������?
	    if(COption::GetOptionString("step2use.optimg", "AGENT_ACTIVE", "N")=="N") {
	        return "CStepUseOptimg::RefreshIndexBase();";
	    }
        
        // ���� �� �������� �������������� ����������� - �������� ��� ��� ��������������, ����� ������� �������� �� ��� � ���������� - ���� �� ��������� �� ���:
        
        /*$uncompressedFiles = $DB->Query("SELECT ALREADY_PROCESSED_TODAY from atl_optimg_files WHERE ALREADY_PROCESSED_TODAY='N' AND WRITE_ERROR!=1");
        if($uncompressedFiles->SelectedRowsCount() == 0){
            $DB->Query("UPDATE atl_optimg_files SET ALREADY_PROCESSED_TODAY='N'");
        }*/

        $res = $DB->Query("SELECT COUNT(*) as CNT 
            FROM atl_optimg_files 
            WHERE ALREADY_PROCESSED_TODAY!='Y' 
            AND WRITE_ERROR!=1");
        $res = $res->Fetch();
        if($res['CNT']==0) {
            $DB->Query("UPDATE atl_optimg_files SET ALREADY_PROCESSED_TODAY='N'");
        }
        
        self::ReindexFileBase();

	    return "CStepUseOptimg::RefreshIndexBase();";
    }

    public static function GetAllIndexed($arFilter = array(), $arOrder = array()){
        $DB = CDatabase::GetModuleConnection('step2use.optimg');
        $arSqlSearch = array();

        $strSql = "
            SELECT
                *
            FROM
                atl_optimg_files
            WHERE
        ";

        if (is_array($arFilter))
        {
            foreach ($arFilter as $key => $val)
            {
                if (strlen($val)<=0 || $val=="NOT_REF")
                    continue;
                switch(strtoupper($key))
                {
                    case "PATH":
                        $arSqlSearch[] = "(PATH='".$DB->ForSql($val)."')";
                        break;
                    case "WRITE_ERROR":
                        $arSqlSearch[] = "(WRITE_ERROR='".$DB->ForSql($val)."')";
                        break;
                    case "ALREADY_PROCESSED_TODAY":
                        $arSqlSearch[] = GetFilterQuery($key, $val,"N");
                        break;
                    default:
                        $arSqlSearch[] = $key." = '".$DB->ForSql($val)."'";
                        break;
                }
            }
        }

        $strSqlSearch = GetFilterSqlSearch($arSqlSearch);

        $arOrderKeys = array_keys($arOrder);
        $orderBy = $arOrderKeys[0];
        $orderDir = $arOrder[$orderBy];

        switch ($orderBy) {
            case 'PATH':
                $strSqlOrder = 'ORDER BY PATH';
                break;
            case 'SIZE_ORIGINAL':
                $strSqlOrder = 'ORDER BY SIZE_ORIGINAL';
                break;
            case 'SIZE_COMPRESSED':
                $strSqlOrder = 'ORDER BY SIZE_COMPRESSED';
                break;
            case 'ALREADY_PROCESSED_TODAY':
                $strSqlOrder = 'ORDER BY ALREADY_PROCESSED_TODAY';
                break;
            default:
                $strSqlOrder = "ORDER BY PATH";
                break;
        }

        if ($orderDir!="asc") {
            $strSqlOrder .= " desc ";
            $orderDir="desc";
        }
        else {
            $strSqlOrder .= " asc ";
        }
        //var_dump($strSql.' '.$strSqlSearch.' '.$strSqlOrder);exit;
        $rs = $DB->Query($strSql.' '.$strSqlSearch.' '.$strSqlOrder, false, $err_mess.__LINE__);
        global $DB;
        $res = $DB->Query($strSql.' '.$strSqlSearch.' '.$strSqlOrder, false, $err_mess.__LINE__);
        while($data = $res->Fetch()) {
            $arResult[] = $data;
        }
        return $arResult;
    }

    /*

    ������� % ������ ��������

    SELECT (SUM(SIZE_ORIGINAL)-SUM(SIZE_COMPRESSED))/SUM(SIZE_ORIGINAL)*100 FROM atl_optimg_files WHERE SIZE_COMPRESSED>0;


    */


    /*
    ��� �������� �� ��������� ������ ��� ����������� � ������������� ������ < 30%
    update atl_optimg_files set ALREADY_PROCESSED_TODAY="N", MTIME=0 where (size_original-size_compressed)/size_original*100<30;

    ������� ��� jpg
    select *, sum(SIZE_ORIGINAL), sum(SIZE_COMPRESSED) from atl_optimg_files where path like '%.jpg';

    ������� ��� jpeg
    select *, sum(SIZE_ORIGINAL), sum(SIZE_COMPRESSED) from atl_optimg_files where path like '%.jpeg';

    */
    
    
  public static function deleteOrigsForRemovedImages(){

global $DB;
// ����� ������� � �������
$max = $DB->Query("SELECT COUNT(*) as CNT FROM atl_optimg_files");
$max_res = $max->Fetch();
$max_limit = $max_res["CNT"];


$limit_value = 1000;
// �������� ������� �� 1000
$count = $max_limit / $limit_value;
if ($count >= 1) {
    $count = floor($count);
} else {
    $count = 0;
}

$delete = array();
// ��������� ������ �� path
for ($i = 0; $i <= $count; $i++) {
    $limit = $i * $limit_value;
    $res = $DB->Query("SELECT PATH FROM atl_optimg_files LIMIT " . $DB->ForSql($limit_value) . " OFFSET " . $DB->ForSql($limit));
    while ($res_val = $res->Fetch()) {
        if (!file_exists($_SERVER["DOCUMENT_ROOT"] . $res_val['PATH'])) {
            // ���� ���� �� ����������, �������� ���� �� -orig � ������ ��
            $optipic_orig = $_SERVER["DOCUMENT_ROOT"] . $res_val['PATH'] . ".optipic-orig";
            if (file_exists($optipic_orig)) {
                if (is_writable($optipic_orig)) {
                    unlink($optipic_orig);
                    $delete["DELETE"][] = $optipic_orig;
                } else {
                    $delete["NO_ACCESS"][] = $optipic_orig;
                }
            }
            $optimg_orig = $_SERVER["DOCUMENT_ROOT"] . $res_val['PATH'] . ".optimg-orig";
            if (file_exists($optimg_orig)) {
                if (is_writable($optimg_orig)) {
                    unlink($optimg_orig);
                    $delete["DELETE"][] = $optimg_orig;
                } else {
                    $delete["NO_ACCESS"][] = $optimg_orig;
                }
            }
        }
    }
}
return $delete;
  }
}

if(function_exists('optipic_cdn_url')==false) {
    function optipic_cdn_url($localUrl, $params=array()) {
        return CdnOptiPic::buildImgUrl($localUrl, $params);
    }
}
?>