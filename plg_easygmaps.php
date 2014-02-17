<?php

/**
 * @version		1
 * @package		K2 EasyGMaps by StVakis
 * @author		Stvakis - http://blog.pointin.gr
 * @license		GNU/GPL license: http://www.gnu.org/licenses/gpl-2.0.html
 */
// no direct access
defined('_JEXEC') or die;

// Load the K2 Plugin API
JLoader::register('K2Plugin', JPATH_ADMINISTRATOR . '/components/com_k2/lib/k2plugin.php');

// Initiate class to hold plugin events
class plgK2Plg_easygmaps extends K2Plugin {

  // Some params
  var $pluginName = 'plg_easygmaps';
  var $pluginNameHumanReadable = 'EasyGMaps Settings';

  function plgK2Example(&$subject, $params) {
    parent::__construct($subject, $params);
  }

  function onK2PrepareContent(&$item, &$params, $limitstart) {
    $mainframe = JFactory::getApplication();
    //$item->text = 'It works! '.$item->text;
  }

  function onK2AfterDisplay(&$item, &$params, $limitstart) {
    $mainframe = JFactory::getApplication();
    return '';
  }

  function onK2BeforeDisplay(&$item, &$params, $limitstart) {
    $mainframe = JFactory::getApplication();
    return '';
  }

  function onK2AfterDisplayTitle(&$item, &$params, $limitstart) {
    $mainframe = JFactory::getApplication();
    return '';
  }

  function onK2BeforeDisplayContent(&$item, &$params, $limitstart) {
    $mainframe = JFactory::getApplication();
    return '';
  }

  // Event to display (in the frontend)
  function onK2AfterDisplayContent(&$item, &$params, $limitstart) {
	if (JRequest::getVar('view')!=="item") //display only in item view
		return '';
		
    //Getting Item params		
    $plugins = new K2Parameter($item->plugins, '', $this->pluginName);
    $lat = trim($plugins->get('latitude'));
    $lon = trim($plugins->get('longitude'));
    if (empty($lat) || empty($lon))
      return false;
    $deflocal = (empty($this->params->get('local')) ? '' : '&language=' . $this->params->get('local'));
    $defcmarker = (empty($this->params->get('cmarker')) ? '' : ',icon: "'.$this->params->get('cmarker').'"');
    $apikey = (empty($this->params->get('apikey')) ? '' : '&key=' . $this->params->get('apikey'));
    $async = (empty($this->params->get('async')) ? FALSE : TRUE);
    $zoom = ($plugins->get('zoom') == '-1' ? $this->params->get('zoom') : $plugins->get('zoom'));
    $mapMaxZoom = ($plugins->get('maxzoom') == '-1' ? $this->params->get('maxzoom') : $plugins->get('maxzoom'));
    $mapMinZoom = ($plugins->get('minzoom') == '-1' ? $this->params->get('minzoom') : $plugins->get('minzoom'));
    $local = (empty($plugins->get('local')) ? $deflocal : '&language=' . $plugins->get('local'));
    $maptype = ($plugins->get('maptype') === "0" ? $this->params->get('maptype') : $plugins->get('maptype'));
	$cmarker=(empty($plugins->get('cmarker')) ? $defcmarker : ',icon: "'.$plugins->get('cmarker').'"');
    $height = (empty($plugins->get('height')) ? $this->params->get('height') : $plugins->get('height'));

    $infowindow = (empty($plugins->get('infowindow')) ? "" : '
			var infowindow = new google.maps.InfoWindow({
				maxWidth: 500,
				content:\'' . trim(addslashes(preg_replace('/\s\s+/', ' ', $plugins->get('infowindow')))) . '\'
			});
			google.maps.event.addListener(marker, \'click\', openmarker);
			function openmarker() {infowindow.open(map, marker);}
			openmarker();');
    $mapsOptions = "
		var mapOptions = {
  			zoom: " . $zoom . ",
    		center: new google.maps.LatLng(" . $lat . ", " . $lon . "),
    		mapTypeId: google.maps.MapTypeId." . $maptype . ",
    		maxZoom:" . $mapMaxZoom . ",
    		minZoom:" . $mapMinZoom . "    		
  		};";
    $document = JFactory::getDocument();
    if (!$async) {
      $document->addScript('https://maps.googleapis.com/maps/api/js?v=3.exp&sensor=false' . $local . $apikey);
      $document->addScriptDeclaration("
		var map;
		function initialize() {
			" . $mapsOptions . "  
			map = new google.maps.Map(document.getElementById('map-canvas'),mapOptions);
			var marker = new google.maps.Marker({
				map: map,
				position: map.getCenter()".$cmarker."
			});
		" . $infowindow . "
		}
		google.maps.event.addDomListener(window, 'load', initialize);");
    } else {
      $document->addScriptDeclaration("
		function initialize() {
			" . $mapsOptions . "  
			var map = new google.maps.Map(document.getElementById('map-canvas'),mapOptions);
			var marker = new google.maps.Marker({
				map: map,
				position: map.getCenter()".$cmarker."
			});
		" . $infowindow . "      
		}
		function loadScript() {
 			var script = document.createElement('script');
			script.type = 'text/javascript';
			script.src = 'https://maps.googleapis.com/maps/api/js?v=3.exp&sensor=false&callback=initialize" . $local . $apikey . "';
			document.body.appendChild(script);
		}
		window.onload = loadScript;");
    }
    $output = '<div id="map-canvas"></div>';
    // Add styles
    $style = '#map-canvas img{max-width:none;}#map-canvas {height:'.$height.'px;margin: 0px;padding: 0px; }';
    $document->addStyleDeclaration($style);

    return $output;
  }

  // Event to display (in the frontend)
  function onK2CategoryDisplay(&$category, &$params, $limitstart) {

    return false;
  }

  // Event to display (in the frontend)
  function onK2UserDisplay(&$user, &$params, $limitstart) {

    return false;
  }

}

// END CLASS
