<?php
/**
 * Elegant UI: 3D buttons + multi-step article booking (Telegram) + promo image popup.
 * Deploy as a Code Snippets "php" snippet (global, active). Do not paste the opening tag.
 *
 * Admin: لوحة التحكم → حجوزات اليجانت
 * Shortcode: [elegant_booking]
 */
if ( ! defined( 'ABSPATH' ) ) {
	return;
}

if ( ! defined( 'ELEGANT_UI_WA' ) ) {
	define( 'ELEGANT_UI_WA', '201556644443' );
}
if ( get_option( 'elegant_ui_ver' ) !== '20260911a' ) {
	update_option( 'elegant_ui_ver', '20260911a' );
	if ( function_exists( 'speedycache_delete_cache' ) ) {
		speedycache_delete_cache( true );
	}
	do_action( 'litespeed_purge_all' );
}

/** @return array<string,mixed> */
function elegant_ui_opts() {
	return array(
		'tg_token'  => (string) get_option( 'elegant_ui_tg_token', '' ),
		'tg_chat'   => (string) get_option( 'elegant_ui_tg_chat', '' ),
		'promo_on'  => (int) get_option( 'elegant_ui_promo_on', 0 ),
		'promo_img' => (int) get_option( 'elegant_ui_promo_img', 0 ),
		'promo_url' => (string) get_option( 'elegant_ui_promo_url', '' ),
		'book_on'   => (int) get_option( 'elegant_ui_book_on', 1 ),
	);
}

/** @return array<string,array<string,mixed>> */
function elegant_ui_catalogs() {
	$pool_fields = array(
		array(
			'name'    => 'pool_type',
			'label'   => 'نوع المسبح',
			'type'    => 'select',
			'options' => array( 'سكيمر', 'أوفر فلو', 'فايبر جلاس', 'خرساني', 'جاكوزي', 'غير متأكد' ),
		),
		array(
			'name'  => 'length',
			'label' => 'الطول التقريبي (متر)',
			'type'  => 'number',
		),
		array(
			'name'  => 'width',
			'label' => 'العرض التقريبي (متر)',
			'type'  => 'number',
		),
		array(
			'name'    => 'place',
			'label'   => 'المسبح داخلي أم خارجي؟',
			'type'    => 'select',
			'options' => array( 'خارجي', 'داخلي', 'كلاهما' ),
		),
		array(
			'name'    => 'equipment_room',
			'label'   => 'هل توجد غرفة معدات؟',
			'type'    => 'select',
			'options' => array( 'نعم', 'لا', 'غير متأكد' ),
		),
		array(
			'name'  => 'photos',
			'label' => 'صور المسبح (اختياري)',
			'type'  => 'file',
		),
	);

	return array(
		'maintenance'  => array(
			'title'       => 'حجز خدمة صيانة المسابح',
			'step1_title' => 'اختر نوع العطل أو الخدمة المطلوبة',
			'options'     => array(
				'pump'     => 'عطل مضخة المسبح',
				'filter'   => 'انسداد أو عطل الفلتر',
				'leak'     => 'تسرب مياه من المسبح',
				'algae'    => 'طحالب واخضرار الماء',
				'heater'   => 'عطل السخان / التدفئة',
				'electric' => 'مشكلة كهرباء أو لوحة التحكم',
				'tiles'    => 'كسر بلاط أو تآكل الفايبر',
				'flow'     => 'ضعف تدفق المياه / رجوع ضعيف',
				'chem'     => 'اختلال الكيماويات أو رائحة الكلور',
				'cover'    => 'غطاء المسبح / الرولر',
				'periodic' => 'صيانة دورية شاملة',
				'other'    => 'عطل آخر (أوضحه في الخطوة التالية)',
			),
			'fields'      => array_merge(
				array(
					array(
						'name'  => 'fault_notes',
						'label' => 'وصف العطل أو الملاحظات الفنية',
						'type'  => 'textarea',
					),
				),
				$pool_fields
			),
		),
		'cleaning'     => array(
			'title'       => 'حجز خدمة تنظيف المسابح',
			'step1_title' => 'اختر خدمات التنظيف المطلوبة',
			'options'     => array(
				'full'     => 'تنظيف شامل للمسبح',
				'vacuum'   => 'شفط الأوساخ من القاع',
				'walls'    => 'غسيل الجدران والأرضية',
				'filter'   => 'تنظيف وغسيل الفلتر',
				'algae'    => 'إزالة الطحالب',
				'chem'     => 'ضبط كيماويات المياه',
				'cover'    => 'تنظيف الغطاء والمحيط',
				'weekly'   => 'عقد تنظيف أسبوعي',
				'monthly'  => 'عقد تنظيف شهري',
			),
			'fields'      => $pool_fields,
		),
		'construction' => array(
			'title'       => 'طلب إنشاء / تركيب مسبح',
			'step1_title' => 'اختر نوع الإنشاء المطلوب',
			'options'     => array(
				'concrete' => 'مسبح خرساني',
				'fiber'    => 'مسبح فايبر جلاس',
				'skimmer'  => 'نظام سكيمر',
				'overflow' => 'نظام أوفر فلو',
				'indoor'   => 'مسبح داخلي',
				'outdoor'  => 'مسبح خارجي',
				'villa'    => 'مسبح فيلا',
				'resort'   => 'منتجع / تجاري',
				'jacuzzi'  => 'جاكوزي ملحق',
			),
			'fields'      => array_merge(
				array(
					array(
						'name'    => 'stage',
						'label'   => 'مرحلة المشروع',
						'type'    => 'select',
						'options' => array( 'فكرة أولية', 'لدي مخطط', 'جاهز للتنفيذ', 'ترميم مسبح قائم' ),
					),
				),
				$pool_fields
			),
		),
		'design'       => array(
			'title'       => 'طلب تصميم مسبح',
			'step1_title' => 'اختر عناصر التصميم',
			'options'     => array(
				'family'    => 'تصميم مسبح عائلي',
				'luxury'    => 'تصميم فيلا فاخرة',
				'waterfall' => 'شلالات ومائية',
				'lighting'  => 'إضاءة LED',
				'landscape' => 'لاندسكيب حول المسبح',
				'indoor'    => 'مسبح داخلي',
			),
			'fields'      => $pool_fields,
		),
		'liner'        => array(
			'title'       => 'طلب تبطين المسبح',
			'step1_title' => 'اختر نوع التبطين',
			'options'     => array(
				'vinyl'   => 'تبطين فينيل',
				'fiber'   => 'تبطين فايبر',
				'epoxy'   => 'دهان إيبوكسي',
				'repair'  => 'ترميم تبطين قائم',
			),
			'fields'      => $pool_fields,
		),
		'sterilize'    => array(
			'title'       => 'طلب تعقيم المسبح',
			'step1_title' => 'اختر نظام التعقيم',
			'options'     => array(
				'chlorine' => 'تعقيم بالكلور',
				'salt'     => 'جهاز ملح / كلوريناتور',
				'uv'       => 'أشعة فوق بنفسجية',
				'ozone'    => 'أوزون',
				'full'     => 'تعقيم شامل مع تنظيف',
			),
			'fields'      => $pool_fields,
		),
		'equipment'    => array(
			'title'       => 'طلب أجهزة ومعدات المسابح',
			'step1_title' => 'اختر المعدات المطلوبة',
			'options'     => array(
				'pump'    => 'مضخة جديدة',
				'filter'  => 'فلتر رمل / خرطوش',
				'heater'  => 'سخان / تدفئة',
				'light'   => 'إضاءة LED',
				'cover'   => 'غطاء آلي',
				'salt'    => 'جهاز ملح',
				'control' => 'لوحة تحكم',
			),
			'fields'      => $pool_fields,
		),
		'waterfall'    => array(
			'title'       => 'طلب شلالات ونوافير',
			'step1_title' => 'اختر نوع العمل المائي',
			'options'     => array(
				'wall'   => 'شلال جداري',
				'fountain'=> 'نافورة',
				'jacuzzi'=> 'جاكوزي',
				'repair' => 'صيانة شلال قائم',
			),
			'fields'      => $pool_fields,
		),
		'leak'         => array(
			'title'       => 'طلب كشف وإصلاح تسربات المسبح',
			'step1_title' => 'حدّد نوع التسرب المتوقع',
			'options'     => array(
				'structure' => 'تسرب من هيكل المسبح',
				'pipes'     => 'تسرب مواسير',
				'skimmer'   => 'تسرب سكيمر / منافذ',
				'pressure'  => 'فحص ضغط النظام',
				'unknown'   => 'نقص ماء بدون سبب واضح',
			),
			'fields'      => array_merge(
				array(
					array(
						'name'  => 'fault_notes',
						'label' => 'متى لاحظت نقص الماء؟',
						'type'  => 'textarea',
					),
				),
				$pool_fields
			),
		),
		'finish'       => array(
			'title'       => 'طلب تشطيب محيط المسبح',
			'step1_title' => 'اختر أعمال التشطيب',
			'options'     => array(
				'mosaic' => 'بلاط موزاييك',
				'epoxy'  => 'دهان إيبوكسي',
				'stone'  => 'حجر طبيعي',
				'deck'   => 'أرضيات محيط المسبح',
				'paint'  => 'دهانات مقاومة للرطوبة',
			),
			'fields'      => $pool_fields,
		),
		'landscape'    => array(
			'title'       => 'طلب تنسيق حدائق المسابح',
			'step1_title' => 'اختر عناصر التنسيق',
			'options'     => array(
				'garden' => 'حديقة حول المسبح',
				'grass'  => 'عشب طبيعي / صناعي',
				'lights' => 'إضاءة خارجية',
				'deck'   => 'جلسة ومحيط خشبي',
				'full'   => 'تنسيق متكامل',
			),
			'fields'      => $pool_fields,
		),
		'default'      => array(
			'title'       => 'حجز خدمة مسابح',
			'step1_title' => 'اختر الخدمات المطلوبة',
			'options'     => array(
				'maintenance'  => 'صيانة مسابح',
				'cleaning'     => 'تنظيف وتعقيم',
				'construction' => 'إنشاء وتركيب',
				'design'       => 'تصميم',
				'leak'         => 'كشف تسربات',
				'equipment'    => 'أجهزة ومعدات',
				'liner'        => 'تبطين',
				'finish'       => 'تشطيب محيط المسبح',
			),
			'fields'      => $pool_fields,
		),
	);
}

