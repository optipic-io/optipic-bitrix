<?php

use Bitrix\Main\Application;
$request = Application::getInstance()->getContext()->getRequest();

$isCDN = !is_null($request->getQuery("cdn"));

$MODULE_ID = "step2use.optimg";
CModule::IncludeModule($MODULE_ID);

IncludeModuleLangFile(__FILE__);

$MODULE_RIGHT = $APPLICATION->GetGroupRight($MODULE_ID);

if ($MODULE_RIGHT < "R")
    $APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

$autoDeactivatedClassicIntegration = false;
$autoDeactivatedCdnIntegration = false;

$arSites = [];
$rsSites = CSite::GetList($by="sort", $order="desc", Array("ACTIVE " => "Y"));
while ($arSite = $rsSites->Fetch()) {
    $arSites[$arSite['LID']] = $arSite;
}

$cdnTabs = [];
$siteIdForStat = '';
if($isCDN) {
    foreach($arSites as $arSite) {
        
        $cdnTab = array(
            'DIV' => 'cdn_config_site_'.$arSite['LID'],
            'TAB' => GetMessage('ATL_CDN_SITE')." [{$arSite['LID']}] {$arSite['NAME']}",
            "TITLE" => GetMessage("ATL_CDN_CONFIG_TITLE"),
            'OPTIONS' => array(
                array(
                    "CDN_AUTOREPLACE_ACTIVE_{$arSite['LID']}", 
                    GetMessage("ATL_CDN_AUTOREPLACE_ACTIVE"),
                    "",
                    array(
                        "checkbox",
                        "",
                        ""
                    )
                ),
                array(
                    "OPTIPIC_SITE_ID_{$arSite['LID']}", 
                    GetMessage("ATL_OPTIPIC_SITE_ID"),
                    "",
                    array(
                        "text",
                        "",
                        ""
                    )
                ),
                array(
                    "OPTIPIC_DOMAINS_{$arSite['LID']}", 
                    GetMessage("ATL_OPTIPIC_DOMAINS"),
                    "",
                    array(
                        "textarea",
                        "6",
                        "30"
                    )
                ),
                array(
                    "OPTIPIC_EXCLUSIONS_URL_{$arSite['LID']}", 
                    GetMessage("ATL_OPTIPIC_EXCLUSIONS_URL"),
                    "",
                    array(
                        "textarea",
                        "6",
                        "30"
                    )
                ),
                array(
                    "OPTIPIC_WHITELIST_IMG_URLS_{$arSite['LID']}", 
                    GetMessage("ATL_OPTIPIC_WHITELIST_IMG_URLS"),
                    "",
                    array(
                        "textarea",
                        "6",
                        "30"
                    )
                ),
                array(
                    "OPTIPIC_SRCSET_ATTRS_{$arSite['LID']}", 
                    GetMessage("ATL_OPTIPIC_SRCSET_ATTRS"),
                    "",
                    array(
                        "textarea",
                        "6",
                        "30"
                    )
                ),
                array(
                    "OPTIPIC_CDN_DOMAIN_{$arSite['LID']}", 
                    GetMessage("ATL_OPTIPIC_CDN_DOMAIN"),//.':<br/>'.GetMessage("ATL_OPTIPIC_CDN_DOMAIN_DESCRIPTION"),
                    "",
                    array(
                        "text",
                        "",
                        ""
                    )
                ),
                array(
                    "note" => GetMessage("ATL_OPTIPIC_CDN_DOMAIN") . ': ' . GetMessage("ATL_OPTIPIC_CDN_DOMAIN_DESCRIPTION")
                ),
                
                
                /*array(
                    "CDN_AUTOREPLACE_IMG_ATTRS_{$arSite['LID']}", 
                    GetMessage("ATL_CDN_AUTOREPLACE_IMG_ATTRS"),
                    COption::GetOptionString($MODULE_ID, "CDN_AUTOREPLACE_IMG_ATTRS_{$arSite['LID']}", CdnOptiPic::DEFAULT_IMG_ATTRS),
                    array(
                        "text",
                        "",
                        ""
                    )
                ),*/
                /*array(
                    "note" => GetMessage("ATL_CDN_HOWTO_INSTALL")
                ),*/
            ),
            
        );
        
        if($cdnCurSiteId = COption::GetOptionString($MODULE_ID, "OPTIPIC_SITE_ID_{$arSite['LID']}", "")) {
            $cdnTab["OPTIONS"][] = [
                "note" => '<a href="https://optipic.io/ru/cdn/cp/site/'.$cdnCurSiteId.'/" target="_blank">'.GetMessage("ATL_CDN_NOTE_SITE_SETTINGS_IN_CP").'</a>',
            ];
            if(!$siteIdForStat) {
                $siteIdForStat = $cdnCurSiteId;
            }
        }
        else {
            $cdnTab["OPTIONS"][] = [
                "note" => GetMessage("ATL_CDN_HOWTO_INSTALL"),
            ];
        }
        
        $cdnTabs[] = $cdnTab;
    }
    //var_dump($cdnTabs);
}
else {
$arOptions = array(
    array(
		"AGENT_ACTIVE",
		GetMessage("ATL_AGENT_ACTIVE"),
		"",
		array(
			"checkbox",
			"",
			""
		)
	),
    array(
        "note" => GetMessage("ATL_AGENT_ACTIVE_DESCR")
    ),
	array(
		"EXTENSIONS_WHITE_LIST",
		GetMessage("ATL_EXTENSIONS_WHITE_LIST"),
		"",
		array(
			"multiselectbox",
			array(
				"png" => "*.png",
				"jpeg" => "*.jpeg",
				"jpg" => "*.jpg"
			)
		)
	),
	array(
		"COMPRESS_LIMIT_BY_STEP",
		GetMessage("ATL_COMPRESS_LIMIT_BY_STEP"),
		"",
		array(
			"text",
			"5"
		)
	),
    array(
        "note" => GetMessage("S2U_ZERO_LIMIT_WARN")
    ),
	array(
		"COMPRESS_QUALITY",
		GetMessage("ATL_COMPRESS_QUALITY"),
		"",
        array(
        "selectbox",
			array(
                "50" => "50",
                "60" => "60",
				"70" => "70 - ".GetMessage("ATL_QUALITY_70_NOTE"),
				"80" => "80",
				"90" => "90",
                "100" => "100 - ".GetMessage("ATL_QUALITY_100_NOTE"),
			))
		/*array(
			"text",
			"5"
		)*/
	),
    array(
        "note" => GetMessage("ATL_COMPRESS_QUALITY_NOTE")
    ),
    array(
		"OPT_PROGRESSIVE_JPEG",
		GetMessage("ATL_OPT_PROGRESSIVE_JPEG"),
		"",
		array(
			"checkbox",
			"",
			""
		)
	),
    array(
		"OPT_STRIPTAGS",
		GetMessage("ATL_OPT_STRIPTAGS"),
		"",
		array(
			"checkbox",
			"",
			""
		)
	),
    array(
		"REINDEX_STEP_LIMIT_IN_SECONDS",
		GetMessage("ATL_REINDEX_STEP_LIMIT_IN_SECONDS"),
		"",
		array(
			"text",
			"5",
		)
	),
    array(
		"RETURN_ORIGS_BY_STEP",
		GetMessage("ATL_RETURN_ORIGS_BY_STEP"),
		"",
		array(
			"text",
			"5",
		)
	),
	array(
		"RECOMPRESS_10_PERCENT",
		GetMessage("ATL_RECOMPRESS_10_PERCENT"),
		"",
		array(
			"checkbox",
			"",
			""
		)
	),
	array(
		"note" => GetMessage("ATL_RECOMPRESS_DESC")
	),
	array(
		"LOGIN",
		GetMessage("ATL_LOGIN"),
		"",
		array(
			"text",
			"25"
		)
	),
	array(
		"PASSWORD",
		GetMessage("ATL_PASSWORD"),
		"",
		array(
			"text",
			"25"
		)
	),
	array(
		"IGNORE_PATH",
		GetMessage("ATL_IGNORE_PATH"),
		"",
		array(
			"textarea",
			"12",
			"60",
		)
	),
	array(
		"INDEX_ONLY",
		GetMessage("ATL_INDEX_ONLY"),
		"",
		array(
			"textarea",
			"12",
			"60",
		)
	),
	array(
		"MAX_WIDTH",
		GetMessage("ATL_MAX_WIDTH"),
		"",
		array(
			"text",
			"4"
		)
	),
	array(
		"MAX_HEIGHT",
		GetMessage("ATL_MAX_HEIGHT"),
		"",
		array(
			"text",
			"4"
		)
	),
	array(
		"SAVE_ORIG",
		GetMessage("ATL_SAVE_ORIG"),
		"",
		array(
			"checkbox",
			"",
			""
		)
	),
	array(
		"note" => GetMessage("ATL_ORIG_NOTE")
	),

);
}

