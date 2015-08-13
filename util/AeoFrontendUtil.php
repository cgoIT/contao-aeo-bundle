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
 * Class AeoFrontendUtil
 */

//define('REGEXP_EMAIL_PREFIX', '(\w[-._\w]*\w)\@');
define('REGEXP_EMAIL', '\w[-._\w]*\w@[\d\w][-._\w]*\w\.\w{2,18}');
define('REGEXP_MAILTO_LINK', '/(?P<all>\<a(?P<before>[^>]+)href\=["\']mailto\:(?P<email>\w[-._\w]*\w)\@(?P<domain>\w[-._\w]*\w)\.(?P<suffix>\w{2,18})(?P<params>\?{0,1}[^"^\']*)["\'](?P<after>[^>]*)\>).*?\<\/a\>/ism');

class AeoFrontendUtil extends \Frontend {

	/**
	 * Replace default obfuscation, default is 1
	 * @var int
	 */
	protected $replace_standard_obfuscation = 1;

	/**
	 * Use additional ROT13 encryption, default is 1
	 * @var int
	 */
	protected $use_rot_13 = 1;

	/**
	 * Virtual path for non javascript users, default is 'contact'
	 * @var string
	 */
	protected $virtual_path = 'contact';

	/**
	 * Weiterleitungsseite für nicht JavaScript Nutzer
	 * @var string
	 */
	protected $jump_to_no_js;

	/**
	 * Methode zum Verschleiern der angezeigten E-Mail-Adresse im Frontend
	 * @var string
	 */
	protected $obfuscation_method;
	
	/**
	 * Instance of AEOUtility
	 * @var AEOUtility
	 */
	protected $aeo;

	/**
	 * Initialize the object
	 * @param array
	 */
	public function __construct($arrAttributes=false) {
		parent::__construct($arrAttributes);

		if (TL_MODE == 'FE') {
			global $objPage;
			$this->import('aeo\\AeoUtil', 'AeoUtil');

			if ($GLOBALS['TL_CONFIG']['aeo_replace_standard_obfuscation'] === true) {
			  	$this->use_rot_13 = $GLOBALS['TL_CONFIG']['aeo_use_rot_13'];
			  	$this->virtual_path = $GLOBALS['TL_CONFIG']['aeo_virtual_path'];
			  	$this->jump_to_no_js = $GLOBALS['TL_CONFIG']['aeo_jump_to_no_js'];
			  	$this->obfuscation_method = $GLOBALS['TL_CONFIG']['aeo_obfuscation_method'];
			  	 
			  	$this->aeo = new Aeo();
			  	$this->aeo->setTooltipNoJS($GLOBALS['TL_LANG']['aeo']['tooltip_no_js']);
			  	$folder = '';
			  	if (!$GLOBALS['TL_CONFIG']['rewriteURL']) {
			  		$folder .= 'index.php/';
			  	}
			  	if ($GLOBALS['TL_CONFIG']['addLanguageToUrl']) {
			  		$folder .= $objPage->rootLanguage.'/';
			  	}
			  	if (in_array('i18nl10n', $this->Config->getActiveModules())) {
			  		$this->AeoUtil->fixupCurrentLanguage();
			  		if ($GLOBALS['TL_CONFIG']['i18nl10n_addLanguageToUrl']) {
			  			$folder .= $GLOBALS['TL_LANGUAGE'] . '/';
			  		}
					$folder .= $this->virtual_path.'/'.$GLOBALS['TL_LANGUAGE'];
				} else {
					$folder .= $this->virtual_path.'/'.$objPage->rootLanguage;
				}
			  	
			  	$this->aeo->setFolder($folder);
			  	$this->aeo->setMethod($this->obfuscation_method);
			  	if ($this->use_rot_13) {
			  		$this->aeo->setROT13(true);
			  	} else {
			  		$this->aeo->setROT13(false);
			  	}
			  	$urlSuffix = '';
			  	if (strlen($GLOBALS['TL_CONFIG']['urlSuffix']) > 0) {
			  		if (in_array('i18nl10n', $this->Config->getActiveModules()) &&
			  		    $GLOBALS['TL_CONFIG']['i18nl10n_alias_suffix']) {
			  			$this->AeoUtil->fixupCurrentLanguage();
			  			$urlSuffix .= '.'.$GLOBALS['TL_LANGUAGE'];
			  		}
			  		$urlSuffix .= $GLOBALS['TL_CONFIG']['urlSuffix'];
			  	}
				$objResult = $this->Database->prepare("SELECT * FROM tl_page WHERE id=?")
													->limit(1)
													->execute($objPage->id)
													->fetchAssoc();
				$url = $this->generateFrontendUrl($objResult);
				if (strstr($url, '?')) {
					$arrParams = explode('?', $url);
					if (count($arrParams) == 2) {
						$arrParamValues = explode('&', $arrParams[1]);
						$added = false;
						foreach ($arrParamValues as $param) {
							if (!strstr($param, 'id=')) {
								$urlSuffix .= ($added ? '&' : '?').$param;
								$added = true;
							}
						}
					}
				}
				$this->aeo->urlSuffix = $urlSuffix;
				
				$this->includeStaticJs();
		  	} else {
		  		// global deaktiviert
		  		$this->replace_standard_obfuscation = 0;
		  	}
		}
	}
	
