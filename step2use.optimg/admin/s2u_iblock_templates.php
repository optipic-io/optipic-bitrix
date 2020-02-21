<?
$sModuleId = "step2use.optimg";
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"] . BX_ROOT . "/modules/main/prolog.php");

global $DBType;

require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/$sModuleId/config.php");
CModule::IncludeModule($sModuleId);?>
<?// @todo HELP FILE
//define("HELP_FILE", "settings/s2u_redirect_list.php");

if (!$USER->CanDoOperation('edit_php') && !$USER->CanDoOperation('view_other_settings'))
    $APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

$isAdmin = $USER->CanDoOperation('edit_php');

IncludeModuleLangFile(__FILE__);

$sTableID = "tbl_s2u_REDIRECT";

//-----------MAKE THE FILTER---------------------------------------------
$oSort = new CAdminSorting($sTableID, "DATE_TIME_CREATE", "DESC");
$lAdmin = new CAdminList($sTableID, $oSort);


$arStatusDropdown = array();
$arStatusDropdown["REFERENCE_ID"] = array("301", "302", "303", "410");
$arStatusDropdown["REFERENCE"] = array(GetMessage("STATUS_301"), GetMessage("STATUS_302"), GetMessage("STATUS_303"), GetMessage("STATUS_410"));
$arActiveDropdown = array();
$arActiveDropdown["REFERENCE_ID"] = array("Y", "N");
$arActiveDropdown["REFERENCE"] = array(GetMessage("S2U_Y"), GetMessage("S2U_N"));


// GROUP ACTIONS
if (($arID = $lAdmin->GroupAction()) && $isAdmin) {
    if ($_REQUEST['action_target'] == 'selected') {
        $arID = Array();
        $dbResultList = S2uIblockTpl::GetList($arFilter);
        foreach ($dbResultList as $v)
            $arID[] = $v["ID"];
    }

    foreach ($arID as $ID) {
        if (strlen($ID) <= 0)
            continue;

        switch ($_REQUEST['action']) {
            case "delete":
                @set_time_limit(0);
                S2uIblockTpl::Delete($ID);
                break;
        }
    }
}

// LOAD DATA

$arResultList = S2uIblockTpl::GetList($arFilter, array($by => $order));
$dbResultList = new CDBResult;
$dbResultList->InitFromArray($arResultList);

$dbResultList = new CAdminResult($dbResultList, $sTableID);
$dbResultList->NavStart();

// NAV PARAMS
$lAdmin->NavText($dbResultList->GetNavPrint(GetMessage("SAA_NAV")));

// THE LIST HEADER
$lAdmin->AddHeaders(array(
    //array("id" => "ID", "content" => '#', "sort" => "ID", "default" => true),
    array("id" => "ID", "content" => GetMessage('ID'), "sort" => "ID", "default" => true),
    array("id" => "NAME", "content" => GetMessage('NAME'), "sort" => "NAME", "default" => true),
));

$arVisibleColumns = $lAdmin->GetVisibleHeaderColumns();

// MAKE THE LIST
while ($arResult = $dbResultList->NavNext(true, "f_")) {
    $row = & $lAdmin->AddRow($f_ID, $arResult, "step2use.optimg_s2u_iblock_template_edit.php?ID=" . UrlEncode($arResult["ID"]) . "&lang=" . LANG, GetMessage("MURL_EDIT"));

    //$row->AddField("ID", $f_ID);
    $row->AddField("ID", $f_ID);
    $row->AddField("NAME", $f_NAME);

    //CONTEXT MENU
    $arActions = Array();
    $arActions[] = array("ICON" => "edit", "TEXT" => GetMessage("MURL_EDIT"), "ACTION" => $lAdmin->ActionRedirect("step2use.optimg_s2u_iblock_template_edit.php?ID=" . UrlEncode($arResult["ID"]) . "&lang=" . LANG), "DEFAULT" => true);
    if ($isAdmin)
        $arActions[] = array("ICON" => "delete", "TEXT" => GetMessage("MURL_DELETE"), "ACTION" => "if(confirm('" . GetMessage("MURL_DELETE_CONF") . "')) " . $lAdmin->ActionDoGroup(UrlEncode($arResult["ID"]), "delete"));

    $row->AddActions($arActions);
}

// FOOTER
$arFooterArray = array(
    array(
        "title" => GetMessage("MAIN_ADMIN_LIST_SELECTED"),
        "value" => $dbResultList->SelectedRowsCount()
    ),
    array(
        "counter" => true,
        "title" => GetMessage("MAIN_ADMIN_LIST_CHECKED"),
        "value" => "0"
    ),
);

$lAdmin->AddFooter($arFooterArray);

$lAdmin->AddGroupActionTable(array(
    "delete" => GetMessage("MAIN_ADMIN_LIST_DELETE"),
));


$aContext = array(
    array(
        "TEXT" => GetMessage("NEW_TEMPLATE"),
        "TITLE" => GetMessage("NEW_TEMPLATE_TITLE"),
        "ICON" => "btn_new",
        "LINK" => "step2use.optimg_s2u_iblock_template_edit.php?ADD=Y"
    )
);

$lAdmin->AddAdminContextMenu($aContext);

// IF SHOW LIST ONLY
$lAdmin->CheckListMode();

$APPLICATION->SetTitle(GetMessage("PAGE_TITLE"));

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_after.php");
?>

<?

// DISPLAY LIST
$lAdmin->DisplayList();

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/epilog_admin.php");
?>
