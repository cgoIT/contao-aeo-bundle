<?php if (!defined('TL_ROOT')) die('You can not access this file directly!');

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

/**
 * Hooks
 */
$GLOBALS['TL_HOOKS']['getFrontendModule'][] = array('aeo\\AeoFrontendUtil', 'aeoGetFrontendModule');
$GLOBALS['TL_HOOKS']['getContentElement'][] = array('aeo\\AeoFrontendUtil', 'aeoGetContentElement');
$GLOBALS['TL_HOOKS']['outputFrontendTemplate'][] = array('aeo\\AeoFrontendUtil', 'obfuscateEmails');
$GLOBALS['TL_HOOKS']['getPageIdFromUrl'][] = array('aeo\\AeoRedirectUtil', 'getRedirectPageAeo');

/**
 * frontend moduls
 */
$GLOBALS['FE_MOD']['application']['aeo'] = 'aeo\\AeoModule';

/**
 * content elements
 */
$GLOBALS['TL_CTE']['includes']['aeo'] = 'aeo\\AeoCE';

/**
 * PageTypes
 */
$GLOBALS['TL_PTY']['root'] = 'aeo\\PageRoot_Aeo';

?>
