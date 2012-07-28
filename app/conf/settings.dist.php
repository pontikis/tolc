<?php
/**
 * @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
 * DO NOT EDIT THIS FILE! --- use conf/settings.php instead
 * @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
 */

/**
 * URI - PATH MAPPING
 */
$tolc_conf['project_dir'] = $_SERVER['DOCUMENT_ROOT'] . '/path/to/tolc';
$tolc_conf['project_url'] = '/relative_url/to/tolc';

/**
 * DATABASE (or SCHEMA) PER DOMAIN (or SERVER NAME or IP)
 */
$tolc_conf['domains_db'] = array(
	'www.domain.tld' => 'tolc',
	'localhost' => 'tolc'
);

/**
 * DEFAULT TEMPLATE ID PER DOMAIN (or SERVER NAME or IP)
 */
$tolc_conf['domains_tmpl'] = array(
	'www.domain.tld' => 1,
	'localhost' => 1
);

/**
 * DATABASE CONNECTION STRING
 * (http://phplens.com/lens/adodb/docs-adodb.htm#drivers)
 */
$tolc_conf['dbdriver'] = 'mysqlt'; // mysql, mysqlt, postgres, firebird
$tolc_conf['dbserver'] = 'SERVER-NAME-OR-IP-HERE';
$tolc_conf['dbuser'] = 'USER-HERE';
$tolc_conf['dbpass'] = 'PASSWORD-HERE';
$tolc_conf['dsn_options'] = '?persist=0&fetchmode=2';
$tolc_conf['dsn_custom'] = ''; // sqlite, oci8 (oracle), access, ado_mssql

/**
 * GLOBAL PREFERENCES
 */
/* valid origins */
$tolc_conf['pref_valid_origins'] = array(
	'www.domain.tld',
	'localhost');

/* error reporting (http://php.net/manual/en/function.error-reporting.php) */
$tolc_conf['pref_error_reporting'] = 'E_ALL ^ E_NOTICE';

/* locale */
$tolc_conf['pref_default_locale_code'] = 'en_GB';
$tolc_conf['pref_default_locale_encoding'] = '.UTF-8';

/* reserved urls */
$tolc_conf['pref_reserved_urls'] = array(
	'login' => '/login',
	'regional' => '/regional'
);

/* tidy settings (http://tidy.sourceforge.net/docs/quickref.html) */
$tolc_conf['pref_use_tidy'] = true;
$tolc_conf['pref_tidy_config'] = array(
	'indent' => TRUE,
	'output-xhtml' => TRUE,
	'wrap' => 200);
$tolc_conf['pref_tidy_encoding'] = 'UTF8';

/* visitor regional settings (default values) */
$tolc_conf['pref_timezone'] = 'UTC';
$tolc_conf['pref_date_format'] = 'd/m/Y'; // http://php.net/manual/en/datetime.formats.date.php
$tolc_conf['pref_decimal_mark'] = '.';
$tolc_conf['pref_thousands_separator'] = ',';
?>