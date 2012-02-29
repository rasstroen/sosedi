<?php
/**
 * ����������� ���� ������ �� ������ ����
 * ��, ��� ��������� � $local_config, ��������� ������ ����.
 * ���� ���� �� ������ ���� � �����������
 */

$local_config = array(
    'base_path' => '/home/sosedi/', // � ����� ���������� �� ������� ����� index.php
    'www_absolute_path' => '', // �������� ��� http://localhost/hello/ ��� ����� /hello
    'www_path' => 'http://electric-citizen.ru',
    'www_domain' => 'electric-citizen.ru',
    'default_page_name' => 'main', // ������� ��� ����� �����
    'static_path' => '/home/sosedi/static', // 
    //USERS
    'default_language' => 'ru',
    //Register
    'register_email_from' => 'amuhc@yandex.ru',
    //Auth
    'auth_cookie_lifetime' => 360000,
    'auth_cookie_hash_name' => 'ls2hash_',
    'auth_cookie_id_name' => 'ls2id_',
    // Avatars
    'avatar_upload_path' => '/home/sosedi/static/upload/avatars', // � ����� ���������� �� ������� ����� index.php
    // MySQL
    'dbuser' => 'root',
    'dbpass' => '2912',
    'dbhost' => 'localhost',
    'dbname' => 'sosedi',
    // MODULES
    'writemodules_path' => '/home/sosedi/modules/write',
    // THEMES
    'default_theme' => 'default',
    // XSLT
    'xslt_files_path' => '/home/sosedi/xslt',
    //CACHE
    'cache_enabled' => false, // ���������/�������� ���� ���
    'cache_default_folder' => '/home/sosedi/cache/var',
    // XSL CACHE
    'xsl_cache_min_sec' => 1,
    'xsl_cache_max_sec' => 300,
    'xsl_cache_file_path' => '/home/sosedi/cache/xsl',
    'xsl_cache_memcache_enabled' => false,
    'xsl_cache_xcache_enabled' => true,
    // XML CACHE
    'xml_cache_min_sec' => 1,
    'xml_cache_max_sec' => 86400,
    'xml_cache_file_path' => '/home/sosedi/cache/xml',
    'xml_cache_memcache_enabled' => false,
    'xml_cache_xcache_enabled' => true,
    // ADMIN
    'phplib_pages_path' => '/home/sosedi/phplib',
    'phplib_modules_path' => '/home/sosedi/phplib',
    // BOOK FILES
    'files_path' => '/home/sosedi/files',
    // HARDCODE
   
   
);
