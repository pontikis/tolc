<?php
if(strlen(session_id()) < 1) {
	session_start();
	session_regenerate_id();
}

/**
 * check valid origin
 */
if(!in_array($_SERVER['SERVER_NAME'], $tolc_conf['pref_valid_origins'])) {
	print 'Access denied - Invalid origin';
	exit;
}

/**
 * error handling
 */
error_reporting($tolc_conf['pref_error_reporting']);
set_error_handler('error_handler');

/**
 * initialize data folder
 */
$site_data_folder = $tolc_conf['domains_data_folder'][$_SERVER['SERVER_NAME']];
define('CONST_DATA_FOLDER_REGEX', '/[;\\\\\\.&,:$><]/i');
if(preg_match(CONST_DATA_FOLDER_REGEX, $site_data_folder) > 0) {
	die('Invalid characters ;\.&,:$>< in site data folder...');
}

$site_data_path = $tolc_conf['project_dir'] . '/data/' . $site_data_folder;
$site_tpl_path = $site_data_path . '/tpl';
$site_files_path = $site_data_path . '/files';
if(!file_exists($site_data_path)) {
	mkdir($site_data_path, 0775);
}
if(!file_exists($site_tpl_path)) {
	mkdir($site_tpl_path, 0775);
}
if(!file_exists($site_files_path)) {
	mkdir($site_files_path, 0775);
}

/**
 * system reserved usernames
 */
$a_sys_reserved_usernames = array('root', 'demo');

/**
 * constants based on settings
 */
$host = $_SERVER['SERVER_NAME'];
$port = $_SERVER['SERVER_PORT'] == '80' ? '' : ':' . $_SERVER['SERVER_PORT'];
$http_prot = empty($_SERVER['HTTPS']) ? 'http' : 'https';
define('CONST_PROJECT_HOST', $http_prot . '://' . $host . $port);
define('CONST_PROJECT_FULL_URL', CONST_PROJECT_HOST . $tolc_conf['project_url']);

/**
 * help
 */
define('CONST_HELP_TAG', '<img src="' . $tolc_conf['project_url'] . '/app/images/help.png">');

/**
 * localization (gettext)
 */
/* initialize $_SESSION['locale'] */
define('CONST_UTF8', 'UTF-8');
define('CONST_UTF8_NO_DASH', 'UTF8');

if(!isset($_SESSION['locale'])) {
	$_SESSION['locale'] = $tolc_conf['pref_default_locale_code'] . '.' . CONST_UTF8;
}
if(function_exists('gettext')) {
	$locale = $_SESSION['locale'];
	putenv("LC_ALL=$locale");
	setlocale(LC_ALL, $locale);
	bindtextdomain("tolc", $tolc_conf['project_dir'] . "/app/i18n");
	textdomain("tolc");
} else {
	require_once $tolc_conf['project_dir'] . '/app/common/gettext_missing.php';
}

/**
 * DATES HANDLING
 * all dates stored as strings, formatted as 14-digit timestamps in UTC using date_encode()
 * dates are served to visitor using date_decode()
 * $_SESSION['user_timezone'] and $_SESSION['user_dateformat'] used as arguments
 */
/* set server default timezone (it is possible to set from php.ini) */
define('CONST_SERVER_TIMEZONE', 'UTC');
date_default_timezone_set(CONST_SERVER_TIMEZONE);

/* set server dateformat */
define('CONST_SERVER_DATEFORMAT', 'YmdHis');

