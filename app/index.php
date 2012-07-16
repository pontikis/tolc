<?php
session_start();
session_regenerate_id(true);
require_once 'common/settings.php';
require_once 'common/error_handler.php';
require_once 'common/init.php';
require_once 'common/gettext.php';
require_once ADODB_PATH . '/adodb.inc.php';
require_once 'common/db_utils.php';
require_once 'common/utils.php';
require_once SIMPLE_HTML_DOM_PATH . '/simple_html_dom.php';

// connect to database
$conn = get_db_conn($DBType, $DBUser, $DBPass, $DBServer, $DBName, $dsn_options);

// retrieve url
$url = DOMAIN_USED ? urldecode($_SERVER['REQUEST_URI']) : mb_substr(urldecode($_SERVER['REQUEST_URI']), mb_strlen(PROJECT_URL));

// check for login request
$login = isset($_SESSION['login']) ? true : false;
// define mode (public mode or admin mode)
$admin_mode = isset($_SESSION['username']);

// check

if ($url == PREF_LOGIN_URL) {
    $_SESSION['login'] = true;
    $url_to_go = isset($_SESSION['url']) ? $_SESSION['url'] : '';
    header('Location: ' . PROJECT_FULL_URL . $url_to_go);
} else {
    unset($_SESSION['login']);
    $url_sql = $conn->qstr($url);
    $_SESSION['url'] = $url;
}


// get template id and page title
$sql = 'SELECT id, title, www_templates_id FROM www_pages WHERE url=' . $url_sql;
$rs = $conn->Execute($sql);
if ($rs === false) {
    trigger_error('Wrong SQL: ' . $sql . ' Error: ' . $conn->ErrorMsg(), E_USER_ERROR);
}
if ($rs->RecordCount() == 0) {
    $www_pages_id = 0;
    $new_page = true;
    // get default template id
    $www_templates_id = $domains_tmpl[$host];
    // get page title
    $page_title = $admin_mode ? gettext('New page') : gettext('Page not found') . '...';
} else {
    $www_pages_id = $rs->fields['id'];
    $new_page = false;
    // get template id for this url
    $www_templates_id = $rs->fields['www_templates_id'];
    // get page title
    $page_title = $rs->fields['title'];
}

// get template path
$sql = 'SELECT template_path, template_file, css_url FROM www_templates WHERE id = ' . $www_templates_id;
$rs = $conn->Execute($sql);
if ($rs === false) {
    trigger_error('Wrong SQL: ' . $sql . ' Error: ' . $conn->ErrorMsg(), E_USER_ERROR);
}
$template_path = $rs->fields['template_path'];
$template_file = $rs->fields['template_file'];
$css_url = $rs->fields['css_url'];
$template_base_url = PROJECT_URL . $template_path;

// store template html to variable
ob_start();
include(PROJECT_DIR . $template_path . $template_file);
$template_html = ob_get_contents();
ob_end_clean();

// create a DOM object
$html = new simple_html_dom();
// load template html
$html->load($template_html);

// convert template images src relevant to root (/)
$template_images = $html->find('img');
foreach ($template_images as $template_image) {
    $img_src = $template_image->src;
    $template_image->src = $template_base_url . $img_src;
}

// convert template css href relevant to root (/)
$template_links = $html->find('link');
foreach ($template_links as $template_link) {
    $link_href = $template_link->href;
    if ($link_href == $css_url) {
        $template_link->href = $template_base_url . $link_href;
        break;
    }
}

// set page title
$res = $html->getElementByTagName('title');
if ($res)
    $res->innertext = $page_title;

// set page content
$a_active_elements = array();
if (!$new_page) {
    // get template active elements ids
    $sql = 'SELECT id, element_id FROM www_template_active_elements WHERE www_templates_id=' . $www_templates_id . ' ORDER BY display_order';
    $rs = $conn->Execute($sql);
    if ($rs === false) {
        trigger_error('Wrong SQL: ' . $sql . ' Error: ' . $conn->ErrorMsg(), E_USER_ERROR);
    }
    $a_elements = $rs->GetRows();

    $now = $conn->qstr(now());
    foreach ($a_elements as $element) {
        // push to active elements array
        array_push($a_active_elements, '#' . $element['element_id']);
        // get content
        $sql = 'SELECT html FROM www_content ' .
            'WHERE www_pages_id=' . $www_pages_id .
            ' AND www_template_active_elements_id=' . $element['id'] .
            ' AND lk_publish_status_id=' . CONST_PUBLISH_STATUS_PUBLISHED_KEY .
            ' AND date_start<=' . $now .
            ' AND (date_end is null OR date_end>=' . $now . ')';
        $rs = $conn->Execute($sql);
        if ($rs === false) {
            trigger_error('Wrong SQL: ' . $sql . ' Error: ' . $conn->ErrorMsg(), E_USER_ERROR);
        }
        if ($rs->RecordCount() == 1) {
            // set element content
            $selector = '[id=' . $element['element_id'] . ']';
            $res = $html->find($selector, 0);
            if ($res)
                $res->innertext = $rs->fields['html'];
        }
    }
}

// set value to active elements hidden input
$active_elements = implode(', ', $a_active_elements);
$res = $html->find('[id=active_elements]', 0);
if ($res)
    $res->value = $active_elements;


// beautify and print page html
if (PREF_USE_TIDY) {
    $tidy = tidy_parse_string($html, unserialize(PREF_TIDY_CONFIG), PREF_TIDY_ENCODING);
    $tidy->cleanRepair();
    echo $tidy;
} else {
    echo $html;
}

// clear DOM object
$html->clear();

// free memory
if ($rs)
    $rs->Close();
//database disconnect
if ($conn)
    $conn->Close();
?>