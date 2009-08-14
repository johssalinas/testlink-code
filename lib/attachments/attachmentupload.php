<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * Filename $RCSfile: attachmentupload.php,v $
 *
 * @version $Revision: 1.22 $
 * @modified $Date: 2009/08/14 20:58:03 $ by $Author: schlundus $
 *
 * Upload dialog for attachments
 *
**/
require_once('../../config.inc.php');
require_once('../functions/common.php');
require_once('../functions/attachments.inc.php');
testlinkInitPage($db,false,false,"checkRights");
	
$args = init_args();
$bUploaded = false;
$msg = null;

if ($args->bPostBack)
{
	$fInfo  = isset($_FILES['uploadedFile']) ? $_FILES['uploadedFile'] : null;
	$id = $_SESSION['s_upload_id'];
	$tableName = $_SESSION['s_upload_tableName'];
	if ($fInfo && $id && $tableName != "")
	{
		$fSize = isset($fInfo['size']) ? $fInfo['size'] : 0;
		$fTmpName = isset($fInfo['tmp_name']) ? $fInfo['tmp_name'] : '';
		if ($fSize && $fTmpName != "")
		{
			$attachmentRepository = tlAttachmentRepository::create($db);
			$bUploaded = $attachmentRepository->insertAttachment($id,$tableName,$args->title,$fInfo);
			if ($bUploaded)
				logAuditEvent(TLS("audit_attachment_created",$args->title,$fInfo['name']),"CREATE",$id,"attachments");
		}
		else
			$msg  = getFileUploadErrorMessage($fInfo);
	}
}
else
{
	$_SESSION['s_upload_tableName'] = $args->tableName;
	$_SESSION['s_upload_id'] = $args->id;
}

$smarty = new TLSmarty();
$smarty->assign('import_limit',TL_REPOSITORY_MAXFILESIZE);
$smarty->assign('id',$args->id);
$smarty->assign('tableName',$args->tableName);
$smarty->assign('bUploaded',$bUploaded);
$smarty->assign('msg',$msg);
$smarty->display('attachmentupload.tpl');

/**
 * @return object returns the arguments for the page
 */
function init_args()
{
	$iParams = array(
		//the id (attachments.fk_id) of the object, to which the attachment belongs to 
		"id" => array("GET",tlInputParameter::INT_N),
		//the table to which the fk_id refers to (attachments.fk_table) of the attachment 
		"tableName" => array("GET",tlInputParameter::STRING_N,0,250),
		//the title of the attachment (attachments.title) 
		"title" => array("POST",tlInputParameter::STRING_N,0,250),
	);
	$args = new stdClass();
	$pParams = I_PARAMS($iParams,$args);
	
	$args->bPostBack = sizeof($_POST);
	
	return $args;
}

/**
 * @param $db resource the database connection handle
 * @param $user the current active user
 * @return boolean returns true if the page can be accessed
 */
function checkRights(&$db,&$user)
{
	return (config_get("attachments")->enabled);
}
?>