function elegant_ui_detect_profile( $title, $slug ) {
	$hay = mb_strtolower( $title . ' ' . $slug, 'UTF-8' );
	$rules = array(
		'leak'         => array( 'تسرب', 'leak' ),
		'liner'        => array( 'تبطين', 'liner', 'vinyl' ),
		'waterfall'    => array( 'شلال', 'نافور', 'waterfall', 'fountain' ),
		'equipment'    => array( 'أجهز', 'معدات', 'مضخ', 'فلتر', 'equipment', 'pump' ),
		'sterilize'    => array( 'تعقيم', 'steril' ),
		'maintenance'  => array( 'صيانة', 'اصلاح', 'إصلاح', 'maintenance', 'repair' ),
		'cleaning'     => array( 'تنظيف', 'cleaning' ),
		'construction' => array( 'إنشاء', 'انشاء', 'بناء', 'تركيب', 'مقاول', 'construction', 'contractor', 'installation', 'basin' ),
		'design'       => array( 'تصميم', 'design' ),
		'finish'       => array( 'تشطيب', 'بلاط', 'دهان', 'جبس', 'finish', 'gypsum', 'painting' ),
		'landscape'    => array( 'حدائق', 'تنسيق', 'landscap', 'garden' ),
	);
	foreach ( $rules as $id => $needles ) {
		foreach ( $needles as $n ) {
			if ( $n !== '' && mb_strpos( $hay, mb_strtolower( $n, 'UTF-8' ) ) !== false ) {
				return $id;
			}
		}
	}
	return 'default';
}

add_action(
	'init',
	function () {
		register_post_type(
			'elg_booking',
			array(
				'labels'       => array(
					'name'          => 'طلبات الحجز',
					'singular_name' => 'طلب حجز',
				),
				'public'       => false,
				'show_ui'      => true,
				'show_in_menu' => 'elegant-leads',
				'supports'     => array( 'title', 'editor', 'custom-fields' ),
				'capability_type' => 'post',
			)
		);
	}
);

add_action(
	'admin_menu',
	function () {
		add_menu_page(
			'حجوزات اليجانت',
			'حجوزات اليجانت',
			'manage_options',
			'elegant-leads',
			'elegant_ui_settings_page',
			'dashicons-clipboard',
			58
		);
	}
);

add_action(
	'admin_enqueue_scripts',
	function ( $hook ) {
		if ( $hook !== 'toplevel_page_elegant-leads' ) {
			return;
		}
		wp_enqueue_media();
	}
);

