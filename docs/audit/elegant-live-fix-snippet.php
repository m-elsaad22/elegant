<?php
/**
 * Plugin Name:       Elegant Live Fix
 * Plugin URI:        https://elegantswimmingpools.com
 * Description:       Must-use bypass for locked KAYAN 1.4.2: force the homepage hero onto the static front page, UAE phone/map replacements via output buffering, /services/ page priority, document title + LocalBusiness schema.
 * Version:           3.0.0
 * Author:            Elegant Swimming Pools
 * Text Domain:       elegant-live-fix
 *
 * Drop this file at: wp-content/mu-plugins/elegant-live-fix.php
 */

if ( ! defined( 'ABSPATH' ) ) {
	return;
}

final class Elegant_Live_Fix {

	const VERSION         = '3.0.0';
	const FRONT_PAGE_ID   = 7411;
	const BLOG_PAGE_ID    = 170;
	const PHONE_LOCAL     = '0521300019';
	const PHONE_E164      = '971521300019';
	const PHONE_TEL       = '+971521300019';
	const PHONE_DISPLAY   = '+971 52 130 0019';
	const ADDRESS_AR      = 'مصفح 23، شارع 15، أبوظبي';
	const ADDRESS_FULL    = 'مصفح 23، شارع 15، أبوظبي، الإمارات العربية المتحدة';
	const MAP_EMBED_SRC   = 'https://maps.google.com/maps?q=Mussafah+23+Street+15+Abu+Dhabi&hl=ar&z=15&output=embed';
	const REWRITE_FLAG    = 'elegant_live_fix_rewrites_v3';

	/** @var bool */
	private static $buffering = false;

	public static function boot() {
		add_action( 'after_setup_theme', array( __CLASS__, 'theme_supports' ), 20 );
		add_action( 'parse_query', array( __CLASS__, 'force_kayan_home_query' ), 1 );
		add_action( 'wp', array( __CLASS__, 'force_kayan_home_flags' ), 0 );
		add_filter( 'template_include', array( __CLASS__, 'force_home_template' ), 99 );
		add_filter( 'register_post_type_args', array( __CLASS__, 'services_cpt_args' ), 99, 2 );
		add_filter( 'request', array( __CLASS__, 'services_page_wins' ), 1 );
		add_action( 'init', array( __CLASS__, 'maybe_flush_rewrites' ), 99 );
		add_action( 'template_redirect', array( __CLASS__, 'start_buffer' ), -5 );
		add_action( 'wp_head', array( __CLASS__, 'print_schema' ), 1 );
		add_filter( 'rest_post_dispatch', array( __CLASS__, 'filter_dni_rest' ), 10, 3 );
		add_filter( 'wp_is_application_passwords_available', '__return_true', 999 );
	}

	/**
	 * KAYAN 1.4.2 does not call add_theme_support( 'title-tag' ).
	 * Core can then emit a correct <title>; the buffer still injects one if the theme omitted it.
	 */
	public static function theme_supports() {
		add_theme_support( 'title-tag' );
		add_theme_support(
			'html5',
			array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script' )
		);
	}

	/**
	 * ThemeStatic::Locate() (template_redirect) only loads Blade('index') — the hero
	 * homepage — when is_home() is true. A static front page makes is_front_page()
	 * true and is_home() false, so Locate() falls through to Blade('page').
	 *
	 * Flip the main query so the static front page is treated as home, and so the
	 * posts page (/blog/) is no longer the KAYAN homepage.
	 *
	 * @param WP_Query $query Main query.
	 */
	public static function force_kayan_home_query( $query ) {
		if ( is_admin() || ! $query->is_main_query() ) {
			return;
		}

		$front_id = self::front_page_id();
		$blog_id  = self::blog_page_id();

		if ( $front_id && $query->is_page && (int) $query->get_queried_object_id() === $front_id ) {
			$query->is_home       = true;
			$query->is_front_page = true;
			$query->is_page       = false;
			$query->is_singular   = false;
			$query->is_archive    = false;
			$query->is_404        = false;
			return;
		}

		if ( $blog_id && $query->is_home && ! $query->is_front_page ) {
			$page = get_post( $blog_id );
			if ( $page ) {
				$query->is_home           = false;
				$query->is_posts_page     = true;
				$query->is_page           = true;
				$query->is_singular       = true;
				$query->is_front_page     = false;
				$query->queried_object    = $page;
				$query->queried_object_id = $blog_id;
			}
		}
	}

