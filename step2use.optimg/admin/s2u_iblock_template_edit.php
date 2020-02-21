<?
$sModuleId = 'step2use.optimg';
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"] . BX_ROOT . "/modules/main/prolog.php");

CJSCore::Init(array("jquery"));

global $DBType;
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/$sModuleId/config.php");
CModule::IncludeModule($sModuleId);

// @todo HELP_FILE
//define("HELP_FILE", "settings/s2u_redirect_edit.php");

// lang
IncludeModuleLangFile(__FILE__);

// check access
if (!$USER->CanDoOperation('edit_php') && !$USER->CanDoOperation('view_other_settings'))
    $APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

// is admin
$isAdmin = $USER->CanDoOperation('edit_php');

// form message
$message = null;

// get vars from form?
$bVarsFromForm = false;

// no adding without tpl ID
if ((strlen($_REQUEST['Update']) > 0 && !$_REQUEST['ID']))
    LocalRedirect("/bitrix/admin/step2use.optimg_s2u_iblock_templates.php?lang=" . LANG);
?>
<?
// browser's title
$APPLICATION->SetTitle((isset($_REQUEST['ADD']) && StrLen($_REQUEST['ADD']) > 0) ? GetMessage("TPL_ADD") : GetMessage("TPL_EDIT"));

// indlude admin core
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_after.php");
?>

<?
if($REQUEST_METHOD == "POST") {
    if(!$_REQUEST['NAME']) {
        $message = new CAdminMessage(array(
            'MESSAGE' => GetMessage('ERROR_NO_NAME'),
            'TYPE' => 'ERROR',
            'DETAILS' => '',
            'HTML' => true
        ));
    }
}

// save for ADD
if ($REQUEST_METHOD == "POST" && strlen($_REQUEST['ADD']) > 0 && $isAdmin && check_bitrix_sessid()) {
    if (!$message) {
        $ignore_iblock = ($_REQUEST['IGNORE_THIS_IBLOCK']) ? 1 : 0;
        $ignore_preview = ($_REQUEST['IGNORE_PREVIEW']) ? 1 : 0;
        $ignore_detail = ($_REQUEST['IGNORE_DETAIL']) ? 1 : 0;
        $ignore_prop = ($_REQUEST['IGNORE_PROP'] != '') ? 1 : 0;

        $arIblockLinks = array();

        if($_POST['IBLOCKS']){
            $arIblockLinks = $_POST['IBLOCKS'];
        }

        $Res__ = S2uIblockTpl::Add(array(
                'NAME' => trim($_REQUEST['NAME']),
                "IGNORE_THIS_IBLOCK"        => $ignore_iblock,
                "COMPRESS_QUALITY"          => trim($_REQUEST['COMPRESS_QUALITY']),
                "MAX_WIDTH"                 => trim($_REQUEST['MAX_WIDTH']),
                "MAX_HEIGHT"                => trim($_REQUEST['MAX_HEIGHT']),
                "IGNORE_PREVIEW"            => trim($_REQUEST['IGNORE_PREVIEW']),
                "COMPRESS_QUALITY_PREVIEW"  => trim($_REQUEST['COMPRESS_QUALITY_PREVIEW']),
                "MAX_WIDTH_PREVIEW"         => trim($_REQUEST['MAX_WIDTH_PREVIEW']),
                "MAX_HEIGHT_PREVIEW"           => trim($_REQUEST['MAX_HEIGHT_PREVIEW']),
                "IGNORE_DETAIL"           => trim($_REQUEST['IGNORE_DETAIL']),
                "COMPRESS_QUALITY_DETAIL"           => trim($_REQUEST['COMPRESS_QUALITY_DETAIL']),
                "MAX_WIDTH_DETAIL"           => trim($_REQUEST['MAX_WIDTH_DETAIL']),
                "MAX_HEIGHT_DETAIL"           => trim($_REQUEST['MAX_HEIGHT_DETAIL']),
                "IGNORE_PROP"           => trim($_REQUEST['IGNORE_PROP']),
                "COMPRESS_QUALITY_PROP"           => trim($_REQUEST['COMPRESS_QUALITY_PROP']),
                "MAX_WIDTH_PROP"           => trim($_REQUEST['MAX_WIDTH_PROP']),
                "MAX_HEIGHT_PROP"           => trim($_REQUEST['MAX_HEIGHT_PROP']),
            ), $arIblockLinks
        );
        if ($Res__ && isset($save)) {
            LocalRedirect("/bitrix/admin/step2use.optimg_s2u_iblock_templates.php?lang=" . LANG);
        }
    }
    else {
        $bVarsFromForm = true;
    }
}

