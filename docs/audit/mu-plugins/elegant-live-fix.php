<?php
/**
 * Elegant live-site repairs — drop into wp-content/mu-plugins/ or Code Snippets (run everywhere).
 * Version: elegant_live_fix_v2
 *
 * Restores the KAYAN posts homepage, UAE phones, map, schema, robots, and /services/ page.
 */
if ( ! defined( 'ABSPATH' ) ) {
	return;
}

if ( ! defined( 'ELEGANT_LIVE_FIX_VER' ) ) {
	define( 'ELEGANT_LIVE_FIX_VER', 'v2' );
}
if ( ! defined( 'ELEGANT_PHONE_LOCAL' ) ) {
	define( 'ELEGANT_PHONE_LOCAL', '0521300019' );
}
if ( ! defined( 'ELEGANT_PHONE_E164' ) ) {
	define( 'ELEGANT_PHONE_E164', '971521300019' );
}
if ( ! defined( 'ELEGANT_PHONE_DISPLAY' ) ) {
	define( 'ELEGANT_PHONE_DISPLAY', '+971 52 130 0019' );
}

add_filter( 'wp_is_application_passwords_available', '__return_true', 999 );

if ( empty( $_SERVER['PHP_AUTH_USER'] ) && empty( $_SERVER['PHP_AUTH_PW'] ) ) {
	$hdr = '';
	if ( ! empty( $_SERVER['HTTP_AUTHORIZATION'] ) ) {
		$hdr = (string) $_SERVER['HTTP_AUTHORIZATION'];
	} elseif ( ! empty( $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ) ) {
		$hdr = (string) $_SERVER['REDIRECT_HTTP_AUTHORIZATION'];
	}
	if ( $hdr && stripos( $hdr, 'basic ' ) === 0 ) {
		$decoded = base64_decode( substr( $hdr, 6 ) );
		if ( is_string( $decoded ) && strpos( $decoded, ':' ) !== false ) {
			list( $_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW'] ) = explode( ':', $decoded, 2 );
		}
	}
}

if ( ! function_exists( 'elegant_opt_set' ) ) {
	function elegant_opt_set( $key, $value ) {
		if ( function_exists( 'yc_update_option' ) ) {
			yc_update_option( $key, $value );
		} else {
			update_option( $key, $value );
		}
	}
}

add_action(
	'after_setup_theme',
	function () {
		add_theme_support( 'title-tag' );
		add_theme_support( 'html5', array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script' ) );
	},
	20
);

add_filter( 'the_generator', '__return_empty_string' );
remove_action( 'wp_head', 'wp_generator' );

add_filter(
	'rest_endpoints',
	function ( $endpoints ) {
		unset( $endpoints['/wp/v2/users'] );
		unset( $endpoints['/wp/v2/users/(?P<id>[\\d]+)'] );
		return $endpoints;
	}
);

add_filter(
	'robots_txt',
	function ( $output, $public ) {
		if ( ! $public ) {
			return $output;
		}
		$lines   = preg_split( '/\R/', (string) $output );
		$kept    = array();
		$seen_sm = false;
		foreach ( $lines as $line ) {
			$trim = trim( $line );
			if ( preg_match( '/^sitemap:\s*https?:\/\/[^\s]+\/sitemap-\d+\.xml/i', $trim ) ) {
				continue;
			}
			if ( preg_match( '/^sitemap:/i', $trim ) ) {
				if ( $seen_sm ) {
					continue;
				}
				$kept[]  = 'Sitemap: https://elegantswimmingpools.com/sitemap.xml';
				$seen_sm = true;
				continue;
			}
			$kept[] = $line;
		}
		if ( ! $seen_sm ) {
			$kept[] = 'Sitemap: https://elegantswimmingpools.com/sitemap.xml';
		}
		return implode( "\n", $kept ) . "\n";
	},
	99,
	2
);

if ( ! function_exists( 'elegant_is_foreign_market_post' ) ) {
	function elegant_is_foreign_market_post( $post_id = 0 ) {
		$slug  = strtolower( (string) get_post_field( 'post_name', $post_id ? $post_id : get_the_ID() ) );
		$title = (string) get_the_title( $post_id ? $post_id : get_the_ID() );
		$foreign = array( 'mansoura', 'egypt', 'cairo', 'tanta', 'giza', 'taif', 'riyadh', 'jeddah', 'dammam', 'sharm', 'hurghada', 'nasr', 'port-said', 'doha', 'qatar', 'kuwait', 'bahrain', 'oman' );
		$ar      = array( 'المنصورة', 'طنطا', 'القاهرة', 'مدينة نصر', 'الطائف', 'الرياض', 'جدة', 'الغردقة', 'شرم الشيخ', 'بورسعيد', 'مصر' );
		foreach ( $foreign as $k ) {
			if ( strpos( $slug, $k ) !== false ) {
				return true;
			}
		}
		foreach ( $ar as $k ) {
			if ( strpos( $title, $k ) !== false ) {
				return true;
			}
		}
		return false;
	}
}

$elegant_robots_cb = function ( $robots ) {
	if ( is_front_page() ) {
		unset( $robots['noindex'] );
		$robots['index']  = true;
		$robots['follow'] = true;
		return $robots;
	}
	if ( is_paged() && ( is_home() || is_front_page() ) ) {
		$robots['noindex'] = true;
		unset( $robots['index'] );
		return $robots;
	}
	if ( is_singular( 'post' ) && elegant_is_foreign_market_post() ) {
		$robots['noindex'] = true;
		unset( $robots['index'] );
	}
	return $robots;
};
add_filter( 'wp_robots', $elegant_robots_cb, 99 );
add_filter( 'rank_math/frontend/robots', $elegant_robots_cb, 99 );

add_filter(
	'redirect_canonical',
	function ( $redirect_url, $requested_url ) {
		if ( is_404() ) {
			return false;
		}
		return $redirect_url;
	},
	10,
	2
);

add_action(
	'template_redirect',
	function () {
		if ( is_404() ) {
			status_header( 404 );
			nocache_headers();
		}
	},
	0
);

add_action(
	'wp_head',
	function () {
		if ( is_front_page() || is_home() ) {
			echo '<meta property="og:title" content="اليجانت للمسابح | إنشاء وصيانة وتنظيف حمامات السباحة في الإمارات" />' . "\n";
			echo '<meta property="og:locale" content="ar_AE" />' . "\n";
		}
	},
	5
);

add_action(
	'wp_enqueue_scripts',
	function () {
		$css = '.kayan-inner-page header .logo img,.kayan-inner-page .logo img{filter:none;opacity:1}'
			. '.kayan-inner-page header .logo{color:#0A1F4E!important}'
			. '.kayan-inner-page .article-title,.kayan-inner-page h1{position:relative;z-index:2}';
		wp_register_style( 'elegant-live-fix', false, array(), ELEGANT_LIVE_FIX_VER );
		wp_enqueue_style( 'elegant-live-fix' );
		wp_add_inline_style( 'elegant-live-fix', $css );
		$js = '(function(){function r(s){return String(s||"").replace(/201556644443/g,"971521300019").replace(/201151481000/g,"971521300019").replace(/\\+20\\s*155\\s*664\\s*4443/g,"+971 52 130 0019").replace(/tel:\\+?201556644443/g,"tel:0521300019");}document.addEventListener("click",function(e){var a=e.target.closest("a");if(!a)return;var h=a.getAttribute("href");if(!h)return;var n=r(h);if(n!==h)a.setAttribute("href",n);},true);document.addEventListener("DOMContentLoaded",function(){document.querySelectorAll("a[href]").forEach(function(a){var n=r(a.getAttribute("href"));if(n)a.setAttribute("href",n);});});})();';
		wp_register_script( 'elegant-live-fix', false, array(), ELEGANT_LIVE_FIX_VER, true );
		wp_enqueue_script( 'elegant-live-fix' );
		wp_add_inline_script( 'elegant-live-fix', $js );
	},
	99
);

add_filter(
	'register_post_type_args',
	function ( $args, $post_type ) {
		if ( $post_type === 'services' ) {
			$args['has_archive'] = false;
			$args['rewrite']     = array(
				'slug'       => 'pool-service',
				'with_front' => false,
			);
		}
		return $args;
	},
	99,
	2
);

add_action(
	'init',
	function () {
		if ( get_option( 'show_on_front' ) === 'page' ) {
			update_option( 'show_on_front', 'posts' );
			update_option( 'page_on_front', 0 );
			if ( ! (int) get_option( 'page_for_posts' ) ) {
				update_option( 'page_for_posts', 170 );
			}
		}
	},
	1
);

add_action(
	'init',
	function () {
		if ( get_option( 'elegant_live_fix_ver' ) === ELEGANT_LIVE_FIX_VER ) {
			return;
		}

		$phone_local = ELEGANT_PHONE_LOCAL;
		$phone_e164  = ELEGANT_PHONE_E164;
		$address     = 'مصفح 23، شارع 15، أبوظبي، الإمارات العربية المتحدة';
		$map         = '<iframe src="https://maps.google.com/maps?q=Mussafah%20ICAD%20Abu%20Dhabi&hl=ar&z=14&output=embed" width="100%" height="320" style="border:0;" allowfullscreen loading="lazy" referrerpolicy="no-referrer-when-downgrade" title="مقر اليجانت للمسابح في مصفح أبوظبي"></iframe>';

		$options = array(
			'phonenumber'                    => $phone_local,
			'whatsapp_number'                => $phone_e164,
			'contact_number'                 => $phone_e164,
			'company__adress'                => $address,
			'company__map_title'             => 'مقرنا الرئيسي — مصفح، أبوظبي',
			'company__map_code'              => $map,
			'footer__company__adress_url'    => 'https://maps.google.com/?q=Mussafah+ICAD+Abu+Dhabi',
			'sitename'                       => 'اليجانت للمسابح',
			'seo__site_name'                 => ' | اليجانت للمسابح',
			'home__title'                    => 'اليجانت للمسابح | إنشاء وصيانة وتنظيف حمامات السباحة في الإمارات',
			'default__title'                 => 'اليجانت للمسابح',
			'seo__title_showsin'             => 'wordpress',
			'hide__theme_seo'                => '1',
			'hide__description_show'         => '1',
			'kayan_homepage_brand_first'     => 'Elegant',
			'kayan_homepage_brand_second'    => 'Pools',
			'kayan_hp_hero_title'            => 'اليجانت للمسابح — <em>إنشاء وصيانة وتنظيف المسابح</em> في الإمارات',
			'kayan_hp_hero_title_en'         => 'Elegant Swimming Pools — <em>build, maintain and clean pools</em> across the UAE',
			'kayan_hp_hero_subtitle'         => 'تصميم وإنشاء وصيانة وتنظيف حمامات السباحة في أبوظبي ودبي وجميع الإمارات — فريق متخصص، مواد معتمدة، وضمان مكتوب يصل إلى 10 سنوات.',
			'kayan_hp_hero_subtitle_en'      => 'Pool design, construction, maintenance and cleaning across Abu Dhabi, Dubai and the UAE — specialist crews and a written warranty up to 10 years.',
			'kayan_homepage_dashboard_title' => 'لوحة خدمات اليجانت للمسابح',
			'kayan_hp_hero_warranty_title'   => 'ضمان مكتوب يصل إلى 10 سنوات',
			'kayan_hp_hero_warranty_sub'     => 'على أعمال العزل المائي وإنشاء وصيانة المسابح المعتمدة',
			'kayan_hp_services_title'        => 'خدمات المسابح <span>المتكاملة</span>',
			'kayan_hp_services_subtitle'     => 'من التصميم والإنشاء إلى الصيانة الدورية والتنظيف ومعالجة المياه.',
			'kayan_hp_services_tag'          => 'خدماتنا',
			'kayan_homepage_why_heading'     => 'لماذا يختار العملاء <span>اليجانت للمسابح؟</span>',
			'kayan_homepage_compare_heading' => 'لماذا اليجانت <span>للمسابح؟</span>',
			'kayan_homepage_areas_intro'     => 'فريق محلي في أبوظبي ودبي والعين وجميع إمارات الدولة.',
			'kayan_homepage_footer_tagline'  => 'اليجانت للمسابح — إنشاء وصيانة وتنظيف حمامات السباحة في الإمارات.',
			'kayan_homepage_copyright'       => '© {{year}} اليجانت للمسابح. جميع الحقوق محفوظة.',
			'kayan_hp_footer_copyright'      => '© {{year}} اليجانت للمسابح. جميع الحقوق محفوظة.',
			'footer__content'                => 'شركة اليجانت للمسابح متخصصة في تصميم وإنشاء وصيانة وتنظيف حمامات السباحة في أبوظبي ودبي وجميع الإمارات. نلتزم بمعايير السلامة والجودة ونقدّم ضماناً مكتوباً على الأعمال المعتمدة.',
			'Copyrights'                     => 'حقوق النشر {%YEAR%} © جميع الحقوق محفوظة لصالح "شركة اليجانت للمسابح"',
			'sitename__schema'               => 'اليجانت للمسابح',
			'default_ping_status'            => 'closed',
			'kayan_dni_enabled'              => '',
			'kayan_show_call_buttons'        => 'on',
			'hide__floating__call'           => '',
			'hide__floating__whatsapp'       => '',
			'show_on_front'                  => 'posts',
			'page_on_front'                  => 0,
			'page_for_posts'                 => 170,
			'timezone_string'                => 'Asia/Dubai',
			'blogdescription'                => 'شركة اليجانت للمسابح: تصميم وإنشاء وصيانة وتنظيف حمامات السباحة في أبوظبي ودبي وجميع الإمارات. واتساب: ' . ELEGANT_PHONE_DISPLAY,
		);

		foreach ( $options as $key => $value ) {
			elegant_opt_set( $key, $value );
		}

		foreach ( array( 'ae', 'sa', 'kw', 'qa', 'bh', 'om', 'eg' ) as $code ) {
			elegant_opt_set( 'kayan_country_' . $code . '_whatsapp', $phone_e164 );
			elegant_opt_set( 'kayan_country_' . $code . '_phone', $phone_local );
		}

		$cities = get_option( 'city_groub' );
		if ( function_exists( 'yc_get_option' ) ) {
			$cities = yc_get_option( 'city_groub', $cities );
		}
		if ( is_array( $cities ) ) {
			foreach ( $cities as $i => $row ) {
				if ( is_array( $row ) ) {
					$cities[ $i ]['number_city'] = $phone_e164;
				}
			}
			elegant_opt_set( 'city_groub', $cities );
		}

		elegant_opt_set(
			'kayan_hp_hero_chips',
			array(
				array( 'icon' => 'fas fa-star', 'text' => 'خبرة في مسابح الإمارات' ),
				array( 'icon' => 'fas fa-clock', 'text' => 'طوارئ 24/7' ),
				array( 'icon' => 'fas fa-shield-halved', 'text' => 'ضمان مكتوب حتى 10 سنوات' ),
				array( 'icon' => 'fas fa-map-location-dot', 'text' => 'تغطية جميع الإمارات' ),
			)
		);
		elegant_opt_set(
			'kayan_hp_stats_items',
			array(
				array( 'label' => 'عميل', 'count' => '15000', 'suffix' => '+', 'dec' => '0' ),
				array( 'label' => 'مشروع', 'count' => '8000', 'suffix' => '+', 'dec' => '0' ),
				array( 'label' => 'تقييم', 'count' => '4.9', 'suffix' => '', 'dec' => '1' ),
			)
		);
		elegant_opt_set(
			'kayan_hp_trust_items',
			array(
				array( 'icon' => 'fas fa-swimmer', 'text' => 'إنشاء وتركيب مسابح' ),
				array( 'icon' => 'fas fa-tools', 'text' => 'صيانة دورية وطارئة' ),
				array( 'icon' => 'fas fa-droplet', 'text' => 'تنظيف ومعالجة مياه' ),
				array( 'icon' => 'fas fa-house-chimney', 'text' => 'تغطية أبوظبي ودبي والعين' ),
			)
		);

		$services_url = home_url( '/services/' );
		elegant_opt_set(
			'kayan_hp_services_cards',
			array(
				array(
					'icon'    => 'fas fa-water',
					'title'   => 'إنشاء وتركيب المسابح',
					'desc'    => 'تصميم وتنفيذ مسابح أوفر فلو وسكيمر للقصور والفلل والمنشآت.',
					'url'     => $services_url,
					'bullets' => "مخطط هندسي واضح\nمواد عزل معتمدة\nتسليم مع تجربة تشغيل",
				),
				array(
					'icon'    => 'fas fa-screwdriver-wrench',
					'title'   => 'صيانة المسابح',
					'desc'    => 'صيانة مضخات وفلاتر وإضاءة وكشف أعطال دورية وطارئة.',
					'url'     => $services_url,
					'bullets' => "عقد صيانة شهري\nقطع غيار أصلية\nاستجابة سريعة",
				),
				array(
					'icon'    => 'fas fa-soap',
					'title'   => 'تنظيف وتعقيم المسابح',
					'desc'    => 'تنظيف قاع وجدران، موازنة كيماويات، وإزالة الطحالب والرواسب.',
					'url'     => $services_url,
					'bullets' => "مياه صافية وآمنة\nجدولة أسبوعية\nتقارير بعد الزيارة",
				),
				array(
					'icon'    => 'fas fa-layer-group',
					'title'   => 'عزل وتبطين الأحواض',
					'desc'    => 'عزل مائي للمسابح والخزانات المرتبطة لمنع التسريب والتآكل.',
					'url'     => $services_url,
					'bullets' => "عزل إيبوكسي وفيبر\nضمان مكتوب\nاختبار تسريب",
				),
				array(
					'icon'    => 'fas fa-search-location',
					'title'   => 'كشف وإصلاح تسريب المسابح',
					'desc'    => 'كشف تسريب الحوض والأنابيب بأجهزة حديثة بدون تكسير عشوائي.',
					'url'     => $services_url,
					'bullets' => "كشف صوتي وحراري\nإصلاح موضعي\nإعادة تشغيل النظام",
				),
				array(
					'icon'    => 'fas fa-lightbulb',
					'title'   => 'إكسسوارات وإضاءة وشلالات',
					'desc'    => 'تركيب إضاءة LED وشلالات ونوافير وأنظمة تعقيم.',
					'url'     => $services_url,
					'bullets' => "إضاءة آمنة\nشلالات ديكورية\nأنظمة ملح وتعقيم",
				),
			)
		);

		elegant_opt_set(
			'YourColor_Schema_business',
			array(
				'hide_schema_business' => '',
				'Business_Name'        => 'اليجانت للمسابح',
				'description'          => 'شركة اليجانت للمسابح: تصميم وإنشاء وصيانة وتنظيف حمامات السباحة في أبوظبي ودبي وجميع الإمارات. تواصل: ' . $phone_local,
				'Street_Address'       => 'مصفح 23، شارع 15',
				'Country'              => 'AE',
				'City'                 => 'أبوظبي',
				'State'                => 'أبوظبي',
				'Postal_Code'          => '',
				'telephone'            => '+' . $phone_e164,
				'openingHours'         => 'Mo-Su 00:00-23:59',
				'Price_Range'          => 'AED',
				'ratingValue'          => '',
				'Rating_Count'         => '',
			)
		);

		$dash_ids = array( 177, 178, 179, 180, 181, 182 );
		$order    = 1;
		foreach ( $dash_ids as $pid ) {
			if ( get_post( $pid ) ) {
				wp_update_post(
					array(
						'ID'         => $pid,
						'menu_order' => $order,
					)
				);
				$order++;
			}
		}

		if ( get_post( 7411 ) ) {
			wp_update_post(
				array(
					'ID'          => 7411,
					'post_status' => 'draft',
				)
			);
		}

		global $wpdb;
		$numbers_table = $wpdb->prefix . 'kayan_numbers';
		if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $numbers_table ) ) === $numbers_table ) {
			$wpdb->query(
				$wpdb->prepare(
					"UPDATE {$numbers_table} SET phone = %s, wa_number = %s, label = %s WHERE active = 1",
					$phone_local,
					$phone_e164,
					'اليجانت للمسابح — الإمارات'
				)
			);
			if ( ! (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$numbers_table} WHERE active = 1" ) ) {
				$wpdb->insert(
					$numbers_table,
					array(
						'label'     => 'اليجانت للمسابح — الإمارات',
						'phone'     => $phone_local,
						'wa_number' => $phone_e164,
						'active'    => 1,
					)
				);
			}
		}

		foreach ( array( '201556644443', '201151481000' ) as $old ) {
			$wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->posts} SET post_content = REPLACE(post_content, %s, %s)", $old, $phone_e164 ) );
			$wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->postmeta} SET meta_value = REPLACE(meta_value, %s, %s)", $old, $phone_e164 ) );
			$wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->options} SET option_value = REPLACE(option_value, %s, %s) WHERE option_name NOT LIKE %s", $old, $phone_e164, '_transient%' ) );
		}
		$wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->posts} SET post_content = REPLACE(post_content, %s, %s)", '+201556644443', '+' . $phone_e164 ) );
		$wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->posts} SET post_content = REPLACE(post_content, %s, %s)", '+20 155 664 4443', ELEGANT_PHONE_DISPLAY ) );

		if ( ! function_exists( 'activate_plugin' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}
		if ( file_exists( WP_PLUGIN_DIR . '/wpforms-lite/wpforms.php' ) && ! is_plugin_active( 'wpforms-lite/wpforms.php' ) ) {
			activate_plugin( 'wpforms-lite/wpforms.php' );
		}
		if ( file_exists( WP_PLUGIN_DIR . '/ameliabooking/ameliabooking.php' ) && is_plugin_active( 'ameliabooking/ameliabooking.php' ) ) {
			deactivate_plugins( 'ameliabooking/ameliabooking.php' );
		}

		flush_rewrite_rules( false );
		if ( function_exists( 'do_action' ) ) {
			do_action( 'litespeed_purge_all' );
		}

		update_option( 'elegant_live_fix_ver', ELEGANT_LIVE_FIX_VER );
		update_option( 'elegant_live_fix_v1', 'done' );
	},
	20
);
