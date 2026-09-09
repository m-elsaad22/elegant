<?php
/**
 * One-time + persistent helpers: unified WhatsApp 201556644443, pool homepage copy.
 * Flag: elegant_hp_fix_ver
 */
if ( ! defined( 'ABSPATH' ) ) {
	return;
}

const ELEGANT_FIX_VER = '20260909c';
const ELEGANT_WA      = '201556644443';

function elegant_opt_set( $key, $value ) {
	if ( function_exists( 'yc_update_option' ) ) {
		yc_update_option( $key, $value );
	}
	update_option( $key, $value );
}

function elegant_apply_homepage_fix() {
	if ( get_option( 'elegant_hp_fix_ver' ) === ELEGANT_FIX_VER ) {
		return;
	}

	$wa = ELEGANT_WA;

	elegant_opt_set( 'whatsapp_number', $wa );
	elegant_opt_set( 'phonenumber', $wa );
	elegant_opt_set( 'contact_number', $wa );
	elegant_opt_set( 'kayan_show_call_buttons', '' );
	elegant_opt_set( 'hide__floating__call', 'on' );
	elegant_opt_set( 'hide__floating__whatsapp', '' );

	foreach ( array( 'ae', 'sa', 'kw', 'qa', 'bh', 'om', 'eg' ) as $code ) {
		elegant_opt_set( 'kayan_country_' . $code . '_whatsapp', $wa );
		elegant_opt_set( 'kayan_country_' . $code . '_phone', $wa );
	}

	$cities = get_option( 'city_groub' );
	if ( function_exists( 'yc_get_option' ) ) {
		$cities = yc_get_option( 'city_groub', $cities );
	}
	if ( is_array( $cities ) ) {
		foreach ( $cities as $i => $row ) {
			if ( is_array( $row ) ) {
				$cities[ $i ]['number_city'] = $wa;
			}
		}
		elegant_opt_set( 'city_groub', $cities );
	}

	elegant_opt_set(
		'kayan_hp_hero_title',
		'شركة اليجانت للمسابح — إنشاء وصيانة وتنظيف <em>حمامات السباحة</em>'
	);
	elegant_opt_set(
		'kayan_hp_hero_subtitle',
		'تصميم وتركيب وصيانة وتنظيف المسابح باحترافية في الإمارات والدول العربية. فريق متخصص، مواد ومعدات معتمدة، وضمان مكتوب على أعمال الإنشاء والصيانة.'
	);
	elegant_opt_set( 'kayan_homepage_dashboard_title', 'خدمات مسابح اليجانت' );
	elegant_opt_set( 'kayan_homepage_brand_first', 'اليجانت' );
	elegant_opt_set( 'kayan_homepage_brand_second', 'للمسابح' );
	elegant_opt_set( 'sitename', 'اليجانت للمسابح' );

	elegant_opt_set(
		'kayan_hp_hero_chips',
		array(
			array( 'icon' => 'fas fa-swimming-pool', 'text' => 'إنشاء وتركيب مسابح' ),
			array( 'icon' => 'fas fa-broom', 'text' => 'تنظيف وصيانة دورية' ),
			array( 'icon' => 'fas fa-map-location-dot', 'text' => 'الإمارات والدول العربية' ),
			array( 'icon' => 'fab fa-whatsapp', 'text' => 'تواصل واتساب مباشر' ),
			array( 'icon' => 'fas fa-shield-halved', 'text' => 'ضمان مكتوب على الأعمال' ),
			array( 'icon' => 'fas fa-clock', 'text' => 'خدمة على مدار الأسبوع' ),
		)
	);

	elegant_opt_set(
		'kayan_hp_stats_items',
		array(
			array( 'icon' => 'fas fa-water-ladder', 'count' => '500', 'suffix' => '+', 'label' => 'مشروع', 'sublabel' => 'مسابح منفذة' ),
			array( 'icon' => 'fas fa-users', 'count' => '1200', 'suffix' => '+', 'label' => 'عميل', 'sublabel' => 'عميل راضٍ' ),
			array( 'icon' => 'fas fa-star', 'count' => '4.9', 'dec' => '1', 'label' => 'تقييم', 'sublabel' => 'رضا العملاء' ),
		)
	);

	elegant_opt_set(
		'kayan_hp_trust_badges',
		array(
			array( 'icon' => 'fas fa-circle-check', 'text' => 'فريق متخصص في المسابح' ),
			array( 'icon' => 'fas fa-user-shield', 'text' => 'تنفيذ بمعايير السلامة' ),
		)
	);

	elegant_opt_set(
		'kayan_hp_trust_items',
		array(
			array( 'icon' => 'fas fa-swimming-pool', 'text' => 'إنشاء مسابح' ),
			array( 'icon' => 'fas fa-screwdriver-wrench', 'text' => 'صيانة متخصصة' ),
			array( 'icon' => 'fas fa-soap', 'text' => 'تنظيف وتعقيم' ),
			array( 'icon' => 'fab fa-whatsapp', 'text' => 'واتساب موحّد لكل الدول' ),
		)
	);

	elegant_opt_set( 'kayan_hp_services_tag', 'خدماتنا' );
	elegant_opt_set( 'kayan_hp_services_title', 'خدمات <span>المسابح</span> المتكاملة' );
	elegant_opt_set(
		'kayan_hp_services_subtitle',
		'من التصميم والإنشاء حتى التنظيف والصيانة الدورية — حلول مسابح كاملة للمنازل والمنتجعات.'
	);
	elegant_opt_set(
		'kayan_hp_services_cards',
		array(
			array(
				'icon'    => 'fas fa-swimming-pool',
				'title'   => 'إنشاء وتركيب مسابح',
				'desc'    => 'تصميم وتنفيذ مسابح أوفر فلو وسكيمر داخلية وخارجية بمواد معتمدة.',
				'url'     => home_url( '/construction-installation-swimming-pools/' ),
				'bullets' => "مسابح خرسانية وفايبر\nأوفر فلو وسكيمر\nتشطيب فاخر",
			),
			array(
				'icon'    => 'fas fa-soap',
				'title'   => 'تنظيف وتعقيم المسابح',
				'desc'    => 'تنظيف شامل وتعقيم دوري بأفضل المواد والمعدات للحفاظ على نقاء المياه.',
				'url'     => home_url( '/swimming-pool-cleaning/' ),
				'bullets' => "غسيل وتعقيم\nضبط كيماويات المياه\nعقود صيانة شهرية",
			),
			array(
				'icon'    => 'fas fa-screwdriver-wrench',
				'title'   => 'صيانة وترميم المسابح',
				'desc'    => 'إصلاح الأعطال، ترميم المسابح القديمة، وصيانة المضخات والفلاتر.',
				'url'     => home_url( '/swimming-pool-maintenance/' ),
				'bullets' => "مضخات وفلاتر\nترميم بلاط وتسربات\nتشغيل دوري",
			),
			array(
				'icon'    => 'fas fa-pen-ruler',
				'title'   => 'تصميم مسابح فاخرة',
				'desc'    => 'تصاميم تناسب المساحة والذوق، من المسابح العائلية إلى الفيلات والمنتجعات.',
				'url'     => 'https://wa.me/' . $wa,
				'bullets' => "معاينة مجانية\nمخطط وتنفيذ\nإضاءة وشلالات",
			),
			array(
				'icon'    => 'fas fa-gears',
				'title'   => 'معدات وأنظمة المسابح',
				'desc'    => 'توريد وتركيب أنظمة الفلترة والتدفئة والإضاءة والتحكم.',
				'url'     => 'https://wa.me/' . $wa,
				'bullets' => "فلاتر ومضخات\nتدفئة وإضاءة\nأغطية مسابح",
			),
			array(
				'icon'    => 'fas fa-calendar-check',
				'title'   => 'عقود صيانة سنوية',
				'desc'    => 'زيارات دورية وفحص شامل لضمان جاهزية المسبح طوال العام.',
				'url'     => 'https://wa.me/' . $wa,
				'bullets' => "جدول زيارات\nتقارير حالة\nدعم عبر واتساب",
			),
		)
	);

	elegant_opt_set( 'kayan_homepage_why_heading', 'لماذا يختار العملاء <span>اليجانت للمسابح</span>؟' );
	elegant_opt_set(
		'kayan_hp_why_steps',
		array(
			array( 'num' => '1', 'title' => 'تواصل عبر واتساب ومعاينة', 'desc' => 'راسلنا على الرقم الموحّد ونعاين الموقع.' ),
			array( 'num' => '2', 'title' => 'عرض سعر شفاف', 'desc' => 'تكلفة واضحة لأعمال الإنشاء أو الصيانة أو التنظيف.' ),
			array( 'num' => '3', 'title' => 'تنفيذ متخصص', 'desc' => 'فريق مسابح بمواد ومعدات معتمدة.' ),
			array( 'num' => '4', 'title' => 'ضمان ومتابعة', 'desc' => 'ضمان مكتوب ودعم بعد التسليم عبر واتساب.' ),
		)
	);
	elegant_opt_set(
		'kayan_hp_why_features',
		array(
			array( 'icon' => 'fas fa-swimming-pool', 'title' => 'تخصص مسابح', 'desc' => 'تركيز كامل على الإنشاء والصيانة والتنظيف.' ),
			array( 'icon' => 'fas fa-user-shield', 'title' => 'فريق متخصص', 'desc' => 'فنيون مدربون على أنظمة المسابح.' ),
			array( 'icon' => 'fas fa-bolt', 'title' => 'استجابة سريعة', 'desc' => 'تواصل واتساب مباشر من أي دولة.' ),
			array( 'icon' => 'fas fa-file-contract', 'title' => 'ضمان مكتوب', 'desc' => 'ضمان موثق على أعمال الإنشاء والصيانة.' ),
			array( 'icon' => 'fas fa-tags', 'title' => 'تسعير واضح', 'desc' => 'بدون رسوم خفية بعد المعاينة.' ),
			array( 'icon' => 'fas fa-globe', 'title' => 'تغطية عربية', 'desc' => 'رقم واتساب واحد لكل الدول.' ),
		)
	);

	elegant_opt_set( 'kayan_hp_blog_disable', 'on' );
	elegant_opt_set( 'kayan_hp_team_disable', 'on' );
	elegant_opt_set( 'kayan_hp_compare_disable', 'on' );
	elegant_opt_set( 'kayan_hp_finder_disable', 'on' );

	$footer = 'شركة اليجانت للمسابح متخصصة في تصميم وإنشاء وصيانة وتنظيف حمامات السباحة. تواصل معنا عبر واتساب على الرقم الموحّد لجميع الدول.';
	elegant_opt_set( 'kayan_hp_footer_tagline', $footer );
	elegant_opt_set( 'kayan_homepage_footer_tagline', $footer );
	elegant_opt_set( 'footer__content', $footer );
	elegant_opt_set( 'kayan_homepage_copyright', '© {{year}} شركة اليجانت للمسابح. جميع الحقوق محفوظة.' );
	elegant_opt_set( 'kayan_hp_footer_copyright', '© {{year}} شركة اليجانت للمسابح. جميع الحقوق محفوظة.' );

	update_option( 'blogname', 'Elegant Swimming Pools - اليجانت للمسابح' );
	update_option(
		'blogdescription',
		'شركة اليجانت للمسابح: تصميم وإنشاء وصيانة وتنظيف حمامات السباحة. تواصل واتساب: +20 155 664 4443'
	);

	$rm = get_option( 'rank-math-options-titles', array() );
	if ( is_array( $rm ) ) {
		$rm['knowledgegraph_type']      = 'company';
		$rm['website_name']             = 'اليجانت للمسابح';
		$rm['knowledgegraph_name']      = 'شركة اليجانت للمسابح';
		$rm['website_alternate_name']   = 'Elegant Swimming Pools';
		$rm['phone']                    = '+' . $wa;
		$rm['pt_post_title']            = '%title% %sep% اليجانت للمسابح';
		$rm['pt_post_default_snippet_name'] = '%title%';
		$rm['pt_page_title']            = '%title% %sep% اليجانت للمسابح';
		$rm['pt_page_default_snippet_name'] = '%title%';
		update_option( 'rank-math-options-titles', $rm, false );
	}

	global $wpdb;
	$like_keys = $wpdb->esc_like( 'widget_post_meta' );
	$rows      = $wpdb->get_results( "SELECT post_id, meta_value FROM {$wpdb->postmeta} WHERE meta_key = 'widget_post_meta'" );
	if ( $rows ) {
		foreach ( $rows as $row ) {
			$data = maybe_unserialize( $row->meta_value );
			$json = wp_json_encode( $data );
			if ( ! is_string( $json ) ) {
				continue;
			}
			$orig = $json;
			$json = str_replace(
				array(
					'+971508715513',
					'971508715513',
					'+971522881997',
					'971522881997',
					'0522881997',
					'522881997',
					'+201094448644',
					'01094448644',
					'201094448644',
				),
				$wa,
				$json
			);
			if ( $json !== $orig ) {
				$decoded = json_decode( $json, true );
				if ( is_array( $decoded ) ) {
					update_post_meta( (int) $row->post_id, 'widget_post_meta', $decoded );
				}
			}
			if ( is_array( $data ) ) {
				$wid = isset( $data['widget_id'] ) ? $data['widget_id'] : '';
				if ( $wid === 'blog_v1' ) {
					$data['hide_section__switch']        = 'on';
					$data['mobile_hide_section__switch'] = 'on';
					update_post_meta( (int) $row->post_id, 'widget_post_meta', $data );
				}
			}
		}
	}

	$home_widgets = get_option( 'widgets_home__meta' );
	if ( function_exists( 'yc_get_option' ) ) {
		$home_widgets = yc_get_option( 'widgets_home__meta', $home_widgets );
	}
	if ( is_array( $home_widgets ) ) {
		foreach ( $home_widgets as $i => $w ) {
			if ( ! is_array( $w ) ) {
				continue;
			}
			if ( ( isset( $w['widget_id'] ) && $w['widget_id'] === 'blog_v1' ) || ( isset( $w['widget_post__id'] ) && is_array( $rows ) ) ) {
				if ( isset( $w['widget_id'] ) && $w['widget_id'] === 'blog_v1' && ! empty( $w['widget_post__id'] ) ) {
					$meta = get_post_meta( (int) $w['widget_post__id'], 'widget_post_meta', true );
					if ( ! is_array( $meta ) ) {
						$meta = array();
					}
					$meta['hide_section__switch']        = 'on';
					$meta['mobile_hide_section__switch'] = 'on';
					update_post_meta( (int) $w['widget_post__id'], 'widget_post_meta', $meta );
				}
			}
		}
	}

	if ( function_exists( 'speedycache_delete_cache' ) ) {
		speedycache_delete_cache( true );
	}
	if ( class_exists( '\\SpeedyCache\\Delete' ) && method_exists( '\\SpeedyCache\\Delete', 'delete' ) ) {
		\SpeedyCache\Delete::delete();
	}
	do_action( 'litespeed_purge_all' );

	update_option( 'elegant_hp_fix_ver', ELEGANT_FIX_VER );
}