// save for UPDATE
if ($REQUEST_METHOD == "POST" && strlen($_REQUEST['Update']) > 0 && $isAdmin && check_bitrix_sessid()) {
    if (!$message) {
        $ignore_iblock = ($_REQUEST['IGNORE_THIS_IBLOCK']) ? 1 : 0;
        $ignore_preview = ($_REQUEST['IGNORE_PREVIEW']) ? 1 : 0;
        $ignore_detail = ($_REQUEST['IGNORE_DETAIL']) ? 1 : 0;
        $ignore_prop = ($_REQUEST['IGNORE_PROP'] != '') ? 1 : 0;

        $arIblockLinks = array();

        if($_POST['IBLOCKS']){
            $arIblockLinks = $_POST['IBLOCKS'];
        }

        $rr = S2uIblockTpl::Update(intval($_REQUEST['ID']), array(
            'NAME' => trim($_REQUEST['NAME']),
            "IGNORE_THIS_IBLOCK"        => $ignore_iblock,
            "COMPRESS_QUALITY"          => trim($_REQUEST['COMPRESS_QUALITY']),
            "MAX_WIDTH"                 => trim($_REQUEST['MAX_WIDTH']),
            "MAX_HEIGHT"                => trim($_REQUEST['MAX_HEIGHT']),
            "IGNORE_PREVIEW"            => trim($_REQUEST['IGNORE_PREVIEW']),
            "COMPRESS_QUALITY_PREVIEW"  => trim($_REQUEST['COMPRESS_QUALITY_PREVIEW']),
            "MAX_WIDTH_PREVIEW"         => trim($_REQUEST['MAX_WIDTH_PREVIEW']),
            "MAX_HEIGHT_PREVIEW"           => trim($_REQUEST['MAX_HEIGHT_PREVIEW']),
            "IGNORE_DETAIL"           => trim($_REQUEST['IGNORE_DETAIL']),
            "COMPRESS_QUALITY_DETAIL"           => trim($_REQUEST['COMPRESS_QUALITY_DETAIL']),
            "MAX_WIDTH_DETAIL"           => trim($_REQUEST['MAX_WIDTH_DETAIL']),
            "MAX_HEIGHT_DETAIL"           => trim($_REQUEST['MAX_HEIGHT_DETAIL']),
            "IGNORE_PROP"           => trim($_REQUEST['IGNORE_PROP']),
            "COMPRESS_QUALITY_PROP"           => trim($_REQUEST['COMPRESS_QUALITY_PROP']),
            "MAX_WIDTH_PROP"           => trim($_REQUEST['MAX_WIDTH_PROP']),
            "MAX_HEIGHT_PROP"           => trim($_REQUEST['MAX_HEIGHT_PROP']),
        ), $arIblockLinks);




        if($rr) {
            if (strlen($apply) <= 0) {
                LocalRedirect("/bitrix/admin/step2use.optimg_s2u_iblock_templates.php?lang=" . LANG . "&" . GetFilterParams("filter_", false));
            };
        } else {
            $message = new CAdminMessage(array(
                'MESSAGE' => GetMessage('SAE_ERROR'),
                'TYPE' => 'ERROR',
                'DETAILS' => '',
                'HTML' => true
            ));
            $bVarsFromForm = true;
        }
    }
    else {
        $bVarsFromForm = true;
    }
}

if(intval($_REQUEST['ID']) && !$bVarsFromForm){
    $tplDataArray = S2uIblockTpl::GetList(array('ID' => intval($_REQUEST['ID'])));
    $tplData = $tplDataArray[0];

    $ibLinkData = S2uIblockTpl::GetTemplateIbLink(intval($_REQUEST['ID']));
}

