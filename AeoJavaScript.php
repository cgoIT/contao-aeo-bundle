<?php

/**
 * Contao Open Source CMS
 * Copyright (C) 2005-2011 Leo Feyer
 *
 * Formerly known as TYPOlight Open Source CMS.
 *
 * This program is free software: you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation, either
 * version 3 of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this program. If not, please visit the Free
 * Software Foundation website at <http://www.gnu.org/licenses/>.
 *
 * PHP version 5
 * @copyright  cgo IT, 2012-2013
 * @author     Carsten Götzinger (info@cgo-it.de)
 * @package    aeo
 * @license    GNU/LGPL
 * @filesource
 */

namespace cgoIT\aeo;

/**
 * Class AeoJavaScript
 */
class AeoJavaScript
{

	/**
	 * Current object instance (Singleton)
	 * @var Cache
	 */
	protected static $objInstance;
	
	/**
	 * Template
	 * @var string
	 */
	protected static $strTemplate = 'js_aeo_deobfuscate';
	
	/**
	 * Prevent direct instantiation (Singleton) 
	 */
	protected function __construct() {}


	/**
	 * Prevent cloning of the object (Singleton)
	 */
	final private function __clone() {}
	
	public function getContent($folder, $rot13 = true) {
		$Template = new \FrontendTemplate(self::$strTemplate);
		$Template->rot13 = $rot13;
		$Template->folder = $folder;
		$Template->tooltip_js_on = $GLOBALS['TL_LANG']['aeo']['tooltip_js'];
		$Template->tooltip_js_off = $GLOBALS['TL_LANG']['aeo']['tooltip_no_js'];
		return $Template->parse();
	}

	/**
	 * Instantiate a new cache object and return it (Factory)
	 * @return Cache
	 */
	public static function getInstance() {
		if (!is_object(self::$objInstance)) {
			self::$objInstance = new self();
		}

		return self::$objInstance;
	}
}

?>