function atlOptipicSalePrint($isCdn=false) {
    $urlToDiscountPage = 'https://optipic.io/ru/gb-free/';
    if($isCdn) {
        $urlToDiscountPage = 'https://optipic.io/ru/cdn/discounts/';
    }
    
?>

<div class="atl-free-gb-list">

    <div class="atl-free-gb">
        <h3><?=GetMessage("ATL_OPTIPIC_ADD_REVIEW_MARKETPLACE")?></h3>
        <h2>
            <?if($isCdn):?>
                <?=GetMessage("ATL_OPTIPIC_PLUS_100_K_VIEWS")?>
            <?else:?>
                <?=GetMessage("ATL_OPTIPIC_PLUS_1_GB")?>
            <?endif;?>
        </h2>
        <a href="https://marketplace.1c-bitrix.ru/solutions/step2use.optimg/#tab-rating-link" target="_blank" class="link"><?=GetMessage("ATL_GOTO_MARKETPLACE")?></a>
        <?/*<a href="<?=$urlToDiscountPage?>" target="_blank" class="link"><?=GetMessage("ATL_OPTIPIC_DETAILS")?></a>*/?>
    </div>
	<div class="atl-free-gb">
		<h3><?=GetMessage("ATL_OPTIPIC_ADD_RECOM")?></h3>
		<h2>
            <?if($isCdn):?>
                <?=GetMessage("ATL_OPTIPIC_PLUS_100_K_VIEWS")?>
            <?else:?>
                <?=GetMessage("ATL_OPTIPIC_PLUS_1_GB")?>
            <?endif;?>
        </h2>
		<a href="<?=$urlToDiscountPage?>" target="_blank" class="link"><?=GetMessage("ATL_OPTIPIC_DETAILS")?></a>
	</div>
    <?/*<div class="atl-free-gb">
        <h3><?=GetMessage("ATL_SOCIAL_AD")?></h3>
        <h2><?=GetMessage("ATL_SOCIAL_AD_DISCOUNT")?></h2>
        <a href="<?=$urlToDiscountPage?>" target="_blank" class="link"><?=GetMessage("ATL_OPTIPIC_DETAILS")?></a>
    </div>*/?>
    <div class="atl-free-gb">
        <h3><?=GetMessage("ATL_SALE_EVERYMOTH")?></h3>
        <h2>
            <?if($isCdn):?>
                <?=GetMessage("ATL_SALE_EVERYMOTH_DESCR_CDN")?>
            <?else:?>
                <?=GetMessage("ATL_SALE_EVERYMOTH_DESCR")?>
            <?endif;?>
        </h2>
        <a href="<?=$urlToDiscountPage?>" target="_blank" class="link"><?=GetMessage("ATL_OPTIPIC_DETAILS")?></a>
    </div>
    <?/*<div class="atl-free-gb">
        <h3><?=GetMessage("ATL_OPTIPIC_BUY_PROLONG")?></h3>
        <h2><?=GetMessage("ATL_OPTIPIC_ABOUT_50GB")?></h2>
        <a href="https://optipic.ru/gb-free" target="_blank" class="link"><?=GetMessage("ATL_OPTIPIC_DETAILS")?></a>
    </div>*/?>
    <div style="clear: both;"></div>
</div>
<div style="text-align: center; padding: 10px 0;"><small><a href="<?=$urlToDiscountPage?>" target="_blank"><?=GetMessage("ATL_SALE_BOTTOM_ABOUT")?></a></small></div>
<?
}

// Сохраняем все настройки
if($_SERVER["REQUEST_METHOD"]=="POST" && strlen($_POST["Update"])>0 && check_bitrix_sessid()) {
    if($isCDN) {
        foreach($cdnTabs as $tab) {
            __AdmSettingsSaveOptions($MODULE_ID, $tab['OPTIONS']);
        }
        // Отключаем фоновое сжатие, если хотя бы у одного сайта активирован CDN
        if(COption::GetOptionString($MODULE_ID, "AGENT_ACTIVE", 'N')=="Y") {
            foreach($arSites as $siteId=>$arSite) {
                if(COption::GetOptionString($MODULE_ID, "CDN_AUTOREPLACE_ACTIVE_$siteId", 'N')=="Y") {
                    COption::SetOptionString($MODULE_ID, "AGENT_ACTIVE", 'N');
                    $autoDeactivatedClassicIntegration = true;
                    break;
                }
            }
        }
    }
    else {
        $_POST["COMPRESS_LIMIT_BY_STEP"] = $COMPRESS_LIMIT_BY_STEP = intval($_POST["COMPRESS_LIMIT_BY_STEP"]);
        //    var_dump($COMPRESS_LIMIT_BY_STEP);
        foreach($arOptions as $option) {
            __AdmSettingsSaveOption("step2use.optimg", $option);
        }
        COption::SetOptionInt("step2use.optimg", "COMPRESS_LIMIT_BY_STEP", $COMPRESS_LIMIT_BY_STEP);
        
        // Отключаем автоподмену CDN, если включено фоновое сжатие
        if(COption::GetOptionString($MODULE_ID, "AGENT_ACTIVE", 'N')=="Y") {
            foreach($arSites as $siteId=>$arSite) {
                if(COption::GetOptionString($MODULE_ID, "CDN_AUTOREPLACE_ACTIVE_$siteId", 'N')=='Y') {
                    COption::SetOptionString($MODULE_ID, "CDN_AUTOREPLACE_ACTIVE_$siteId", 'N');
                    $autoDeactivatedCdnIntegration = true;
                }
            }
        }
    }
}