	/**
	 * Fügt dem Frontend-Modul ggf. die stop- und continue-Marker hinzu.
	 *
	 * @param unknown_type $objRow
	 * @param unknown_type $strBuffer
	 */
	public function aeoGetFrontendModule($objRow, $strBuffer) {
		return $this->checkAeoDisabled($objRow, $strBuffer);
	}

	/**
	 * Fügt dem Inhaltelement ggf. die stop- und continue-Marker hinzu.
	 *
	 * @param unknown_type $objRow
	 * @param unknown_type $strBuffer
	 */
	public function aeoGetContentElement($objRow, $strBuffer) {
		return $this->checkAeoDisabled($objRow, $strBuffer);
	}

	/**
	 * Verschleierung der E-Mail-Adressen.
	 * 
	 * @param $strContent
	 * @param $strTemplate
	 */
  	public function obfuscateEmails($strContent, $strTemplate)
	{
		global $objPage;
		$objPage = $this->getPageDetails($objPage->id);
		$redirectPageId = $this->AeoUtil->getRedirectPageForLanguage(deserialize($this->jump_to_no_js), $objPage->rootLanguage);
		
		if (TL_MODE == 'FE' && $this->replace_standard_obfuscation && $objPage->id != $redirectPageId) {
			$strContent = $this->aeoReplaceInsertTags($strContent);
			
			$this->import('String');
			
			// erst alle Mailadresse decodieren (Verschleierung von Contao rückgänging machen)
			$intOffset = 0;
			$arrNoAeoAreas = $this->aeo->getNoAeoAreas($strContent);
			while (preg_match('/(&#[x]?\w+;)+/i', $strContent, $arrEmail, PREG_OFFSET_CAPTURE, $intOffset)) {
				if ($this->aeo->isEnabled($arrEmail[0][1], $arrNoAeoAreas)) {
					$strDecodedMail = $this->String->decodeEntities($arrEmail[0][0]);
					if (preg_match('/mailto:'.REGEXP_EMAIL.'/i', $strDecodedMail)) {
						// erst alle verlinkten eMail-Adressen entschleiern
						$strContent = $this->aeo->str_replace($arrEmail[0][0], $strDecodedMail, $strContent, $arrEmail[0][1]);
						$intOffset = $arrEmail[0][1] + strlen($strDecodedMail);
						
						// Array muss neu aufgebaut werden, da sich die offsets geändert haben
						$arrNoAeoAreas = $this->aeo->getNoAeoAreas($strContent);
					} else if (preg_match('/'.REGEXP_EMAIL.'/i', $strDecodedMail)) {
						// dann alle nicht verlinkten eMail-Adressen entschleiern
						$strContent = $this->aeo->str_replace($arrEmail[0][0], $strDecodedMail, $strContent, $arrEmail[0][1]);
						$intOffset = $arrEmail[0][1] + strlen($strDecodedMail);
						
						// Array muss neu aufgebaut werden, da sich die offsets geändert haben
						$arrNoAeoAreas = $this->aeo->getNoAeoAreas($strContent);
					} else {
						$intOffset = $arrEmail[0][1] + 1;
					}
				} else {
					$intOffset = $arrEmail[0][1] + 1;
				}
			}

			$strContent = $this->aeo->prepareOutput($strContent, $objPage->id);
		}

		return $strContent;
	}
	
	private function includeStaticJs() {
	   global $objPage;
	   $objPage = $this->getPageDetails($objPage);
	   
	   $objLayout = $this->Database->prepare("SELECT * FROM tl_layout WHERE id=?")
													->limit(1)
													->execute($objPage->layout)
													->fetchAssoc();
	   
	   $GLOBALS['TL_JAVASCRIPT'][] = 'system/modules/zaeo/public/js/onReadyAeo.min.js|static';
	   $GLOBALS['TL_JAVASCRIPT'][] = 'system/modules/zaeo/public/js/aeo.min.js|static';
	}
	