/* available date formats */
define('CONST_DF_EU_FULLYEAR_LZ_SLASH_24H_LZ', 'EU_FULLYEAR_LZ_SLASH_24H_LZ'); // EU format (day/month/year) with full year, slash as delimiter with leading zeros. 24h clock with leading zeros.
define('CONST_DF_EU_SHORTYEAR_LZ_SLASH_24H_LZ', 'EU_SHORTYEAR_LZ_SLASH_24H_LZ'); // EU format (day/month/year) with short year, slash as delimiter with leading zeros. 24h clock with leading zeros.
define('CONST_DF_US_FULLYEAR_LZ_SLASH_24H_LZ', 'US_FULLYEAR_LZ_SLASH_24H_LZ'); // US format (month/day/year) with full year, slash as delimiter with leading zeros. 24h clock with leading zeros.
define('CONST_DF_US_SHORTYEAR_LZ_SLASH_24H_LZ', 'US_SHORTYEAR_LZ_SLASH_24H_LZ'); // US format (month/day/year) with short year, slash as delimiter with leading zeros. 24h clock with leading zeros.
define('CONST_DF_EU_FULLYEAR_LZ_DASH_24H_LZ', 'EU_FULLYEAR_LZ_DASH_24H_LZ'); // EU format (day/month/year) with full year, dash as delimiter with leading zeros. 24h clock with leading zeros.
define('CONST_DF_EU_SHORTYEAR_LZ_DASH_24H_LZ', 'EU_SHORTYEAR_LZ_DASH_24H_LZ'); // EU format (day/month/year) with short year, dash as delimiter with leading zeros. 24h clock with leading zeros.
define('CONST_DF_US_FULLYEAR_LZ_DASH_24H_LZ', 'US_FULLYEAR_LZ_DASH_24H_LZ'); // US format (month/day/year) with full year, dash as delimiter with leading zeros. 24h clock with leading zeros.
define('CONST_DF_US_SHORTYEAR_LZ_DASH_24H_LZ', 'US_SHORTYEAR_LZ_DASH_24H_LZ'); // US format (month/day/year) with short year, dash as delimiter with leading zeros. 24h clock with leading zeros.

$a_date_format = array(
	CONST_DF_EU_FULLYEAR_LZ_SLASH_24H_LZ => array('php_datetime' => 'd/m/Y H:i:s', 'php_datetime_short' => 'd/m/Y H:i', 'php_date' => 'd/m/Y', 'jq_date' => 'dd/mm/yy', 'jq_time' => 'hh:mm:ss', 'jq_time_short' => 'hh:mm'),
	CONST_DF_EU_SHORTYEAR_LZ_SLASH_24H_LZ => array('php_datetime' => 'd/m/y H:i:s', 'php_datetime_short' => 'd/m/y H:i', 'php_date' => 'd/m/y', 'jq_date' => 'dd/mm/y', 'jq_time' => 'hh:mm:ss', 'jq_time_short' => 'hh:mm'),
	CONST_DF_US_FULLYEAR_LZ_SLASH_24H_LZ => array('php_datetime' => 'm/d/Y H:i:s', 'php_datetime_short' => 'm/d/Y H:i', 'php_date' => 'm/d/Y', 'jq_date' => 'mm/dd/yy', 'jq_time' => 'hh:mm:ss', 'jq_time_short' => 'hh:mm'),
	CONST_DF_US_SHORTYEAR_LZ_SLASH_24H_LZ => array('php_datetime' => 'm/d/y H:i:s', 'php_datetime_short' => 'm/d/y H:i', 'php_date' => 'm/d/y', 'jq_date' => 'mm/dd/y', 'jq_time' => 'hh:mm:ss', 'jq_time_short' => 'hh:mm'),
	CONST_DF_EU_FULLYEAR_LZ_DASH_24H_LZ => array('php_datetime' => 'd-m-Y H:i:s', 'php_datetime_short' => 'd-m-Y H:i', 'php_date' => 'd-m-Y', 'jq_date' => 'dd-mm-yy', 'jq_time' => 'hh:mm:ss', 'jq_time_short' => 'hh:mm'),
	CONST_DF_EU_SHORTYEAR_LZ_DASH_24H_LZ => array('php_datetime' => 'd-m-y H:i:s', 'php_datetime_short' => 'd-m-y H:i', 'php_date' => 'd-m-y', 'jq_date' => 'dd-mm-y', 'jq_time' => 'hh:mm:ss', 'jq_time_short' => 'hh:mm'),
	CONST_DF_US_FULLYEAR_LZ_DASH_24H_LZ => array('php_datetime' => 'm-d-Y H:i:s', 'php_datetime_short' => 'm-d-Y H:i', 'php_date' => 'm-d-Y', 'jq_date' => 'mm-dd-yy', 'jq_time' => 'hh:mm:ss', 'jq_time_short' => 'hh:mm'),
	CONST_DF_US_SHORTYEAR_LZ_DASH_24H_LZ => array('php_datetime' => 'm-d-y H:i:s', 'php_datetime_short' => 'm-d-y H:i', 'php_date' => 'm-d-y', 'jq_date' => 'mm-dd-y', 'jq_time' => 'hh:mm:ss', 'jq_time_short' => 'hh:mm')
);

