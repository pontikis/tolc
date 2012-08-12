<?php
session_start();
session_regenerate_id(true);

// allow inclusion of tolc_head_*.php tolc_panel.php tolc_functions.php
$tolc_include = true;

require_once 'conf/settings.php';
require_once $tolc_conf['project_dir'] . '/app/common/init.php';
require_once ADODB_PATH . '/adodb.inc.php';
require_once $tolc_conf['project_dir'] . '/app/common/utils_db.php';
require_once $tolc_conf['project_dir'] . '/app/common/utils.php';
require_once SIMPLE_HTML_DOM_PATH . '/simple_html_dom.php';

// retrieve url
$url = mb_substr(urldecode($_SERVER['REQUEST_URI']), mb_strlen($tolc_conf['project_url']));

// check for valid URL
$url = preg_replace('/\s+/', ' ', $url); //replace multiple spaces with one
$invalid_url = preg_match(CONST_REGEX_SANITIZE_URL, $url) ? true : false;
if($invalid_url) {
	$www_pages_id = 0;
	$www_page_versions_id = 0;
	$page_title = gettext('Invalid URL');
}

// check for direct access of '/app/index.php'
if($url == '/app/index.php' || $url == '/app/') {
	header('Location: ' . CONST_PROJECT_FULL_URL);
}

// connect to database
$conn = get_db_conn($tolc_conf['dbdriver']);

// retrieve user role
if(isset($_SESSION['username'])) {
	$sql = 'SELECT id, email, lk_roles_id FROM www_users WHERE username=' . $conn->qstr($_SESSION['username']);
	$rs = $conn->Execute($sql);
	if($rs === false) {
		trigger_error('Wrong SQL: ' . $sql . ' Error: ' . $conn->ErrorMsg(), E_USER_ERROR);
	}
	$user_id = $rs->fields['id'];
	$lk_roles_id = $rs->fields['lk_roles_id'];
	$user_email = $rs->fields['email'];
} else {
	$user_id = null;
	$lk_roles_id = null;
	$user_email = null;
}

// get current time (in UTC)
$now = $conn->qstr(now());

// check for reserved url (CASE INSENSITIVE)
if(in_array(mb_strtolower($url), array_map('mb_strtolower', $tolc_conf['pref_reserved_urls']))) {
	$_SESSION['url_reserved'] = $url;
	$url_to_go = isset($_SESSION['url']) ? $_SESSION['url'] : '';
	header('Location: ' . CONST_PROJECT_FULL_URL . $url_to_go);
} else {
	$url_sql = $conn->qstr(mb_strtolower($url));
	$_SESSION['url'] = $url;
}

if(!$invalid_url) {
// get page id and page title (CASE INSENSITIVE URL search)
	if(!$tolc_conf['pref_use_prepared_statements']) {
		$sql = 'SELECT id, title, is_removed FROM www_pages WHERE LOWER(url)=' . $url_sql;
		$rs = $conn->Execute($sql);

	} else {
		$sql = 'SELECT id, title, is_removed FROM www_pages WHERE LOWER(url)=?';
		$pst = $conn->Prepare($sql);
		$rs = $conn->Execute($pst, array(mb_strtolower($url)));
	}

	if($rs === false) {
		trigger_error('Wrong SQL: ' . $sql . ' Error: ' . $conn->ErrorMsg(), E_USER_ERROR);
	}
	$page_has_been_removed = $rs->fields['is_removed'] == 1 ? true : false;

	if($rs->RecordCount() == 0) {
		// set page id
		$www_pages_id = 0;
		// set page title
		$page_title = isset($_SESSION['username']) ? gettext('New page') : gettext('Page does not exist') . '...';
	} else {
		// retrieve page id
		$www_pages_id = $rs->fields['id'];

		// check for published version
		if($page_has_been_removed) {
			// set page version
			$www_page_versions_id = 0;
			// set page title
			$page_title = isset($_SESSION['username']) ? $rs->fields['title'] : gettext('Page not found') . '...';
		} else {
			// get page version
			$sql = 'SELECT id, author_id FROM www_page_versions ' .
				'WHERE www_pages_id=' . $www_pages_id .
				' AND lk_content_status_id=' . CONST_CONTENT_STATUS_APPROVED_KEY .
				' AND date_publish_start<=' . $now .
				' AND (date_publish_end IS NULL OR date_publish_end>' . $now . ')' .
				' ORDER BY date_publish_start DESC';
			$rs1 = $conn->SelectLimit($sql, 1, 0);
			if($rs1 === false) {
				trigger_error('Wrong SQL: ' . $sql . ' Error: ' . $conn->ErrorMsg(), E_USER_ERROR);
			}
			if($rs1->RecordCount() == 0) {
				// set page version
				$www_page_versions_id = 0;
				// set page title
				$page_title = isset($_SESSION['username']) ? $rs->fields['title'] : gettext('Page not found') . '...';
			} else {
				$www_page_versions_id = $rs1->fields['id'];
				// retrieve page title
				$page_title = $rs->fields['title'];
			}
		}
	}
}