add_action( 'init', 'elegant_apply_homepage_fix', 30 );

add_action(
	'template_redirect',
	function () {
		if ( is_admin() || wp_doing_ajax() || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ) {
			return;
		}
		ob_start( 'elegant_filter_html_output' );
	},
	0
);

function elegant_filter_html_output( $html ) {
	if ( ! is_string( $html ) || $html === '' ) {
		return $html;
	}
	$wa = ELEGANT_WA;
	$html = str_replace(
		array( '0522881997', '+971522881997', '971522881997' ),
		$wa,
		$html
	);
	if ( ! is_front_page() && ! is_home() ) {
		return $html;
	}
	$html = preg_replace(
		'/<h1>.*?الخدمات المنزلية المتكاملة.*?<\/h1>/su',
		'<h1>شركة اليجانت للمسابح — إنشاء وصيانة وتنظيف <em>حمامات السباحة</em></h1>',
		$html,
		1
	);
	$html = preg_replace(
		'/<p class="sub">.*?عزل الأسطح.*?<\/p>/su',
		'<p class="sub">تصميم وتركيب وصيانة وتنظيف المسابح باحترافية في الإمارات والدول العربية. فريق متخصص، مواد معتمدة، وتواصل واتساب موحّد لجميع الدول.</p>',
		$html,
		1
	);
	$mini = '<div class="mini"><i class="fas fa-swimming-pool"></i><span>إنشاء مسابح</span></div>'
		. '<div class="mini"><i class="fas fa-soap"></i><span>تنظيف مسابح</span></div>'
		. '<div class="mini"><i class="fas fa-screwdriver-wrench"></i><span>صيانة مسابح</span></div>'
		. '<div class="mini"><i class="fas fa-hammer"></i><span>ترميم مسابح</span></div>'
		. '<div class="mini"><i class="fas fa-gears"></i><span>معدات وفلاتر</span></div>'
		. '<div class="mini"><i class="fas fa-calendar-check"></i><span>تشغيل دوري</span></div>';
	$html = preg_replace(
		'/<div class="dash-mini">[\s\S]*?<div class="dash-stats">/',
		'<div class="dash-mini">' . $mini . '</div><div class="dash-stats">',
		$html,
		1
	);
	if ( strpos( $html, '<title>' ) === false && strpos( $html, '<title ' ) === false ) {
		$html = preg_replace(
			'/<meta charset="utf-8">/i',
			'<meta charset="utf-8"><title>اليجانت للمسابح — إنشاء وصيانة وتنظيف المسابح</title>',
			$html,
			1
		);
	}
	if ( strpos( $html, 'property="og:title"' ) === false ) {
		$html = str_replace(
			'<meta property="og:type" content="website" />',
			'<meta property="og:type" content="website" />' . "\n" . '<meta property="og:title" content="اليجانت للمسابح — إنشاء وصيانة وتنظيف المسابح" />',
			$html
		);
	}
	$html = str_replace(
		array(
			'على أعمال العزل المائي والحراري',
			'معتمد من بلدية دبي',
			'"call_show":true',
		),
		array(
			'على أعمال إنشاء وصيانة المسابح',
			'فريق متخصص في المسابح',
			'"call_show":false',
		),
		$html
	);
	$html = preg_replace(
		'/<span class="chip"><i class="fas fa-star star"><\/i> 4\.9\/5 \(1,247\+ تقييم Google\)<\/span>/u',
		'<span class="chip"><i class="fas fa-swimming-pool"></i> إنشاء وتركيب مسابح</span>',
		$html
	);
	$html = str_replace( '15,000+ عميل راضٍ', 'مشاريع مسابح منفذة', $html );
	$html = str_replace( 'طوارئ 24/7', 'تواصل واتساب مباشر', $html );
	$html = preg_replace(
		'/"telephone"\s*:\s*"0522881997"/',
		'"telephone":"+' . $wa . '"',
		$html
	);
	$html = str_replace(
		array(
			'https://elegantswimmingpools.com/blog/"',
			'content="Blog - اليجانت للمسابح"',
		),
		array(
			'https://elegantswimmingpools.com/"',
			'content="اليجانت للمسابح — إنشاء وصيانة وتنظيف المسابح"',
		),
		$html
	);
	$html = preg_replace(
		'/<a href="tel:201556644443"[^>]*class="btn btn-call"[^>]*>.*?<\/a>/su',
		'<a href="https://wa.me/201556644443?text=' . rawurlencode( 'مرحباً! أرغب في خدمة مسابح من اليجانت' ) . '" target="_blank" rel="noopener noreferrer" class="btn btn-wa"><i class="fab fa-whatsapp"></i> واتساب</a>',
		$html
	);
	$html = preg_replace(
		'/<a href="tel:201556644443"[^>]*class="fab-btn fab-call"[^>]*>.*?<\/a>/su',
		'',
		$html
	);
	return $html;
}