define('CONST_SAFE_DATEFORMAT_STRTOTIME', 'Y-m-d H:i:s');

/* initialize $_SESSION['user_timezone'] (default visitor timezone) */
if(!isset($_SESSION['user_timezone'])) {
	$_SESSION['user_timezone'] = $tolc_conf['pref_timezone'];
}

/* initialize $_SESSION['user_dateformat'] (default visitor dateformat) */
if(!isset($_SESSION['user_dateformat'])) {
	$_SESSION['user_dateformat'] = CONST_DF_EU_FULLYEAR_LZ_SLASH_24H_LZ;
}

/**
 * constants TINYMCE
 * UPLOADS_URL must be writable from web server, trailing slash required
 */
define('CONST_BASE_URL', $tolc_conf['project_url'] . '/'); // used by tinymce
define('UPLOADS_URL', $tolc_conf['project_url'] . '/data/' . $site_data_folder . '/'); // used from ezfilemanager

/**
 * sanitize URL regex
 *
 * \040 space
 * \w letters digits and underscore
 * u force UTF8
 *
 */
define('CONST_REGEX_SANITIZE_URL', '/[^\040\w\/\.\-\:]/u');
define('CONST_REGEX_SANITIZE_URL_LEGACY', '/[' . preg_quote('!"#$%&' . "'" . '()*+,;<=>?@[\]^`{|}~') . ']/');
define('CONST_URL_DB_MAXLENGTH', 254);

/**
 * Various
 */
define('CONST_ACCESS_DENIED', gettext('Access denied'));

/**
 * lookups
 */
/* user status */
define('CONST_USER_STATUS_PENDING_KEY', 1);
define('CONST_USER_STATUS_ACTIVE_KEY', 2);
define('CONST_USER_STATUS_INACTIVE_KEY', 3);

define('CONST_USER_STATUS_PENDING_VALUE', gettext('pending registration'));
define('CONST_USER_STATUS_ACTIVE_VALUE', gettext('active user'));
define('CONST_USER_STATUS_INACTIVE_VALUE', gettext('inactive user'));

$a_user_status_keys = array(CONST_USER_STATUS_PENDING_KEY,
	CONST_USER_STATUS_ACTIVE_KEY,
	CONST_USER_STATUS_INACTIVE_KEY
);
$a_user_status_values = array(CONST_USER_STATUS_PENDING_VALUE,
	CONST_USER_STATUS_ACTIVE_VALUE,
	CONST_USER_STATUS_INACTIVE_VALUE
);

/* user roles */
define('CONST_ROLE_ADMIN_KEY', 1);
define('CONST_ROLE_EDITOR_KEY', 2);
define('CONST_ROLE_AUTHOR_KEY', 3);

define('CONST_ROLE_ADMIN_VALUE', gettext('admin'));
define('CONST_ROLE_EDITOR_VALUE', gettext('editor'));
define('CONST_ROLE_AUTHOR_VALUE', gettext('author'));

$a_role_keys = array(CONST_ROLE_ADMIN_KEY,
	CONST_ROLE_EDITOR_KEY,
	CONST_ROLE_AUTHOR_KEY
);
$a_role_values = array(CONST_ROLE_ADMIN_VALUE,
	CONST_ROLE_EDITOR_VALUE,
	CONST_ROLE_AUTHOR_VALUE
);

