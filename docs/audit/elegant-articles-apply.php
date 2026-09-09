<?php
/**
 * REST + CSS for unique pool articles.
 * Apply payload from the generator, fill KAYAN metaboxes, Rank Math, tags.
 * Flag: elegant_art_apply_ver
 */
if ( ! defined( 'ABSPATH' ) ) {
	return;
}

const ELEGANT_ART_WA = '201556644443';
const ELEGANT_ART_VER = '20260909b';

add_action(
	'wp_head',
	function () {
		if ( ! is_singular( 'post' ) ) {
			return;
		}
		echo '<style id="elegant-article-ui">
.elg-article{color:var(--text,#1C2E44);line-height:1.85;font-size:17px}
.elg-article h2{color:var(--navy,#0A1F4E);font-size:clamp(20px,4.2vw,28px);margin:1.6em 0 .6em;line-height:1.45}
.elg-article h3{color:var(--navy2,#1A3A6B);font-size:clamp(17px,3.4vw,20px);margin:1.15em 0 .4em}
.elg-article p{margin:0 0 1em}
.elg-article ul,.elg-article ol{margin:0 0 1.1em;padding-inline-start:1.2em}
.elg-article li{margin:0 0 .35em}
.article-hero{background:linear-gradient(135deg,var(--navy,#0A1F4E),var(--blue,#2980D4));color:#fff;border-radius:18px;padding:22px 20px;margin:0 0 1.4em}
.article-hero .hero-label{display:inline-block;background:rgba(255,255,255,.14);border:1px solid rgba(255,255,255,.22);border-radius:999px;padding:4px 12px;font-size:13px;margin-bottom:10px}
.article-hero h2{color:#fff;margin:.2em 0 .45em;font-size:clamp(20px,4.5vw,26px)}
.article-hero p{color:#E8F2FF;margin:0 0 14px}
.hero-buttons,.cta-section .cta-row{display:flex;flex-wrap:wrap;gap:10px}
.cta-button{display:inline-flex;align-items:center;gap:8px;background:var(--wa,#25D366);color:#fff!important;border-radius:12px;padding:12px 16px;font-weight:700;min-height:44px}
.cta-button.ghost{background:rgba(255,255,255,.12);border:1px solid rgba(255,255,255,.3)}
.features-grid,.steps-grid,.elg-stats{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:12px;margin:0 0 1.3em}
.feature-card,.step-card,.elg-stat{background:var(--bg,#F4F8FD);border:1px solid var(--border,#E2EAF5);border-radius:16px;padding:16px}
.feature-card i,.step-card i{color:var(--turq,#2E9DF7);font-size:22px;margin-bottom:8px;display:block}
.step-number{display:inline-flex;align-items:center;justify-content:center;width:36px;height:36px;border-radius:10px;background:linear-gradient(135deg,var(--turq,#2E9DF7),var(--blue,#2980D4));color:#fff;font-weight:800;margin-bottom:8px}
.warning-box,.expert-tip{border-radius:16px;padding:14px 16px;margin:0 0 1.2em;border:1px solid var(--border,#E2EAF5)}
.warning-box{background:#FFF8E8;border-color:#F0CE73}
.expert-tip{background:#F0F7FF;border-color:#B7D9F7}
.cta-section{background:linear-gradient(135deg,var(--navy2,#1A3A6B),var(--footer,#124C9C));color:#fff;border-radius:18px;padding:22px 20px;margin:1.4em 0}
.cta-section h2{color:#fff;margin:0 0 .4em}
.cta-section p{color:#DCE9F8}
.responsive-table{width:100%;overflow-x:auto;-webkit-overflow-scrolling:touch;margin:0 0 1.3em;border:1px solid var(--border,#E2EAF5);border-radius:14px}
.responsive-table table{width:100%;min-width:520px;border-collapse:collapse;background:#fff}
.responsive-table th{background:var(--navy,#0A1F4E);color:#fff;text-align:right;padding:10px 12px;font-weight:700;white-space:nowrap}
.responsive-table td{padding:10px 12px;border-bottom:1px solid var(--border,#E2EAF5);vertical-align:top}
.responsive-table tr:nth-child(even) td{background:#F7FBFF}
.elg-toc{background:#fff;border:1px solid var(--border,#E2EAF5);border-radius:16px;padding:16px;margin:0 0 1.3em}
.elg-toc a{color:var(--blue,#2980D4)}
.author-box{background:var(--bg,#F4F8FD);border-radius:14px;padding:12px 14px;margin:1em 0;color:var(--text2,#3A5068);font-size:15px}
@media(max-width:640px){
.features-grid,.steps-grid,.elg-stats{grid-template-columns:1fr}
.article-hero,.cta-section,.feature-card,.step-card{padding:16px}
.responsive-table table{min-width:100%;font-size:14px}
.responsive-table th,.responsive-table td{padding:8px 9px}
.cta-button{width:100%;justify-content:center}
}
</style>';
	},
	40
);

function elegant_art_can() {
	return current_user_can( 'edit_posts' );
}

function elegant_art_switch_on() {
	return 'on';
}

function elegant_apply_payload( $p ) {
	$id = isset( $p['id'] ) ? (int) $p['id'] : 0;
	if ( ! $id || get_post_type( $id ) !== 'post' ) {
		return new WP_Error( 'bad_id', 'Invalid post', array( 'status' => 400 ) );
	}

	$update = array( 'ID' => $id );
	if ( ! empty( $p['title'] ) ) {
		$update['post_title'] = wp_kses_post( $p['title'] );
	}
	if ( isset( $p['content'] ) ) {
		$update['post_content'] = $p['content'];
	}
	if ( isset( $p['excerpt'] ) ) {
		$update['post_excerpt'] = wp_kses_post( $p['excerpt'] );
	}
	remove_action( 'post_updated', 'wp_save_post_revision', 10 );
	add_filter( 'wp_revisions_to_keep', '__return_zero' );
	wp_defer_term_counting( true );

	wp_update_post( wp_slash( $update ), true );

	$meta = isset( $p['meta'] ) && is_array( $p['meta'] ) ? $p['meta'] : array();
	foreach ( $meta as $key => $val ) {
		update_post_meta( $id, sanitize_key( $key ), $val );
	}

	update_post_meta( $id, 'whatsapp_number', ELEGANT_ART_WA );
	update_post_meta( $id, 'phone_number', '' );
	update_post_meta( $id, 'hide__card__callbutton', 'on' );
	update_post_meta( $id, 'hide__service__callbutton', 'on' );
	update_post_meta( $id, 'hide__floating__call', 'on' );
	update_post_meta( $id, 'hide__card__whatsapp', '' );
	update_post_meta( $id, 'hide__service__whatsapp', '' );
	update_post_meta( $id, 'hide__post__faqs', '' );
	update_post_meta( $id, 'hide_features__section', '' );
	update_post_meta( $id, 'hide_work_steps', '' );
	update_post_meta( $id, 'hide_services_section', '' );
	update_post_meta( $id, 'hide_price_list__section', '' );
	update_post_meta( $id, 'hide_call_section', '' );
	update_post_meta( $id, 'hide__feedback__rating', 'on' );
	update_post_meta( $id, 'articon', '<i class="fas fa-swimming-pool"></i>' );

	if ( ! empty( $p['faqs'] ) && is_array( $p['faqs'] ) ) {
		update_post_meta( $id, 'yourcolor__faqs', $p['faqs'] );
	}
	if ( ! empty( $p['features'] ) && is_array( $p['features'] ) ) {
		update_post_meta( $id, 'post__features__data', $p['features'] );
	}
	if ( ! empty( $p['steps'] ) && is_array( $p['steps'] ) ) {
		update_post_meta( $id, 'post__work_steps__data', $p['steps'] );
	}
	if ( ! empty( $p['services'] ) && is_array( $p['services'] ) ) {
		update_post_meta( $id, 'post__services__data', $p['services'] );
	}
	if ( ! empty( $p['prices'] ) && is_array( $p['prices'] ) ) {
		update_post_meta( $id, 'post__price_list__data', $p['prices'] );
	}
	if ( ! empty( $p['call'] ) && is_array( $p['call'] ) ) {
		$call = $p['call'];
		$call['call_section_whatsapp'] = ELEGANT_ART_WA;
		$call['call_section_phone']    = '';
		update_post_meta( $id, 'post__call_section__data', $call );
	}
	if ( ! empty( $p['card'] ) && is_array( $p['card'] ) ) {
		$card = $p['card'];
		$card['hide__card__callbutton'] = 'on';
		$card['whatsapp_chat_mode']     = 'on';
		update_post_meta( $id, 'post__card__data', $card );
	}
	if ( ! empty( $p['service_request'] ) && is_array( $p['service_request'] ) ) {
		$sr = $p['service_request'];
		$sr['hide__service__callbutton'] = 'on';
		update_post_meta( $id, 'post__service_request__data', $sr );
	}
	if ( ! empty( $p['popover'] ) && is_array( $p['popover'] ) ) {
		update_post_meta( $id, 'post__popover__data', $p['popover'] );
	}
	if ( ! empty( $p['schema_service'] ) && is_array( $p['schema_service'] ) ) {
		$sch = $p['schema_service'];
		$sch['telephone'] = '+201556644443';
		update_post_meta( $id, 'YourColor_Service', $sch );
	}
	if ( ! empty( $p['schema_article'] ) && is_array( $p['schema_article'] ) ) {
		update_post_meta( $id, 'YourColor_Article', $p['schema_article'] );
	}
	if ( ! empty( $p['schema_image'] ) && is_array( $p['schema_image'] ) ) {
		update_post_meta( $id, 'YourColor_ImageObject', $p['schema_image'] );
	}
	if ( ! empty( $p['references'] ) ) {
		update_post_meta( $id, 'references', wp_kses_post( $p['references'] ) );
	}

	// Do not invent ratings.
	update_post_meta(
		$id,
		'YourColor__Rating',
		array(
			'hide_schema' => 'on',
		)
	);

	if ( ! empty( $p['rank_math'] ) && is_array( $p['rank_math'] ) ) {
		$rm = $p['rank_math'];
		if ( ! empty( $rm['title'] ) ) {
			update_post_meta( $id, 'rank_math_title', sanitize_text_field( $rm['title'] ) );
		}
		if ( ! empty( $rm['description'] ) ) {
			update_post_meta( $id, 'rank_math_description', sanitize_text_field( $rm['description'] ) );
		}
		if ( ! empty( $rm['focus'] ) ) {
			update_post_meta( $id, 'rank_math_focus_keyword', sanitize_text_field( $rm['focus'] ) );
		}
	}

	if ( ! empty( $p['tags'] ) && is_array( $p['tags'] ) ) {
		wp_set_post_terms( $id, array_map( 'sanitize_text_field', $p['tags'] ), 'post_tag', false );
	}
	if ( ! empty( $p['city'] ) ) {
		wp_set_object_terms( $id, sanitize_text_field( $p['city'] ), 'cities', false );
	}

	update_post_meta( $id, '_elegant_article_v', ELEGANT_ART_VER );

	wp_defer_term_counting( false );

	$post = get_post( $id );
	$text = wp_strip_all_tags( $post->post_content );
	$words = preg_split( '/\s+/u', trim( $text ), -1, PREG_SPLIT_NO_EMPTY );

	return array(
		'ok'     => true,
		'id'     => $id,
		'link'   => get_permalink( $id ),
		'words'  => is_array( $words ) ? count( $words ) : 0,
		'title'  => $post->post_title,
		'ver'    => ELEGANT_ART_VER,
	);
}

function elegant_apply_article( WP_REST_Request $req ) {
	$p = $req->get_json_params();
	if ( ! is_array( $p ) ) {
		$p = $req->get_params();
	}
	return elegant_apply_payload( $p );
}

add_action(
	'rest_api_init',
	function () {
		register_rest_route(
			'elegant/v1',
			'/apply',
			array(
				'methods'             => 'POST',
				'permission_callback' => 'elegant_art_can',
				'callback'            => 'elegant_apply_article',
			)
		);
		register_rest_route(
			'elegant/v1',
			'/status',
			array(
				'methods'             => 'GET',
				'permission_callback' => 'elegant_art_can',
				'callback'            => function () {
					$q = new WP_Query(
						array(
							'post_type'      => 'post',
							'post_status'    => 'publish',
							'posts_per_page' => 1,
							'fields'         => 'ids',
							'meta_key'       => '_elegant_article_v',
							'meta_value'     => ELEGANT_ART_VER,
						)
					);
					$total = wp_count_posts( 'post' );
					return array(
						'done'    => (int) $q->found_posts,
						'publish' => isset( $total->publish ) ? (int) $total->publish : 0,
						'ver'     => ELEGANT_ART_VER,
					);
				},
			)
		);
	}
);