// Переиндексация базы
if($_GET["Reindex"]=="Y") {

    if(!isset($_REQUEST['NS']["last_file"])) {
        //CStepUseOptimg::ClearindexFileBase(true);
    }

	$endOfReindex = CStepUseOptimg::ReindexFileBase(true, $_REQUEST['NS']["last_file"]);
    $res = ($endOfReindex!==true)? GetMessage('ATL_REINDEX_RESULT')."<br/>".'<div id="continue_href">'.$endOfReindex.'</div>': GetMessage('ATL_REINDEX_RESULT_END', array("#RAND#"=>uniqid()));

    $message = new CAdminMessage(array(
        'MESSAGE' => ($endOfReindex===true)? GetMessage('ATL_REINDEX_RESULT_TITLE_END', array("#RAND#"=>uniqid(""))): GetMessage('ATL_REINDEX_RESULT_TITLE'),
       	'TYPE' => 'OK',
       	'DETAILS' => $res,
       	'HTML' => true
    ));
    echo $message->Show();

	/*$messageErr = new CAdminMessage(array(
		'MESSAGE' => ($endOfReindex===true)? GetMessage('ATL_REINDEX_RESULT_TITLE_END', array("#RAND#"=>uniqid(""))): GetMessage('ATL_REINDEX_RESULT_TITLE'),
		'TYPE' => 'OK',
		'DETAILS' => $res,
		'HTML' => true
	));

	echo $messageErr->Show();*/
    ?>
    <script>
    CloseWaitWindow();
	DoReindexNext({'NS':{'last_file':"<? echo $endOfReindex ?>"}});

	<? if($endOfReindex===true): ?>
	EndReindex();
	<? endif; ?>
    </script>
    <?
    exit;
}

// Сжатие
/*if($_SERVER["REQUEST_METHOD"]=="POST" && strlen($_POST["Docompress"])>0 && check_bitrix_sessid()) {
    CStepUseOptimg::OptimizeAllImgs(true);
}*/
if($_GET["Docompress"]=="Y") {

	if(CStepUseOptimg::GetFilesCount()==0) {
	    $message = new CAdminMessage(array(
            'MESSAGE' => GetMessage('ATL_API_ERROR_ZERO_INDEX'),
           	'TYPE' => 'ERROR',
           	'DETAILS' => GetMessage('ATL_API_ERROR_ZERO_INDEX_DESC'),
           	'HTML' => true
        ));
        echo $message->Show();
        ?>
        <script>
        CloseWaitWindow();
	    EndCompress();
        </script>
        <?
        exit;
	}

    CStepUseOptimg::OptimizeAllImgs(false, false);

    if(COption::GetOptionString("step2use.optimg", "API_ERROR")=="Y") {
        $message = new CAdminMessage(array(
            'MESSAGE' => GetMessage('ATL_API_ERROR'),
           	'TYPE' => 'ERROR',
           	'DETAILS' => (COption::GetOptionString("step2use.optimg", "LAST_API_ERROR"))? GetMessage("ATL_API_ERROR_".COption::GetOptionString("step2use.optimg", "LAST_API_ERROR"), array("#EMAIL#"=>COption::GetOptionString("step2use.optimg", "LOGIN"))).'<br/>'.GetMessage('ATL_API_ERROR_HELP'): GetMessage('ATL_API_ERROR_UNKNOWN').'<br/>'.GetMessage('ATL_API_ERROR_HELP'),
           	'HTML' => true
        ));
        echo $message->Show();
        ?>
        <script>
        CloseWaitWindow();
	    EndCompress();
        </script>
        <?
        exit;
    }

    $res = $DB->Query("SELECT count(*) as CNT FROM atl_optimg_files WHERE ALREADY_PROCESSED_TODAY='N'", false, $err_mess.__LINE__);
	$fileDB = $res->Fetch();
	//var_dump($fileDB["CNT"]);exit;


	//$endOfReindex = CStepUseOptimg::ReturnOrigFiles(true, $_REQUEST['NS']["last_file"]);

    $res = ($fileDB["CNT"]!=0)? GetMessage('ATL_COMPRESS_PROCESSING', array("#CNT#"=>$fileDB["CNT"])): GetMessage("ATL_COMPRESS_PROCESSING_END", array("#RAND#"=>uniqid()));

    $message = new CAdminMessage(array(
        'MESSAGE' => ($fileDB["CNT"]!=0)? GetMessage('ATL_COMPRESS_PROCESSING_TITLE'): GetMessage('ATL_COMPRESS_PROCESSING_TITLE_END', array("#RAND#"=>uniqid(""))),
       	'TYPE' => 'OK',
       	'DETAILS' => $res,
       	'HTML' => true
    ));
    echo $message->Show();
    ?>
    <script>
    CloseWaitWindow();
	DoCompressNext({'NS':{'last_file':"<? echo $endOfReindex ?>"}});

	<? if($fileDB["CNT"]==0): ?>
	EndCompress();
	<? endif; ?>
    </script>
    <?
    exit;
}


// Возвращаем оригиналы
if($_SERVER["REQUEST_METHOD"]=="POST" && strlen($_POST["ReturnOrig"])>0 && check_bitrix_sessid()) {
    CStepUseOptimg::ReturnOrigFiles();
}
if($_GET["ReturnOrig"]=="Y") {

	$funcReturn = CStepUseOptimg::ReturnOrigFiles(true);

    $res = ($funcReturn!==true)? GetMessage('ATL_REINDEX_RESULT')."<br/>".'<div id="continue_href">'.$funcReturn.'</div>': GetMessage('ATL_REINDEX_RESULT_END', array("#RAND#"=>uniqid()));

    $message = new CAdminMessage(array(
        'MESSAGE' => ($funcReturn===true)? GetMessage('ATL_RETURNORIG_RESULT_TITLE_END'): GetMessage('ATL_RETURNORIG_RESULT_TITLE'),
       	'TYPE' => 'OK',
       	'DETAILS' => $res,
       	'HTML' => true
    ));
    echo $message->Show();
    ?>
    <script>
    CloseWaitWindow();
DoReturnorigNext({});
<? if($funcReturn===true): ?>
EndReturnorig();
	<? endif; ?>
    </script>
    <?
    exit;
}


// Удаляем оригиналы
if($_SERVER["REQUEST_METHOD"]=="POST" && strlen($_POST["DeleteOrig"])>0 && check_bitrix_sessid()) {
	CStepUseOptimg::DeleteOrigFiles();
}
if($_GET["DeleteOrig"]=="Y") {

	$funcReturn = CStepUseOptimg::DeleteOrigFiles(true);

	$res = ($funcReturn!==true)? GetMessage('ATL_REINDEX_RESULT')."<br/>".'<div id="continue_href">'.$funcReturn.'</div>': GetMessage('ATL_REINDEX_RESULT_END', array("#RAND#"=>uniqid()));

	$message = new CAdminMessage(array(
		'MESSAGE' => ($funcReturn===true)? GetMessage('ATL_DELETEORIG_RESULT_TITLE_END'): GetMessage('ATL_DELETEORIG_RESULT_TITLE'),
		'TYPE' => 'OK',
		'DETAILS' => $res,
		'HTML' => true
	));
	echo $message->Show();
	?>
	<script>
		CloseWaitWindow();
		DoDeleteorigNext({});
		<? if($funcReturn===true): ?>
		EndDeleteorig();
		<? endif; ?>
	</script>
	<?
	exit;
}

