<?
$sModuleId = "step2use.optimg";
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"] . BX_ROOT . "/modules/main/prolog.php");

global $DBType;


CModule::IncludeModule($sModuleId);?>
<?// @todo HELP FILE
//define("HELP_FILE", "settings/s2u_redirect_list.php");

if (!$USER->CanDoOperation('edit_php') && !$USER->CanDoOperation('view_other_settings'))
    $APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

$isAdmin = $USER->CanDoOperation('edit_php');

IncludeModuleLangFile(__FILE__);

$sTableID = "tbl_s2u_OPTIMG_FILES";

//-----------MAKE THE FILTER---------------------------------------------
$oSort = new CAdminSorting($sTableID, "DATE_TIME_CREATE", "DESC");
$lAdmin = new CAdminList($sTableID, $oSort);

$arFilterFields = array(
    'filter_path',
    'filter_write_error',
    'filter_already_processed'
);

$lAdmin->InitFilter($arFilterFields);

$arFilter = array();
// SETTING THE FILTER CURRENT VALUES
if (StrLen($filter_path) > 0)
    $arFilter["PATH"] = $filter_path;
if (StrLen($filter_write_error) > 0)
    $arFilter["WRITE_ERROR"] = $filter_write_error;
if (StrLen($filter_already_processed) > 0)
    $arFilter["ALREADY_PROCESSED_TODAY"] = $filter_already_processed;

$arProcessedDropdown = array();
$arProcessedDropdown["REFERENCE_ID"] = array("Y", "N");
$arProcessedDropdown["REFERENCE"] = array(GetMessage("S2U_Y"), GetMessage("S2U_N"));

// GROUP ACTIONS
if (($arID = $lAdmin->GroupAction()) && $isAdmin) {
    if ($_REQUEST['action_target'] == 'selected') {
        $arID = Array();
        $dbResultList = S2uRedirectsRulesDB::GetList($arFilter);
        foreach ($dbResultList as $v)
            $arID[] = $v["ID"];
    }

    foreach ($arID as $ID) {
        if (strlen($ID) <= 0)
            continue;

        switch ($_REQUEST['action']) {
            case "delete":
                @set_time_limit(0);
                S2uRedirectsRulesDB::Delete($ID);
                break;
            case "activate":
            case "deactivate":
                $arFields = Array("ACTIVE"=>($_REQUEST['action']=="activate"?"Y":"N"));
                S2uRedirectsRulesDB::Update($ID, $arFields);
        }
    }
}

// LOAD DATA

$arResultList = CStepUseOptimg::GetAllIndexed($arFilter, array($by => $order));
$dbResultList = new CDBResult;
$dbResultList->InitFromArray($arResultList);

$dbResultList = new CAdminResult($dbResultList, $sTableID);
$dbResultList->NavStart();
// NAV PARAMS
$lAdmin->NavText($dbResultList->GetNavPrint(GetMessage("SAA_NAV")));
// THE LIST HEADER
$lAdmin->AddHeaders(array(
    //array("id" => "ID", "content" => '#', "sort" => "ID", "default" => true),
    array("id" => "PATH", "content" => GetMessage('FILTER_PATH'), "sort" => "PATH", "default" => true),
    array("id" => "SIZE_ORIGINAL", "content" => GetMessage('SIZE_ORIGINAL'), "sort" => "SIZE_ORIGINAL", "default" => true),
    array("id" => "SIZE_COMPRESSED", "content" => GetMessage('SIZE_COMPRESSED'), "sort" => "SIZE_COMPRESSED", "default" => true),
    array("id" => "WRITE_ERROR", "content" => GetMessage('FILTER_WRITE_ERROR'), "sort" => "WRITE_ERROR", "default" => true),
    array("id" => "ALREADY_PROCESSED_TODAY", "content" => GetMessage('FILTER_ALREADY_PROCESSED_TODAY'), "sort" => "ALREADY_PROCESSED_TODAY", "default" => true)
));

