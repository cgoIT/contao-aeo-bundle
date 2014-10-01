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
 * Class AeoModule
 */
class AeoHybrid extends \Hybrid
{

	/**
	 * Primary key
	 * @var string
	 */
	protected $strPk = 'id';

	/**
	 * Template
	 * @var string
	 */
	protected $strTemplate = 'aeo_default_no_js';
	
	/**
	 * Use additional ROT13 encryption, default is 1
	 * @var int
	 */
	protected $use_rot_13 = 1;

	/**
	 * Show standard-info to frontend user, default is true
	 * @var int
	 */
	protected $show_standard_info = true;

	/**
	 * custom info to frontend user, default is ''
	 * @var int
	 */
	protected $info = '';

	protected $objCaptcha;

	/**
	 * Initialize the object
	 * @param array
	 */
	public function __construct($objElement, $strColumn='main') {
		parent::__construct($objElement, $strColumn);
	}

	/**
	 * Display a wildcard in the back end
	 * @return string
	 */
	public function generate()
	{
		global $objPage;
		$GLOBALS['TL_LANGUAGE'] = $objPage->language;

		if (TL_MODE == 'BE')
		{
			$objTemplate = new \BackendTemplate('be_wildcard');

			$objTemplate->wildcard = '### AEO JavaScript Fallback ###';
			$objTemplate->title = $this->headline;
			$objTemplate->id = $this->id;
			$objTemplate->link = $this->name;
			$objTemplate->href = 'contao/main.php?do=themes&amp;table=tl_module&amp;act=edit&amp;id=' . $this->id;

			return $objTemplate->parse();
		}
		
		$this->use_rot_13 = $GLOBALS['TL_CONFIG']['aeo_use_rot_13'] === true;
		
		// Fallback auf default template
		$strTemplate = deserialize($this->getParent()->aeo_custom_template, true);
		if (!is_array($strTemplate) || count($strTemplate) < 1) {
			$this->strTemplate = 'aeo_default_no_js';
		} else {
			$this->strTemplate = $strTemplate[0];
		}
		
		// Default Info für Frontend-User anzeigen
		$numShowInfo = deserialize($this->getParent()->aeo_show_info, true);
		if (is_array($numShowInfo) && count($numShowInfo) >= 1) {
			$this->show_standard_info = $numShowInfo[0];
		} else {
			$this->show_standard_info = false;
		}
		
		// Eigene Info für Frontend-User anzeigen
		$strInfo = deserialize($this->getParent()->aeo_info_text, true);
		if (is_array($strInfo) && count($strInfo) >= 1) {
			$this->info = $strInfo[0];
		}
		if (strlen($this->info) == 0) {
			$this->show_standard_info = true;
		}
		
		return parent::generate();
	}


	/**
	 * Generate the module
	 */
	protected function compile()
	{
		global $objPage;
		$GLOBALS['TL_LANGUAGE'] = $objPage->language;
		
		$this->loadLanguageFile('default');

		// Auf E-Mail-Adresse weiterleiten
		$this->Template = new \FrontendTemplate($this->strTemplate);
		if ($this->getParent() instanceof \ModuleModel) {
			$this->Template->setData($this->getParent()->row());
			
			// Überschrift setzen
			$arrHeadline = deserialize($this->getParent()->headline);
			if (is_array($arrHeadline)) {
				$this->Template->hl = $arrHeadline['unit'];
				$this->Template->headline = $arrHeadline['value'];
			}
		} else {
			$this->Template->setData($this->arrData);
		}
		
		$doSubmit = true;
		
		$arrCaptcha = array
		(
			'id' => 'aeo',
			'label' => $GLOBALS['TL_LANG']['MSC']['securityQuestion'],
			'type' => 'captcha',
			'mandatory' => true,
			'required' => true,
			'tableless' => true
		);

		$strClass = $GLOBALS['TL_FFL']['captcha'];

		// Fallback auf Default Captcha, falls Klasse nicht existiert
		if (!$this->classFileExists($strClass))
		{
			$strClass = '\\FormCaptcha';
		}

		$objCaptcha = new $strClass($arrCaptcha);
		$this->objCaptcha = $objCaptcha;
		
		if ($this->Input->post('FORM_SUBMIT') == 'tl_aeo') {
			$this->objCaptcha->validate();

			if ($this->objCaptcha->hasErrors()) {
				$doSubmit = false;
			}
		}
		
		$objResult = $this->Database->prepare("SELECT * FROM tl_page WHERE id=?")
											->limit(1)
											->execute($this->Input->get('p'))
											->fetchAssoc();
		$pageDetails = $this->getPageDetails($objResult['id']);
		$backLink = $this->generateFrontendUrl($objResult);
		
		/**
		 * Template variables
		 */
		$this->import('String');
		$this->Template->action = $this->getIndexFreeRequest();
		$this->Template->n = $this->Input->get('n');
		$this->Template->d = $this->Input->get('d');
		$this->Template->t = $this->Input->get('t');
		$this->Template->p = $this->Input->get('param');
		$this->Template->captcha = $this->objCaptcha;
		$this->Template->captchaDetails = $GLOBALS['TL_LANG']['MSC']['securityQuestion'];
		if ($this->show_standard_info) {
			$this->Template->info = $GLOBALS['TL_LANG']['aeo']['info'];
		} else {
			$this->Template->info = $this->info;
		}
		$this->Template->formId = 'tl_aeo';
		$this->Template->buttonLabel = $GLOBALS['TL_LANG']['aeo']['buttonLabel'];
		$this->Template->backLink = $backLink;
		$this->Template->backLabel = $GLOBALS['TL_LANG']['MSC']['goBack'];
		
		if ($this->Input->post('FORM_SUBMIT') == 'tl_aeo' && $doSubmit) {
			if ($this->use_rot_13) {
				$email = strip_tags(str_rot13($this->Input->post('n')).'@'.str_rot13($this->Input->post('d')).'.'.str_rot13($this->Input->post('t')));
			} else {
				$email = strip_tags($this->Input->post('n').'@'.$this->Input->post('d').'.'.$this->Input->post('t'));
			}
			
			if ($this->Input->post('p')) {
				$email .= $this->String->decodeEntities(base64_decode($this->String->decodeEntities($this->Input->post('p'))));
			}
			
			$this->Template->isHuman = true;
			$this->Template->success = sprintf($GLOBALS['TL_LANG']['aeo']['success'], $this->String->encodeEmail($email), $this->String->encodeEmail(preg_replace('/\?.*$/', '', $email)));
			
			if (!headers_sent()) {
				header('HTTP/1.1 303 See Other');
				header('Location: mailto:'.$email);
			}
			else {
				die('<html><head><meta http-equiv="refresh" content="0; url=mailto:'.$email.'"></head><body></body></html>');
			}
		} 
	}
}

?>