if($_GET["installed"]=="Y") {
    $message = new CAdminMessage(array(
        'MESSAGE' => GetMessage('ATL_OPTIPIC_INSTALLED'),
       	'TYPE' => 'OK',
       	'DETAILS' =>  '', //GetMessage('ATL_OPTIPIC_INSTALLED_DETAIL', array('#EMAIL#'=>COption::GetOptionString("step2use.optimg", "LOGIN"))),
       	'HTML' => true
    ));
    echo $message->Show();
}
if($_GET["reinstalled"]=="Y") {
    $message = new CAdminMessage(array(
        'MESSAGE' => GetMessage('ATL_OPTIPIC_REINSTALLED'),
       	'TYPE' => 'OK',
       	'HTML' => true
    ));
    echo $message->Show();
}


if(!$isCDN) {
    // Проверяем, хватает ли на балансе МБ для сжатия тех файлов, которые еще не сжаты
    //$indexedTotal = CStepUseOptimg::GetSumOriginSize();
    //$compressedTotal = CStepUseOptimg::GetSumCompressedSize();
    $diffBytes = CStepUseOptimg::GetSizeLeftToCompress();
    $remainingBytes = CStepUseOptimg::GetActiveBytes();

    if($diffBytes > $remainingBytes) {
        $recommendData = CStepUseOptimg::getRecommendedTariff($diffBytes);

        $messageRec = new CAdminMessage(array(
            'MESSAGE' => GetMessage('ATL_BALANCE_WARNING'),
            'TYPE' => 'ERROR',
            'DETAILS' => GetMessage('ATL_RECOMMEND_TARIFF') . ' <a href="' . $recommendData['url_to_pay'] .  '">' . GetMessage('ATL_BALANCE_ADD') . ' ' . CStepUseOptimg::fromUtf($recommendData['name']) . '</a>',
            'HTML' => true
        ));
        echo $messageRec->Show();
    }
}

if($autoDeactivatedClassicIntegration) {
    $message = new CAdminMessage(array(
        'MESSAGE' => GetMessage('ATL_AUTO_DISABLE_CLASSIC_TITLE'),
       	'TYPE' => 'ERROR',
       	'DETAILS' => GetMessage('ATL_AUTO_DISABLE_DETAIL'),
       	'HTML' => true
    ));
    echo $message->Show();
}

if($autoDeactivatedCdnIntegration) {
    $message = new CAdminMessage(array(
        'MESSAGE' => GetMessage('ATL_AUTO_DISABLE_CDN_TITLE'),
       	'TYPE' => 'ERROR',
       	'DETAILS' => GetMessage('ATL_AUTO_DISABLE_DETAIL'),
       	'HTML' => true
    ));
    echo $message->Show();
}




// Выводим последнюю ошибку связи с API OptiPic
if(COption::GetOptionString("step2use.optimg", "API_ERROR")=="Y" && !$isCDN) {
    $message = new CAdminMessage(array(
        'MESSAGE' => GetMessage('ATL_API_ERROR'),
       	'TYPE' => 'ERROR',
       	'DETAILS' => (COption::GetOptionString("step2use.optimg", "LAST_API_ERROR"))? GetMessage("ATL_API_ERROR_".COption::GetOptionString("step2use.optimg", "LAST_API_ERROR"), array("#EMAIL#"=>COption::GetOptionString("step2use.optimg", "LOGIN"))).'<br/>'.GetMessage('ATL_API_ERROR_HELP'): GetMessage('ATL_API_ERROR_UNKNOWN').'<br/>'.GetMessage('ATL_API_ERROR_HELP'),
       	'HTML' => true
    ));
    echo $message->Show();
}


if($isCDN) {
    if($activeBalance = CStepUseOptimg::GetActiveBytes($isCDN)) {
        echo '<div style="margin-bottom: 10px;">'.GetMessage('ATL_ACCOUNT_BYTES').': '. number_format($activeBalance, 0, '.', ' ').' '.GetMessage('ATL_CDN_VIEWS').'</div>'; 
    }
}

/*if($isCDN) {
    $aTabs = array(
        array("DIV" => "cdn_config", "TAB" => GetMessage("ATL_CDN_CONFIG"), "ICON" => "main_settings", "TITLE" => GetMessage("ATL_CDN_CONFIG_TITLE")),
        //array("DIV" => "config", "TAB" => GetMessage("MAIN_TAB_SET"), "ICON" => "main_settings", "TITLE" => GetMessage("MAIN_TAB_TITLE_SET")),
        //array("DIV" => "stats", "TAB" => GetMessage("ATL_STATISTIC"), "ICON" => "main_settings", "TITLE" => GetMessage("ATL_STATISTIC_TITLE")),
        //array("DIV" => "reindex", "TAB" => GetMessage("ATL_REINDEX"), "ICON" => "main_settings", "TITLE" => GetMessage("ATL_REINDEX")),
        //array("DIV" => "docompress", "TAB" => GetMessage("ATL_DOCOMPRESS"), "ICON" => "main_settings", "TITLE" => GetMessage("ATL_DOCOMPRESS_TITLE")),
        //array("DIV" => "returnorig", "TAB" => GetMessage("ATL_RETURN_ORIG"), "ICON" => "main_settings", "TITLE" => GetMessage("ATL_RETURN_ORIG_TITLE")),
        //array("DIV" => "deleteorig", "TAB" => GetMessage("ATL_DELETE_ORIG"), "ICON" => "main_settings", "TITLE" => GetMessage("ATL_DELETE_ORIG_TITLE")),
        //array("DIV" => "sale", "TAB" => GetMessage("ATL_SALE"), "ICON" => "main_settings", "TITLE" => GetMessage("ATL_SALE_TITLE")),
        //array("DIV" => "partneram", "TAB" => GetMessage("ATL_PARTNERAM"), "ICON" => "main_settings", "TITLE" => GetMessage("ATL_PARTNERAM_TITLE")),
        
    );
}
else {*/

$aTabs = [];

