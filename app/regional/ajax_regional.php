<?php
session_start();
session_regenerate_id(true);

// prevent direct access
$isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) AND
    strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
if (!$isAjax) {
    print 'Access denied - not an AJAX request...';
    exit;
}

require_once '../conf/settings.php';
require_once $tolc_conf['project_dir'] . '/app/common/init.php';

// get params
$timezone = $_POST['timezone'];
$dateformat = $_POST['dateformat'];

$_SESSION['timezone'] = $timezone;
$_SESSION['dateformat'] = $dateformat;
?>