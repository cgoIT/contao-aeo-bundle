<?php

/**
 * Contao Open Source CMS
 * 
 * Copyright (C) 2005-2013 Leo Feyer
 * 
 * @package Zaeo
 * @link    http://contao.org
 * @license http://www.gnu.org/licenses/lgpl-3.0.html LGPL
 */


/**
 * Register the namespaces
 */
ClassLoader::addNamespaces(array
(
	'cgoIT',
));


/**
 * Register the classes
 */
ClassLoader::addClasses(array
(
	'cgoIT\aeo\AeoJavaScript'   => 'system/modules/zaeo/AeoJavaScript.php',
	'cgoIT\aeo\AeoHybrid'       => 'system/modules/zaeo/AeoHybrid.php',
	'cgoIT\aeo\AeoCE'           => 'system/modules/zaeo/AeoCE.php',
	'cgoIT\aeo\AeoModule'       => 'system/modules/zaeo/AeoModule.php',
	'cgoIT\aeo\PageRoot_Aeo'    => 'system/modules/zaeo/PageRoot_Aeo.php',
	// Util
	'cgoIT\aeo\AeoFrontendUtil' => 'system/modules/zaeo/util/AeoFrontendUtil.php',
	'cgoIT\aeo\AeoRedirectUtil' => 'system/modules/zaeo/util/AeoRedirectUtil.php',
	'cgoIT\aeo\AeoUtil'         => 'system/modules/zaeo/util/AeoUtil.php',
	'cgoIT\aeo\McwPageTree'     => 'system/modules/zaeo/widgets/McwPageTree.php',
));


/**
 * Register the templates
 */
TemplateLoader::addFiles(array
(
	'aeo_default_no_js'  => 'system/modules/zaeo/templates',
	'js_aeo_deobfuscate' => 'system/modules/zaeo/templates',
));