add_action(
	'wp_head',
	function () {
		echo '<style id="elegant-wa-only">.btn-call,.fab-call,a.fab-btn.fab-call,.rukn-call,.-YC-WidgetType-blog_v1{display:none!important}</style>';
		if ( ! current_theme_supports( 'title-tag' ) ) {
			echo '<title>' . esc_html( wp_get_document_title() ) . '</title>';
		}
	},
	0
);

add_filter(
	'pre_get_document_title',
	function ( $title ) {
		if ( is_front_page() || is_home() ) {
			return 'اليجانت للمسابح — إنشاء وصيانة وتنظيف المسابح';
		}
		return $title;
	},
	99
);

add_action(
	'wp_footer',
	function () {
		$wa = ELEGANT_WA;
		$msg = rawurlencode( 'مرحباً! أرغب في خدمة مسابح من اليجانت' );
		?>
<script id="elegant-wa-home">
(function(){
  var WA=<?php echo wp_json_encode( $wa ); ?>;
  var MSG=<?php echo wp_json_encode( $msg ); ?>;
  var waUrl="https://wa.me/"+WA+"?text="+MSG;
  function digits(s){return String(s||"").replace(/\D+/g,"")}
  function convert(root){
    root=root||document;
    root.querySelectorAll('a[href*="wa.me"],a[href*="api.whatsapp"]').forEach(function(a){
      var href=a.getAttribute("href")||"";
      if(href.indexOf("201151481000")!==-1) return;
      a.setAttribute("href", waUrl);
    });
    root.querySelectorAll('a[href^="tel:"]').forEach(function(a){
      a.setAttribute("href", waUrl);
      a.setAttribute("target","_blank");
      a.classList.remove("btn-call");
      if((a.textContent||"").indexOf("اتصل")!==-1){ a.innerHTML='<i class="fab fa-whatsapp"></i> واتساب'; }
    });
  }
  function rewriteHero(){
    var h1=document.querySelector(".hero-copy h1");
    if(h1 && /الخدمات المنزلية|عزل|تسربات|تكييف/.test(h1.textContent||"")){
      h1.innerHTML="شركة اليجانت للمسابح — إنشاء وصيانة وتنظيف <em>حمامات السباحة</em>";
    }
    var sub=document.querySelector(".hero-copy .sub");
    if(sub && /تسربات|تكييف|عزل الأسطح/.test(sub.textContent||"")){
      sub.textContent="تصميم وتركيب وصيانة وتنظيف المسابح باحترافية في الإمارات والدول العربية. فريق متخصص ومواد معتمدة، وتواصل واتساب موحّد لجميع الدول.";
    }
    var minis=document.querySelectorAll(".dash-mini .mini span");
    var labels=["إنشاء مسابح","تنظيف مسابح","صيانة مسابح","ترميم مسابح","معدات وفلاتر","تشغيل دوري"];
    var icons=["fa-swimming-pool","fa-soap","fa-screwdriver-wrench","fa-hammer","fa-gears","fa-calendar-check"];
    if(minis.length && /تسربات|تكييف|حشرات|عزل/.test((minis[0].textContent||""))){
      minis.forEach(function(el,i){ if(labels[i]) el.textContent=labels[i]; });
      document.querySelectorAll(".dash-mini .mini i").forEach(function(ic,i){
        if(icons[i]) ic.className="fas "+icons[i];
      });
    }
    var war=document.querySelector(".warranty small");
    if(war && /عزل مائي|حراري/.test(war.textContent||"")){
      war.textContent="على أعمال إنشاء وصيانة المسابح";
    }
    var warb=document.querySelector(".warranty b");
    if(warb){ warb.textContent="ضمان مكتوب على أعمال المسابح"; }
    var dt=document.querySelectorAll(".dash-trust .dt");
    if(dt[0] && /بلدية دبي/.test(dt[0].textContent||"")){
      dt[0].innerHTML='<i class="fas fa-circle-check"></i> فريق متخصص في المسابح';
    }
    if(window.RuknCS){
      window.RuknCS.call_show=false;
      window.RuknCS.wa_show=true;
      window.RuknCS.wa_number=WA;
      window.RuknCS.call_number="";
      window.RuknCS.wa_message="مرحباً! أرغب في خدمة مسابح من اليجانت";
    }
  }
  function run(){ convert(document); rewriteHero(); }
  if(document.readyState==="loading"){ document.addEventListener("DOMContentLoaded", run); }
  else { run(); }
  setTimeout(run, 400);
})();
</script>
		<?php
	},
	99
);
