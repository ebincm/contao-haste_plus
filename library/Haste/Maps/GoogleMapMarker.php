<?php
/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2016 Heimrich & Hannot GmbH
 *
 * @package haste_plus
 * @author  Rico Kaltofen <r.kaltofen@heimrich-hannot.de>
 * @license http://www.gnu.org/licences/lgpl-3.0.html LGPL
 */

namespace HeimrichHannot\Haste\Map;

class GoogleMapMarker
{
	protected $arrOptions = array();

	public function __construct()
	{
		if (!static::init())
		{
			throw new \Exception('dlh_googlemaps module is not enabled');
		}

		$this->prepare();
	}

	public function generate(array $arrOptions = array())
	{
		$this->arrOptions = array_merge($this->arrOptions, $arrOptions);

		$arrData = $this->getData($this->arrOptions);
		
		$strTemplate = sprintf('dlh_%s', $arrData['customTpl'] ?: strtolower($arrData['type']));

		$objTemplate          = new \FrontendTemplate($strTemplate);
		$objTemplate->map     = $arrData['map'];
		$objTemplate->element = $arrData;

		return $objTemplate->parse();
	}

	public function generateStatic(array $arrOptions = array())
	{
		$this->arrOptions = array_merge($this->arrOptions, $arrOptions);

		$arrData = $this->getData($this->arrOptions);

		$strMarker = '';

		switch($arrData['type']) {
			case 'MARKER':
				if($arrData['markerType'] == 'ICON') {
					$arrData['iconSRC'] = \FilesModel::findByUuid($arrData['iconSRC'])->path;
					return array('icon:'.rawurlencode(\Environment::get('base').$arrData['iconSRC']).'|shadow:false|' => $arrData['singleCoords']);
				}
				else
				{
					$strMarker = '&amp;markers='.$arrData['singleCoords'];
				}
				break;
			case 'POLYLINE':
				if(is_array($arrData['multiCoords']) && count($arrData['multiCoords'])>0) {
					$strMarker .= '&amp;path=weight:'.$arrData['strokeWeight']['value'].'|color:0x'.$arrData['strokeColor'].dechex($arrData['strokeOpacity']*255);
					foreach($arrData['multiCoords'] as $coords) {
						$strMarker .= '|'.str_replace(' ','',$coords);
					}
				}
				break;
			case 'POLYGON':
				if(is_array($arrData['multiCoords']) && count($arrData['multiCoords'])>0) {
					$strMarker .= '&amp;path=weight:'.$arrData['strokeWeight']['value'].'|color:0x'.$arrData['strokeColor'].dechex($arrData['strokeOpacity']*255).'|fillcolor:0x'.$arrData['fillColor'].dechex($arrData['fillOpacity']*255);
					foreach($arrData['multiCoords'] as $coords) {
						$strMarker .= '|'.str_replace(' ','',$coords);
					}
					$strMarker .= '|'.str_replace(' ','',$arrData['multiCoords'][0]);
				}
				break;
		}

		return $strMarker;
	}

	/**
	 * @param $strCoordinates the coordinates where to route to
	 *
	 * @return $this
	 */
	public function setRoute($strCoordinates)
	{
		$this->routingAddress = $strCoordinates;
		$this->markerAction = 'INFO';
	}

	public function setTitle($strTitle)
	{
		$this->title = $strTitle;
		$this->markerShowTitle = true;
	}

	public function setPosition($strCoordinates)
	{
		$this->singleCoords = $strCoordinates;
;
	}

