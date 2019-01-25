<?php
$dirs = [
	__DIR__ . '/functions/',
	__DIR__ . '/functions/classes/',
	__DIR__ . '/functions/widgets/',
];

foreach ( $dirs as $dir ) {
	if ( file_exists( $dir ) ) {
		$files = scandir( $dir );

		foreach ( $files as $file ) {
			if ( pathinfo( $file, PATHINFO_EXTENSION ) == 'php' ) {
				$file = $dir . $file;

				if ( file_exists( $file ) && is_file( $file ) ) {
					require_once $file;
				}
			}
		}
	}
}