/* content status */
define('CONST_CONTENT_STATUS_DRAFT_KEY', 1);
define('CONST_CONTENT_STATUS_PENDING_REVIEW_KEY', 2);
define('CONST_CONTENT_STATUS_UNDER_REVIEW_KEY', 3);
define('CONST_CONTENT_STATUS_APPROVED_KEY', 4);
define('CONST_CONTENT_STATUS_REJECTED_KEY', 5);

define('CONST_CONTENT_STATUS_DRAFT_VALUE', gettext('draft'));
define('CONST_CONTENT_STATUS_PENDING_REVIEW_VALUE', gettext('pending review'));
define('CONST_CONTENT_STATUS_UNDER_REVIEW_VALUE', gettext('under review'));
define('CONST_CONTENT_STATUS_APPROVED_VALUE', gettext('approved'));
define('CONST_CONTENT_STATUS_REJECTED_VALUE', gettext('rejected'));

/**
 * paths (lib)
 */
define('LIB_URL', $tolc_conf['project_url'] . '/lib');
define('LIB_DIR', $tolc_conf['project_dir'] . '/lib');
define('LIB_EXT_DIR', '/ext');

define('JQUERY_URL', LIB_URL . LIB_EXT_DIR . '/jquery-1.8.2/jquery-1.8.2.min.js');

define('JQUERY_UI_URL', LIB_URL . LIB_EXT_DIR . '/jquery-ui-1.8.23.custom/js/jquery-ui-1.8.23.custom.min.js');
define('JQUERY_UI_CSS_URL', LIB_URL . LIB_EXT_DIR . '/jquery-ui-1.8.23.custom/css/' . $tolc_conf['pref_jqueryui_theme'] . '/jquery-ui-1.8.23.custom.css');
define('JQUERY_UI_i18n_DIR', LIB_URL . LIB_EXT_DIR . '/jquery-ui-localize');
define('JQUERY_UI_DATETIMEPICKER_URL', LIB_URL . LIB_EXT_DIR . '/jquery-ui-timepicker-addon-1.0.3/jquery-ui-timepicker-addon.js');
define('JQUERY_UI_DATETIMEPICKER_i18n_URL', LIB_URL . LIB_EXT_DIR . '/jquery-ui-timepicker-addon-1.0.3/localization/jquery-ui-timepicker-' . substr($_SESSION['locale'], 0, 2) . '.js');
define('JQUERY_UI_DATETIMEPICKER_CSS_URL', LIB_URL . LIB_EXT_DIR . '/jquery-ui-timepicker-addon-1.0.3/jquery-ui-timepicker-addon.css');
define('JQUERY_UI_EXT_AUTOCOMPLETE_HTML_URL', LIB_URL . LIB_EXT_DIR . '/jquery-ui-extensions/jquery.ui.autocomplete.html.js');
define('JQUERY_UI_LAYOUT_URL', LIB_URL . LIB_EXT_DIR . '/jquery-ui-layout.v.1.3.0-rc30.4/jquery.layout.js');
define('JQUERY_UI_LAYOUT_CSS_URL', LIB_URL . LIB_EXT_DIR . '/jquery-ui-layout.v.1.3.0-rc30.4/layout-default.css');
define('JSTREE_URL', LIB_URL . LIB_EXT_DIR . '/jstree.v.pre1.0_fix1/jquery.jstree.js');
define('QTIP2_URL', LIB_URL . LIB_EXT_DIR . '/Craga89-qTip2-bbb88cf/jquery.qtip.min.js');
define('QTIP2_CSS_URL', LIB_URL . LIB_EXT_DIR . '/Craga89-qTip2-bbb88cf/jquery.qtip.css');
define('PASSWORDSTRENGTH_URL', LIB_URL . LIB_EXT_DIR . '/passwordstrength/passwordstrength.js');
define('PASSWORDSTRENGTH_CSS_URL', LIB_URL . LIB_EXT_DIR . '/passwordstrength/passwordstrength.css');
define('JUI_ALERT_URL', LIB_URL . '/jui_alert_1.0/jquery.jui_alert.js');
define('JQ_EASY_SLIDE_PANEL_URL', LIB_URL . LIB_EXT_DIR . '/jqEasySlidePanel_1.0/jquery.slidePanel.js');

