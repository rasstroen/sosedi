<?php

class Jgeo_module extends JBaseModule {

	function process() {


		switch ($_POST['action']) {
			case 'fill_selects':
				$this->fillSelects();
				break;
			case 'get_country_by_name':
				$this->getCountryByName();
				break;
			case 'get_city_by_name_and_country':
				$this->getCityByNameAndCountry();
				break;
			case 'get_region_by_name_and_city':
				$this->getRegionByNameAndCity();
				break;
			case 'get_steet_by_region_and_name':
				$this->getStreetByNameAndRegion();
				break;
		}
	}

	function fillSelects() {
		$city_id = max(0, (int) $_POST['city_id']);
		$country_id = max(0, (int) $_POST['country_id']);
		$region_id = max(0, (int) $_POST['country_id']);
		$default_country_id = 1;
		// get all countries
		$this->data['countryes'] = GeoLibrary::getCountries();
		// get all cities by country
		$this->data['cities'] = GeoLibrary::getCities($country_id ? $country_id : $default_country_id);
		// get all regions
	}

	function getCountryByName() {
		$s = $_POST['s'];
		$this->data['countryes'] = GeoLibrary::getCountries(false, $s);
	}

	function getCityByNameAndCountry() {
		$s = $_POST['s'];
		$sc = $_POST['sc'];
		$id_country = max(0, (int) $_POST['country_id']);
		if (!$id_country && $sc) {
			$id_country = GeoLibrary::getCountries(false, $sc);
			$id_country = array_pop($id_country);
			$id_country = $id_country['id'];
		}
		$this->data['cityes'] = GeoLibrary::getCities($id_country, $s);
		$this->data['country_id'] = $id_country;
	}
	
	function getRegionByNameAndCity(){
		$s = $_POST['s'];
		$sc = $_POST['sc'];
		$id_city = max(0, (int) $_POST['city_id']);
		$id_country = max(0, (int) $_POST['country_id']);
		if (!$id_city && $sc) {
			$id_city = GeoLibrary::getCities($id_country, $sc);
			$id_city = array_pop($id_city);
			$id_city = $id_city['id'];
		}
		$this->data['regions'] = GeoLibrary::getRegions($id_city, $s);
		$this->data['country_id'] = $id_country;
		$this->data['city_id'] = $id_city;
	}
	
	function getStreetByNameAndRegion(){
		$s = $_POST['s'];
		$sc = $_POST['sc'];
		$id_city = max(0, (int) $_POST['city_id']);
		$id_country = max(0, (int) $_POST['country_id']);
		$id_region = max(0, (int) $_POST['region_id']);
		if (!$id_region && $sc) {
			$id_region = GeoLibrary::getStreets($id_region, $sc);
			$id_region = array_pop($id_region);
			$id_region = $id_region['id'];
		}
		$this->data['streets'] = GeoLibrary::getStreets($id_region, $s);
		$this->data['country_id'] = $id_country;
		$this->data['city_id'] = $id_city;
		$this->data['region_id'] = $id_region;
	}

}