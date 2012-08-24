<?php

/**
 * @param $driver
 * @return database connection
 */
function get_db_conn($driver) {

	global $tolc_conf;

	switch($driver) {
		case 'mysql':
		case 'mysqlt':
		case 'mysqli':
			$dsn = $driver . '://' . $tolc_conf['dbuser'] . ':' . rawurlencode($tolc_conf['dbpass']) .
				'@' . $tolc_conf['dbserver'] . '/' .
				$tolc_conf['domains_db'][$_SERVER['SERVER_NAME']] . $tolc_conf['dsn_options'];
			$conn = NewADOConnection($dsn);
			$conn->execute('SET NAMES UTF8'); // TODO it is required to 5.3.3-7+squeeze14 (but not needed to Arclinux php 5.4.4)
			return $conn;
		case 'postgres':
		case 'firebird':
			$dsn = $driver . '://' . $tolc_conf['dbuser'] . ':' . rawurlencode($tolc_conf['dbpass']) .
				'@' . $tolc_conf['dbserver'] . '/' .
				$tolc_conf['domains_db'][$_SERVER['SERVER_NAME']] . $tolc_conf['dsn_options'];
			$conn = NewADOConnection($dsn);
			return $conn;
			break;
		case 'sqlite':
		case 'oci8':
			return NewADOConnection($tolc_conf['dsn_custom']);
			break;
		case 'access':
		case 'db2':  // TODO not tested - possible & must be removed for php > 4
			$db =& ADONewConnection($driver);
			$db->Connect($tolc_conf['dsn_custom']);
			return $db;
			break;
		case 'odbc_mssql': // TODO not tested - possible & must be removed for php > 4
			$db =& ADONewConnection($driver);
			$db->Connect($tolc_conf['dsn_custom'], $tolc_conf['dbuser'], $tolc_conf['dbpass']);
			return $db;
			break;
	}

}

?>