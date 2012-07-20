<?php
session_start();
session_regenerate_id(true);
require_once '../../common/settings.php';
require_once PROJECT_DIR . '/app/common/error_handler.php';
require_once PROJECT_DIR . '/app/common/init.php';
require_once PROJECT_DIR . '/app/common/gettext.php';
require_once ADODB_PATH . '/adodb.inc.php';
require_once PROJECT_DIR . '/app/common/db_utils.php';
require_once PROJECT_DIR . '/app/common/utils.php';
//require_once SIMPLE_HTML_DOM_PATH . '/simple_html_dom.php';

// check for logged in user
if(!isset($_SESSION['username'])) {
    print gettext('Access denied') . '...';
    exit;
}

?>

<html>
<head>
    <link href="<?php print JQUERY_UI_CSS_URL ?>" rel="stylesheet" type="text/css" />
    <script type="text/javascript" src="<?php print JQUERY_URL ?>"></script>
    <script type="text/javascript" src="<?php print JQUERY_UI_URL ?>"></script>
    <script type="text/javascript" src="<?php print JQUERY_TINYMCE_URL ?>"></script>
    <script type="text/javascript" src="<?php print PROJECT_URL ?>/app/admin/rte/rte.js?version=1"></script>
</head>

<body>
<div id="rte_div"
     title="<?php print gettext('Edit')?>">
    <textarea id="rte" rows="10" cols="60">
    </textarea>
</div>
</body>

</html>