if($isCDN) {
    if(count($cdnTabs)) {
        $aTabs = array_merge($cdnTabs, $aTabs);
    }
    //$aTabs[] = array("DIV" => "cdn_config", "TAB" => GetMessage("ATL_CDN_CONFIG"), "ICON" => "main_settings", "TITLE" => GetMessage("ATL_CDN_CONFIG_TITLE"));
    //$aTabs[] = array("DIV" => "config", "TAB" => GetMessage("MAIN_TAB_SET"), "ICON" => "main_settings", "TITLE" => GetMessage("MAIN_TAB_TITLE_SET"));
}
else {
    $aTabs[] = array("DIV" => "config", "TAB" => GetMessage("MAIN_TAB_SET"), "ICON" => "main_settings", "TITLE" => GetMessage("MAIN_TAB_TITLE_SET"));
    //$aTabs[] = array("DIV" => "cdn_config", "TAB" => GetMessage("ATL_CDN_CONFIG"), "ICON" => "main_settings", "TITLE" => GetMessage("ATL_CDN_CONFIG_TITLE"));
}
$aTabs[] = array("DIV" => "stats", "TAB" => GetMessage("ATL_STATISTIC"), "ICON" => "main_settings", "TITLE" => GetMessage("ATL_STATISTIC_TITLE"));
$aTabs[] = array("DIV" => "reindex", "TAB" => GetMessage("ATL_REINDEX"), "ICON" => "main_settings", "TITLE" => GetMessage("ATL_REINDEX"));
$aTabs[] = array("DIV" => "docompress", "TAB" => GetMessage("ATL_DOCOMPRESS"), "ICON" => "main_settings", "TITLE" => GetMessage("ATL_DOCOMPRESS_TITLE"));
$aTabs[] = array("DIV" => "returnorig", "TAB" => GetMessage("ATL_RETURN_ORIG"), "ICON" => "main_settings", "TITLE" => GetMessage("ATL_RETURN_ORIG_TITLE"));
$aTabs[] = array("DIV" => "deleteorig", "TAB" => GetMessage("ATL_DELETE_ORIG"), "ICON" => "main_settings", "TITLE" => GetMessage("ATL_DELETE_ORIG_TITLE"));
$aTabs[] = array("DIV" => "sale", "TAB" => GetMessage("ATL_SALE"), "ICON" => "main_settings", "TITLE" => ($isCDN? GetMessage("ATL_SALE_TITLE_CDN"): GetMessage("ATL_SALE_TITLE")));
$aTabs[] = array("DIV" => "partneram", "TAB" => GetMessage("ATL_PARTNERAM"), "ICON" => "main_settings", "TITLE" => GetMessage("ATL_PARTNERAM_TITLE"));

    /*$aTabs = array(
        array("DIV" => "cdn_config", "TAB" => GetMessage("ATL_CDN_CONFIG"), "ICON" => "main_settings", "TITLE" => GetMessage("ATL_CDN_CONFIG_TITLE")),
        array("DIV" => "config", "TAB" => GetMessage("MAIN_TAB_SET"), "ICON" => "main_settings", "TITLE" => GetMessage("MAIN_TAB_TITLE_SET")),
        array("DIV" => "stats", "TAB" => GetMessage("ATL_STATISTIC"), "ICON" => "main_settings", "TITLE" => GetMessage("ATL_STATISTIC_TITLE")),
        array("DIV" => "reindex", "TAB" => GetMessage("ATL_REINDEX"), "ICON" => "main_settings", "TITLE" => GetMessage("ATL_REINDEX")),
        array("DIV" => "docompress", "TAB" => GetMessage("ATL_DOCOMPRESS"), "ICON" => "main_settings", "TITLE" => GetMessage("ATL_DOCOMPRESS_TITLE")),
        array("DIV" => "returnorig", "TAB" => GetMessage("ATL_RETURN_ORIG"), "ICON" => "main_settings", "TITLE" => GetMessage("ATL_RETURN_ORIG_TITLE")),
        array("DIV" => "deleteorig", "TAB" => GetMessage("ATL_DELETE_ORIG"), "ICON" => "main_settings", "TITLE" => GetMessage("ATL_DELETE_ORIG_TITLE")),
        array("DIV" => "sale", "TAB" => GetMessage("ATL_SALE"), "ICON" => "main_settings", "TITLE" => GetMessage("ATL_SALE_TITLE")),
        array("DIV" => "partneram", "TAB" => GetMessage("ATL_PARTNERAM"), "ICON" => "main_settings", "TITLE" => GetMessage("ATL_PARTNERAM_TITLE")),
    );*/
//}

$tabControl = new CAdminTabControl("tabControl", $aTabs);

$tabControl->Begin();
//$tabControl->BeginNextTab();

?>
<form method="post" action="<? echo $APPLICATION->GetCurPage() ?>?mid=<?= urlencode($MODULE_ID) ?>&amp;lang=<?= LANGUAGE_ID ?><?if($isCDN) {echo '&cdn=1';}?>">
<?
echo bitrix_sessid_post();
//__AdmSettingsSaveOptions($MODULE_ID, $arOptions);
//var_dump($arOptions);

if(count($cdnTabs)) {
    foreach ($cdnTabs as $aTab) {
        $tabControl->BeginNextTab();
        __AdmSettingsDrawList($MODULE_ID, $aTab['OPTIONS']);
    }
}
else {
    $tabControl->BeginNextTab();
    __AdmSettingsDrawList($MODULE_ID, $arOptions);
}

//$tabControl->BeginNextTab();

// Настройки
$tabControl->BeginNextTab();

$sizeLeftToCompress = CStepUseOptimg::GetSizeLeftToCompress();

$processedCount = CStepUseOptimg::GetFilesProcessedCount();

if($processedCount > 0) {
    $efficiencyData = CStepUseOptimg::GetApiEfficiency();
    $efficiencyPercent = $efficiencyData['effective'];
}else{
    $efficiencyPercent = '0';
}


echo GetMessage("ATL_ALL_AMOUNT").": ".CStepUseOptimg::GetFilesCount()." ".GetMessage("ATL_PCS")." (".CStepUseOptimg::GetHumanFilesize(CStepUseOptimg::GetSumOriginSize()).")<br/>";
echo GetMessage("ATL_ALL_COMPRESSED_AMOUNT").": ". $processedCount ." ".GetMessage("ATL_PCS")." (".CStepUseOptimg::GetHumanFilesize(CStepUseOptimg::GetSumCompressedSize()).")<br/>";
echo GetMessage("ATL_EFFICIENCY").": ". $efficiencyPercent."%<br/>";
$strLeftToCompress =  GetMessage("ATL_LEFT_TO_COMPRESS").": ".CStepUseOptimg::GetUncompressedFilesCount()." ".GetMessage("ATL_PCS");
if($sizeLeftToCompress > 0){
	$strLeftToCompress .= " (".CStepUseOptimg::GetHumanFilesize($sizeLeftToCompress).")";
}
$strLeftToCompress .= "<br/><br/>";
echo $strLeftToCompress;

$activeBytes = CStepUseOptimg::GetActiveBytes();
echo GetMessage("ATL_ACCOUNT_BYTES").": ".CStepUseOptimg::GetHumanFilesize($activeBytes)." (<a href='https://optipic.io/account' target='_blank'>".GetMessage("ATL_ADD_FUNDS")."</a>)";
// Если меньше 100 МБ - выводим предупреждение
//if($activeBytes<104857600) {

//}

// Переиндексация
$tabControl->BeginNextTab();