	/**
	  * Zusätzliches InsertTag für E-Mail-Verschleierung
	  * {{aeo-email::value@domain.tld::<method>}}    <method>: Angabe der Verschleierungmethode im Frontend.
	  * 											 Mögliche Werte:
	  *                                              - none     = keine Verschleierung
	  *                                              - rtl      = Right-to-Left
	  *                                              - nullspan = Null-Span
	  *                                              - shorten  = Verkürzung
	  */
  	private function aeoReplaceInsertTags($strContent) {
		// Preserve insert tags
		if ($GLOBALS['TL_CONFIG']['disableInsertTags'])
		{
			return parent::restoreBasicEntities($strBuffer);
		}

	  	$arrPossibleValues = array('none', 'rtl', 'nullspan', 'shorten');
		
	  	$intOffset = 0;
		$arrNoAeoAreas = $this->aeo->getNoAeoAreas($strContent);
		while (preg_match('/\{\{([^\}]+)\}\}/', $strContent, $arrTags, PREG_OFFSET_CAPTURE, $intOffset)) {
			global $objPage;
	  		
			$position = $arrTags[0][1];
	  		$arrTag = explode('::', $arrTags[1][0]);
	  		
	  		// aeo-email-Tag ohne Verschleierungsmethode angegeben --> zu normalem email-Tag umwandeln
	  		if (is_array($arrTag) && count($arrTag) == 2 && $arrTag[0] === 'aeo-email') {
	  			$strValue = '{{email::'.$arrTag[1].'}}';
	  			$strContent = $this->aeo->str_replace($arrTags[0][0], $strValue, $strContent, $position);
	  			$intOffset += strlen($strValue);
	  			continue;
	  		}
	  		
	  		// Es handelt sich nicht um ein aeo-email-Tag
	  		if(!is_array($arrTag) || count($arrTag) != 3 || 
	  		    $arrTag[0] !== 'aeo-email' || !in_array($arrTag[2], $arrPossibleValues, true)) {
	  		    $intOffset += strlen($arrTags[0][0]);
	      		continue;
	  		}
			
			$this->import('String');
			if ($GLOBALS['TL_CONFIG']['aeo_replace_standard_obfuscation'] === true &&
	  		    $this->aeo->isEnabled($position, $arrNoAeoAreas)) {
	  		    // AEO aktiv und Bereich nicht auf deaktiviert gestellt
		  		$strValue = $this->aeo->obfuscateSingle($this->String->decodeEntities($arrTag[1]), $objPage->id, $arrTag[2]);
	  			$strContent = $this->aeo->str_replace($arrTags[0][0], $strValue, $strContent, $position);
	  			$intOffset += strlen($strValue);
	  		} else {
	  			// AEO nicht aktiv oder Bereich auf deaktiviert gestellt
				$strEmail = $this->String->encodeEmail($this->String->decodeEntities($arrTag[1]));
				$strValue = '<a href="&#109;&#97;&#105;&#108;&#116;&#111;&#58;' . $this->String->encodeEmail($strEmail) . '" class="email">' . $this->String->encodeEmail(preg_replace('/\?.*$/', '', $strEmail)) . '</a>';
				$strContent = $this->aeo->str_replace($arrTags[0][0], $strValue, $strContent, $position);
				$intOffset += strlen($strValue);
	  		}
	  		
			// Positionen aktualisieren, da sich die Positionen geändert haben
	  		$arrNoAeoAreas = $this->aeo->getNoAeoAreas($strContent);
		}
		
		// normale Methode aufrufen
		$strContent = parent::replaceInsertTags($strContent);
		
		return $strContent;
  	}
	
  	/**
  	 * Einfügen der stop- und continue-Marker
  	 * 
  	 * @param $objRow
  	 * @param $strBuffer
  	 */
	private function checkAeoDisabled($objRow, $strBuffer) {
		if ($objRow->aeo_disable == '1') {
			$strBuffer = "\n<!-- aeo::stop -->". $strBuffer ."<!-- aeo::continue -->\n";
		}
		return $strBuffer;
	}
}

class Aeo extends \System {
	var $buffer;
	var $folder = "contact";
	var $tooltip_js_off;
	var $rot13 = true;
	var $urlSuffix = '';
	var $method;
	
