<?php
/**
 * CSS と JavaScript の読み込み。
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function oasis_enqueue() {
	// Web フォント（表示をブロックしない形で読み込む）
	wp_enqueue_style(
		'oasis-fonts',
		'https://fonts.googleapis.com/css2?family=Shippori+Mincho:wght@600;700&family=Zen+Kaku+Gothic+New:wght@400;700&display=swap',
		array(),
		null
	);

	wp_enqueue_style( 'oasis-style', OASIS_URI . '/assets/css/style.css', array(), OASIS_VERSION );
	wp_enqueue_style( 'oasis-animations', OASIS_URI . '/assets/css/animations.css', array( 'oasis-style' ), OASIS_VERSION );
	wp_enqueue_style( 'oasis-theme', get_stylesheet_uri(), array( 'oasis-style' ), OASIS_VERSION );

	/*
	 * 画面の動きは機能ごとにファイルを分けています。
	 * base.js が共通の土台（起動のタイミングなど）なので、必ず先に読み込みます。
	 * ほかのファイルは base.js に依存させているので、順番は WordPress が守ります。
	 */
	wp_enqueue_script( 'oasis-base', OASIS_URI . '/assets/js/base.js', array(), OASIS_VERSION, true );
	foreach ( array( 'reveal', 'header', 'drawer', 'filter', 'hours', 'gallery' ) as $oasis_js ) {
		wp_enqueue_script(
			'oasis-' . $oasis_js,
			OASIS_URI . '/assets/js/' . $oasis_js . '.js',
			array( 'oasis-base' ),
			OASIS_VERSION,
			true
		);
	}

	// 営業時間を管理画面の設定から渡す。「本日 営業中」の自動表示に使われます。
	$holidays = array_values( array_filter( array_map( 'trim', preg_split( '/\r\n|\r|\n/', (string) oasis_option( 'holidays', '' ) ) ) ) );
	wp_localize_script( 'oasis-hours', 'OASIS_HOURS', array(
		'open'           => (string) oasis_option( 'open', '11:00' ),
		'close'          => (string) oasis_option( 'close', '19:00' ),
		'closedDays'     => array_map( 'intval', (array) oasis_option( 'closed_days', array( 2 ) ) ),
		'closedDayLabel' => oasis_closed_label(),
		'holidays'       => $holidays,
	) );
}
add_action( 'wp_enqueue_scripts', 'oasis_enqueue' );

/**
 * フォントを非同期で読み込む（表示が止まらないようにするため）。
 */
function oasis_async_font( $html, $handle ) {
	if ( 'oasis-fonts' !== $handle ) {
		return $html;
	}
	$html = str_replace( "media='all'", "media='print' onload=\"this.media='all'\"", $html );
	$html = str_replace( 'media="all"', 'media="print" onload="this.media=\'all\'"', $html );
	return $html;
}
add_filter( 'style_loader_tag', 'oasis_async_font', 10, 2 );

/**
 * 管理画面のスタイルとメディア選択用のスクリプト。
 */
function oasis_admin_enqueue( $hook ) {
	$screen = get_current_screen();
	$is_animal   = $screen && 'animal' === $screen->post_type;
	$is_settings = ( 'settings_page_oasis-settings' === $hook );
	$is_photos   = ( 'settings_page_oasis-photos' === $hook );

	if ( ! $is_animal && ! $is_settings && ! $is_photos ) {
		return;
	}

	wp_enqueue_style( 'oasis-admin', OASIS_URI . '/assets/css/admin.css', array(), OASIS_VERSION );

	if ( $is_animal || $is_photos ) {
		wp_enqueue_media();
		wp_enqueue_script( 'oasis-admin', OASIS_URI . '/assets/js/admin.js', array( 'jquery', 'jquery-ui-sortable' ), OASIS_VERSION, true );
	}
}
add_action( 'admin_enqueue_scripts', 'oasis_admin_enqueue' );