	/**
	 * Safety net after WP_Query is fully set up, before template_redirect (v3 + Locate).
	 */
	public static function force_kayan_home_flags() {
		if ( is_admin() ) {
			return;
		}

		global $wp_query;
		if ( ! $wp_query instanceof WP_Query ) {
			return;
		}

		$front_id = self::front_page_id();
		$blog_id  = self::blog_page_id();

		if ( $front_id && is_front_page() && ! is_home() ) {
			$wp_query->is_home       = true;
			$wp_query->is_front_page = true;
			$wp_query->is_page       = false;
			$wp_query->is_singular   = false;
		}

		if ( $blog_id && is_home() && ! is_front_page() ) {
			$page = get_post( $blog_id );
			if ( $page ) {
				$wp_query->is_home           = false;
				$wp_query->is_posts_page     = true;
				$wp_query->is_page           = true;
				$wp_query->is_singular       = true;
				$wp_query->queried_object    = $page;
				$wp_query->queried_object_id = $blog_id;
			}
		}
	}

	/**
	 * If a conventional theme template is used (Locate() does not use this path),
	 * still prefer home.php / index.php on the front page.
	 *
	 * @param string $template Located template path.
	 * @return string
	 */
	public static function force_home_template( $template ) {
		if ( is_admin() || ! is_front_page() ) {
			return $template;
		}
		$home = locate_template( array( 'home.php', 'front-page.php', 'index.php' ) );
		return $home ? $home : $template;
	}

	/**
	 * Stop the services CPT from owning /services/ (page slug + CPT rewrite clash).
	 *
	 * @param array  $args      Post type args.
	 * @param string $post_type Post type name.
	 * @return array
	 */
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