add_action(
	'admin_post_elegant_ui_save',
	function () {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( 'forbidden' );
		}
		check_admin_referer( 'elegant_ui_save' );
		update_option( 'elegant_ui_tg_token', sanitize_text_field( wp_unslash( $_POST['tg_token'] ?? '' ) ) );
		update_option( 'elegant_ui_tg_chat', sanitize_text_field( wp_unslash( $_POST['tg_chat'] ?? '' ) ) );
		update_option( 'elegant_ui_promo_on', empty( $_POST['promo_on'] ) ? 0 : 1 );
		update_option( 'elegant_ui_promo_img', absint( $_POST['promo_img'] ?? 0 ) );
		update_option( 'elegant_ui_promo_url', esc_url_raw( wp_unslash( $_POST['promo_url'] ?? '' ) ) );
		update_option( 'elegant_ui_book_on', empty( $_POST['book_on'] ) ? 0 : 1 );
		wp_safe_redirect( admin_url( 'admin.php?page=elegant-leads&saved=1' ) );
		exit;
	}
);

function elegant_ui_settings_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	$o     = elegant_ui_opts();
	$thumb = $o['promo_img'] ? wp_get_attachment_image_url( $o['promo_img'], 'medium' ) : '';
	?>
	<div class="wrap" dir="rtl">
		<h1>حجوزات اليجانت والإشعار الترويجي</h1>
		<?php if ( ! empty( $_GET['saved'] ) ) : ?>
			<div class="notice notice-success"><p>تم حفظ الإعدادات.</p></div>
		<?php endif; ?>
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<?php wp_nonce_field( 'elegant_ui_save' ); ?>
			<input type="hidden" name="action" value="elegant_ui_save" />
			<table class="form-table" role="presentation">
				<tr>
					<th>نظام الحجز داخل المقالات</th>
					<td><label><input type="checkbox" name="book_on" value="1" <?php checked( $o['book_on'], 1 ); ?> /> تفعيل نموذج الحجز المرحلي</label></td>
				</tr>
				<tr>
					<th>مفتاح بوت تيليجرام</th>
					<td>
						<input type="password" class="regular-text" name="tg_token" value="<?php echo esc_attr( $o['tg_token'] ); ?>" autocomplete="off" />
						<p class="description">من BotFather. لا يظهر للزوار. مثال: 123456:ABC-xxx</p>
					</td>
				</tr>
				<tr>
					<th>Chat ID</th>
					<td>
						<input type="text" class="regular-text" name="tg_chat" value="<?php echo esc_attr( $o['tg_chat'] ); ?>" />
						<p class="description">معرّف المحادثة أو القناة التي تصلك عليها الطلبات.</p>
					</td>
				</tr>
				<tr>
					<th>إشعار الصورة (إعلان صغير)</th>
					<td><label><input type="checkbox" name="promo_on" value="1" <?php checked( $o['promo_on'], 1 ); ?> /> إظهار الإشعار عند زيارة الموقع</label></td>
				</tr>
				<tr>
					<th>صورة الإشعار</th>
					<td>
						<input type="hidden" id="elegant-promo-img" name="promo_img" value="<?php echo esc_attr( (string) $o['promo_img'] ); ?>" />
						<button type="button" class="button" id="elegant-promo-pick">اختيار / تغيير الصورة</button>
						<button type="button" class="button" id="elegant-promo-clear">إزالة الصورة</button>
						<div id="elegant-promo-preview" style="margin-top:10px;max-width:280px">
							<?php if ( $thumb ) : ?>
								<img src="<?php echo esc_url( $thumb ); ?>" style="max-width:100%;border-radius:12px" alt="" />
							<?php endif; ?>
						</div>
					</td>
				</tr>
				<tr>
					<th>رابط عند الضغط على الإشعار</th>
					<td>
						<input type="url" class="regular-text ltr" name="promo_url" value="<?php echo esc_attr( $o['promo_url'] ); ?>" placeholder="https://" />
					</td>
				</tr>
			</table>
			<?php submit_button( 'حفظ الإعدادات' ); ?>
		</form>
		<script>
		jQuery(function($){
			var frame;
			$('#elegant-promo-pick').on('click', function(e){
				e.preventDefault();
				if (frame) { frame.open(); return; }
				frame = wp.media({ title: 'صورة الإشعار', button: { text: 'استخدام هذه الصورة' }, multiple: false });
				frame.on('select', function(){
					var att = frame.state().get('selection').first().toJSON();
					$('#elegant-promo-img').val(att.id);
					$('#elegant-promo-preview').html('<img src="'+(att.sizes && att.sizes.medium ? att.sizes.medium.url : att.url)+'" style="max-width:100%;border-radius:12px" alt="" />');
				});
				frame.open();
			});
			$('#elegant-promo-clear').on('click', function(){
				$('#elegant-promo-img').val('0');
				$('#elegant-promo-preview').empty();
			});
		});
		</script>
	</div>
	<?php
}

add_action(
	'rest_api_init',
	function () {
		register_rest_route(
			'elegant/v1',
			'/booking',
			array(
				'methods'             => 'POST',
				'permission_callback' => '__return_true',
				'callback'            => 'elegant_ui_handle_booking',
			)
		);
		register_rest_route(
			'elegant/v1',
			'/ui-settings',
			array(
				'methods'             => array( 'GET', 'POST' ),
				'permission_callback' => function () {
					return current_user_can( 'manage_options' );
				},
				'callback'            => 'elegant_ui_rest_settings',
			)
		);
	}
);

function elegant_ui_rest_settings( WP_REST_Request $req ) {
	if ( $req->get_method() === 'POST' ) {
		$p = $req->get_json_params();
		if ( ! is_array( $p ) ) {
			$p = $req->get_params();
		}
		if ( isset( $p['tg_token'] ) ) {
			update_option( 'elegant_ui_tg_token', sanitize_text_field( $p['tg_token'] ) );
		}
		if ( isset( $p['tg_chat'] ) ) {
			update_option( 'elegant_ui_tg_chat', sanitize_text_field( $p['tg_chat'] ) );
		}
		if ( isset( $p['promo_on'] ) ) {
			update_option( 'elegant_ui_promo_on', empty( $p['promo_on'] ) ? 0 : 1 );
		}
		if ( isset( $p['promo_img'] ) ) {
			update_option( 'elegant_ui_promo_img', absint( $p['promo_img'] ) );
		}
		if ( isset( $p['promo_url'] ) ) {
			update_option( 'elegant_ui_promo_url', esc_url_raw( $p['promo_url'] ) );
		}
		if ( isset( $p['book_on'] ) ) {
			update_option( 'elegant_ui_book_on', empty( $p['book_on'] ) ? 0 : 1 );
		}
		do_action( 'litespeed_purge_all' );
	}
	$o            = elegant_ui_opts();
	$o['tg_token']  = $o['tg_token'] ? 'saved' : '';
	$o['promo_src'] = $o['promo_img'] ? wp_get_attachment_image_url( (int) $o['promo_img'], 'medium' ) : '';
	return $o;
}