$arVisibleColumns = $lAdmin->GetVisibleHeaderColumns();

// MAKE THE LIST
while ($arResult = $dbResultList->NavNext(true, "f_")) {
    $row = & $lAdmin->AddRow($f_ID, $arResult, "", GetMessage("MURL_EDIT"));
    //$row->AddField("ID", $f_ID);
    $row->AddField("PATH", $f_PATH);
    $row->AddField("SIZE_ORIGINAL", $f_SIZE_ORIGINAL);
    $row->AddField("SIZE_COMPRESSED", $f_SIZE_COMPRESSED);
    $row->AddField("WRITE_ERROR", $f_WRITE_ERROR);
    $row->AddField("ALREADY_PROCESSED_TODAY", $f_ALREADY_PROCESSED_TODAY);

    //CONTEXT MENU
    /*$arActions = Array();
    $arActions[] = array("ICON" => "edit", "TEXT" => GetMessage("MURL_EDIT"), "ACTION" => $lAdmin->ActionRedirect("step2use_redirects_edit.php?ID=" . UrlEncode($arResult["ID"]) . "&lang=" . LANG), "DEFAULT" => true);
    if ($isAdmin)
        $arActions[] = array("ICON" => "delete", "TEXT" => GetMessage("MURL_DELETE"), "ACTION" => "if(confirm('" . GetMessage("MURL_DELETE_CONF") . "')) " . $lAdmin->ActionDoGroup(UrlEncode($arResult["ID"]), "delete"));

    $row->AddActions($arActions);
    */
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

/*$lAdmin->AddGroupActionTable(array(
    "delete" => GetMessage("MAIN_ADMIN_LIST_DELETE"),
    "activate" => GetMessage("MAIN_ADMIN_LIST_ACTIVATE"),
    "deactivate" => GetMessage("MAIN_ADMIN_LIST_DEACTIVATE")
));*/

$arDDMenu = array();

$dbRes = CLang::GetList(($b = "sort"), ($o = "asc"));
while (($arRes = $dbRes->Fetch())) {

}

// IF SHOW LIST ONLY
$lAdmin->CheckListMode();

$APPLICATION->SetTitle(GetMessage("S2U_INDEXTABLE_TITLE"));

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_after.php");
?>
<form name="find_form" method="GET" action="<? echo $APPLICATION->GetCurPage() ?>?">
    <?
    $oFilter = new CAdminFilter(
        $sTableID . "_filter",
        array(
            GetMessage('PATH'),
            GetMessage('ALREADY_PROCESSED_TODAY'),
            GetMessage('WRITE_ERROR')
        )
    );

    $oFilter->Begin();
    ?>
    <tr>
        <td><?= GetMessage('FILTER_PATH') ?>:</td>
        <td align="left" nowrap>
            <input type="text" name="filter_path" size="50" value="<?= htmlspecialcharsEx($filter_path)?>">
            &nbsp;<?=ShowFilterLogicHelp()?>
        </td>
    </tr>
    <tr>
        <td><?= GetMessage('FILTER_WRITE_ERROR') ?>:</td>
        <td align="left" nowrap>
            <input type="text" name="filter_write_error" size="50" value="<?= htmlspecialcharsEx($filter_write_error)?>">
            &nbsp;<?=ShowFilterLogicHelp()?>
        </td>
    </tr>
    <tr>
        <td><?= GetMessage('FILTER_ALREADY_PROCESSED_TODAY') ?>:</td>
        <td>
            <?echo SelectBoxFromArray("filter_already_processed", $arProcessedDropdown, $filter_already_processed, GetMessage("S2U_ALL"));?>
        </td>
    </tr>

    <?
    $oFilter->Buttons(
        array(
            "table_id" => $sTableID,
            "url" => $APPLICATION->GetCurPage(),
            "form" => "find_form"
        )
    );
    $oFilter->End();
    ?>
</form>
<?
// DISPLAY LIST
$lAdmin->DisplayList();
die();
?>

<?
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/epilog_admin.php");
?>