	var $i = 2;

	/**
	 * Verschleierung der E-Mail-Adressen. Parsen des Codes einer Seite und ersetzen der normalen
	 * E-Mail-Links durch verschleierte.
	 * 
	 * @param $output
	 * @param $pageId
	 */
	function prepareOutput($output, $pageId) {
		// Erst alle verlinkten eMail-Adressen verschleiern
		$arrNoAeoAreas = $this->getNoAeoAreas($output);
		$intOffset = 0;
 
		while(preg_match(REGEXP_MAILTO_LINK, $output, $arrLink, PREG_OFFSET_CAPTURE, $intOffset)) {
			if ($this->isEnabled($arrLink['all'][1], $arrNoAeoAreas)) {
				$output = $this->obfuscate($arrLink, $output, $pageId, $this->urlSuffix == null ? '' : $this->urlSuffix, $arrLink['all'][1], $intOffset);

				// Array muss neu aufgebaut werden, da sich die offsets geändert haben
				$arrNoAeoAreas = $this->getNoAeoAreas($output);
			} else {
				$intOffset = strlen($arrLink['all'][0]) + $arrLink['all'][1];
			}
		}

		// jetzt alle nicht verlinkten eMail-Adressen verschleiern.
		$arrNoAeoAreas = $this->getNoAeoAreas($output);
		$intOffset = 0;
		while(preg_match('/'.REGEXP_EMAIL.'/esm', $output, $arrNonLinkedeMail, PREG_OFFSET_CAPTURE, $intOffset)) {
			if ($this->isEnabled($arrNonLinkedeMail[0][1], $arrNoAeoAreas)) {
				$output = $this->str_replace($arrNonLinkedeMail[0][0], $this->obfuscateWithMethod($arrNonLinkedeMail[0][0], $this->method, false, $arrNonLinkedeMail[0][1], $intOffset), $output, $arrNonLinkedeMail[0][1]);

				// Array muss neu aufgebaut werden, da sich die offsets geändert haben
				$arrNoAeoAreas = $this->getNoAeoAreas($output);
			} else {
				$intOffset = strlen($arrNonLinkedeMail[0][0]) + $arrNonLinkedeMail[0][1];
			}
		}
		$close_head = array("</head>", "</HEAD>");
		$output = str_replace($close_head, $this->dropJS() . "\n</head>", $output);
		if ($this->method != 'shorten') {
			$output = str_replace($close_head, $this->dropCSS() . "\n</head>", $output);
		}
		return $this->restoreBasisEntities($output);
	}
	
	/**
	 * Verschleierung eines einzelnen Links. Dazu kann die zu verwendendet Verschleierungsmethode
	 * angegeben werden.
	 * 
	 * @param $email
	 * @param $pageId
	 * @param $method
	 */
	function obfuscateSingle($email, $pageId, $method) {
		$arrEmail = explode('@', $email);
		$strDomain = substr($arrEmail[1], 0, strrpos($arrEmail[1], '.'));
		$strTld = substr($arrEmail[1], strrpos($arrEmail[1], '.') + 1);

		$uId = microtime();
		$uId = substr($uId, 0, strpos($uId, ' '));
		
		$strLink = '<a title="'.$this->tooltip_js_off.'"';
		$strLink .= ' href="'.$this->folder.'/aeo/'.
		              ($this->rot13 ? str_rot13($arrEmail[0]) : $arrEmail[0]).'+'.
		              ($this->rot13 ? str_rot13($strDomain) : $strDomain).'+'.
		              ($this->rot13 ? str_rot13($strTld) : $strTld).'+'.
		              $pageId.($this->urlSuffix == null ? '' : $this->urlSuffix).
		              '" rel="nofollow" name="'.uniqid('aeo-obfuscated-', true).'" class="email aeo">';
		
		if ($method !== 'none') {
			// passendes CSS hinzufügen
			$strLink .= $this->obfuscateWithMethod($arrEmail[0], $method, true, 0, 0).'@'.$strDomain.'.'.$strTld;
		} else {
			$this->import('String');
			$strLink .= $this->String->encodeEmail($email);
		}
		$strLink .= '</a>';
		return $this->createSpecialEntities($strLink);
	}
	