if($_SERVER["REQUEST_METHOD"]=="POST" && strlen($_POST["Reindex"])>0 && check_bitrix_sessid()) {
    echo GetMessage("ATL_REINDEX_DONE").GetMessage("ATL_FILE_IN_INDEX").": ".CStepUseOptimg::GetFilesCount()."<br/><br/>";
}
else {
    echo GetMessage("ATL_REINDEX_ABOUT")."<br/><br/>";
}
?>
<div id="reindex_result_div">&nbsp;</div>
<input type="button" id="start_button_reindex" name="Reindex" value="<?echo GetMessage("ATL_REINDEX_BUTTON")?>" title="<?echo GetMessage("ATL_REINDEX_BUTTON")?>" class="adm-btn-save" onclick="StartReindex();">
<input type="button" id="stop_button_reindex" value="<? echo GetMessage('ATL_BUTTON_STOP'); ?>" onclick="StopReindex();" disabled="">
<input type="button" id="continue_button_reindex" value="<? echo GetMessage('ATL_BUTTON_CONTINUE'); ?>" onclick="ContinueReindex();" disabled="">
<?

// Сжатие
$tabControl->BeginNextTab();

if($_SERVER["REQUEST_METHOD"]=="POST" && strlen($_POST["Docompress"])>0 && check_bitrix_sessid()) {
    echo GetMessage("ATL_DOCOMPRESS_DONE")."<br/><br/>";
}

?>
<? /*<input type="submit" name="Docompress" value="<?echo GetMessage("ATL_DOCOMPRESS")?>" title="<?echo GetMessage("ATL_DOCOMPRESS")?>" class="adm-btn-save">*/?>

<div id="compress_result_div">&nbsp;</div>
<input type="button" id="start_button_compress" name="Docompress" value="<?echo GetMessage("ATL_DOCOMPRESS")?>" title="<?echo GetMessage("ATL_DOCOMPRESS")?>" class="adm-btn-save" onclick="StartCompress();">
<input type="button" id="stop_button_compress" value="<? echo GetMessage('ATL_BUTTON_STOP'); ?>" onclick="StopCompress();" disabled="">
<input type="button" id="continue_button_compress" value="<? echo GetMessage('ATL_BUTTON_CONTINUE'); ?>" onclick="ContinueCompress();" disabled="">
<?

// Вернуть оригиналы
$tabControl->BeginNextTab();

if($_SERVER["REQUEST_METHOD"]=="POST" && strlen($_POST["ReturnOrig"])>0 && check_bitrix_sessid()) {
    echo GetMessage("ATL_RETURN_ORIG_DONE")."<br/><br/>";
}

?>


<? /*<input type="submit" name="ReturnOrig" value="<?echo GetMessage("ATL_RETURN_ORIG")?>" title="<?echo GetMessage("ATL_RETURN_ORIG")?>" class="adm-btn-save">*/ ?>

<div id="returnorig_result_div">&nbsp;</div>
<input type="button" id="start_button_returnorig" name="ReturnOrig" value="<?echo GetMessage("ATL_RETURN_ORIG")?>" title="<?echo GetMessage("ATL_RETURN_ORIG")?>" class="adm-btn-save" onclick="StartReturnorig();">
<input type="button" id="stop_button_returnorig" value="<? echo GetMessage('ATL_BUTTON_STOP'); ?>" onclick="StopReturnorig();" disabled="">
<input type="button" id="continue_button_returnorig" value="<? echo GetMessage('ATL_BUTTON_CONTINUE'); ?>" onclick="ContinueReturnorig();" disabled="">

	<?
	// Удалить оригиналы
	$tabControl->BeginNextTab();
	?>

	<div id="deleteorig_result_div">&nbsp;</div>
	<input type="button" id="start_button_deleteorig" name="DeleteOrig" value="<?echo GetMessage("ATL_DELETE_ORIG")?>" title="<?echo GetMessage("ATL_DELETE_ORIG")?>" class="adm-btn-save" onclick="StartDeleteorig();">
	<input type="button" id="stop_button_deleteorig" value="<? echo GetMessage('ATL_BUTTON_STOP'); ?>" onclick="StopDeleteorig();" disabled="">
	<input type="button" id="continue_button_deleteorig" value="<? echo GetMessage('ATL_BUTTON_CONTINUE'); ?>" onclick="ContinueDeleteorig();" disabled="">
	<?

// Акции
$tabControl->BeginNextTab();
?>
<style>
.atl-free-gb-list {
    clear: both;
    width: 100%;
}
.atl-free-gb {
    float: left;
    width: 25%;
    text-align: center;
	padding: 0 4%;
}

