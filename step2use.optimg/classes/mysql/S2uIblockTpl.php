<?
class S2uIblockTpl {
    const MODULE_ID = 'step2use.optimg';

    public static function GetList($arFilter = array(), $arOrder = array()){
        $DB = CDatabase::GetModuleConnection('step2use.optimg');
        $arSqlSearch = array();

        $strSql = "
            SELECT
                *
            FROM
                atl_optipic_tpl
            WHERE
        ";

        if (is_array($arFilter))
        {
            foreach ($arFilter as $key => $val)
            {
                if (strlen($val)<=0)
                    continue;
                switch(strtoupper($key))
                {
                    case "IGNORE_THIS_IBLOCK":
                        $arSqlSearch[] = GetFilterQuery($key, $val,"N");
                        break;
                    case "IGNORE_PREVIEW":
                        $arSqlSearch[] = GetFilterQuery($key, $val,"N");
                        break;
                    case "IGNORE_DETAIL":
                        $arSqlSearch[] = GetFilterQuery($key, $val,"N");
                        break;
                    case "IGNORE_PROP":
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

        /*switch ($orderBy) {
            case 'SITE_ID':
                $strSqlOrder = 'ORDER BY SITE_ID';
                break;
            case 'OLD_LINK':
                $strSqlOrder = 'ORDER BY OLD_LINK';
                break;
            case 'NEW_LINK':
                $strSqlOrder = 'ORDER BY NEW_LINK';
                break;
            case 'DATE_TIME_CREATE':
                $strSqlOrder = 'ORDER BY DATE_TIME_CREATE';
                break;
            case 'STATUS':
                $strSqlOrder = 'ORDER BY STATUS';
                break;
            case 'ACTIVE':
                $strSqlOrder = 'ORDER BY ACTIVE';
                break;
            case 'COMMENT':
                $strSqlOrder = 'ORDER BY COMMENT';
                break;
            default:
                //$strSqlOrder = "ORDER BY DATE_TIME_CREATE";
                break;
        }

        if ($orderDir!="asc") {
            $strSqlOrder .= " desc ";
            $orderDir="desc";
        }
        else {
            $strSqlOrder .= " asc ";
        }*/
        //var_dump($strSql.' '.$strSqlSearch.' '.$strSqlOrder);exit;
        $rs = $DB->Query($strSql.' '.$strSqlSearch, false, __LINE__);
        $arResult = array();
        while($data = $rs->Fetch()) {
            $arResult[] = $data;
        }

        return $arResult;
    }

    public static function Add($arFields, $arIblockLink){
        $DB = $moduleDB = CDatabase::GetModuleConnection('step2use.optimg');

        $DB->PrepareFields("atl_optipic_tpl");
        $arFields = array(
            "NAME"                      => "'".$DB->ForSql(trim($arFields["NAME"]))."'",
            "IGNORE_THIS_IBLOCK"        => "'".$DB->ForSql(trim($arFields["IGNORE_THIS_IBLOCK"]))."'",
            "COMPRESS_QUALITY"          => "'".$DB->ForSql(trim($arFields["COMPRESS_QUALITY"]))."'",
            "MAX_WIDTH"                 => "'".$DB->ForSql(trim($arFields["MAX_WIDTH"]))."'",
            "MAX_HEIGHT"                => "'".$DB->ForSql(trim($arFields["MAX_HEIGHT"]))."'",
            "IGNORE_PREVIEW"            => "'".$DB->ForSql(trim($arFields["IGNORE_PREVIEW"]))."'",
            "COMPRESS_QUALITY_PREVIEW"  => "'".$DB->ForSql(trim($arFields["COMPRESS_QUALITY_PREVIEW"]))."'",
            "MAX_WIDTH_PREVIEW"         => "'".$DB->ForSql(trim($arFields["MAX_WIDTH_PREVIEW"]))."'",
            "MAX_HEIGHT_PREVIEW"           => "'".$DB->ForSql(trim($arFields["MAX_HEIGHT_PREVIEW"]))."'",
            "IGNORE_DETAIL"           => "'".$DB->ForSql(trim($arFields["IGNORE_DETAIL"]))."'",
            "COMPRESS_QUALITY_DETAIL"           => "'".$DB->ForSql(trim($arFields["COMPRESS_QUALITY_DETAIL"]))."'",
            "MAX_WIDTH_DETAIL"           => "'".$DB->ForSql(trim($arFields["MAX_WIDTH_DETAIL"]))."'",
            "MAX_HEIGHT_DETAIL"           => "'".$DB->ForSql(trim($arFields["MAX_HEIGHT_DETAIL"]))."'",
            "IGNORE_PROP"           => "'".$DB->ForSql(trim($arFields["IGNORE_PROP"]))."'",
            "COMPRESS_QUALITY_PROP"           => "'".$DB->ForSql(trim($arFields["COMPRESS_QUALITY_PROP"]))."'",
            "MAX_WIDTH_PROP"           => "'".$DB->ForSql(trim($arFields["MAX_WIDTH_PROP"]))."'",
            "MAX_HEIGHT_PROP"           => "'".$DB->ForSql(trim($arFields["MAX_HEIGHT_PROP"]))."'"
        );

        $ID = $DB->Insert("atl_optipic_tpl", $arFields, __LINE__);

        if($arIblockLink){
            self::createIblockLink($ID, $arIblockLink);
        }

        return ($ID)? true: false;
    }

    static function Update($ID, $arFields, $arIblockLink) {

        $DB = $moduleDB = CDatabase::GetModuleConnection('step2use.optimg');

        $DB->PrepareFields("atl_optipic_tpl");

        $arFields = array(
            "NAME"                      => "'".$DB->ForSql(trim($arFields["NAME"]))."'",
            "IGNORE_THIS_IBLOCK"        => "'".$DB->ForSql(trim($arFields["IGNORE_THIS_IBLOCK"]))."'",
            "COMPRESS_QUALITY"          => "'".$DB->ForSql(trim($arFields["COMPRESS_QUALITY"]))."'",
            "MAX_WIDTH"                 => "'".$DB->ForSql(trim($arFields["MAX_WIDTH"]))."'",
            "MAX_HEIGHT"                => "'".$DB->ForSql(trim($arFields["MAX_HEIGHT"]))."'",
            "IGNORE_PREVIEW"            => "'".$DB->ForSql(trim($arFields["IGNORE_PREVIEW"]))."'",
            "COMPRESS_QUALITY_PREVIEW"  => "'".$DB->ForSql(trim($arFields["COMPRESS_QUALITY_PREVIEW"]))."'",
            "MAX_WIDTH_PREVIEW"         => "'".$DB->ForSql(trim($arFields["MAX_WIDTH_PREVIEW"]))."'",
            "MAX_HEIGHT_PREVIEW"           => "'".$DB->ForSql(trim($arFields["MAX_HEIGHT_PREVIEW"]))."'",
            "IGNORE_DETAIL"           => "'".$DB->ForSql(trim($arFields["IGNORE_DETAIL"]))."'",
            "COMPRESS_QUALITY_DETAIL"           => "'".$DB->ForSql(trim($arFields["COMPRESS_QUALITY_DETAIL"]))."'",
            "MAX_WIDTH_DETAIL"           => "'".$DB->ForSql(trim($arFields["MAX_WIDTH_DETAIL"]))."'",
            "MAX_HEIGHT_DETAIL"           => "'".$DB->ForSql(trim($arFields["MAX_HEIGHT_DETAIL"]))."'",
            "IGNORE_PROP"           => "'".$DB->ForSql(trim($arFields["IGNORE_PROP"]))."'",
            "COMPRESS_QUALITY_PROP"           => "'".$DB->ForSql(trim($arFields["COMPRESS_QUALITY_PROP"]))."'",
            "MAX_WIDTH_PROP"           => "'".$DB->ForSql(trim($arFields["MAX_WIDTH_PROP"]))."'",
            "MAX_HEIGHT_PROP"           => "'".$DB->ForSql(trim($arFields["MAX_HEIGHT_PROP"]))."'"
        );


        $updated = $DB->Update("atl_optipic_tpl", $arFields, "WHERE ID='".$ID."'", __LINE__);
        if($arIblockLink){
            self::updateIblockLink($ID, $arIblockLink);
        }


        return ($updated)? true: false;
    }

    public static function Delete($id){
        $DB = $moduleDB = CDatabase::GetModuleConnection('step2use.optimg');
        $deleted = $DB->Query("DELETE FROM atl_optipic_tpl WHERE ID='".$DB->ForSql($id)."'");
        $DB->Query("DELETE FROM atl_optipic_tpl_to_iblock WHERE TEMPLATE_ID = '".$DB->ForSql($id)."'");
        return $deleted;
    }

    public static function GetLinkableIblocksList($tplId = false){
        CModule::IncludeModule('iblock');
        $arIblocks = array();
        $db_iblock_type = CIBlockType::GetList();
        while($ar_iblock_type = $db_iblock_type->Fetch())
        {
            if($arIBType = CIBlockType::GetByIDLang($ar_iblock_type["ID"], LANG))
            {
                $arIblocksTypes[$ar_iblock_type["ID"]] = $arIBType["NAME"];
            }
        }
        $arFilter = array();
        $arFilter['!ID'] = self::GetOccupiedIblocks($tplId);

        $res = CIBlock::GetList(Array("iblock_type"=>"asc"), $arFilter);
        while($ar_res = $res->Fetch()) {
            $arIblocks[$ar_res['ID']] = "[".$arIblocksTypes[$ar_res['IBLOCK_TYPE_ID']]."] ".$ar_res['NAME'];

        }
        return $arIblocks;
    }

    public static function GetOccupiedIblocks($tplId){
        $DB = CDatabase::GetModuleConnection('step2use.optimg');

        $strSql = "
            SELECT
                IBLOCK_ID
            FROM
                atl_optipic_tpl_to_iblock
            "
        ;

        if($tplId){
            $strSql .= "
            WHERE
                 TEMPLATE_ID <> '".$DB->ForSql($tplId)."'
            ";
        }


        $ibResult = array();
        $rs = $DB->Query($strSql, false, __LINE__);

        while($ib = $rs->GetNext()){
            $ibResult[] = $ib['IBLOCK_ID'];

        }

        return $ibResult;

    }

    public static function createIblockLink($tplId, $iblocksArray){
        $DB = CDatabase::GetModuleConnection('step2use.optimg');
        $resultIDs = array();
        if($tplId && $iblocksArray){
            foreach($iblocksArray as $iblockId){
                $resultIDs = $DB->Insert("atl_optipic_tpl_to_iblock", array(
                    "IBLOCK_ID"        => "'".$DB->ForSql(trim($iblockId))."'",
                    "TEMPLATE_ID" => "'".$DB->ForSql(trim($tplId))."'"
                ), __LINE__);
            }

        }
        return $resultIDs;

    }

    public static function updateIblockLink($tplId, $iblocksArray){
        $DB = CDatabase::GetModuleConnection('step2use.optimg');

        $DB->Query("DELETE FROM atl_optipic_tpl_to_iblock WHERE TEMPLATE_ID='".$DB->ForSql($tplId)."'");

        $resultIDs = self::createIblockLink($tplId, $iblocksArray);
        return $resultIDs;
    }

    public static function GetTemplateIbLink($tplId){
        $DB = CDatabase::GetModuleConnection('step2use.optimg');

        $strSql = "
            SELECT
                IBLOCK_ID
            FROM
                atl_optipic_tpl_to_iblock
            WHERE
                 TEMPLATE_ID = '".$DB->ForSql($tplId)."'"
        ;

        $ibResult = array();
        $rs = $DB->Query($strSql, false, __LINE__);

        while($ib = $rs->GetNext()){
            $ibResult[] = $ib['IBLOCK_ID'];

        }

        return $ibResult;

    }

    public static function getFileIblock($filepath){
        $DB = CDatabase::GetModuleConnection('step2use.optimg');

        $result = array();

        $fileName = basename($filepath);
        
        $localPath = CStepUseOptimg::getLocalPath($filepath);
        $localPathInfo = pathinfo($localPath);
        $subdir = $localPathInfo['dirname'];
        if(strpos($subdir, '/upload/')===0) {
            $subdir = substr($subdir, 8);
        }

        $fileDb = $DB->Query("
            SELECT
                ID
            FROM
                b_file
            WHERE
                SUBDIR = '" . $DB->ForSql($subdir) . "' 
                AND
                FILE_NAME = '" . $DB->ForSql($fileName) . "'"
        );

        if($fileArr = $fileDb->Fetch()){
            $fileID = $fileArr['ID'];
            
        }

        if($fileID){
            // проверяем в анонсах
            $previewDb = $DB->Query("
                SELECT
                    IBLOCK_ID
                FROM
                    b_iblock_element
                WHERE
                    PREVIEW_PICTURE = '" . $DB->ForSql($fileID) . "'"
            );

            if($previewIb = $previewDb->Fetch()){
                $result['IBLOCK'] = $previewIb['IBLOCK_ID'];
                $result['TYPE'] = 'preview';
                return $result;
            }

            // проверяем в детальных
            $detailDb = $DB->Query("
                SELECT
                    IBLOCK_ID
                FROM
                    b_iblock_element
                WHERE
                    DETAIL_PICTURE = '" . $DB->ForSql($fileID) . "'"
            );

            if($detailIb = $detailDb->Fetch()){
                $result['IBLOCK'] = $detailIb['IBLOCK_ID'];
                $result['TYPE'] = 'detail';
                return $result;
            }

            // проверяем в свойствах

            /*$propDbId = $DB->Query("
                SELECT
                    IBLOCK_PROPERTY_ID
                FROM
                    b_iblock_element_property
                WHERE
                    IBLOCK_PROPERTY_ID IN ( SELECT ID from b_iblock_property WHERE PROPERTY_TYPE = 'F')
                AND
                    VALUE = '" . $DB->ForSql($fileID) . "'"
            );*/
            $propDbId = $DB->Query("SELECT
                    ep.IBLOCK_PROPERTY_ID as IBLOCK_PROPERTY_ID,
                    p.IBLOCK_ID as IBLOCK_ID
                FROM
                    b_iblock_element_property ep
                LEFT JOIN b_iblock_property p ON p.ID = ep.IBLOCK_PROPERTY_ID
                WHERE
                    ep.VALUE_NUM = '" . $DB->ForSql($fileID) . "'
                    AND p.PROPERTY_TYPE = 'F'"
            );

            if($propRes = $propDbId->Fetch()){
                
                $propId = $propRes['IBLOCK_PROPERTY_ID'];
                $result['IBLOCK'] = $propRes['IBLOCK_ID'];
                $result['TYPE'] = 'prop';
                
                return $result;
                
                /*$propId = $propRes['IBLOCK_PROPERTY_ID'];
                $propertyDbInfo = $DB->Query("SELECT IBLOCK_ID FROM b_iblock_property WHERE ID = '" . $DB->ForSql($propId) . "'")->Fetch();
                if($propertyDbInfo['IBLOCK_ID']){
                    $result['IBLOCK'] = $propertyDbInfo['IBLOCK_ID'];
                    $result['TYPE'] = 'prop';
                    return $result;
                }*/
            }

        }

        return false;
    }

    public static function GetIblockTemplateParams($iblockId){
        $DB = CDatabase::GetModuleConnection('step2use.optimg');

        $tplParams = array();

        $tplId = self::GetLinkedTemplate($iblockId);
        if($tplId){
            $tplParams = self::GetList(array('ID' => $tplId));

        }

        return $tplParams;
    }

    public static function GetLinkedTemplate($iblockId){
        $DB = CDatabase::GetModuleConnection('step2use.optimg');

        $templateId = false;
        $tplDb = $DB->Query("select TEMPLATE_ID from atl_optipic_tpl_to_iblock WHERE IBLOCK_ID='".$DB->ForSql($iblockId)."'");

        if($tplId = $tplDb->Fetch()){
            $templateId = $tplId['TEMPLATE_ID'];
        }

        return $templateId;
    }
    
    /** 
     * Возвращает кол-во правил инфоблока в базе
     */
    public static function GetCount() {
        global $DB;
	    $res = $DB->Query("SELECT COUNT(*) as CNT FROM atl_optipic_tpl");
	    $res = $res->Fetch();
	    //var_dump($res);exit;
	    return $res["CNT"];
    }
}