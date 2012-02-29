<?php

class GeoLibrary {

	public static $countries = array();
	public static $cityes = array();
	public static $regions = array();
	public static $streets = array();

	public static function addStreet($id_region, $name) {
		if (!$id_region)
			return false;
		$query = 'INSERT INTO `lib_street` SET `verified`=0, `name`=' . Database::escape($name) . ',`id_region`=' . (int) $id_region . '
			ON DUPLICATE KEY UPDATE `id_region`=' . (int) $id_region;
		Database::query($query);
		$id = Database::lastInsertId();
		if (!$id) {
			$id = Database::sql2single('SELECT id FROM `lib_street` WHERE `id_region`=' . (int) $id_region . '
				AND  `name`=' . Database::escape($name));
		}
		return $id;
	}

	public static function addRegion($id_city, $name) {
		if (!$id_city)
			return false;
		$query = 'INSERT INTO `lib_region` SET `verified`=0, `name`=' . Database::escape($name) . ',`id_city`=' . (int) $id_city . '
			ON DUPLICATE KEY UPDATE `id_city`=' . (int) $id_city;
		Database::query($query);
		$id = Database::lastInsertId();
		if (!$id) {
			$id = Database::sql2single('SELECT id FROM `lib_region` WHERE `id_city`=' . (int) $id_city . '
				AND  `name`=' . Database::escape($name));
		}
		return $id;
	}

	public static function addCity($id_country, $name) {
		if (!$id_country)
			return false;
		$query = 'INSERT INTO `lib_city` SET `verified`=0, `name`=' . Database::escape($name) . ',`country_id`=' . (int) $id_country . '
			ON DUPLICATE KEY UPDATE `country_id`=' . (int) $id_country;
		Database::query($query);
		$id = Database::lastInsertId();
		if (!$id) {
			$id = Database::sql2single('SELECT id FROM `lib_city` WHERE `country_id`=' . (int) $id_country . '
				AND  `name`=' . Database::escape($name));
		}
		return $id;
	}

	private static function loadCountries() {
		self::$countries = Database::sql2array('SELECT * FROM `lib_country`');
	}

	private static function loadCities($id_country, $name = false) {
		if ($name)
			$name = strtoupperfirst($name);
		if ($name) {
			self::$cityes[$id_country] = Database::sql2array('SELECT * FROM `lib_city` WHERE `country_id`=' . $id_country . ' LIMIT 100');
		}else
			self::$cityes[$id_country] = Database::sql2array('SELECT * FROM `lib_city` WHERE `country_id`=' . $id_country . ' 
				AND `name` LIKE "%' . Database::escape($name) . '%" LIMIT 100');
	}

	private static function loadRegions($id_city, $name = false) {
		if ($name)
			$name = strtoupperfirst($name);
		if ($name) {
			self::$regions[$id_city] = Database::sql2array('SELECT * FROM `lib_region` WHERE `id_city`=' . $id_city . ' LIMIT 100');
		}else
			self::$regions[$id_city] = Database::sql2array('SELECT * FROM `lib_region` WHERE `id_city`=' . $id_city . ' 
				AND `name` LIKE "%' . Database::escape($name) . '%" LIMIT 100');
	}

	private static function loadStreets($id_region, $name = false) {
		if ($name)
			$name = strtoupperfirst($name);
		if ($name) {
			self::$streets[$id_region] = Database::sql2array('SELECT * FROM `lib_street` WHERE `id_region`=' . $id_region . ' LIMIT 100');
		}else
			self::$streets[$id_region] = Database::sql2array('SELECT * FROM `lib_street` WHERE `id_region`=' . $id_region . ' 
				AND `name` LIKE "%' . Database::escape($name) . '%" LIMIT 100');
	}

	public static function getCountries($id_country, $name = false) {
		if ($name)
			$name = strtoupperfirst($name);
		if (self::$countries == null) {
			self::loadCountries();
		}
		$out = array();

		if ($name) {
			$name = preg_quote($name);
			$pattern = '/.*' . $name . '.*/isU';
			foreach (self::$countries as $country) {
				if (preg_match($pattern, $country['name'])) {
					$out[$country['id']] = $country;
				} else {
					
				}
			}
		} else {
			$out = self::$countries;
		}
		return $out;
	}

	public static function getCities($id_country, $name = false) {
		if ($name)
			$name = strtoupperfirst($name);
		$id_country = max(0, $id_country);
		if (!isset(self::$cityes[$id_country])) {
			self::loadCities($id_country, $name);
		}

		$out = array();

		if ($name) {
			$name = preg_quote($name);
			$pattern = '/.*' . $name . '.*/isU';
			foreach (self::$cityes[$id_country] as $city) {
				if (preg_match($pattern, $city['name'])) {
					$out[$city['id']] = $city;
				} else {
					
				}
			}
		} else {
			$out = self::$cityes[$id_country];
		}
		return $out;
	}

	public static function getRegions($id_city, $name = false) {
		if ($name)
			$name = strtoupperfirst($name);
		$id_city = max(0, $id_city);
		if (!isset(self::$regions[$id_city])) {
			self::loadRegions($id_city, $name);
		}

		$out = array();

		if ($name) {
			$name = preg_quote($name);
			$pattern = '/.*' . $name . '.*/isU';
			foreach (self::$regions[$id_city] as $region) {
				if (preg_match($pattern, $region['name'])) {
					$out[$region['id']] = $region;
				} else {
					
				}
			}
		} else {
			$out = self::$regions[$id_city];
		}
		return $out;
	}

	public static function getStreets($id_region, $name = false) {
		if ($name)
			$name = strtoupperfirst($name);
		$id_region = max(0, $id_region);
		if (!isset(self::$streets[$id_region])) {
			self::loadStreets($id_region, $name);
		}

		$out = array();

		if ($name) {
			$name = preg_quote($name);
			$pattern = '/.*' . $name . '.*/isU';
			foreach (self::$streets[$id_region] as $street) {
				if (preg_match($pattern, $street['name'])) {
					$out[$street['id']] = $street;
				} else {
					
				}
			}
		} else {
			$out = self::$street[$id_region];
		}
		return $out;
	}

}