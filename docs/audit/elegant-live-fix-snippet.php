<?php
/**
 * Plugin Name: Elegant Live Fix
 * Description: Restore the KAYAN homepage, UAE phones/map, /services/ page, title + LocalBusiness schema. Upload this COMPLETE file.
 * Version:     4.0.0
 *
 * INSTALL (cPanel File Manager):
 *   wp-content/mu-plugins/elegant-live-fix.php
 * The first characters of this file MUST be: <?php
 * Do not paste a function fragment. Do not close the PHP tag at the end of this file.
 */

if ( ! defined( 'ABSPATH' ) ) {
	return;
}

if ( ! defined( 'ELEGANT_LIVE_FIX_VER' ) ) {
	define( 'ELEGANT_LIVE_FIX_VER', '4.0.0' );
}

final class Elegant_Live_Fix {

	const PHONE_LOCAL   = '0521300019';
	const PHONE_E164    = '971521300019';
	const PHONE_TEL     = '+971521300019';
	const PHONE_DISPLAY = '+971 52 130 0019';
	const ADDRESS_AR    = 'مصفح 23، شارع 15، أبوظبي';
	const MAP_QUERY     = 'Mussafah+23+Street+15+Abu+Dhabi';
	const BLOG_PAGE_ID  = 170;
	const FRONT_DEAD_ID = 7411;

	public static function boot() {
		self::boot_buffer();
		add_action( 'init', array( __CLASS__, 'restore_posts_homepage' ), 0 );
		add_filter( 'register_post_type_args', array( __CLASS__, 'services_cpt_args' ), 99, 2 );
		add_filter( 'request', array( __CLASS__, 'services_page_wins' ), 1 );
		add_action( 'init', array( __CLASS__, 'maybe_flush_rewrites' ), 99 );
		add_action( 'after_setup_theme', array( __CLASS__, 'theme_supports' ), 20 );
		add_action( 'wp_head', array( __CLASS__, 'print_schema' ), 1 );
		add_filter( 'rest_post_dispatch', array( __CLASS__, 'filter_dni_rest' ), 10, 3 );
		add_filter( 'wp_is_application_passwords_available', '__return_true', 999 );
	}

	/**
	 * Buffer starts here (mu-plugin load), not at template_redirect, so leaked
	 * source from a broken sibling file still gets stripped before the browser.
	 */
	private static function boot_buffer() {
		if ( defined( 'ELEGANT_LIVE_FIX_BUFFERING' ) ) {
			return;
		}
		define( 'ELEGANT_LIVE_FIX_BUFFERING', true );
		if ( self::skip_buffer() ) {
			return;
		}
		ob_start( array( __CLASS__, 'rewrite_html' ) );
	}

	/**
	 * KAYAN 1.4.2 homepage is Blade('index') only when is_home() is true.
	 * Static front page 7411 made / a broken inner page and moved the hero to /blog/.
	 * Restoring "latest posts" as the front is the correct fix for this locked theme.
	 */
	public static function restore_posts_homepage() {
		if ( get_option( 'show_on_front' ) !== 'page' ) {
			return;
		}
		update_option( 'show_on_front', 'posts' );
		update_option( 'page_on_front', 0 );
		if ( ! (int) get_option( 'page_for_posts' ) ) {
			update_option( 'page_for_posts', self::BLOG_PAGE_ID );
		}
		if ( get_post( self::FRONT_DEAD_ID ) ) {
			wp_update_post(
				array(
					'ID'          => self::FRONT_DEAD_ID,
					'post_status' => 'draft',
					'post_content'=> '',
				)
			);
		}
		if ( function_exists( 'do_action' ) ) {
			do_action( 'litespeed_purge_all' );
		}
	}

	public static function theme_supports() {
		add_theme_support( 'title-tag' );
	}

	public static function services_cpt_args( $args, $post_type ) {
		if ( 'services' !== $post_type ) {
			return $args;
		}
		$args['has_archive'] = false;
		$args['rewrite']     = array(
			'slug'       => 'pool-service',
			'with_front' => false,
		);
		return $args;
	}

	public static function services_page_wins( $vars ) {
		$is_archive = isset( $vars['post_type'] ) && 'services' === $vars['post_type']
			&& empty( $vars['name'] )
			&& empty( $vars['services'] )
			&& empty( $vars['pagename'] );
		if ( ! $is_archive ) {
			return $vars;
		}
		$page = get_page_by_path( 'services' );
		if ( ! $page || 'publish' !== $page->post_status ) {
			return $vars;
		}
		return array(
			'page_id'  => (int) $page->ID,
			'pagename' => 'services',
		);
	}

	public static function maybe_flush_rewrites() {
		if ( get_option( 'elegant_live_fix_rewrites' ) === ELEGANT_LIVE_FIX_VER ) {
			return;
		}
		flush_rewrite_rules( false );
		update_option( 'elegant_live_fix_rewrites', ELEGANT_LIVE_FIX_VER, false );
	}

