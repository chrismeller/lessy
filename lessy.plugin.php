<?php

	namespace LESSy;

	class LESSy extends \Habari\Plugin {

		public function action_init ( ) {

			spl_autoload_register( function ( $class ) {
				if ( $class == 'lessc' ) {
					spl_autoload( 'lessphp/' . $class . '.inc' );
				}
			} );

		}

		public function filter_stack_out ( $stack, $stack_name, $format ) {

			$less = new \lessc();

			$site_url = \Habari\Site::get_url( 'habari' );

			foreach ( $stack as $item ) {

				if ( is_array( $item->resource ) ) {
					$item_url = $item->resource[0];
				}
				else {
					$item_url = $item->resource;
				}

				// we only want to touch things that are local
				// @todo trim off the scheme and see if this is a // but still points to our site?
				if ( strpos( $item_url, $site_url ) === 0 ) {

					// trim the site off our item URL to get what should be a path to the file
					$item_filename = substr( $item_url, strlen( \Habari\Site::get_url( 'habari' ) ) );
					$item_filepath = HABARI_PATH . $item_filename;

					// make sure it's a LESS file
					if ( pathinfo( $item_filepath, PATHINFO_EXTENSION ) == 'less' ) {

						// the name we'll compile it into
						$dest_filename = pathinfo( $item->resource, PATHINFO_FILENAME ) . '.css';
						$dest_filepath = \Habari\Site::get_dir( 'user' ) . '/cache/' . $dest_filename;

						$r = $less->checkedCompile( $item_filepath, $dest_filepath );

						$dest_url = \Habari\Site::get_url( 'habari' ) . '/user/cache/' . $dest_filename;

						// change the resource URL
						$item->resource = $dest_url;

					}

				}

			}

			return $stack;

		}

	}

?>