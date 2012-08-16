<?php
session_start();
session_regenerate_id(true);

require_once '../../conf/settings.php';
require_once $tolc_conf['project_dir'] . '/app/common/init.php';
require_once ADODB_PATH . '/adodb.inc.php';
require_once $tolc_conf['project_dir'] . '/app/common/utils_db.php';
require_once $tolc_conf['project_dir'] . '/app/common/utils.php';
require_once $tolc_conf['project_dir'] . '/app/common/utils_cms.php';
require_once SIMPLE_HTML_DOM_PATH . '/simple_html_dom.php';

// check for logged in user
if(!isset($_SESSION['username'])) {
	print CONST_ACCESS_DENIED . ' (' . __FILE__ . ')';
	exit;
}

// connect to database
$conn = get_db_conn($tolc_conf['dbdriver']);

// get page
$a_page = get_page($conn, $_SESSION['url']);
$www_pages_id = $a_page['page_id'];
$page_title = $a_page['page_title'];
$page_has_been_removed = $a_page['page_has_been_removed'] == 1 ? true : false;

$title = $page_title . ($page_has_been_removed ? ' (' . gettext('removed page') . ')' : '');
?>


<!DOCTYPE html>
<html>

<head>
	<title><?php print $title ?></title>

	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>

	<link href="<?php print JQUERY_UI_CSS_URL ?>" rel="stylesheet"
		  type="text/css"/>
	<link href="<?php print JQUERY_UI_DATETIMEPICKER_CSS_URL ?>"
		  rel="stylesheet" type="text/css">
	<link
		href="<?php print $tolc_conf['project_url'] ?>/app/admin/rte/rte.css?version=1"
		rel="stylesheet"
		type="text/css"/>

	<script type="text/javascript" src="<?php print JQUERY_URL ?>"></script>
	<script type="text/javascript" src="<?php print JQUERY_UI_URL ?>"></script>

	<script type="text/javascript"
			src="<?php print JQUERY_UI_DATETIMEPICKER_URL ?>"></script>

	<?php if(substr($_SESSION['locale'], 0, 2) !== 'en') { ?>
	<script type="text/javascript"
			src="<?php print JQUERY_UI_i18n_DIR . '/jquery.ui.datepicker-' . substr($_SESSION['locale'], 0, 2) . '.js' ?>"></script>
	<script type="text/javascript"
			src="<?php print JQUERY_UI_DATETIMEPICKER_i18n_URL ?>"></script>
	<?php } ?>

	<script type="text/javascript" src="<?php print JUI_ALERT_URL ?>"></script>

	<script type="text/javascript"
			src="<?php print JQUERY_TINYMCE_URL ?>"></script>
	<script type="text/javascript"
			src="<?php print $tolc_conf['project_url'] ?>/app/admin/rte/rte.js?version=1"></script>
</head>

<body>

<div id="user_message">
</div>

<fieldset class="ui-widget ui-widget-content">
	<legend class="ui-widget-header ui-corner-all">
		<div><?php print gettext('Edit') ?></div>
	</legend>

	<div id="rte_tools1" class="rte_tools">

		<select id="www_page_versions_id">
		</select>

		<a id="btn_save"><?php print gettext('Save') ?></a>

		<label for="new_version"><?php print gettext('new_version') ?></label>
		<input id="new_version" type="checkbox">

	</div>

	<div id="rte_tools2" class="rte_tools">

		<label
			for="author_id"><?php print gettext('Submitted from') ?></label>
		<select id="author_id">
		</select>

		<label
			for="lk_content_status_id"><?php print gettext('as') ?></label>
		<select id="lk_content_status_id">
		</select>

		<label
			for="date_publish_start"><?php print gettext('to be published from') ?></label>
		<input id="date_publish_start">

		<label for="date_publish_end"><?php print gettext('until') ?></label>
		<input id="date_publish_end">

		<label for="editor_id"><?php print gettext('Managed by') ?></label>
		<select id="editor_id">
		</select>

	</div>

</fieldset>

<div id="rte_div">
	<textarea id="rte" rows="10" cols="60">
	</textarea>
</div>


<input id="lang" type="hidden"
	   value="<?php print substr($_SESSION['locale'], 0, 2) ?>">
<input id="dateformat" type="hidden" value="dd/mm/yy">

</body>

</html>