if($bVarsFromForm){
    $tplData = array(
        "NAME"                      => htmlspecialcharsbx($_POST['NAME']),
        "IGNORE_THIS_IBLOCK"        => htmlspecialcharsbx($_POST['IGNORE_THIS_IBLOCK']),
        "COMPRESS_QUALITY"          => htmlspecialcharsbx($_POST['COMPRESS_QUALITY']),
        "MAX_WIDTH"                 => htmlspecialcharsbx($_POST['MAX_WIDTH']),
        "MAX_HEIGHT"                => htmlspecialcharsbx($_POST['MAX_HEIGHT']),
        "IGNORE_PREVIEW"            => htmlspecialcharsbx($_POST['IGNORE_PREVIEW']),
        "COMPRESS_QUALITY_PREVIEW"  => htmlspecialcharsbx($_POST['COMPRESS_QUALITY_PREVIEW']),
        "MAX_WIDTH_PREVIEW"         => htmlspecialcharsbx($_POST['MAX_WIDTH_PREVIEW']),
        "MAX_HEIGHT_PREVIEW"           => htmlspecialcharsbx($_POST['MAX_HEIGHT_PREVIEW']),
        "IGNORE_DETAIL"           => htmlspecialcharsbx($_POST['IGNORE_DETAIL']),
        "COMPRESS_QUALITY_DETAIL"           => htmlspecialcharsbx($_POST['COMPRESS_QUALITY_DETAIL']),
        "MAX_WIDTH_DETAIL"           => htmlspecialcharsbx($_POST['MAX_WIDTH_DETAIL']),
        "MAX_HEIGHT_DETAIL"           => htmlspecialcharsbx($_POST['MAX_HEIGHT_DETAIL']),
        "IGNORE_PROP"           => htmlspecialcharsbx($_POST['IGNORE_PROP']),
        "COMPRESS_QUALITY_PROP"           => htmlspecialcharsbx($_POST['COMPRESS_QUALITY_PROP']),
        "MAX_WIDTH_PROP"           => htmlspecialcharsbx($_POST['MAX_WIDTH_PROP']),
        "MAX_HEIGHT_PROP"           => htmlspecialcharsbx($_POST['MAX_HEIGHT_PROP']),
    );

}
?>
<?
$aTabs = array(
    array("DIV" => "common_settings", "TAB" => GetMessage("S2U_COMMON_SET"), "ICON" => "main_settings", "TITLE" => GetMessage("S2U_COMMON_SET_TITLE")),
    array("DIV" => "preview", "TAB" => GetMessage("S2U_PREVIEW_SET"), "ICON" => "main_settings", "TITLE" => GetMessage("S2U_PREVIEW_SET_TITLE")),
    array("DIV" => "detail", "TAB" => GetMessage("S2U_DETAIL_SET"), "ICON" => "main_settings", "TITLE" => GetMessage("S2U_DETAIL_SET_TITLE")),
    array("DIV" => "props", "TAB" => GetMessage("S2U_PROP_SET"), "ICON" => "main_settings", "TITLE" => GetMessage("S2U_PROP_SET_TITLE")),
    array("DIV" => "iblock_link", "TAB" => GetMessage("S2U_IBLOCK_LINK_SET"), "ICON" => "main_settings", "TITLE" => GetMessage("S2U_IBLOCK_LINK_SET")),
);?>
<?
// show messages (errors and ok's)
if($message) echo $message->Show();
?>
<form method="POST" action="<?= $APPLICATION->GetCurPageParam() ?>" name="form1">
    <? echo GetFilterHiddens("filter_"); ?>
    <? if ($_REQUEST['ADD'] != 'Y'): ?>
        <input type="hidden" name="Update" value="Y">
    <? else: ?>
        <input type="hidden" name="ADD" value="Y">
    <? endif; ?>
    <input type="hidden" name="lang" value="<?= LANG ?>">
    <input type="hidden" name="ID" value="<?= htmlspecialcharsbx($_REQUEST['ID']) ?>">

    <?= bitrix_sessid_post() ?>
<?
$tabControl = new CAdminTabControl("tabControl", $aTabs);

$tabControl->Begin();