	/**
	 * Same-request fallback: if WP resolved /services/ as a CPT archive, load the Page instead.
	 *
	 * @param array $vars Request query vars.
	 * @return array
	 */
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
		if ( get_option( self::REWRITE_FLAG ) === self::VERSION ) {
			return;
		}
		flush_rewrite_rules( false );
		update_option( self::REWRITE_FLAG, self::VERSION, false );
	}

	/**
	 * Start a single outer output buffer before KAYAN lockdown (-1) and Locate (10).
	 * Skips admin, REST, cron, feeds, and sitemaps so TTFB is untouched there.
	 */
	public static function start_buffer() {
		if ( self::$buffering || self::skip_buffer() ) {
			return;
		}
		self::$buffering = true;
		ob_start( array( __CLASS__, 'rewrite_html' ) );
	}

	/**
	 * Cheap-exit rewriter: strpos gate, then C-level str_replace. Regex only for the map iframe.
	 *
	 * @param string $html Buffered document.
	 * @return string
	 */
	public static function rewrite_html( $html ) {
		if ( ! is_string( $html ) || $html === '' || ( isset( $html[0] ) && $html[0] !== '<' ) ) {
			return $html;
		}

		$needles = array(
			'201556644443',
			'201151481000',
			'Dubai,United',
			'Dubai, United',
			'كيان ويب',
			'KAYAN WEB',
			'kayan web',
		);

		$hit = false;
		foreach ( $needles as $needle ) {
			if ( $needle !== '' && strpos( $html, $needle ) !== false ) {
				$hit = true;
				break;
			}
		}

		$needs_title = ( stripos( $html, '<title' ) === false && stripos( $html, '</head>' ) !== false );

		if ( ! $hit && ! $needs_title ) {
			return $html;
		}

		if ( $hit ) {
			$search  = array(
				'201556644443',
				'201151481000',
				'+20 155 664 4443',
				'+201556644443',
				'tel:+201556644443',
				'tel:201556644443',
			);
			$replace = array(
				self::PHONE_E164,
				self::PHONE_E164,
				self::PHONE_DISPLAY,
				self::PHONE_TEL,
				'tel:' . self::PHONE_LOCAL,
				'tel:' . self::PHONE_LOCAL,
			);
			$html = str_replace( $search, $replace, $html );
			$html = str_replace(
				array( '052-130-0019', '052 130 0019', '052.130.0019' ),
				self::PHONE_LOCAL,
				$html
			);
			$html = str_replace(
				array( 'Dubai,United+Arab+Emirates', 'Dubai,United Arab Emirates' ),
				array( 'Mussafah+23+Street+15+Abu+Dhabi', 'Mussafah 23 Street 15 Abu Dhabi' ),
				$html
			);

			$credit = preg_replace(
				'#<a[^>]+href=["\']https?://wa\.me/(?:201151481000|971521300019)[^"\']*["\'][^>]*>\s*(?:KAYAN WEB|كيان ويب)\s*</a>#iu',
				'',
				$html
			);
			if ( is_string( $credit ) ) {
				$html = $credit;
			}
			$html = str_replace(
				array( 'شركة كيان ويب للتسويق الإلكتروني', 'كيان ويب', 'KAYAN WEB' ),
				array( 'اليجانت للمسابح', 'اليجانت للمسابح', 'اليجانت للمسابح' ),
				$html
			);
		}

		if ( $needs_title ) {
			$title   = esc_html( wp_get_document_title() );
			$headed  = preg_replace( '#</head>#i', '<title>' . $title . '</title></head>', $html, 1 );
			if ( is_string( $headed ) ) {
				$html = $headed;
			}
		}

		return $html;
	}

	/**
	 * Valid LocalBusiness JSON-LD for a UAE pool contractor. Printed early in wp_head.
	 */
	public static function print_schema() {
		if ( is_admin() ) {
			return;
		}

		$schema = array(
			'@context'        => 'https://schema.org',
			'@type'           => array( 'LocalBusiness', 'HomeAndConstructionBusiness' ),
			'@id'             => home_url( '/#localbusiness' ),
			'name'            => 'اليجانت للمسابح',
			'alternateName'   => 'Elegant Swimming Pools',
			'description'     => 'شركة مسابح متخصصة في إنشاء وصيانة وتنظيف حمامات السباحة في أبوظبي ودبي وجميع الإمارات.',
			'url'             => home_url( '/' ),
			'telephone'       => self::PHONE_TEL,
			'priceRange'      => 'AED',
			'image'           => home_url( '/' ),
			'address'         => array(
				'@type'           => 'PostalAddress',
				'streetAddress'   => 'مصفح 23، شارع 15',
				'addressLocality' => 'أبوظبي',
				'addressRegion'   => 'أبوظبي',
				'addressCountry'  => 'AE',
			),
			'geo'             => array(
				'@type'     => 'GeoCoordinates',
				'latitude'  => 24.3620,
				'longitude' => 54.5000,
			),
			'areaServed'      => array(
				array( '@type' => 'City', 'name' => 'Abu Dhabi' ),
				array( '@type' => 'City', 'name' => 'Dubai' ),
				array( '@type' => 'City', 'name' => 'Al Ain' ),
				array( '@type' => 'AdministrativeArea', 'name' => 'United Arab Emirates' ),
			),
			'openingHours'    => 'Mo-Su 00:00-23:59',
			'sameAs'          => array(
				'https://wa.me/' . self::PHONE_E164,
			),
		);

		echo '<script type="application/ld+json" id="elegant-localbusiness-schema">'
			. wp_json_encode( $schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES )
			. '</script>' . "\n";
	}

	/**
	 * KAYAN DNI is JSON, not HTML — the output buffer never sees it.
	 *
	 * @param WP_REST_Response          $result  Response.
	 * @param WP_REST_Server            $server  Server.
	 * @param WP_REST_Request           $request Request.
	 * @return WP_REST_Response
	 */
	public static function filter_dni_rest( $result, $server, $request ) {
		unset( $server );
		if ( ! ( $result instanceof WP_REST_Response ) ) {
			return $result;
		}
		$route = $request instanceof WP_REST_Request ? $request->get_route() : '';
		if ( strpos( (string) $route, '/kayan/v1/dni' ) === false ) {
			return $result;
		}
		$data = $result->get_data();
		if ( ! is_array( $data ) ) {
			return $result;
		}
		$data['phone']     = self::PHONE_LOCAL;
		$data['wa_number'] = self::PHONE_E164;
		$result->set_data( $data );
		return $result;
	}

	private static function front_page_id() {
		$id = (int) get_option( 'page_on_front' );
		return $id ? $id : self::FRONT_PAGE_ID;
	}

	private static function blog_page_id() {
		$id = (int) get_option( 'page_for_posts' );
		return $id ? $id : self::BLOG_PAGE_ID;
	}

	private static function skip_buffer() {
		if ( is_admin() || wp_doing_ajax() || wp_doing_cron() || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ) {
			return true;
		}
		if ( is_feed() || is_robots() || is_trackback() ) {
			return true;
		}
		if ( isset( $_GET['sitemap'] ) || ( function_exists( 'wp_is_json_request' ) && wp_is_json_request() ) ) {
			return true;
		}
		$uri = isset( $_SERVER['REQUEST_URI'] ) ? (string) $_SERVER['REQUEST_URI'] : '';
		if ( $uri && ( strpos( $uri, '/wp-json/' ) !== false || strpos( $uri, 'wp-cron.php' ) !== false ) ) {
			return true;
		}
		return false;
	}
}

Elegant_Live_Fix::boot();
