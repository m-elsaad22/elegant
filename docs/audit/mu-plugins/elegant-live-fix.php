<?php
/**
 * Plugin Name: Elegant Live Fix
 * Description: Restore the KAYAN homepage, UAE phones/map, /services/ page, GSC index coverage, title + LocalBusiness schema. Upload this COMPLETE file.
 * Version:     5.0.0
 *
 * INSTALL (cPanel File Manager):
 *   wp-content/mu-plugins/elegant-live-fix.php
 * The first characters of this file MUST be: <?php
 * Do not paste a function fragment. Do not close the PHP tag at the end of this file.
 *
 * Also replace public_html/robots.txt with the copy in docs/audit/cpanel/robots.txt
 * if this plugin cannot write it. Then LiteSpeed Cache → Purge All (including 404).
 */

if ( ! defined( 'ABSPATH' ) ) {
	return;
}

if ( ! defined( 'ELEGANT_LIVE_FIX_VER' ) ) {
	define( 'ELEGANT_LIVE_FIX_VER', '5.0.0' );
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
	const EMPTY_SITEMAP_PAGE_ID = 282;

	public static function boot() {
		self::boot_buffer();
		add_action( 'init', array( __CLASS__, 'restore_posts_homepage' ), 0 );
		add_action( 'init', array( __CLASS__, 'repair_public_files' ), 1 );
		add_filter( 'register_post_type_args', array( __CLASS__, 'services_cpt_args' ), 99, 2 );
		add_filter( 'request', array( __CLASS__, 'services_page_wins' ), 1 );
		add_action( 'init', array( __CLASS__, 'maybe_flush_rewrites' ), 99 );
		add_action( 'after_setup_theme', array( __CLASS__, 'theme_supports' ), 20 );
		add_action( 'pre_get_posts', array( __CLASS__, 'constrain_home_query' ), 1 );
		add_action( 'template_redirect', array( __CLASS__, 'redirect_paged_home' ), 0 );
		add_filter( 'redirect_canonical', array( __CLASS__, 'stop_pretty_trap' ), 10, 2 );
		add_filter( 'robots_txt', array( __CLASS__, 'filter_robots_txt' ), 99, 2 );
		add_filter( 'wp_robots', array( __CLASS__, 'filter_wp_robots' ), 99 );
		add_filter( 'rank_math/frontend/robots', array( __CLASS__, 'filter_rank_math_robots' ), 99 );
		add_filter( 'rank_math/frontend/disable_adjacent_rel_links', '__return_true', 99 );
		add_filter( 'rank_math/sitemap/enable_caching', '__return_false', 99 );
		add_filter( 'rank_math/sitemap/content', array( __CLASS__, 'rewrite_html' ), 99 );
		add_filter( 'rank_math/sitemap/entry', array( __CLASS__, 'filter_sitemap_entry' ), 99, 3 );
		add_action( 'wp_head', array( __CLASS__, 'print_schema' ), 1 );
		add_action( 'wp_head', array( __CLASS__, 'print_noindex_if_needed' ), 0 );
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
		self::purge_all_caches();
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
		self::purge_all_caches();
	}

	/**
	 * Homepage is a KAYAN landing page, not a paginated blog. rel=next to /page/2/
	 * is a GSC 404 and a duplicate-home signal.
	 */
	public static function constrain_home_query( $query ) {
		if ( is_admin() || ! $query->is_main_query() ) {
			return;
		}
		if ( ! $query->is_home() ) {
			return;
		}
		$query->set( 'no_found_rows', true );
		if ( (int) $query->get( 'paged' ) > 1 ) {
			$query->set( 'paged', 1 );
		}
	}

	public static function redirect_paged_home() {
		if ( is_admin() ) {
			return;
		}
		if ( ( is_home() || is_front_page() ) && is_paged() ) {
			wp_safe_redirect( home_url( '/' ), 301 );
			exit;
		}
	}

	/**
	 * Pretty permalinks currently 404 at LiteSpeed before PHP. Do not 301 a working
	 * ?pagename= / index.php URL onto a dead pretty permalink.
	 */
	public static function stop_pretty_trap( $redirect_url, $requested_url ) {
		unset( $requested_url );
		if ( empty( $redirect_url ) ) {
			return $redirect_url;
		}
		if ( isset( $_GET['pagename'] ) || isset( $_GET['page_id'] ) || isset( $_GET['p'] ) || isset( $_GET['name'] ) || isset( $_GET['sitemap'] ) || isset( $_GET['rest_route'] ) ) {
			return false;
		}
		$uri = isset( $_SERVER['REQUEST_URI'] ) ? (string) $_SERVER['REQUEST_URI'] : '';
		if ( strpos( $uri, 'index.php' ) !== false ) {
			return false;
		}
		return $redirect_url;
	}

	public static function filter_robots_txt( $output, $public ) {
		unset( $output, $public );
		return self::robots_body();
	}

	public static function filter_wp_robots( $robots ) {
		if ( ! is_array( $robots ) ) {
			$robots = array();
		}
		if ( self::current_url_should_noindex() ) {
			$robots['noindex']  = true;
			$robots['nofollow'] = true;
		}
		return $robots;
	}

	public static function filter_rank_math_robots( $robots ) {
		if ( ! is_array( $robots ) ) {
			$robots = array();
		}
		if ( self::current_url_should_noindex() ) {
			$robots['index']  = 'noindex';
			$robots['follow'] = 'nofollow';
		}
		return $robots;
	}

	/**
	 * Drop thin/off-market URLs and the empty /sitemap/ page from Rank Math XML.
	 *
	 * @param array|false $url URL parts.
	 * @param string      $type post|term|user.
	 * @param object      $object Post or term.
	 * @return array|false
	 */
	public static function filter_sitemap_entry( $url, $type, $object ) {
		unset( $type );
		if ( empty( $url ) || ! is_array( $url ) || empty( $url['loc'] ) ) {
			return $url;
		}
		$loc = (string) $url['loc'];
		if ( self::is_off_market_text( $loc ) ) {
			return false;
		}
		$path = (string) parse_url( $loc, PHP_URL_PATH );
		if ( $path === '/sitemap/' || $path === '/sitemap' ) {
			return false;
		}
		if ( is_object( $object ) && isset( $object->ID ) && (int) $object->ID === self::EMPTY_SITEMAP_PAGE_ID ) {
			return false;
		}
		if ( is_object( $object ) && ! empty( $object->post_name ) && self::is_off_market_text( $object->post_name . ' ' . ( isset( $object->post_title ) ? $object->post_title : '' ) ) ) {
			return false;
		}
		if ( is_object( $object ) && ! empty( $object->slug ) && self::is_off_market_text( $object->slug . ' ' . ( isset( $object->name ) ? $object->name : '' ) ) ) {
			return false;
		}
		return $url;
	}

	public static function print_noindex_if_needed() {
		if ( is_admin() || ! self::current_url_should_noindex() ) {
			return;
		}
		echo '<meta name="robots" content="noindex, nofollow" />' . "\n";
	}

	/**
	 * Overwrite the physical robots.txt (LiteSpeed never hits WP for /robots.txt),
	 * quarantine gambling HTML parked on /contact-us/ and /cities/, and merge
	 * rewrite rules so pretty permalinks and Rank Math XML reach WordPress.
	 */
	public static function repair_public_files() {
		self::write_robots_file();
		self::quarantine_spam_paths();
		self::ensure_htaccess_rules();
	}

	/**
	 * @param string $html Buffered output.
	 * @return string
	 */
	public static function rewrite_html( $html ) {
		if ( ! is_string( $html ) || $html === '' ) {
			return $html;
		}

		if ( strpos( $html, '<sitemapindex' ) !== false || strpos( $html, '<urlset' ) !== false ) {
			return self::rewrite_sitemap_xml( $html );
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

		if ( strpos( $html, 'rel=' ) !== false && strpos( $html, '/page/' ) !== false ) {
			$no_rel = preg_replace( '#<link\b[^>]*href=["\'][^"\']*/page/[0-9]+/?["\'][^>]*>#i', '', $html );
			if ( is_string( $no_rel ) ) {
				$html = $no_rel;
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
	 * LiteSpeed 404s pretty Rank Math XML. Point every <loc> at the query form
	 * that already returns 200: /?sitemap=post&sitemap_n=1
	 */
	private static function rewrite_sitemap_xml( $xml ) {
		$sheet = strpos( $xml, '<?xml-stylesheet' );
		if ( $sheet !== false ) {
			$end = strpos( $xml, '>', $sheet );
			if ( $end !== false ) {
				$xml = substr( $xml, 0, $sheet ) . ltrim( substr( $xml, $end + 1 ) );
			}
		}
		$rewritten = preg_replace_callback(
			'#https?://[^/\s<]+/([a-z0-9_-]+)-sitemap([0-9]+)?\.xml#i',
			array( __CLASS__, 'sitemap_loc_to_query' ),
			$xml
		);
		if ( is_string( $rewritten ) ) {
			$xml = $rewritten;
		}
		$home = preg_replace_callback(
			'#<(loc)>\s*(https?://[^<\s]+/sitemap_index\.xml)\s*</\1>#i',
			array( __CLASS__, 'sitemap_index_loc_to_query' ),
			$xml
		);
		if ( is_string( $home ) ) {
			$xml = $home;
		}
		$dropped = preg_replace(
			'#<url>\s*<loc>\s*https?://[^<\s]+/sitemap/?\s*</loc>.*?</url>#is',
			'',
			$xml
		);
		if ( is_string( $dropped ) ) {
			$xml = $dropped;
		}
		return $xml;
	}

	/**
	 * @param array $m Regex matches.
	 * @return string
	 */
	public static function sitemap_loc_to_query( $m ) {
		$type  = strtolower( $m[1] );
		$query = 'sitemap=' . rawurlencode( $type );
		if ( isset( $m[2] ) && $m[2] !== '' ) {
			$query .= '&sitemap_n=' . rawurlencode( $m[2] );
		}
		return htmlspecialchars( home_url( '/?' . $query ), ENT_QUOTES | ENT_XML1, 'UTF-8' );
	}

	/**
	 * @param array $m Regex matches.
	 * @return string
	 */
	public static function sitemap_index_loc_to_query( $m ) {
		return '<' . $m[1] . '>' . home_url( '/?sitemap=1' ) . '</' . $m[1] . '>';
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
		if ( strpos( $uri, '/wp-json/' ) !== false || strpos( $uri, 'admin-ajax.php' ) !== false || strpos( $uri, 'rest_route=' ) !== false ) {
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

	private static function current_url_should_noindex() {
		if ( function_exists( 'is_paged' ) && is_paged() ) {
			return true;
		}
		if ( function_exists( 'is_search' ) && is_search() ) {
			return true;
		}
		if ( function_exists( 'is_feed' ) && is_feed() ) {
			return true;
		}
		if ( function_exists( 'is_page' ) && is_page( array( 'sitemap', self::EMPTY_SITEMAP_PAGE_ID ) ) ) {
			return true;
		}
		$uri = isset( $_SERVER['REQUEST_URI'] ) ? (string) $_SERVER['REQUEST_URI'] : '';
		if ( self::is_off_market_text( $uri ) ) {
			return true;
		}
		if ( function_exists( 'get_queried_object' ) ) {
			$obj = get_queried_object();
			if ( is_object( $obj ) ) {
				$blob = '';
				foreach ( array( 'post_name', 'post_title', 'slug', 'name' ) as $field ) {
					if ( ! empty( $obj->{$field} ) ) {
						$blob .= ' ' . $obj->{$field};
					}
				}
				if ( $blob !== '' && self::is_off_market_text( $blob ) ) {
					return true;
				}
			}
		}
		return false;
	}

	/**
	 * Keep UAE URLs. noindex / sitemap-drop Egypt, KSA, and other off-market city mills.
	 */
	private static function is_off_market_text( $text ) {
		$text = strtolower( rawurldecode( (string) $text ) );
		if ( $text === '' ) {
			return false;
		}
		$keep = array(
			'abu-dhabi',
			'abudhabi',
			'abu_dhabi',
			'dubai',
			'sharjah',
			'ajman',
			'fujairah',
			'ras-al-khaimah',
			'ras_al_khaimah',
			'umm-al-quwain',
			'al-ain',
			'alain',
			'mussafah',
			'musaffah',
			'united-arab-emirates',
			'/uae',
			'uae-',
			'-uae',
			'emirates',
			'أبوظبي',
			'دبي',
			'الشارقة',
			'عجمان',
			'الفجيرة',
			'رأس الخيمة',
			'أم القيوين',
			'العين',
			'مصفح',
		);
		foreach ( $keep as $needle ) {
			if ( strpos( $text, $needle ) !== false ) {
				return false;
			}
		}
		$drop = array(
			'yanbu',
			'jazan',
			'jizan',
			'jeddah',
			'riyadh',
			'dammam',
			'khobar',
			'makkah',
			'mecca',
			'madinah',
			'medina',
			'taif',
			'tabuk',
			'abha',
			'najran',
			'hail',
			'qassim',
			'buraidah',
			'saudi',
			'-ksa',
			'ksa-',
			'-sa/',
			'-sa-',
			'egypt',
			'cairo',
			'giza',
			'alexandria',
			'mansoura',
			'tanta',
			'zagazig',
			'aswan',
			'luxor',
			'hurghada',
			'sharm',
			'6th-of-october',
			'october-city',
			'qatar',
			'doha',
			'kuwait',
			'bahrain',
			'manama',
			'muscat',
			'amman',
			'jordan',
			'baghdad',
			'morocco',
			'tunis',
			'algeria',
			'lebanon',
			'beirut',
			'المنصورة',
			'الرياض',
			'جدة',
			'ينبع',
			'الطائف',
			'مصر',
			'السعودية',
		);
		foreach ( $drop as $needle ) {
			if ( strpos( $text, $needle ) !== false ) {
				return true;
			}
		}
		return false;
	}

	private static function robots_body() {
		$sitemap = home_url( '/?sitemap=1' );
		return "# Elegant Live Fix " . ELEGANT_LIVE_FIX_VER . "\n"
			. "User-agent: *\n"
			. "Allow: /\n"
			. "Disallow: /page/\n"
			. "Disallow: /?s=\n"
			. "Disallow: /wp-admin/\n"
			. "Allow: /wp-admin/admin-ajax.php\n"
			. "\n"
			. "Sitemap: " . $sitemap . "\n";
	}

	private static function write_robots_file() {
		$path = ABSPATH . 'robots.txt';
		$want = self::robots_body();
		$have = is_readable( $path ) ? (string) file_get_contents( $path ) : '';
		if ( $have === $want ) {
			return;
		}
		if ( $have !== '' && ! is_writable( $path ) && is_file( $path ) ) {
			return;
		}
		@file_put_contents( $path, $want );
	}

	private static function quarantine_spam_paths() {
		$slugs = array( 'contact-us', 'cities' );
		foreach ( $slugs as $slug ) {
			$path = ABSPATH . $slug;
			if ( ! is_dir( $path ) ) {
				continue;
			}
			if ( ! self::path_looks_like_spam( $path ) ) {
				continue;
			}
			self::quarantine_path( $path );
		}
	}

	private static function path_looks_like_spam( $path ) {
		$candidates = array(
			$path . '/index.html',
			$path . '/index.htm',
			$path . '/index.php',
			$path . '/KOKO5000.html',
		);
		$markers = array( 'KOKO5000', 'VIP288', 'kepitinggabut', 'IRI CEPAT MAMPUS', 'Anti Nawala' );
		foreach ( $candidates as $file ) {
			if ( ! is_readable( $file ) ) {
				continue;
			}
			$chunk = (string) file_get_contents( $file, false, null, 0, 8192 );
			foreach ( $markers as $marker ) {
				if ( strpos( $chunk, $marker ) !== false ) {
					return true;
				}
			}
		}
		return false;
	}

	private static function quarantine_path( $path ) {
		$dest_root = WP_CONTENT_DIR . '/elegant-quarantine';
		if ( ! is_dir( $dest_root ) ) {
			wp_mkdir_p( $dest_root );
			@file_put_contents( $dest_root . '/index.php', "<?php\n// Silence is golden.\n" );
		}
		$target = $dest_root . '/' . basename( $path ) . '-' . gmdate( 'YmdHis' );
		if ( @rename( $path, $target ) ) {
			self::purge_all_caches();
			return;
		}
		foreach ( array( 'index.html', 'index.htm', 'index.php' ) as $name ) {
			$file = $path . '/' . $name;
			if ( is_file( $file ) ) {
				@rename( $file, $target . '-' . $name );
			}
		}
	}

	private static function ensure_htaccess_rules() {
		$htaccess = ABSPATH . '.htaccess';
		$have     = is_readable( $htaccess ) ? (string) file_get_contents( $htaccess ) : '';
		if ( strpos( $have, 'BEGIN Elegant Live Fix' ) !== false && strpos( $have, ELEGANT_LIVE_FIX_VER ) !== false ) {
			return;
		}
		if ( ! function_exists( 'insert_with_markers' ) ) {
			$misc = ABSPATH . 'wp-admin/includes/misc.php';
			if ( is_readable( $misc ) ) {
				require_once $misc;
			}
		}
		if ( ! function_exists( 'insert_with_markers' ) ) {
			return;
		}
		$lines = array(
			'# ' . ELEGANT_LIVE_FIX_VER,
			'<IfModule mod_rewrite.c>',
			'RewriteEngine On',
			'RewriteRule ^sitemap_index\\.xml$ /index.php?sitemap=1 [L]',
			'RewriteRule ^([^/]+?)-sitemap([0-9]+)?\\.xml$ /index.php?sitemap=$1&sitemap_n=$2 [L]',
			'RewriteRule ^page/([0-9]+)/?$ / [R=301,L]',
			'</IfModule>',
		);
		$ok = insert_with_markers( $htaccess, 'Elegant Live Fix', $lines );
		if ( $ok && strpos( $have, 'RewriteRule . /index.php' ) === false && strpos( $have, 'BEGIN WordPress' ) === false ) {
			$wp_lines = array(
				'<IfModule mod_rewrite.c>',
				'RewriteEngine On',
				'RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]',
				'RewriteBase /',
				'RewriteRule ^index\\.php$ - [L]',
				'RewriteCond %{REQUEST_FILENAME} !-f',
				'RewriteCond %{REQUEST_FILENAME} !-d',
				'RewriteRule . /index.php [L]',
				'</IfModule>',
			);
			insert_with_markers( $htaccess, 'WordPress', $wp_lines );
		}
		self::purge_all_caches();
	}

	private static function purge_all_caches() {
		if ( function_exists( 'do_action' ) ) {
			do_action( 'litespeed_purge_all' );
			do_action( 'rank_math/sitemap/invalidate_object_type' );
		}
		if ( function_exists( 'wp_cache_flush' ) ) {
			wp_cache_flush();
		}
	}
}

Elegant_Live_Fix::boot();
