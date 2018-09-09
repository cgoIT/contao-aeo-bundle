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
 * Class PageRoot_Aeo
 */
class PageRoot_Aeo extends \PageRoot {

	/**
	 * Referenz auf AeoRedirectUtil
	 * @var AeoRedirectUtil
	 */
	
	public function __construct() {
		parent::__construct();
		$this->import('\\Database', 'Database');
		$this->import('cgoIT\\aeo\\AeoRedirectUtil', 'AeoRedirectUtil');
	}
	
	/**
	 * Redirect to the first active regular page
	 * @param integer
	 * @param boolean
	 * @return integer
	 */
	public function generate($pageId, $blnReturn=false, $blnPreferAlias=false) {
		$id = $this->AeoRedirectUtil->redirectFromRootPage();
		if ($id !== FALSE) {
			return $id;
		} else {
			return parent::generate($pageId, $blnReturn, $blnPreferAlias);
		}
	}
}
?>