function elegant_ui_handle_booking( WP_REST_Request $req ) {
	$nonce = (string) $req->get_param( 'nonce' );
	if ( ! $nonce ) {
		$nonce = (string) $req->get_header( 'X-WP-Nonce' );
	}
	if ( ! wp_verify_nonce( $nonce, 'elegant_ui_book' ) ) {
		return new WP_Error( 'bad_nonce', 'جلسة غير صالحة، حدّث الصفحة وحاول مرة أخرى.', array( 'status' => 403 ) );
	}

	$payload = $req->get_param( 'payload' );
	if ( is_string( $payload ) ) {
		$payload = json_decode( wp_unslash( $payload ), true );
	}
	if ( ! is_array( $payload ) ) {
		$json = $req->get_json_params();
		$payload = is_array( $json ) ? $json : array();
	}

	$name  = sanitize_text_field( $payload['name'] ?? '' );
	$phone = preg_replace( '/\D+/', '', (string) ( $payload['phone'] ?? '' ) );
	$email = sanitize_email( $payload['email'] ?? '' );
	if ( mb_strlen( $name ) < 2 || strlen( $phone ) < 8 ) {
		return new WP_Error( 'invalid', 'الاسم ورقم الواتساب مطلوبان.', array( 'status' => 400 ) );
	}

	$services = array();
	if ( ! empty( $payload['services'] ) && is_array( $payload['services'] ) ) {
		foreach ( $payload['services'] as $s ) {
			$s = sanitize_text_field( (string) $s );
			if ( $s !== '' ) {
				$services[] = $s;
			}
		}
	}
	if ( ! $services ) {
		return new WP_Error( 'invalid', 'اختر خدمة واحدة على الأقل.', array( 'status' => 400 ) );
	}

	$details = array();
	if ( ! empty( $payload['details'] ) && is_array( $payload['details'] ) ) {
		foreach ( $payload['details'] as $k => $v ) {
			if ( $k === 'photos' ) {
				continue;
			}
			$details[ sanitize_key( (string) $k ) ] = is_array( $v )
				? array_map( 'sanitize_text_field', $v )
				: sanitize_textarea_field( (string) $v );
		}
	}

	$row = array(
		'name'      => $name,
		'phone'     => $phone,
		'email'     => $email,
		'city'      => sanitize_text_field( $payload['city'] ?? '' ),
		'address'   => sanitize_text_field( $payload['address'] ?? '' ),
		'date'      => sanitize_text_field( $payload['date'] ?? '' ),
		'time'      => sanitize_text_field( $payload['time'] ?? '' ),
		'notes'     => sanitize_textarea_field( $payload['notes'] ?? '' ),
		'services'  => $services,
		'details'   => $details,
		'profile'   => sanitize_key( $payload['profile'] ?? '' ),
		'postTitle' => sanitize_text_field( $payload['postTitle'] ?? '' ),
		'postUrl'   => esc_url_raw( $payload['postUrl'] ?? '' ),
	);

	$body  = "🛎️ طلب حجز جديد — اليجانت للمسابح\n\n";
	$body .= '📄 المقال: ' . ( $row['postTitle'] ?: '-' ) . "\n";
	if ( $row['postUrl'] ) {
		$body .= '🔗 ' . $row['postUrl'] . "\n";
	}
	$body .= "\n✅ الخدمات / الخيارات:\n";
	foreach ( $row['services'] as $s ) {
		$body .= '• ' . $s . "\n";
	}
	if ( $row['details'] ) {
		$labels = array(
			'pool_type'       => 'نوع المسبح',
			'length'          => 'الطول (م)',
			'width'           => 'العرض (م)',
			'place'           => 'داخلي/خارجي',
			'equipment_room'  => 'غرفة معدات',
			'fault_notes'     => 'وصف العطل',
			'stage'           => 'مرحلة المشروع',
		);
		$body .= "\n📋 تفاصيل إضافية:\n";
		foreach ( $row['details'] as $k => $v ) {
			if ( $v === '' || $v === null ) {
				continue;
			}
			$lab = $labels[ $k ] ?? $k;
			$body .= '• ' . $lab . ': ' . ( is_array( $v ) ? implode( '، ', $v ) : $v ) . "\n";
		}
	}
	$body .= "\n👤 بيانات العميل:\n";
	$body .= '• الاسم: ' . $row['name'] . "\n";
	$body .= '• واتساب: ' . $row['phone'] . "\n";
	if ( $row['email'] ) {
		$body .= '• البريد: ' . $row['email'] . "\n";
	}
	if ( $row['city'] ) {
		$body .= '• المدينة: ' . $row['city'] . "\n";
	}
	if ( $row['address'] ) {
		$body .= '• العنوان: ' . $row['address'] . "\n";
	}
	if ( $row['date'] || $row['time'] ) {
		$body .= '• الموعد: ' . trim( $row['date'] . ' ' . $row['time'] ) . "\n";
	}
	if ( $row['notes'] ) {
		$body .= '• ملاحظات: ' . $row['notes'] . "\n";
	}

	$post_id = wp_insert_post(
		array(
			'post_type'   => 'elg_booking',
			'post_status' => 'private',
			'post_title'  => $row['name'] . ' — ' . implode( '، ', array_slice( $row['services'], 0, 3 ) ),
			'post_content'=> $body,
		),
		true
	);
	if ( is_wp_error( $post_id ) ) {
		$post_id = 0;
	} else {
		update_post_meta( $post_id, '_elg_payload', wp_json_encode( $row, JSON_UNESCAPED_UNICODE ) );
	}

	$files = array();
	if ( ! empty( $_FILES['photos'] ) && is_array( $_FILES['photos']['name'] ) ) {
		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/image.php';
		require_once ABSPATH . 'wp-admin/includes/media.php';
		$count = min( 4, count( $_FILES['photos']['name'] ) );
		for ( $i = 0; $i < $count; $i++ ) {
			if ( (int) $_FILES['photos']['error'][ $i ] !== UPLOAD_ERR_OK ) {
				continue;
			}
			if ( (int) $_FILES['photos']['size'][ $i ] > 4 * 1024 * 1024 ) {
				continue;
			}
			$single = array(
				'name'     => $_FILES['photos']['name'][ $i ],
				'type'     => $_FILES['photos']['type'][ $i ],
				'tmp_name' => $_FILES['photos']['tmp_name'][ $i ],
				'error'    => $_FILES['photos']['error'][ $i ],
				'size'     => $_FILES['photos']['size'][ $i ],
			);
			$overrides = array( 'test_form' => false, 'mimes' => array( 'jpg|jpeg|jpe' => 'image/jpeg', 'png' => 'image/png', 'webp' => 'image/webp' ) );
			$uploaded  = wp_handle_upload( $single, $overrides );
			if ( empty( $uploaded['error'] ) && ! empty( $uploaded['file'] ) ) {
				$files[] = $uploaded;
				if ( $post_id ) {
					$att = wp_insert_attachment(
						array(
							'post_mime_type' => $uploaded['type'],
							'post_title'     => sanitize_file_name( $single['name'] ),
							'post_content'   => '',
							'post_status'    => 'private',
						),
						$uploaded['file'],
						$post_id
					);
					if ( ! is_wp_error( $att ) ) {
						wp_update_attachment_metadata( $att, wp_generate_attachment_metadata( $att, $uploaded['file'] ) );
					}
				}
			}
		}
	}

	$tg = elegant_ui_telegram_send( $body, $files );

	return array(
		'ok'       => true,
		'telegram' => ! empty( $tg['ok'] ),
		'saved'    => (bool) $post_id,
	);
}