	/**
	 * Aufbau des verschleierten E-Mail-Links
	 * 
	 * @param $arrLink
	 * @param $output
	 * @param $pageId
	 * @param $urlSuffix
	 * @param $intPos
	 * @param $intOffset
	 */
	function obfuscate($arrLink, $output, $pageId, $urlSuffix, $intPos, &$intOffset) {
		$newLink = '<a';
		
		// Originalen title auswerten
		$originalTitle = $this->getOrigValue('title', $arrLink);
		
		// Originalen class auswerten
		$originalClass = $this->getOrigValue('class', $arrLink);
		
		if (strpos($originalClass, 'aeo')) {
			// Adresse wurde bereits behandelt
			return $output;
		}
		
		if (strlen(trim($arrLink['before'][0]))) {
			$newLink .= ' '.trim($arrLink['before'][0]);
		}
		if (strlen(trim($arrLink['after'][0]))) {
			$newLink .= ' '.trim($arrLink['after'][0]);
		}
		if (strlen($originalTitle)) {
			$newLink .= ' title="'.$originalTitle.'"';
			if (strlen($originalClass)) {
				$newLink .= ' class="'.$originalClass.' aeo-with-title"';	
			} else {
				$newLink .= ' class="aeo-with-title"';	
			}
		} else {
			$newLink .= ' title="'.$this->tooltip_js_off.'"';
			if (strlen($originalClass)) {
				$newLink .= ' class="'.$originalClass.'"';	
			}
		}

		$strParams = '';
		if (strlen($arrLink['params'][0]) && '?' === substr($arrLink['params'][0], 0, 1)) {
			$strParams = base64_encode($arrLink['params'][0]);
		}
		
		$newLink .= ' href="'.$this->folder.'/aeo/'.
		              ($this->rot13 ? str_rot13($arrLink['email'][0]) : $arrLink['email'][0]).'+'.
		              ($this->rot13 ? str_rot13($arrLink['domain'][0]) : $arrLink['domain'][0]).'+'.
		              ($this->rot13 ? str_rot13($arrLink['suffix'][0]) : $arrLink['suffix'][0]).'+'.
		              $pageId.'+'.$strParams.$urlSuffix.'" rel="nofollow" name="aeo-obfuscated-'.$intPos.'"';
		$newLink .= '>';
		$output = $this->str_replace($arrLink['all'][0], $newLink, $output, $intOffset);
		$intOffset = $intPos + strlen($newLink);
		return $output;
	}
	
	/**
	 * Ursprünglichen Wert eines Attributs ermitteln.
	 * 
	 * @param $strAttr
	 * @param $arrLink
	 */
	function getOrigValue($strAttr, &$arrLink) {
		$strOriginal = '';
		if (stristr($arrLink['before'][0], $strAttr)) {
			preg_match('/'.$strAttr.'=[\"\'](?P<'.$strAttr.'>.*)[\"\']/i', $arrLink['before'][0], $matches);
			$strOriginal = $matches[$strAttr];
			$arrLink['before'][0] = preg_replace('/(.*)'.$strAttr.'=[\"\'].*[\"\'](.*)/i', '$1 $2', $arrLink['before'][0]);
		}
		if (stristr($arrLink['after'][0], $strAttr)) {
			preg_match('/'.$strAttr.'=[\"\'](?P<'.$strAttr.'>.*)[\"\']/i', $arrLink['after'][0], $matches);
			$strOriginal = $matches[$strAttr];
			$arrLink['after'][0] = preg_replace('/(.*)'.$strAttr.'=[\"\'].*[\"\'](.*)/i', '$1 $2', $arrLink['after'][0]);
		}
		return $strOriginal;
	}

	function dropJS() {
		$this->import('aeo\\AeoJavaScript', 'AeoJavaScript');
		$strContentJs = $this->AeoJavaScript->getContent(str_replace("/", "\/", $this->folder), $this->rot13);
		$strContentJs = "\n<script type=\"text/javascript\">\n$strContentJs\n</script>\n";
// 		$strContentJs .= "<script src=\"system/modules/zaeo/public/js/onReadyAeo.js\" type=\"text/javascript\"></script>";
// 		$strContentJs .= "<script src=\"system/modules/zaeo/public/js/aeo.js\" type=\"text/javascript\"></script>";
		return $strContentJs;
	}

	function dropCSS() {
		$css = "\n<style type=\"text/css\">\n\t";
		switch ($this->method) {
			case 'rtl':
				$css .= '.obfuscated { unicode-bidi: bidi-override; direction: rtl; }';
				// Hack für FireFox
				//$css .= '*>.obfuscated { unicode-bidi: -moz-isolate-override !important; }';
				break;
			case 'nullspan':
				$css .= 'span.obfuscated { display: none; white-space: nowrap;}';
				break;
		}
		$css .= "\n</style>"; 
		return $css;
	}

