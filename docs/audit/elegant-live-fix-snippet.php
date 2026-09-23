<?php
/**
 * Plugin Name: Elegant Live Fix
 * Description: Restore the KAYAN homepage, UAE phones/map, /services/ page, title + LocalBusiness schema. Upload this COMPLETE file.
 * Version:     4.2.0
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
	define( 'ELEGANT_LIVE_FIX_VER', '4.2.0' );
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
		add_action( 'wp_footer', array( __CLASS__, 'print_fab_markup' ), 999 );
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

	/**
	 * Print our own circular corner FABs at the very end of the document.
	 * Later Code Snippets CSS hides .fab-btn; IDs here win that fight.
	 */
	public static function print_fab_markup() {
		if ( is_admin() ) {
			return;
		}
		$wa  = 'https://wa.me/' . self::PHONE_E164;
		$tel = 'tel:' . self::PHONE_LOCAL;
		$icon_wa = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="26" height="26" fill="#fff" aria-hidden="true"><path d="M20.5 3.5A11 11 0 0 0 1.9 18.7L1 23l4.4-.9A11 11 0 0 0 12 23a11 11 0 0 0 8.5-19.5zM12 21.2a9.2 9.2 0 0 1-4.7-1.3l-.3-.2-2.6.5.5-2.5-.2-.3A9.2 9.2 0 1 1 12 21.2zm5.3-6.9c-.3-.1-1.7-.8-2-.9s-.5-.1-.7.2l-.9 1.1c-.2.2-.3.2-.6.1a7.5 7.5 0 0 1-2.2-1.4 8.3 8.3 0 0 1-1.5-1.9c-.2-.3 0-.4.1-.6l.5-.6.2-.3a.5.5 0 0 0 0-.5l-.9-2.2c-.2-.6-.5-.5-.7-.5h-.6a1.2 1.2 0 0 0-.9.4 3.6 3.6 0 0 0-1.1 2.7 6.3 6.3 0 0 0 1.3 3.3 14.4 14.4 0 0 0 5.5 5 19 19 0 0 0 1.9.7 4.5 4.5 0 0 0 2.1.1 3.4 3.4 0 0 0 2.2-1.5 2.8 2.8 0 0 0 .2-1.5c-.1-.1-.3-.2-.6-.3z"/></svg>';
		$icon_call = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="22" height="22" fill="#fff" aria-hidden="true"><path d="M6.6 10.8a15.2 15.2 0 0 0 6.6 6.6l2.2-2.2a1 1 0 0 1 1-.2 11.4 11.4 0 0 0 3.6.6 1 1 0 0 1 1 1V20a1 1 0 0 1-1 1A17 17 0 0 1 3 4a1 1 0 0 1 1-1h3.5a1 1 0 0 1 1 1 11.4 11.4 0 0 0 .6 3.6 1 1 0 0 1-.3 1z"/></svg>';
		echo '<style id="elegant-fab-corner">'
			. 'html body #elegant-3d-dock,html body .fab-stack,html body #ruknFab{display:none!important}'
			. 'html body #elegant-corner-fabs{position:fixed!important;left:16px!important;right:auto!important;bottom:18px!important;z-index:2147483000!important;display:flex!important;flex-direction:column!important;gap:10px!important;width:56px!important;margin:0!important;padding:0!important;pointer-events:none}'
			. 'html body #elegant-corner-fabs a,html body.rukn-hide-call #elegant-corner-call,html body.rukn-hide-wa #elegant-corner-wa,html body.rukn-hide-call a#elegant-corner-call[href^="tel:"]{pointer-events:auto;display:flex!important;align-items:center!important;justify-content:center!important;width:56px!important;height:56px!important;min-width:56px!important;border-radius:50%!important;text-decoration:none!important;box-shadow:0 8px 20px rgba(10,31,78,.3)!important;visibility:visible!important;opacity:1!important}'
			. 'html body #elegant-corner-call{background:#0EA5C0!important}'
			. 'html body #elegant-corner-wa{background:#25D366!important}'
			. 'html body #elegant-corner-fabs svg{display:block;width:26px;height:26px}'
			. '@media(min-width:769px){html body #elegant-corner-fabs{left:22px!important;bottom:24px!important}}'
			. '</style>'
			. '<div id="elegant-corner-fabs">'
			. '<a id="elegant-corner-call" href="' . esc_attr( $tel ) . '" aria-label="اتصال">' . $icon_call . '</a>'
			. '<a id="elegant-corner-wa" href="' . esc_url( $wa ) . '" target="_blank" rel="noopener noreferrer" aria-label="واتساب">' . $icon_wa . '</a>'
			. '</div>' . "\n";
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