	protected function getData(array $arrData)
	{
		$arrData['singleCoords'] = str_replace(' ','',$arrData['singleCoords']);

		$arrData['multiCoords'] = deserialize($arrData['multiCoords']);
		if(is_array($arrData['multiCoords'])) {
			$tmp1 = array();
			foreach($arrData['multiCoords'] as $coords) {
				$tmp2 = explode(',',$coords);
				$tmp1[0][] = $tmp2[0];
				$tmp1[1][] = $tmp2[1];
			}
			$arrData['windowPosition'] = array_sum($tmp1[0])/sizeof($tmp1[0]).','.array_sum($tmp1[1])/sizeof($tmp1[1]);
		}

		$arrData['iconSize'] = deserialize($arrData['iconSize']);

		$arrData['iconAnchor'] = deserialize($arrData['iconAnchor']);

		if(!$arrData['iconAnchor'][0] || $arrData['iconAnchor'][0]==0) {
			$arrData['iconAnchor'][0] = floor($arrData['iconSize'][0]/2);
		}
		else
		{
			$arrData['iconAnchor'][0] = floor($arrData['iconSize'][0]/2) + $arrData['iconAnchor'][0];
		}

		if(!$arrData['iconAnchor'][1] || $arrData['iconAnchor'][1]==0) {
			$arrData['iconAnchor'][1] = floor($arrData['iconSize'][1]/2);
		}
		else
		{
			$arrData['iconAnchor'][1] = floor($arrData['iconSize'][1]/2) + $arrData['iconAnchor'][1];
		}

		$objFile = \FilesModel::findByPk($arrData['overlaySRC']);
		$arrData['overlaySRC'] = $objFile->path;

		$objFile = \FilesModel::findByPk($arrData['shadowSRC']);
		$arrData['shadowSRC'] = $objFile->path;

		$arrData['shadowSize'] = deserialize($arrData['shadowSize']);

		$arrData['strokeWeight'] = deserialize($arrData['strokeWeight']);

		$tmp1 = deserialize($arrData['strokeOpacity']);
		if (isset($tmp1['value'])) {
			$arrData['strokeOpacity'] = ($tmp1['value']/100);
		}

		$tmp1 = deserialize($arrData['fillOpacity']);
		if (isset($tmp1['value'])) {
			$arrData['fillOpacity'] = ($tmp1['value']/100);
		}

		$arrData['radius'] = deserialize($arrData['radius']);
		$arrData['bounds'] = deserialize($arrData['bounds']);
		$tmp1 = explode(',',$arrData['bounds'][0]);
		$tmp2 = explode(',',$arrData['bounds'][1]);
		$arrData['bounds'][2] = (trim($tmp1[0]).trim($tmp2[0]))/2 . ',' . (trim($tmp1[1]).trim($tmp2[1]))/2;
		$arrData['infoWindow'] = preg_replace('/[\n\r\t]+/i', '', str_replace('\"','"', addslashes(\Controller::replaceInsertTags($arrData['infoWindow']))));

		$arrData['infoWindowAnchor'] = deserialize($arrData['infoWindowAnchor']);
		$arrData['infoWindowAnchor'][0] = $arrData['infoWindowAnchor'][0] ? -1 * $arrData['infoWindowAnchor'][0] : 0;
		$arrData['infoWindowAnchor'][1] = $arrData['infoWindowAnchor'][1] ? -1 * $arrData['infoWindowAnchor'][1] : 0;

		$tmpSize = deserialize($arrData['infoWindowSize']);

		$arrData['infoWindowSize'] = '';
		if(is_array($tmpSize) && $tmpSize[0] > 0 && $tmpSize[1] > 0) {
			$arrData['infoWindowSize'] = sprintf(' style="width:%spx;height:%spx;"', $tmpSize[0], $tmpSize[1]);
		}

		$arrData['routingAddress'] = str_replace('\"','"', addslashes(str_replace('
','',$arrData['routingAddress'])));
		$arrData['labels'] = $GLOBALS['TL_LANG']['dlh_googlemaps']['labels'];

		$arrData['staticMapPart'] = '';

		//supporting insertags
		$arrData['kmlUrl'] =  \Controller::replaceInsertTags($arrData['kmlUrl'],false);

		return $arrData;
	}


	protected function isAvailable()
	{
		return in_array('dlh_googlemaps', \ModuleLoader::getActive());
	}

	protected function prepare(array $arrOptions = array())
	{
		$arrDefaults = array
		(
			'map'                    => '', // the id of the GoogleMap
			'customTpl'              => '',
			'type'                   => 'MARKER',
			'typesAvailable'         => array('MARKER', 'INFOWINDOW', 'POLYLINE', 'POLYGON', 'GROUND_OVERLAY', 'RECTANGLE', 'CIRCLE', 'KML'),
			'singleCoords'           => '',
			'markerType'             => 'SIMPLE',
			'markerTypesAvailable'   => array('SIMPLE', 'ICON'),
			'markerAction'           => 'NONE',
			'markerActionsAvailable' => array('NONE', 'LINK', 'INFO'),
			'multiCoords'			 => NULL,
			'markerShowTitle'		 => true,
			'overlaySRC'			 => NULL,
			'iconSRC'		 		 => NULL,
			'iconSize'               => array(16, 16, 'px'),
			'iconAnchor'             => array(0, 0, 'px'),
			'hasShadow'              => '',
			'shadowSize'             => array(32, 32, 'px'),
			'strokeColor'            => '000000',
			'strokeOpacity'          => 100,
			'strokeWeight'           => 1,
			'fillColor'              => '',
			'fillOpacity'            => 100,
			'radius'                 => 1000,
			'bounds'                 => '',
			'zIndex'                 => 1,
			'popupInfoWindow'        => false,
			'useRouting'             => true,
			'routingAddress'         => '',
			'infoWindow'             => '',
			'infoWindowSize'         => array(320, 160, 'px'),
			'infoWindowAnchor'       => array(0, 0, 'px'),
			'url'                    => '',
			'target'                 => '',
			'linkTitle'              => '',
			'parameter'              => '',
			'kmlUrl'                 => '',
			'kmlClickable'           => true,
			'kmlPreserveViewport'    => false,
			'kmlScreenOverlays'      => true,
			'kmlSuppressInfowindows' => false,
			'published'              => true,
		);

		$this->arrOptions = array_merge($arrDefaults, $arrOptions);
	}

	private static function init()
	{
		if (!in_array('dlh_googlemaps', \ModuleLoader::getActive())) {
			return false;
		}

		\Controller::loadLanguageFile('tl_dlh_googlemaps');

		return true;
	}

	/**
	 * Set an object property
	 *
	 * @param string $strKey
	 * @param mixed  $varValue
	 */
	public function __set($strKey, $varValue)
	{
		$this->arrOptions[$strKey] = $varValue;
	}


	/**
	 * Return an object property
	 *
	 * @param string $strKey
	 *
	 * @return mixed
	 */
	public function __get($strKey)
	{
		if (isset($this->arrOptions[$strKey]))
		{
			return $this->arrOptions[$strKey];
		}

		return parent::__get($strKey);
	}


	/**
	 * Check whether a property is set
	 *
	 * @param string $strKey
	 *
	 * @return boolean
	 */
	public function __isset($strKey)
	{
		return isset($this->arrOptions[$strKey]);
	}


	public function getOptions()
	{
		return $this->arrOptions;
	}

}