// COMMON settings
$tabControl->BeginNextTab();
?>
    <tr>
        <td width="50%"><span class="required">*</span><?echo GetMessage("S2U_TPL_NAME")?></td>
        <td width="50%"><input type="text" size="30" maxlength="40" value="<?=$tplData['NAME']?>" name="NAME">
        </td>
    </tr>
    <tr>
        <td width="50%" class="adm-detail-content-cell-l"><label for="IGNORE_THIS_IBLOCK"><?=GetMessage('S2U_IGNORE_THIS_IBLOCK')?>:</label><a name="IGNORE_THIS_IBLOCK"></a></td>
        <td width="50%" class="adm-detail-content-cell-r"><input type="checkbox" id="IGNORE_THIS_IBLOCK" name="IGNORE_THIS_IBLOCK" value="1" <?=$tplData['IGNORE_THIS_IBLOCK'] == 1 ? "checked" : ""?> class="adm-designed-checkbox"><label class="adm-designed-checkbox-label" for="IGNORE_THIS_IBLOCK" title=""></label></td>
    </tr>
    <tr>
        <td width="50%"><?echo GetMessage("S2U_COMPRESS_QUALITY")?></td>
        <td width="50%"><input type="text" size="30" maxlength="40" value="<?=$tplData['COMPRESS_QUALITY']?>" name="COMPRESS_QUALITY">
        </td>
    </tr>
    <tr>
        <td width="50%"><?echo GetMessage("S2U_MAX_WIDTH")?></td>
        <td width="50%"><input type="text" size="30" maxlength="40" value="<?=$tplData['MAX_WIDTH']?>" name="MAX_WIDTH">
        </td>
    </tr>
    <tr>
        <td width="50%"><?echo GetMessage("S2U_MAX_HEIGHT")?></td>
        <td width="50%"><input type="text" size="30" maxlength="40" value="<?=$tplData['MAX_HEIGHT']?>" name="MAX_HEIGHT">
        </td>
    </tr>
<?
// PREVIEW_PICTURE settings
$tabControl->BeginNextTab();
?>
    <tr>
        <td width="50%" class="adm-detail-content-cell-l"><label for="IGNORE_PREVIEW"><?=GetMessage('S2U_IGNORE_PREVIEW')?>:</label><a name="IGNORE_PREVIEW"></a></td>
        <td width="50%" class="adm-detail-content-cell-r"><input type="checkbox" id="IGNORE_PREVIEW" name="IGNORE_PREVIEW" value="1" <?=$tplData['IGNORE_PREVIEW'] == '1' ? "checked" : ""?> class="adm-designed-checkbox"><label class="adm-designed-checkbox-label" for="IGNORE_PREVIEW" title=""></label></td>
    </tr>
    <tr>
        <td width="50%"><?echo GetMessage("S2U_COMPRESS_QUALITY")?></td>
        <td width="50%"><input type="text" size="30" maxlength="40" value="<?=$tplData['COMPRESS_QUALITY_PREVIEW']?>" name="COMPRESS_QUALITY_PREVIEW">
        </td>
    </tr>
    <tr>
        <td width="50%"><?echo GetMessage("S2U_MAX_WIDTH")?></td>
        <td width="50%"><input type="text" size="30" maxlength="40" value="<?=$tplData['MAX_WIDTH_PREVIEW']?>" name="MAX_WIDTH_PREVIEW">
        </td>
    </tr>
    <tr>
        <td width="50%"><?echo GetMessage("S2U_MAX_HEIGHT")?></td>
        <td width="50%"><input type="text" size="30" maxlength="40" value="<?=$tplData['MAX_HEIGHT_PREVIEW']?>" name="MAX_HEIGHT_PREVIEW">
        </td>
    </tr>
