<?php

/**
 * Run this script to generate or update the documentation
 * 
 * 
 * If you run this script the first time or after you deleted
 * the configuration file (build/phpdox.xml), a new configuration 
 * file based on your filesystem will be created
 * 
 * After running this script you can find the new or updated 
 * documetation in the the html and xml directories.
 */

$root = dirname(dirname(__FILE__));

$dir_app = $root . '/app';
$dir_docs = $root . '/docs/';
$dir_build = $dir_docs . 'build';

$xml_phpdox = $root . '/docs/build/phpdox.xml';
$xml_phploc = $root . '/docs/build/phploc.xml';

if(!is_file($xml_phpdox)) {
	shell_exec("php $dir_build/phpDox.phar --skel > $xml_phpdox");

	$str=file_get_contents($xml_phpdox);
	$str=str_replace( 
		[ 
			'${basedir}/src', 
			'${basedir}/build/phpdox/',
			'${basedir}/build', 
			'<source type="phploc" />', 
			'${basedir}' 
		], 
		[ 
			$dir_app, 
			$dir_docs, 
			$dir_build, 
			'--><source type="phploc" /><!--',
			$root 
		], 
		$str);

	file_put_contents($xml_phpdox, $str);
}

shell_exec("php $dir_build/phpLoc.phar $dir_app --log-xml $xml_phploc");
shell_exec("php $dir_build/phpDox.phar -f $xml_phpdox");