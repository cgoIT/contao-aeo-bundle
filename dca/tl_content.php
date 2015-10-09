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

/**
 * palettes
 */
$GLOBALS['TL_DCA']['tl_content']['palettes']['aeo'] = '{type_legend},type,headline;{aeo_legend},aeo_show_info,aeo_info_text,aeo_disable;{template_legend:hide},aeo_custom_template;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID,space';

/**
 * extend existing expert_legend
 */
foreach ($GLOBALS['TL_DCA']['tl_content']['palettes'] as $key => $palette) {
	if (!is_array($palette) && $key != 'aeo') {
		$GLOBALS['TL_DCA']['tl_content']['palettes'][$key] = $palette.';{aeo_legend:hide},aeo_disable';
	}
}

/**
 * fields
 */
$GLOBALS['TL_DCA']['tl_content']['fields']['aeo_custom_template'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_content']['aeo_custom_template'],
	'default'                 => 'aeo_default_no_js',
	'exclude'                 => true,
	'inputType'               => 'select',
	'options_callback'        => array('tl_content_aeo', 'getAeoTemplates'),
	'eval'                    => array('tl_class'=>'w50')
);

$GLOBALS['TL_DCA']['tl_content']['fields']['aeo_show_info'] = array
(
      'label'                   => &$GLOBALS['TL_LANG']['tl_content']['aeo_show_info'],
      'exclude'                 => true,
      'filter'                  => false,
      'search'                  => false,
      'inputType'               => 'checkbox',
      'eval'                    => array('tl_class'=>'w50')
);

$GLOBALS['TL_DCA']['tl_content']['fields']['aeo_info_text'] = array
(
      'label'                   => &$GLOBALS['TL_LANG']['tl_content']['aeo_info_text'],
      'exclude'                 => true,
      'filter'                  => false,
      'search'                  => false,
	  'inputType'               => 'textarea',
	  'eval'                    => array('rte'=>'tinyMCE', 'tl_class'=>'clr')
);

$GLOBALS['TL_DCA']['tl_content']['fields']['aeo_disable'] = array
(
      'label'                   => &$GLOBALS['TL_LANG']['tl_content']['aeo_disable'],
	  'default'                 => '',
      'exclude'                 => true,
      'filter'                  => true,
      'search'                  => false,
      'inputType'               => 'checkbox',
      'eval'                    => array('tl_class'=>'long')
);

/**
 * Class tl_content_aeo
 */
class tl_content_aeo extends \Backend
{

	/**
	 * Return all navigation templates as array
	 * @param DataContainer
	 * @return array
	 */
	public function getAeoTemplates(\DataContainer $dc)
	{
		$intPid = $dc->activeRecord->pid;

		if ($this->Input->get('act') == 'overrideAll')
		{
			$intPid = $this->Input->get('id');
		}

		return $this->getTemplateGroup('aeo_', $intPid);
	}
}
?>