	/**
	 * @param string $html Buffered output.
	 * @return string
	 */
	public static function rewrite_html( $html ) {
		if ( ! is_string( $html ) || $html === '' ) {
			return $html;
		}

		$doc = stripos( $html, '<!DOCTYPE' );
		if ( false === $doc ) {
			$doc = stripos( $html, '<html' );
		}
		if ( false !== $doc && $doc > 0 ) {
			$prefix = substr( $html, 0, $doc );
			if ( preg_match( '/force_kayan_home_query|public static function|\$query->is_home|<\?php/', $prefix ) ) {
				$html = substr( $html, $doc );
			}
		}

		if ( strpos( $html, 'force_kayan_home_query' ) !== false ) {
			$stripped = preg_replace(
				'/public static function force_kayan_home_query\s*\([^)]*\)\s*\{.*?^\s*\}/ms',
				'',
				$html
			);
			if ( is_string( $stripped ) ) {
				$html = $stripped;
			}
		}

		$needles = array( '201556644443', '201151481000', 'Dubai,United', 'كيان ويب', 'KAYAN WEB' );
		$hit     = false;
		foreach ( $needles as $needle ) {
			if ( strpos( $html, $needle ) !== false ) {
				$hit = true;
				break;
			}
		}

		$needs_title = ( stripos( $html, '<title' ) === false && stripos( $html, '</head>' ) !== false );

		if ( ! $hit && ! $needs_title ) {
			return $html;
		}

		if ( $hit ) {
			$html = str_replace(
				array(
					'201556644443',
					'201151481000',
					'+20 155 664 4443',
					'+201556644443',
					'tel:+201556644443',
					'tel:201556644443',
					'052-130-0019',
					'052 130 0019',
					'Dubai,United+Arab+Emirates',
					'Dubai,United Arab Emirates',
					'شركة كيان ويب للتسويق الإلكتروني',
					'كيان ويب',
					'KAYAN WEB',
				),
				array(
					self::PHONE_E164,
					self::PHONE_E164,
					self::PHONE_DISPLAY,
					self::PHONE_TEL,
					'tel:' . self::PHONE_LOCAL,
					'tel:' . self::PHONE_LOCAL,
					self::PHONE_LOCAL,
					self::PHONE_LOCAL,
					self::MAP_QUERY,
					'Mussafah 23 Street 15 Abu Dhabi',
					'اليجانت للمسابح',
					'اليجانت للمسابح',
					'اليجانت للمسابح',
				),
				$html
			);

			$credit = preg_replace(
				'#<a[^>]+href=["\']https?://wa\.me/(?:201151481000|971521300019)[^"\']*["\'][^>]*>\s*(?:KAYAN WEB|كيان ويب|اليجانت للمسابح)\s*</a>#iu',
				'',
				$html
			);
			if ( is_string( $credit ) ) {
				$html = $credit;
			}
		}

		if ( $needs_title && function_exists( 'wp_get_document_title' ) ) {
			$title  = esc_html( wp_get_document_title() );
			$headed = preg_replace( '#</head>#i', '<title>' . $title . '</title></head>', $html, 1 );
			if ( is_string( $headed ) ) {
				$html = $headed;
			}
		}

		return $html;
	}

	public static function print_schema() {
		if ( is_admin() ) {
			return;
		}
		$schema = array(
			'@context'      => 'https://schema.org',
			'@type'         => array( 'LocalBusiness', 'HomeAndConstructionBusiness' ),
			'@id'           => home_url( '/#localbusiness' ),
			'name'          => 'اليجانت للمسابح',
			'alternateName' => 'Elegant Swimming Pools',
			'description'   => 'شركة مسابح متخصصة في إنشاء وصيانة وتنظيف حمامات السباحة في أبوظبي ودبي وجميع الإمارات.',
			'url'           => home_url( '/' ),
			'telephone'     => self::PHONE_TEL,
			'priceRange'    => 'AED',
			'address'       => array(
				'@type'           => 'PostalAddress',
				'streetAddress'   => self::ADDRESS_AR,
				'addressLocality' => 'أبوظبي',
				'addressRegion'   => 'أبوظبي',
				'addressCountry'  => 'AE',
			),
			'areaServed'    => 'AE',
			'openingHours'  => 'Mo-Su 00:00-23:59',
			'sameAs'        => array( 'https://wa.me/' . self::PHONE_E164 ),
		);
		echo '<script type="application/ld+json" id="elegant-localbusiness-schema">'
			. wp_json_encode( $schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES )
			. '</script>' . "\n";
	}

	public static function filter_dni_rest( $result, $server, $request ) {
		unset( $server );
		if ( ! ( $result instanceof WP_REST_Response ) || ! ( $request instanceof WP_REST_Request ) ) {
			return $result;
		}
		if ( strpos( (string) $request->get_route(), '/kayan/v1/dni' ) === false ) {
			return $result;
		}
		$data = $result->get_data();
		if ( is_array( $data ) ) {
			$data['phone']     = self::PHONE_LOCAL;
			$data['wa_number'] = self::PHONE_E164;
			$result->set_data( $data );
		}
		return $result;
	}

	private static function skip_buffer() {
		if ( PHP_SAPI === 'cli' ) {
			return true;
		}
		$uri = isset( $_SERVER['REQUEST_URI'] ) ? (string) $_SERVER['REQUEST_URI'] : '';
		if ( $uri === '' ) {
			return false;
		}
		if ( strpos( $uri, '/wp-json/' ) !== false || strpos( $uri, 'admin-ajax.php' ) !== false ) {
			return true;
		}
		if ( strpos( $uri, '/wp-admin/' ) !== false && strpos( $uri, 'admin-post.php' ) === false ) {
			return true;
		}
		if ( strpos( $uri, 'wp-cron.php' ) !== false || strpos( $uri, '/xmlrpc.php' ) !== false ) {
			return true;
		}
		return false;
	}
}

Elegant_Live_Fix::boot();