// get template id
if($www_pages_id == 0) {
	// set default template id
	$www_templates_id = $tolc_conf['domains_tmpl'][$_SERVER['SERVER_NAME']];
} else {
	$sql = 'SELECT www_templates_id FROM www_page_templates ' .
		'WHERE www_pages_id=' . $www_pages_id .
		' AND date_start<=' . $now .
		' ORDER BY date_start DESC';
	$rs = $conn->SelectLimit($sql, 1, 0);
	if($rs === false) {
		trigger_error('Wrong SQL: ' . $sql . ' Error: ' . $conn->ErrorMsg(), E_USER_ERROR);
	} else {
		$www_templates_id = $rs->fields['www_templates_id'];
	}
}

// get template path
$sql = 'SELECT template_path, template_file, css_url FROM www_templates WHERE id = ' . $www_templates_id;
$rs = $conn->Execute($sql);
if($rs === false) {
	trigger_error('Wrong SQL: ' . $sql . ' Error: ' . $conn->ErrorMsg(), E_USER_ERROR);
}
$template_path = $rs->fields['template_path'];
$template_file = $rs->fields['template_file'];
$css_url = $rs->fields['css_url'];
$template_base_url = $tolc_conf['project_url'] . $template_path;

// store template html to variable
ob_start();
include($tolc_conf['project_dir'] . $template_path . $template_file);
$template_html = ob_get_contents();
ob_end_clean();

// create a DOM object
$html = new simple_html_dom();

// load template html
$html->load($template_html);


/**
 * set default favicon (just for Chrome)
 * http://code.google.com/p/chromium/issues/detail?id=39402
 * Chrome requests favicons on every request on pages that don't have a favicon (SO SCRIPT RUNS TWICE)
 */
$favicon_html = '<link rel="shortcut icon" href="' . $tolc_conf['project_url'] . '/favicon.ico" />';
// convert template head <link> href relevant to website root and collect <link> tags
$template_link_html = '';
$template_links = $html->find('link');
foreach($template_links as $template_link) {
	$link_rel = $template_link->rel;
	$link_href = $template_link->href;
	$template_link->href = $template_base_url . $link_href;
	if($link_rel == 'shortcut icon') {
		$favicon_html = $template_link->outertext;
	} else {
		$template_link_html .= $template_link->outertext;
	}
}

// convert template head <script> src relevant to website root and collect <script> tags
$template_scripts_html = '';
$template_scripts = $html->find('script');
foreach($template_scripts as $template_script) {
	$script_src = $template_script->src;
	$template_script->src = $template_base_url . $script_src;
	$template_scripts_html .= $template_script->outertext;
}

// collect template <meta> tags
$template_meta_html = '';
$template_meta_tags = $html->find('meta');
foreach($template_meta_tags as $template_meta_tag) {
	$template_meta_html .= $template_meta_tag->outertext;
}

// convert template <img> src relevant to website root
$template_images = $html->find('img[src]');
foreach($template_images as $template_image) {
	$img_src = $template_image->src;
	$template_image->src = $template_base_url . $img_src;
}