define('JQUERY_TINYMCE_DIR', '/tinymce_3.5.7_jquery');
define('JQUERY_TINYMCE_PATH', LIB_URL . LIB_EXT_DIR . JQUERY_TINYMCE_DIR);
define('JQUERY_TINYMCE_URL', JQUERY_TINYMCE_PATH . '/jquery.tinymce.js');
define('TINYMCE_URL', JQUERY_TINYMCE_PATH . '/tiny_mce.js');
define('TINYMCE_POPUP_URL', JQUERY_TINYMCE_PATH . '/tiny_mce_popup.js');
define('EZFILEMANAGER_URL', JQUERY_TINYMCE_PATH . '/plugins/ezfilemanager/index.php');

define('PHPASS', LIB_DIR . LIB_EXT_DIR . '/phpass-0.3/PasswordHash.php');
define('ADODB_PATH', LIB_DIR . LIB_EXT_DIR . '/adodb_5.18a');
define('SIMPLE_HTML_DOM_PATH', LIB_DIR . LIB_EXT_DIR . '/simplehtmldom_1_5');
define('HTML_PURIFIER_PATH', LIB_DIR . LIB_EXT_DIR . '/htmlpurifier-4.4.0-lite/library/HTMLPurifier.auto.php');

/**
 * @param $err_no
 * @param $err_str
 * @param $err_file
 * @param $err_line
 * Error handler function. Replaces PHP's error handler.
 * E_ERROR, E_PARSE, E_CORE_ERROR, E_CORE_WARNING, E_COMPILE_ERROR, E_COMPILE_WARNING are always handled by PHP.
 * E_WARNING, E_NOTICE, E_USER_ERROR, E_USER_WARNING, E_USER_NOTICE are handled by this function.
 */
function error_handler($err_no, $err_str, $err_file, $err_line) {
	// if error_reporting is set to 0, exit. This is also the case when using @
	if(ini_get('error_reporting') == '0')
		return;
	// handle error
	switch($err_no) {
		case E_WARNING:
			$msg = '[ErrNo=' . $err_no . ' (WARNING), File=' . $err_file . ', Line=' . $err_line . '] ' . $err_str;
			log_error($msg, (!defined('INSTALLING'))); // e.g. warnings are hidden while installing
			if(!defined('INSTALLING'))
				exit;
			break;
		case E_USER_ERROR:
			$msg = '[ErrNo=' . $err_no . ' (USER_ERROR), File=' . $err_file . ', Line=' . $err_line . '] ' . $err_str;
			log_error($msg);
			exit;
			break;
		case E_USER_WARNING:
			$msg = $err_str;
			set_last_message(false, $msg);
			header('Location: ' . CONST_PROJECT_FULL_URL);
			exit;
			break;
		case E_NOTICE:
		case E_USER_NOTICE:
		case 2048: // E_STRICT in PHP5
			// ignore
			break;
		default:
			// unknown error. Log in file (only) and continue execution
			$msg = '[ErrNo=' . $err_no . ' (UNKNOWN_ERROR), File=' . $err_file . ', Line=' . $err_line . '] ' . $err_str;
			log_error($msg, false);
			break;
	}
}

/**
 * @param $msg
 * @param bool $show_onscreen
 * Log an error to custom file (error.log in project's directory)
 */
function log_error($msg, $show_onscreen = true) {
	global $tolc_conf;
	// put in screen
	if($show_onscreen)
		print $msg;

	// put in file
	@error_log(date('Y-m-d H:i:s') . ': ' . $msg . "\n", 3, $tolc_conf['project_dir'] . '/log/error.log');
}

?>