<?
// DETAIL_PICTURE settings
$tabControl->BeginNextTab();
?>
    <tr>
        <td width="50%" class="adm-detail-content-cell-l"><label for="IGNORE_DETAIL"><?=GetMessage('S2U_IGNORE_DETAIL')?>:</label><a name="IGNORE_DETAIL"></a></td>
        <td width="50%" class="adm-detail-content-cell-r"><input type="checkbox" id="IGNORE_DETAIL" name="IGNORE_DETAIL" value="1" <?=$tplData['IGNORE_DETAIL'] == '1' ? "checked" : ""?> class="adm-designed-checkbox"><label class="adm-designed-checkbox-label" for="IGNORE_DETAIL" title=""></label></td>
    </tr>
    <tr>
        <td width="50%"><?echo GetMessage("S2U_COMPRESS_QUALITY")?></td>
        <td width="50%"><input type="text" size="30" maxlength="40" value="<?=$tplData['COMPRESS_QUALITY_DETAIL']?>" name="COMPRESS_QUALITY_DETAIL">
        </td>
    </tr>
    <tr>
        <td width="50%"><?echo GetMessage("S2U_MAX_WIDTH")?></td>
        <td width="50%"><input type="text" size="30" maxlength="40" value="<?=$tplData['MAX_WIDTH_DETAIL']?>" name="MAX_WIDTH_DETAIL">
        </td>
    </tr>
    <tr>
        <td width="50%"><?echo GetMessage("S2U_MAX_HEIGHT")?></td>
        <td width="50%"><input type="text" size="30" maxlength="40" value="<?=$tplData['MAX_HEIGHT_DETAIL']?>" name="MAX_HEIGHT_DETAIL">
        </td>
    </tr>
<?
// PROPS settings
$tabControl->BeginNextTab();
?>
    <tr>
        <td width="50%" class="adm-detail-content-cell-l"><label for="IGNORE_PROP"><?=GetMessage('S2U_IGNORE_PROP')?>:</label><a name="IGNORE_PROP"></a></td>
        <td width="50%" class="adm-detail-content-cell-r"><input type="checkbox" id="IGNORE_PROP" name="IGNORE_PROP" value="1" <?=$tplData['IGNORE_PROP'] == '1' ? "checked" : ""?> class="adm-designed-checkbox"><label class="adm-designed-checkbox-label" for="IGNORE_PROP" title=""></label></td>
    </tr>
    <tr>
        <td width="50%"><?echo GetMessage("S2U_COMPRESS_QUALITY")?></td>
        <td width="50%"><input type="text" size="30" maxlength="40" value="<?=$tplData['COMPRESS_QUALITY_PROP']?>" name="COMPRESS_QUALITY_PROP">
        </td>
    </tr>
    <tr>
        <td width="50%"><?echo GetMessage("S2U_MAX_WIDTH")?></td>
        <td width="50%"><input type="text" size="30" maxlength="40" value="<?=$tplData['MAX_WIDTH_PROP']?>" name="MAX_WIDTH_PROP">
        </td>
    </tr>
    <tr>
        <td width="50%"><?echo GetMessage("S2U_MAX_HEIGHT")?></td>
        <td width="50%"><input type="text" size="30" maxlength="40" value="<?=$tplData['MAX_HEIGHT_PROP']?>" name="MAX_HEIGHT_PROP">
        </td>
    </tr>
<?
// iblock linking settings
$tabControl->BeginNextTab();
?>
    <?
        $iblocksArray = S2uIblockTpl::GetLinkableIblocksList(intval($_REQUEST['ID']));
    ?>
    <tr>
        <td width="50%" class="adm-detail-content-cell-l"><label for="IBLOCKS"><?=GetMessage('S2U_IBLOCKS_SELECT')?>:</label><a name="IBLOCKS"></a></td>
        <td width="50%" class="adm-detail-content-cell-r">
            <select name="IBLOCKS[]" id="IBLOCKS" multiple>
                <option><?=GetMessage("NOT_SELECTED")?></option>
                <?if($iblocksArray):?>
                    <?foreach($iblocksArray as $id=>$iblockOption):?>
                        <option value="<?=$id?>" <?=in_array($id, $ibLinkData) ? 'selected' : ''?>><?=$iblockOption?></option>
                    <?endforeach;?>
                <?endif;?>
            </select>
        </td>
    </tr>    
<?
$tabControl->Buttons(
    array(
        "disabled" => !$isAdmin,
        "back_url" => "/bitrix/admin/step2use.optimg_s2u_iblock_templates.php?lang=" . LANG . "&" . GetFilterParams("filter_", false)
    )
);

$tabControl->End();

?>
</form>
<?
$tabControl->ShowWarnings("form1", $message);
?>
<? require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/epilog_admin.php"); ?>