	function setTooltipJS($tooltip) {
		$this->tooltip_js_on = $tooltip;
	}

	function setTooltipNoJS($tooltip) {
		$this->tooltip_js_off = $tooltip;
	}
	
	function setFolder($folder) {
		$this->folder = $folder;
	}
	
	function setROT13($rot13) {
		$this->rot13 = $rot13;
	}
	
	function setMethod($method) {
		$this->method = $method;
	}
	
	function obfuscateWithMethod($email, $method, $includeCss, $intPos, &$intOffset) {
		switch ($method) {
			case 'rtl' :
				$strEmail = $this->rtl($email, $includeCss);
				break;
			case 'nullspan' :
				$strEmail = $this->nullspan($email, $includeCss);
				break;
			default:
				$strEmail = $this->shorten($email, $includeCss);
		}
		
		// Offset korrigieren
		$intOffset = $intPos + strlen($strEmail);
		return $strEmail;
	}
	
	function shorten ($email, $includeCss) {
		$arrEmail = explode('@', $email);
		if (strlen ($arrEmail[0]) <= 4) {
			$email = substr ($arrEmail[0], 0, 1);
		} else if (strlen ($arrEmail[0]) <= 6) {
			$email = substr ($arrEmail[0], 0, 3);
		} else {
			$email = substr ($arrEmail[0], 0, 4);
		}
		return $email.'...'.($includeCss ? '' : '&#64;').$arrEmail[1];
	}
		
	function rtl ($email, $includeCss) {
		$strEmail = strrev($email);
		if ($includeCss) {
			return '<span style="unicode-bidi: bidi-override; direction: rtl;">'.$strEmail.'</span>';
		}
		return '<span class="obfuscated">'.$strEmail.'</span>';
	}
			
	function nullspan ($email, $includeCss) {
		$arrEmail = explode('@', $email);
		if (strlen ($arrEmail[0]) <= 4) {
			$email1 = substr ($arrEmail[0], 0, 1);
			$email2 = substr ($arrEmail[0], 1);
		} else if (strlen ($arrEmail[0]) <= 6) {
			$email1 = substr ($arrEmail[0], 0, 3);
			$email2 = substr ($arrEmail[0], 3);
		} else {
			$email1 = substr ($arrEmail[0], 0, 4);
			$email2 = substr ($arrEmail[0], 4);
		}
		
		if ($includeCss) {
			return $email1.'<span style="display: none; white-space: nowrap;">null</span>'.$email2.'&#64;'.$arrEmail[1];
		}
		return $email1.'<span class="obfuscated">null</span>'.$email2.'&#64;'.$arrEmail[1];
	}
	
	function getNoAeoAreas($output) {
		$arrNoAeoAreas = array();
		$intOffset = 0;
		while (preg_match('/<!-- aeo::stop -->/', $output, $arrOuter, PREG_OFFSET_CAPTURE, $intOffset)) {
			$intOffset = strlen($arrOuter[0][0]) + $arrOuter[0][1];
			preg_match('/<!-- aeo::continue -->/', $output, $arrInner, PREG_OFFSET_CAPTURE, $intOffset);
			$arrNoAeoAreas[] = array('start' => $arrOuter[0][1], 'end' => $arrInner[0][1]);
			$intOffset = strlen($arrInner[0][0]) + $arrInner[0][1];
		}
		return $arrNoAeoAreas;
	}
	
	function isEnabled($intPos, $arrNoAeoAreas) {
		foreach ($arrNoAeoAreas as $arrDisabledArea) {
			if ($arrDisabledArea['start'] < $intPos && 
			    $arrDisabledArea['end'] > $intPos) {
			    	return false;
			    }
		}
		return true;
	}
	
	function str_replace($search, $replacement, $subject, $offset) {
		$mysubject = substr($subject, 0, $offset);
		$strRest = substr($subject, $offset);
		$mysubject .= preg_replace('/'.preg_quote($search, '/').'/sm', $replacement, $strRest, 1);
		return $mysubject;
	}
	
	function createSpecialEntities($strContent) {
		return str_replace(array('@', '&#x40;', '&#64;'), array('[at]', '[at]', '[at]'), $strContent);
	}
		
	function restoreBasisEntities($strContent) {
		return str_replace(array('[at]'), array('&#64;'), $strContent);
	}
}
?>