function elegant_ui_telegram_send( $text, $files = array() ) {
	$o     = elegant_ui_opts();
	$token = trim( $o['tg_token'] );
	$chat  = trim( $o['tg_chat'] );
	if ( $token === '' || $chat === '' ) {
		return array( 'ok' => false, 'error' => 'missing' );
	}
	$api = 'https://api.telegram.org/bot' . $token . '/sendMessage';
	$res = wp_remote_post(
		$api,
		array(
			'timeout' => 20,
			'body'    => array(
				'chat_id'    => $chat,
				'text'       => $text,
				'disable_web_page_preview' => true,
			),
		)
	);
	$ok = ! is_wp_error( $res ) && wp_remote_retrieve_response_code( $res ) < 300;
	if ( $ok && $files ) {
		foreach ( $files as $f ) {
			if ( empty( $f['file'] ) || ! file_exists( $f['file'] ) ) {
				continue;
			}
			if ( ! class_exists( 'CURLFile' ) ) {
				break;
			}
			$ch = curl_init( 'https://api.telegram.org/bot' . $token . '/sendPhoto' );
			curl_setopt_array(
				$ch,
				array(
					CURLOPT_POST           => true,
					CURLOPT_RETURNTRANSFER => true,
					CURLOPT_TIMEOUT        => 30,
					CURLOPT_POSTFIELDS     => array(
						'chat_id' => $chat,
						'photo'   => new CURLFile( $f['file'] ),
					),
				)
			);
			curl_exec( $ch );
			curl_close( $ch );
		}
	}
	return array( 'ok' => $ok );
}

add_shortcode(
	'elegant_booking',
	function () {
		return elegant_ui_booking_markup();
	}
);

add_filter(
	'the_content',
	function ( $content ) {
		if ( is_admin() || ! is_singular( 'post' ) ) {
			return $content;
		}
		$o = elegant_ui_opts();
		if ( empty( $o['book_on'] ) ) {
			return $content;
		}
		if ( strpos( $content, 'elg-book' ) !== false || strpos( $content, '[elegant_booking]' ) !== false ) {
			return $content;
		}
		return $content . elegant_ui_booking_markup();
	},
	30
);

add_action(
	'wp_footer',
	function () {
		$o = elegant_ui_opts();
		if ( empty( $o['book_on'] ) || is_admin() || ! is_singular( 'post' ) ) {
			return;
		}
		echo elegant_ui_booking_markup();
	},
	5
);

function elegant_ui_booking_markup() {
	static $printed = false;
	if ( $printed ) {
		return '';
	}
	$printed = true;
	$post  = get_post();
	$title = $post ? wp_strip_all_tags( get_the_title( $post ) ) : '';
	$slug  = $post ? $post->post_name : '';
	$pid   = elegant_ui_detect_profile( $title, $slug );
	$cats  = elegant_ui_catalogs();
	if ( ! isset( $cats[ $pid ] ) ) {
		$pid = 'default';
	}
	$cfg = array(
		'rest'      => esc_url_raw( rest_url( 'elegant/v1/booking' ) ),
		'nonce'     => wp_create_nonce( 'elegant_ui_book' ),
		'profile'   => $pid,
		'catalog'   => $cats[ $pid ],
		'postTitle' => $title,
		'postUrl'   => $post ? get_permalink( $post ) : '',
		'cities'    => array( 'دبي', 'أبوظبي', 'الشارقة', 'عجمان', 'رأس الخيمة', 'الفجيرة', 'أم القيوين', 'القاهرة', 'الجيزة', 'الإسكندرية', 'طنطا', 'المنصورة', 'الرياض', 'جدة', 'الدمام', 'أخرى' ),
		'times'     => array( 'صباحاً', 'ظهراً', 'عصراً', 'مساءً', 'مرن / في أقرب وقت' ),
	);
	ob_start();
	?>
	<section class="elg-book" id="elg-book" dir="rtl" data-elg-book="<?php echo esc_attr( wp_json_encode( $cfg, JSON_UNESCAPED_UNICODE ) ); ?>">
		<div class="elg-book-card">
			<h2 class="elg-book-title" id="elg-book-title"><?php echo esc_html( $cfg['catalog']['title'] ); ?></h2>
			<div id="elg-book-steps"></div>
			<p class="elg-book-msg" id="elg-book-msg" hidden></p>
			<div class="elg-book-actions" id="elg-book-actions"></div>
		</div>
	</section>
	<?php
	return ob_get_clean();
}