.atl-free-gb a.link {
    display: inline-block;

    background-color: #86ad00!important;
	-webkit-box-shadow: 0 1px 1px rgba(0,0,0,.25), inset 0 1px 0 #cbdc00;
	box-shadow: 0 1px 1px rgba(0,0,0,.25), inset 0 1px 0 #cbdc00;
	border: solid 1px;
	border-color:#97c004 #7ea502 #648900;
	background-image: -webkit-linear-gradient(bottom, #729e00, #97ba00)!important;
	background-image: -moz-linear-gradient(bottom, #729e00, #97ba00)!important;
	background-image: -ms-linear-gradient(bottom, #729e00, #97ba00)!important;
	background-image: -o-linear-gradient(bottom, #729e00, #97ba00)!important;
	background-image: linear-gradient(bottom, #729e00, #97ba00)!important;
	color:#fff;
	text-shadow:0 1px rgba(0,0,0,0.1);
	-webkit-font-smoothing: antialiased;
	padding:0px 13px 2px;

	-webkit-border-radius: 4px;
	border-radius: 4px;
	height: 29px;
	line-height: 29px;
	color:#fff;
	text-shadow:0 1px rgba(0,0,0,0.1);
	-webkit-font-smoothing: antialiased;
	text-decoration: none;
	font-weight: bold;
}
<?if($isCDN):?>
#tab_cont_config, #tab_cont_stats, #tab_cont_reindex, #tab_cont_docompress, #tab_cont_returnorig, #tab_cont_deleteorig,
#config.adm-detail-content, #stats.adm-detail-content, #reindex.adm-detail-content, #docompress.adm-detail-content, #returnorig.adm-detail-content, #deleteorig.adm-detail-content/*,
#tab_cont_sale, #sale.adm-detail-content*/ {
    display: none !important;
}
<?else:?>
#tab_cont_cdn_config,
#cdn_config.adm-detail-content {
    display: none;
}
<?endif;?>
</style>
<?
atlOptipicSalePrint($isCDN);

// Партнерская программа
$tabControl->BeginNextTab();
?>
<?
?>
	<iframe src="https://optipic.io/ru/partneram/?iframe=1" width="100%" height="850px"></iframe>
<?

$tabControl->Buttons();
?>
<input type="submit" <? if ($MODULE_RIGHT < "W") echo "disabled" ?> name="Update" value="<? echo GetMessage("MAIN_SAVE") ?>">
<?

$tabControl->End();

?>
</form>

<?/*<?if($isCDN):?>
    <h2 style="text-align: center;"><?=GetMessage("ATL_SALE_TITLE_CDN");?></h2>
<?else:?>
    <h2 style="text-align: center;"><?=GetMessage("ATL_SALE_TITLE");?></h2>
<?endif;?>
<? atlOptipicSalePrint($isCDN); ?>*/?>

<script language="JavaScript">

// Переиндексация
var savedNS_reindex;
var stop_reindex;
var interval_reindex = 0;
function StartReindex() {
	stop_reindex=false;
	document.getElementById('reindex_result_div').innerHTML='';
	document.getElementById('stop_button_reindex').disabled=false;
	document.getElementById('start_button_reindex').disabled=true;
	document.getElementById('continue_button_reindex').disabled=true;
	DoReindexNext();
}
function DoReindexNext(NS) {
	var queryString = '&Reindex=Y'
		+ '&lang=ru';
    //queryString += '&NS[sleep_min]=' + document.getElementById('s2u_sleep_min').value;
    //queryString += '&NS[sleep_max]=' + document.getElementById('s2u_sleep_max').value;
    //queryString += '&NS[site_id]=' + document.getElementById('s2u_site_id').value;

	if(!NS)
	{
		//interval = document.getElementById('max_execution_time').value;
        interval_reindex = 0;
		queryString += '&sessid=<?=bitrix_sessid();?>';
	}

	savedNS_reindex = NS;

	if(!stop_reindex) {
		ShowWaitWindow();
		BX.ajax.post(
			'/bitrix/admin/settings.php?mid=step2use.optimg&lang=ru'+queryString,
			NS,
			function(result) {
				document.getElementById('reindex_result_div').innerHTML = result;
				var href = document.getElementById('continue_href');
				if(!href)
				{
					CloseWaitWindow();
					StopReindex();
				}
			}
		);
	}

	return false;
}
function StopReindex() {
	stop_reindex=true;
	document.getElementById('stop_button_reindex').disabled=true;
	document.getElementById('start_button_reindex').disabled=false;
	document.getElementById('continue_button_reindex').disabled=false;
}
function ContinueReindex() {
	stop_reindex=false;
	document.getElementById('stop_button_reindex').disabled=false;
	document.getElementById('start_button_reindex').disabled=true;
	document.getElementById('continue_button_reindex').disabled=true;
	DoReindexNext(savedNS_reindex);
}
function EndReindex() {
	stop_reindex=true;
	document.getElementById('stop_button_reindex').disabled=true;
	document.getElementById('start_button_reindex').disabled=false;
	document.getElementById('continue_button_reindex').disabled=true;
}


// Сжатие
var savedNS_compress;
var stop_compress;
var interval_compress = 0;
function StartCompress() {
	stop_compress=false;
	console.log(stop_compress);
	document.getElementById('compress_result_div').innerHTML='';
	document.getElementById('stop_button_compress').disabled=false;
	document.getElementById('start_button_compress').disabled=true;
	document.getElementById('continue_button_compress').disabled=true;
	DoCompressNext();
}
function DoCompressNext(NS) {
	var queryString = '&Docompress=Y'
		+ '&lang=ru';
    //queryString += '&NS[sleep_min]=' + document.getElementById('s2u_sleep_min').value;
    //queryString += '&NS[sleep_max]=' + document.getElementById('s2u_sleep_max').value;
    //queryString += '&NS[site_id]=' + document.getElementById('s2u_site_id').value;

	if(!NS)
	{
		//interval = document.getElementById('max_execution_time').value;
        interval_compress = 0;
		queryString += '&sessid=<?=bitrix_sessid();?>';
	}

	savedNS_compress = NS;

	if(!stop_compress) {
		ShowWaitWindow();
		BX.ajax.post(
			'/bitrix/admin/settings.php?mid=step2use.optimg&lang=ru'+queryString,
			NS,
			function(result) {
				document.getElementById('compress_result_div').innerHTML = result;
				var href = document.getElementById('continue_href');
				if(!href)
				{
					CloseWaitWindow();
					StopCompress();
				}
			}
		);
	}

	return false;
}
function StopCompress() {
	stop_compress=true;
	document.getElementById('stop_button_compress').disabled=true;
	document.getElementById('start_button_compress').disabled=false;
	document.getElementById('continue_button_compress').disabled=false;
}
function ContinueCompress() {
	stop_compress=false;
	document.getElementById('stop_button_compress').disabled=false;
	document.getElementById('start_button_compress').disabled=true;
	document.getElementById('continue_button_compress').disabled=true;
	DoCompressNext(savedNS_compress);
}
function EndCompress() {
	stop_compress=true;
	document.getElementById('stop_button_compress').disabled=true;
	document.getElementById('start_button_compress').disabled=false;
	document.getElementById('continue_button_compress').disabled=true;
}


// Возврат оригиналов
var savedNS_returnorig;
var stop_returnorig;
var interval_returnorig = 0;
function StartReturnorig() {
    var rlyStartReturn = confirm("<?=GetMessage('ATL_RETURN_CONFIRM_MSG')?>");
    if(rlyStartReturn){
        stop_returnorig=false;
        document.getElementById('returnorig_result_div').innerHTML='';
        document.getElementById('stop_button_returnorig').disabled=false;
        document.getElementById('start_button_returnorig').disabled=true;
        document.getElementById('continue_button_returnorig').disabled=true;
        DoReturnorigNext();
    }

}
function DoReturnorigNext(NS) {
	var queryString = '&ReturnOrig=Y'
		+ '&lang=ru';
    //queryString += '&NS[sleep_min]=' + document.getElementById('s2u_sleep_min').value;
    //queryString += '&NS[sleep_max]=' + document.getElementById('s2u_sleep_max').value;
    //queryString += '&NS[site_id]=' + document.getElementById('s2u_site_id').value;

	if(!NS)
	{
		//interval = document.getElementById('max_execution_time').value;
        interval_returnorig = 0;
		queryString += '&sessid=<?=bitrix_sessid();?>';
	}

	savedNS_returnorig = NS;

	if(!stop_returnorig) {
		ShowWaitWindow();
		BX.ajax.post(
			'/bitrix/admin/settings.php?mid=step2use.optimg&lang=ru'+queryString,
			NS,
			function(result) {
				document.getElementById('returnorig_result_div').innerHTML = result;
				var href = document.getElementById('continue_href');
				if(!href)
				{
					CloseWaitWindow();
					StopReturnorig();
				}
			}
		);
	}

	return false;
}
function EndReturnorig() {
    stop_returnorig=true;
	document.getElementById('stop_button_returnorig').disabled=true;
	document.getElementById('start_button_returnorig').disabled=false;
	document.getElementById('continue_button_returnorig').disabled=true;
}
function StopReturnorig() {
	stop_returnorig=true;
	document.getElementById('stop_button_returnorig').disabled=true;
	document.getElementById('start_button_returnorig').disabled=false;
	document.getElementById('continue_button_returnorig').disabled=false;
}
function ContinueReturnorig() {
	stop_returnorig=false;
	document.getElementById('stop_button_returnorig').disabled=false;
	document.getElementById('start_button_returnorig').disabled=true;
	document.getElementById('continue_button_returnorig').disabled=true;
	DoReturnorigNext(savedNS_returnorig);
}

// Удаление оригиналов
var savedNS_deleteorig;
var stop_deleteorig;
var interval_deleteorig = 0;
function StartDeleteorig() {
    var rlyStartDelete = confirm("<?=GetMessage('ATL_DELETE_CONFIRM_MSG')?>");
    if(rlyStartDelete){
        stop_deleteorig=false;
        document.getElementById('deleteorig_result_div').innerHTML='';
        document.getElementById('stop_button_deleteorig').disabled=false;
        document.getElementById('start_button_deleteorig').disabled=true;
        document.getElementById('continue_button_deleteorig').disabled=true;
        DoDeleteorigNext();
    }

}
function DoDeleteorigNext(NS) {
	var queryString = '&DeleteOrig=Y'
		+ '&lang=ru';
	//queryString += '&NS[sleep_min]=' + document.getElementById('s2u_sleep_min').value;
	//queryString += '&NS[sleep_max]=' + document.getElementById('s2u_sleep_max').value;
	//queryString += '&NS[site_id]=' + document.getElementById('s2u_site_id').value;

	if(!NS)
	{
		//interval = document.getElementById('max_execution_time').value;
		interval_deleteorig = 0;
		queryString += '&sessid=<?=bitrix_sessid();?>';
	}

	savedNS_deleteorig = NS;

	if(!stop_deleteorig) {
		ShowWaitWindow();
		BX.ajax.post(
			'/bitrix/admin/settings.php?mid=step2use.optimg&lang=ru'+queryString,
			NS,
			function(result) {
				document.getElementById('deleteorig_result_div').innerHTML = result;
				var href = document.getElementById('continue_href');
				if(!href)
				{
					CloseWaitWindow();
					StopDeleteorig();
				}
			}
		);
	}

	return false;
}
function EndDeleteorig() {
	stop_deleteorig=true;
	document.getElementById('stop_button_deleteorig').disabled=true;
	document.getElementById('start_button_deleteorig').disabled=false;
	document.getElementById('continue_button_deleteorig').disabled=true;
}
function StopDeleteorig() {
	stop_deleteorig=true;
	document.getElementById('stop_button_deleteorig').disabled=true;
	document.getElementById('start_button_deleteorig').disabled=false;
	document.getElementById('continue_button_deleteorig').disabled=false;
}
function ContinueDeleteorig() {
	stop_deleteorig=false;
	document.getElementById('stop_button_deleteorig').disabled=false;
	document.getElementById('start_button_deleteorig').disabled=true;
	document.getElementById('continue_button_deleteorig').disabled=true;
	DoDeleteorigNext(savedNS_deleteorig);
}


if(window.location.hash.length > 0) {
    var tabID = window.location.hash.replace("#","");
    tabControl.SelectTab(tabID);
}
</script>

<?
//var_dump(date('Y-m-d H:i:s'));
//var_dump(filemtime(__FILE__));
//var_dump(date('Y-m-d H:i:s', filemtime(__FILE__)));
//3600
?>
<?if(false && time()-filemtime(__FILE__)>15*60):?>
<script>
// ---------------------------------------
var optipicReviewCloseCookieName = 'step2use_optimg_review_popup_closed';
if(typeof BX.getCookie(optipicReviewCloseCookieName) == 'undefined') {
    setTimeout(function() {
        var popup = BX.PopupWindowManager.create("popup-message", null, {
        content: `<div style="padding: 10px; text-align: center;">
            <h1><?=GetMessage("ATL_POPUP_H1")?></h1>
            <h2><?=GetMessage("ATL_POPUP_H2_1")?></h2>
            <h3><?=GetMessage("ATL_POPUP_H2_2")?></h3>
            <h2>
                <?if($isCDN):?>
                <?=GetMessage("ATL_OPTIPIC_PLUS_100_K_VIEWS")?>
                <?else:?>
                <?=GetMessage("ATL_OPTIPIC_PLUS_1_GB")?>
                <?endif;?>
            </h2>
            <div class="popup-window-buttons">
                <a href="https://marketplace.1c-bitrix.ru/solutions/step2use.optimg/#tab-rating-link" target="_blank" class="popup-window-button ui-btn popup-window-button-create" id="optipic-add-review" style="text-decoration: none;"><?=GetMessage("ATL_GOTO_MARKETPLACE")?></a>
            </div>
        </div>`,
        closeByEsc: true,
        autoHide: true,
        closeIcon: {
            opacity: 1
        },
        events: {
           onPopupClose: function() {
              BX.setCookie('step2use_optimg_review_popup_closed', '1', {expires: 86400});
           }
        }
    });
    popup.show();
    }, 10000);
}
// ---------------------------------------
</script>
<?endif;?>

<?
$arModuleVersion = array();
include(dirname(__FILE__)."/install/version.php");
$statParams = array(
    'domain' => $_SERVER["HTTP_HOST"],
    'sid' => $siteIdForStat,
    'cms' => 'bitrix',
    'stype' => ($isCDN)? 'cdn': 'classic',
    'append_to' => '#adm-workarea',
    'version' => $arModuleVersion['VERSION'],
    //'site_chooser_selector' => "input[name^='OPTIPIC_SITE_ID_']",
);
if(!defined('BX_UTF')) {
    $statParams['charset'] = 'windows-1251';
}
?>
<script src="https://optipic.io/api/cp/stat?<?=http_build_query($statParams)?>"></script>

<? CJSCore::Init(array("jquery")); ?>
<script>
/*$(function() {
    //var siteIdInputSelector = "input[name='OPTIPIC_SITE_ID_s1']:first";
    //var $siteIdInput = $(siteIdInputSelector);
    
    var clickedButtonChooseSite = [];
    
    $("input[name^='OPTIPIC_SITE_ID_']").each(function() {
        $('<button class="optipic-cdn-site-choose-button">...</button>').data("optipic-target", "input[name='" + $(this).attr("name") + "']").insertAfter($(this));
    });
    
    
    
    window.addEventListener("message", function(event) {
        console.log(event.data.siteId);
        if(clickedButtonChooseSite.length==1) {
            if(selectorInput = clickedButtonChooseSite.data("optipic-target")) {
                $(selectorInput).val(event.data.siteId);
            }
            //$siteIdInput.val(event.data.siteId);
        }
        window.newWin.close();
    }, false);
    
    $(".optipic-cdn-site-choose-button").click(function(e) {
        e.preventDefault();
        
        clickedButtonChooseSite = $(this);
        
        //window.newWin = window.open("https://optipic.io/ru/cdn/cp/?chooser=1", "optipic", "width=735,height=500");
        var siteHost = window.location.protocol + '//' + window.location.host;
        window.newWin = window.open("https://optipic.io/en/cdn/cp/site-selector/?site_host=" + siteHost + "&layoutmode=hide-footer-and-menu", "optipic", "width=735,height=500");  
        
        //newWin.document.write("Привет, мир!");
    });
    
    //$siteIdInput.focus(function() {
    //    $("#optipic-cdn-site-choose-button").trigger("click");
    //});
});*/
</script>
<style>
/*.optipic-cdn-site-choose-button {
    margin-left: 10px;
}*/
</style>