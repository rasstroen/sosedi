<?php

class Config {

	// сколько и за какое действие даём

	private static $config = array(
	    'base_path' => './', // в какой директории на сервере лежит index.php
	    'www_absolute_path' => '/dnafka', // например для http://localhost/hello/ это будет /hello
	    'www_path' => 'http://10.0.2.97/dnafka/',
	    'www_domain' => '10.0.2.97',
	    'default_page_name' => 'main', // синоним для корня сайта
	    'static_path' => './static/', // 
	    //USERS
	    'default_language' => 'ru',
	    //Register
	    'register_email_from' => 'amuhc@yandex.ru',
	    //Auth
	    'auth_cookie_lifetime' => 360000,
	    'auth_cookie_hash_name' => 'wddhash_',
	    'auth_cookie_id_name' => 'wddid_',
	    // Avatars
	    'avatar_upload_path' => './static/upload/avatars',
	    // Mongo
	    'mongohost' => 'localhost',
	    // MySQL
	    'dbuser' => 'root',
	    'dbpass' => '2912',
	    'dbhost' => 'localhost',
	    'dbname' => 'diary',
	    // MODULES
	    'writemodules_path' => './modules/write',
	    // THEMES
	    'default_theme' => 'default',
	    // XSLT
	    'xslt_files_path' => './xslt',
	    //CACHE
	    'cache_enabled' => false, // отключить/включить весь кеш
	    'cache_default_folder' => './cache/var',
	    // XSL CACHE
	    'xsl_cache_min_sec' => 1,
	    'xsl_cache_max_sec' => 300,
	    'xsl_cache_file_path' => './cache/xsl',
	    'xsl_cache_memcache_enabled' => false,
	    'xsl_cache_xcache_enabled' => true,
	    // XML CACHE
	    'xml_cache_min_sec' => 1,
	    'xml_cache_max_sec' => 86400,
	    'xml_cache_file_path' => './cache/xml',
	    'xml_cache_memcache_enabled' => false,
	    'xml_cache_xcache_enabled' => true,
	    // ADMIN
	    'phplib_pages_path' => './phplib',
	    'phplib_modules_path' => './phplib',
	   
	);

// получем переменную из конфига
	public static function need($var_name, $default = false) {
		if (isset(self::$config[$var_name])) {
			return self::$config[$var_name];
		}
		return $default;
	}

	public static function init($local_config = false) {
		if ($local_config) {
			foreach ($local_config as $name => $value) {
				self::$config[$name] = $value;
			}
		}
	}

}