add_action( 'wp_head', 'elegant_ui_print_css', 99 );
function elegant_ui_print_css() {
	echo '<style id="elegant-3d-ui">
html body a.btn.btn-call,html body a.fab-btn.fab-call,html body .rukn-call,
html body.rukn-hide-call a.btn.btn-call,html body.rukn-hide-call a.fab-btn.fab-call,
html body.rukn-hide-call a[href^="tel:"].btn,html body.rukn-hide-call a[href^="tel:"].fab-btn{
  display:inline-flex!important;visibility:visible!important;opacity:1!important
}
html body a.btn.btn-wa,html body a.btn.btn-call,html body a.btn.btn-quote,
html body a.btn-ket_1,html body a.btn-ket_2,html body .nav-cta a.btn,
html body a.cta-button,html body a.--contact--button-call-link,
html body .elg-btn{
  border:0!important;border-radius:999px!important;color:#fff!important;font-weight:800!important;
  letter-spacing:.2px;text-shadow:0 1px 0 rgba(0,0,0,.15);
  transform:translateY(0);transition:transform .12s ease,box-shadow .12s ease,filter .12s ease;
  position:relative
}
html body a.btn.btn-wa,html body a.btn-ket_2,html body a.fab-btn.fab-wa,html body .elg-btn-wa{
  background:linear-gradient(180deg,#57e06a 0%,#2ecc4a 48%,#1db954 100%)!important;
  box-shadow:0 5px 0 #148a2a,0 10px 18px rgba(20,138,42,.28),inset 0 2px 0 rgba(255,255,255,.4),inset 0 -2px 0 rgba(0,0,0,.12)!important
}
html body a.btn.btn-call,html body a.btn-ket_1,html body a.fab-btn.fab-call,html body .elg-btn-call{
  background:linear-gradient(180deg,#5ad7ea 0%,#1cb5c9 48%,#0ea5c0 100%)!important;
  box-shadow:0 5px 0 #0a7a90,0 10px 18px rgba(10,122,144,.28),inset 0 2px 0 rgba(255,255,255,.4),inset 0 -2px 0 rgba(0,0,0,.12)!important
}
html body a.btn.btn-quote,html body .elg-btn-next{
  background:linear-gradient(180deg,#5aa8ff 0%,#1a73e8 50%,#1565d0 100%)!important;
  box-shadow:0 5px 0 #0d47a1,0 10px 18px rgba(13,71,161,.25),inset 0 2px 0 rgba(255,255,255,.35),inset 0 -2px 0 rgba(0,0,0,.12)!important
}
html body .elg-btn-prev{
  background:linear-gradient(180deg,#8a96a8 0%,#5c6775 55%,#4a5461 100%)!important;
  box-shadow:0 5px 0 #2f3640,0 8px 14px rgba(47,54,64,.25),inset 0 2px 0 rgba(255,255,255,.28)!important;color:#fff!important
}
html body .elg-btn-ok{
  background:linear-gradient(180deg,#57e06a 0%,#22c55e 50%,#16a34a 100%)!important;
  box-shadow:0 5px 0 #15803d,0 10px 18px rgba(21,128,61,.28),inset 0 2px 0 rgba(255,255,255,.4)!important
}
html body a.btn.btn-wa:hover,html body a.btn.btn-call:hover,html body a.btn.btn-quote:hover,
html body a.btn-ket_1:hover,html body a.btn-ket_2:hover,html body .elg-btn:hover{
  filter:brightness(1.05);transform:translateY(-1px)
}
html body a.btn.btn-wa:active,html body a.btn.btn-call:active,html body a.btn.btn-quote:active,
html body a.btn-ket_1:active,html body a.btn-ket_2:active,html body .elg-btn:active,
html body a.fab-btn:active{
  transform:translateY(4px)!important;box-shadow:0 1px 0 rgba(0,0,0,.25),inset 0 2px 6px rgba(0,0,0,.2)!important
}
html body a.fab-btn.fab-wa,html body a.fab-btn.fab-call{
  border:0!important;border-radius:50%!important;color:#fff!important
}
#elegant-3d-dock{display:none}
@media(max-width:768px){
  html body a.fab-btn.fab-wa,html body a.fab-btn.fab-call{display:none!important}
  #elegant-3d-dock{display:flex;position:fixed;left:12px;right:12px;bottom:10px;z-index:99985;flex-direction:column;gap:8px}
  #elegant-3d-dock a{display:flex!important;align-items:center;justify-content:center;height:48px;font-size:18px;text-decoration:none}
  body{padding-bottom:118px!important}
}
.elg-book{margin:28px 0 40px}
.elg-book-card{background:#fff;border-radius:22px;padding:18px 16px 20px;box-shadow:0 10px 28px rgba(16,40,80,.08);border:1px solid #e8eef6}
.elg-book-title{margin:0 0 14px;text-align:center;color:#1a73e8;font-size:22px;font-weight:800}
.elg-opt{display:flex;align-items:center;gap:10px;width:100%;margin:0 0 10px;padding:12px 14px;border-radius:16px;border:1px solid #d5e4f5;background:#f7fbff;cursor:pointer;font-weight:700;color:#1c2e44}
.elg-opt input{width:20px;height:20px;accent-color:#1a73e8;flex:0 0 20px}
.elg-opt span{flex:1;text-align:right}
.elg-field{margin:0 0 12px}
.elg-field label{display:block;margin:0 0 6px;font-weight:700;color:#1c2e44}
.elg-field input,.elg-field select,.elg-field textarea{
  width:100%;border:1px solid #d5e4f5;background:#fff;border-radius:12px;padding:10px 12px;min-height:44px;font-size:15px
}
.elg-field textarea{min-height:90px;resize:vertical}
.elg-row2{display:grid;grid-template-columns:1fr 1fr;gap:10px}
@media(max-width:520px){.elg-row2{grid-template-columns:1fr}}
.elg-book-actions{display:flex;justify-content:space-between;align-items:center;margin-top:8px;gap:10px}
.elg-btn{display:inline-flex;align-items:center;justify-content:center;min-width:96px;height:42px;padding:0 18px;cursor:pointer;font-size:15px}
.elg-book-msg{margin:10px 0 0;text-align:center;font-weight:700}
.elg-book-msg.ok{color:#15803d}
.elg-book-msg.err{color:#b91c1c}
#elg-promo{position:fixed;inset:0;z-index:100000;display:flex;align-items:center;justify-content:center;background:rgba(8,18,32,.45);padding:18px}
#elg-promo[hidden]{display:none!important}
.elg-promo-card{position:relative;width:min(72vw,320px);background:#fff;border-radius:16px;box-shadow:0 18px 40px rgba(0,0,0,.28);overflow:hidden}
.elg-promo-card img{display:block;width:100%;height:auto;vertical-align:middle}
.elg-promo-x{position:absolute;top:6px;left:6px;width:26px;height:26px;border:0;border-radius:50%;background:rgba(0,0,0,.55);color:#fff;font-size:18px;line-height:26px;cursor:pointer;z-index:2}
.elg-promo-link{display:block}
</style>';
}

add_action( 'wp_footer', 'elegant_ui_print_js', 120 );
function elegant_ui_print_js() {
	$o     = elegant_ui_opts();
	$promo = array( 'on' => false );
	if ( ! empty( $o['promo_on'] ) && ! empty( $o['promo_img'] ) ) {
		$src = wp_get_attachment_image_url( (int) $o['promo_img'], 'large' );
		if ( $src ) {
			$promo = array(
				'on'  => true,
				'id'  => (int) $o['promo_img'],
				'src' => $src,
				'url' => $o['promo_url'],
			);
		}
	}
	$wa  = ELEGANT_UI_WA;
	$tel = '+' . $wa;
	$msg = rawurlencode( 'مرحباً! أرغب في خدمة مسابح من اليجانت' );
	?>
<div id="elegant-3d-dock" dir="rtl">
	<a class="elg-btn elg-btn-wa" href="https://wa.me/<?php echo esc_attr( $wa ); ?>?text=<?php echo esc_attr( $msg ); ?>" target="_blank" rel="noopener noreferrer">واتساب</a>
	<a class="elg-btn elg-btn-call" href="tel:<?php echo esc_attr( $tel ); ?>">اتصال</a>
</div>
<?php if ( ! empty( $promo['on'] ) ) : ?>
<div id="elg-promo" hidden>
	<div class="elg-promo-card">
		<button type="button" class="elg-promo-x" id="elg-promo-x" aria-label="إغلاق">×</button>
		<?php if ( ! empty( $promo['url'] ) ) : ?>
			<a class="elg-promo-link" id="elg-promo-link" href="<?php echo esc_url( $promo['url'] ); ?>" target="_blank" rel="noopener noreferrer">
				<img src="<?php echo esc_url( $promo['src'] ); ?>" alt="إعلان" />
			</a>
		<?php else : ?>
			<img src="<?php echo esc_url( $promo['src'] ); ?>" alt="إعلان" />
		<?php endif; ?>
	</div>
</div>
<?php endif; ?>
<script id="elegant-3d-ui-js">
(function(){
  var WA=<?php echo wp_json_encode( $wa ); ?>;
  var TEL="+"+WA;
  var WAURL="https://wa.me/"+WA+"?text=<?php echo esc_js( $msg ); ?>";
  var PROMO=<?php echo wp_json_encode( $promo ); ?>;
  function digits(s){return String(s||"").replace(/\D+/g,"");}
  function fixLinks(){
    if(document.body) document.body.classList.remove("rukn-hide-call");
    document.querySelectorAll('a[href*="wa.me"],a[href*="api.whatsapp"]').forEach(function(a){
      var h=a.getAttribute("href")||"";
      if(h.indexOf("201151481000")!==-1) return;
      a.setAttribute("href", WAURL);
    });
    document.querySelectorAll("a.btn-call,a.fab-call,a.btn-ket_1,a.elg-btn-call").forEach(function(a){
      a.setAttribute("href","tel:"+TEL);
      a.removeAttribute("target");
    });
    document.querySelectorAll('a[href^="tel:"]').forEach(function(a){
      var d=digits(a.getAttribute("href"));
      if(!d) return;
      if(a.classList.contains("btn-wa")||a.classList.contains("fab-wa")||a.classList.contains("elg-btn-wa")){
        a.setAttribute("href", WAURL); return;
      }
      if(/971521300019|0521300019|521300019|522881997|508715513/.test(d) || d.length>=8){
        if(a.classList.contains("btn-call")||a.classList.contains("fab-call")||a.classList.contains("btn-ket_1")||a.classList.contains("elg-btn-call")){
          a.setAttribute("href","tel:"+TEL);
        }
      }
    });
  }
  function bootPromo(){
    if(!PROMO || !PROMO.on) return;
    var key="elg_promo_closed_"+PROMO.id;
    try{ if(localStorage.getItem(key)==="1") return; }catch(e){}
    var box=document.getElementById("elg-promo");
    var x=document.getElementById("elg-promo-x");
    if(!box) return;
    function close(ev){ if(ev) ev.preventDefault(); box.hidden=true; try{ localStorage.setItem(key,"1"); }catch(e){} }
    if(x) x.addEventListener("click", function(ev){ ev.preventDefault(); ev.stopPropagation(); close(ev); });
    box.addEventListener("click", function(ev){ if(ev.target===box) close(ev); });
    setTimeout(function(){ box.hidden=false; }, 900);
  }
  function bootBook(){
    var root=document.getElementById("elg-book");
    if(!root) return;
    var cfg={};
    try{ cfg=JSON.parse(root.getAttribute("data-elg-book")||"{}"); }catch(e){ return; }
    var cat=cfg.catalog||{};
    var options=cat.options||{};
    var fields=cat.fields||[];
    var step=1;
    var chosen={};
    var details={};
    var files=[];
    var contact={name:"",phone:"",email:"",city:(cfg.cities&&cfg.cities[0])||"",address:"",date:"",time:"",notes:""};
    var stepsEl=document.getElementById("elg-book-steps");
    var actEl=document.getElementById("elg-book-actions");
    var msgEl=document.getElementById("elg-book-msg");
    var titleEl=document.getElementById("elg-book-title");
    function showMsg(t, ok){ msgEl.hidden=!t; msgEl.textContent=t||""; msgEl.className="elg-book-msg "+(ok?"ok":"err"); }
    function btn(cls, label, fn){
      var b=document.createElement("button"); b.type="button"; b.className="elg-btn "+cls; b.textContent=label; b.addEventListener("click", fn); return b;
    }
    function fieldHtml(f){
      var wrap=document.createElement("div"); wrap.className="elg-field";
      var lab=document.createElement("label"); lab.textContent=f.label||f.name; wrap.appendChild(lab);
      var el;
      if(f.type==="select"){
        el=document.createElement("select");
        (f.options||[]).forEach(function(opt){ var o=document.createElement("option"); o.value=opt; o.textContent=opt; el.appendChild(o); });
        el.value=details[f.name]||(f.options&&f.options[0])||"";
      } else if(f.type==="textarea"){
        el=document.createElement("textarea"); el.value=details[f.name]||"";
      } else if(f.type==="file"){
        el=document.createElement("input"); el.type="file"; el.accept="image/jpeg,image/png,image/webp"; el.multiple=true;
      } else {
        el=document.createElement("input"); el.type=f.type||"text"; if(f.type==="number") el.min="0"; el.value=details[f.name]||"";
      }
      el.name=f.name;
      el.addEventListener("change", function(){
        if(f.type==="file"){ files=Array.prototype.slice.call(el.files||[]).slice(0,4); }
        else { details[f.name]=el.value; }
      });
      wrap.appendChild(el); return wrap;
    }
    function render(){
      showMsg("", true);
      stepsEl.innerHTML=""; actEl.innerHTML="";
      if(step===1){
        if(titleEl) titleEl.textContent=cat.step1_title||cat.title||"اختر الخدمات المطلوبة";
        Object.keys(options).forEach(function(k){
          var lab=document.createElement("label"); lab.className="elg-opt";
          var inp=document.createElement("input"); inp.type="checkbox"; inp.value=options[k]; inp.checked=!!chosen[k];
          inp.addEventListener("change", function(){ chosen[k]=inp.checked; });
          var sp=document.createElement("span"); sp.textContent=options[k];
          lab.appendChild(inp); lab.appendChild(sp); stepsEl.appendChild(lab);
        });
        actEl.appendChild(btn("elg-btn-next","التالي", function(){
          var n=0; Object.keys(chosen).forEach(function(k){ if(chosen[k]) n++; });
          if(!n){ showMsg("اختر خياراً واحداً على الأقل", false); return; }
          step=2; render();
        }));
      } else if(step===2){
        if(titleEl) titleEl.textContent=cat.title||"تفاصيل الخدمة";
        var picked=document.createElement("p"); picked.style.margin="0 0 10px"; picked.style.fontWeight="700";
        picked.textContent=Object.keys(chosen).filter(function(k){return chosen[k];}).map(function(k){return options[k];}).join("، ");
        stepsEl.appendChild(picked);
        fields.forEach(function(f){ stepsEl.appendChild(fieldHtml(f)); });
        actEl.appendChild(btn("elg-btn-prev","السابق", function(){ step=1; render(); }));
        actEl.appendChild(btn("elg-btn-next","التالي", function(){ step=3; render(); }));
      } else {
        if(titleEl) titleEl.textContent=cat.title||"بيانات التواصل";
        function inp(name, label, type, req){
          var w=document.createElement("div"); w.className="elg-field";
          var l=document.createElement("label"); l.textContent=label+(req?"*":""); w.appendChild(l);
          var el=document.createElement(type==="textarea"?"textarea":(type==="select"?"select":"input"));
          if(type==="select"){
            (cfg[name+"s"]||cfg.cities||[]).forEach(function(c){ var o=document.createElement("option"); o.value=c; o.textContent=c; el.appendChild(o); });
            if(name==="time"){ el.innerHTML=""; (cfg.times||[]).forEach(function(c){ var o=document.createElement("option"); o.value=c; o.textContent=c; el.appendChild(o); }); }
          } else if(type!=="textarea"){ el.type=type||"text"; }
          el.value=contact[name]||"";
          el.addEventListener("input", function(){ contact[name]=el.value; });
          el.addEventListener("change", function(){ contact[name]=el.value; });
          w.appendChild(el); return w;
        }
        stepsEl.appendChild(inp("name","الاسم الكامل","text",true));
        stepsEl.appendChild(inp("phone","رقم الهاتف (واتساب)","tel",true));
        stepsEl.appendChild(inp("email","البريد الإلكتروني","email"));
        stepsEl.appendChild(inp("city","المدينة","select"));
        stepsEl.appendChild(inp("address","العنوان / المنطقة","text"));
        var row=document.createElement("div"); row.className="elg-row2";
        var d=inp("date","تاريخ الخدمة","date"); var t=inp("time","وقت الخدمة","select");
        row.appendChild(d); row.appendChild(t); stepsEl.appendChild(row);
        stepsEl.appendChild(inp("notes","تعليمات أخرى","textarea"));
        actEl.appendChild(btn("elg-btn-prev","السابق", function(){ step=2; render(); }));
        actEl.appendChild(btn("elg-btn-ok","تأكيد الحجز", submit));
      }
    }
    function submit(){
      if(!contact.name || contact.name.trim().length<2 || !contact.phone || digits(contact.phone).length<8){
        showMsg("الاسم ورقم الواتساب مطلوبان", false); return;
      }
      var services=Object.keys(chosen).filter(function(k){return chosen[k];}).map(function(k){return options[k];});
      var payload={
        name:contact.name, phone:contact.phone, email:contact.email, city:contact.city,
        address:contact.address, date:contact.date, time:contact.time, notes:contact.notes,
        services:services, details:details, profile:cfg.profile, postTitle:cfg.postTitle, postUrl:cfg.postUrl
      };
      var fd=new FormData();
      fd.append("nonce", cfg.nonce);
      fd.append("payload", JSON.stringify(payload));
      files.forEach(function(f){ fd.append("photos[]", f); });
      showMsg("جاري إرسال الطلب...", true);
      fetch(cfg.rest, { method:"POST", body:fd, credentials:"same-origin" })
        .then(function(r){ return r.json().then(function(j){ return {ok:r.ok, j:j}; }); })
        .then(function(res){
          if(!res.ok || (res.j && res.j.code)){
            var m=(res.j && (res.j.message||res.j.code)) || "تعذر إرسال الطلب";
            showMsg(typeof m==="string"?m:"تعذر إرسال الطلب", false); return;
          }
          step=9; stepsEl.innerHTML=""; actEl.innerHTML="";
          showMsg("تم إرسال طلبك بنجاح. سنتواصل معك عبر واتساب قريباً.", true);
        })
        .catch(function(){ showMsg("تعذر الاتصال بالخادم. حاول مرة أخرى.", false); });
    }
    render();
  }
  function run(){ fixLinks(); bootPromo(); bootBook(); }
  if(document.readyState==="loading") document.addEventListener("DOMContentLoaded", run);
  else run();
  setTimeout(fixLinks, 500);
})();
</script>
	<?php
}