// convert template <input> src relevant to website root
$template_inputs = $html->find('input[src]');
foreach($template_inputs as $template_input) {
	$input_src = $template_input->src;
	$template_input->src = $template_base_url . $input_src;
}

// set page content
$a_active_elements = array();
if($www_page_versions_id > 0) {
	// get template active elements ids
	$sql = 'SELECT id, element_id FROM www_template_active_elements WHERE www_templates_id=' . $www_templates_id . ' ORDER BY display_order';
	$rs = $conn->Execute($sql);
	if($rs === false) {
		trigger_error('Wrong SQL: ' . $sql . ' Error: ' . $conn->ErrorMsg(), E_USER_ERROR);
	}
	$a_elements = $rs->GetRows();

	foreach($a_elements as $element) {
		// push to active elements array
		array_push($a_active_elements, '#' . $element['element_id']);
		// get content
		$sql = 'SELECT html FROM www_content ' .
			'WHERE www_page_versions_id=' . $www_page_versions_id .
			' AND www_template_active_elements_id=' . $element['id'];
		$rs = $conn->Execute($sql);
		if($rs === false) {
			trigger_error('Wrong SQL: ' . $sql . ' Error: ' . $conn->ErrorMsg(), E_USER_ERROR);
		}
		if($rs->RecordCount() == 1) {
			// set element content
			$selector = '[id=' . $element['element_id'] . ']';
			$res = $html->find($selector, 0);
			if($res) {
				$res->innertext = $rs->fields['html'];
			}
		}
	}
}

// set value to active elements hidden input
$active_elements = implode(', ', $a_active_elements);

// compose page title html
$page_title_html = '<title>' . $page_title . '</title>';

// compose favicon html (in case template has no favicon)
if(mb_strlen($favicon_html) == 0) {
	// store tolc head favicon html to variable
	ob_start();
	include($tolc_conf['project_dir'] . '/app/tolc_head_favicon.php');
	$tolc_head_favicon_html = ob_get_contents();
	ob_end_clean();
	$favicon_html = $tolc_head_favicon_html;
}

// store tolc head css html to variable
ob_start();
include($tolc_conf['project_dir'] . '/app/tolc_head_css.php');
$tolc_head_css_html = ob_get_contents();
ob_end_clean();

// store tolc head js html to variable
ob_start();
include($tolc_conf['project_dir'] . '/app/tolc_head_js.php');
$tolc_head_js_html = ob_get_contents();
ob_end_clean();

// store tolc panel html to variable
$tolc_panel_html = '';
if(isset($_SESSION['username'])) {
	ob_start();
	include($tolc_conf['project_dir'] . '/app/tolc_panel.php');
	$tolc_panel_html = ob_get_contents();
	ob_end_clean();
}

// store tolc functions html to variable
ob_start();
include($tolc_conf['project_dir'] . '/app/tolc_functions.php');
$tolc_functions_html = ob_get_contents();
ob_end_clean();

// page head
$template_head = $html->getElementByTagName('head');
if($template_head) {
	$head = $page_title_html . PHP_EOL .
		$favicon_html . PHP_EOL .
		$template_meta_html . PHP_EOL .
		$tolc_head_css_html . PHP_EOL .
		$template_link_html . PHP_EOL .
		$tolc_head_js_html . PHP_EOL .
		$template_scripts_html;
	$template_head->innertext = $head;
}

// page body
$template_body = $html->getElementByTagName('body');
if($template_body) {
	$template_body_html = $template_body->innertext;
	$body = $tolc_panel_html . PHP_EOL .
		$template_body_html . PHP_EOL .
		$tolc_functions_html;
	$template_body->innertext = $body;
}

// beautify and print page html
if($tolc_conf['pref_use_tidy'] && function_exists('tidy_parse_string')) {
	$tidy = tidy_parse_string($html, $tolc_conf['pref_tidy_config'], $tolc_conf['pref_tidy_encoding']);
	$tidy->cleanRepair();
	echo $tidy;
} else {
	echo $html;
}

// clear DOM object
$html->clear();

// free memory from database objects
if($rs)
	$rs->Close();
//database disconnect
if($conn)
	$conn->Close();
?>