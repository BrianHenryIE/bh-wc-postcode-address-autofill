<?php
/**
 * PHPUnit bootstrap file for WP_Mock.
 *
 * @package brianhenryie/bh-wc-postcode-address-autofill
 */

use Composer\ClassMapGenerator\ClassMapGenerator;

WP_Mock::setUsePatchwork( true );
WP_Mock::bootstrap();

global $plugin_root_dir;

/**
 * @see ClassMapGenerator::createMap()
 */
$create_map = function ( string $path, array $excludedDirs ): array {
	$generator = new ClassMapGenerator();
	$generator->scanPaths( $path, null, 'classmap', null, $excludedDirs );
	return $generator->getClassMap()->getMap();
};
$class_map  = $create_map(
	$plugin_root_dir . '/wp-content/plugins/woocommerce',
	array(
		'lib/packages/GraphQL',
		'src/Api',
		'Internal/Api',
	)
);

spl_autoload_register(
	function ( $classname ) use ( $class_map ) {
		if ( array_key_exists( $classname, $class_map ) && file_exists( $class_map[ $classname ] ) ) {
			require_once $class_map[ $classname ];
		}
	}
);
