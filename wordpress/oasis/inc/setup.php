<?php
/**
 * テーマの基本設定と、写真の切り抜きサイズ。
 *
 * 写真は「1枚アップロードすれば、必要な大きさに自動で切り抜かれる」形にしています。
 * サイズを変えたあとは、プラグイン「Regenerate Thumbnails」などで
 * 作り直すと既存の写真にも反映されます。
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function oasis_setup() {
	load_theme_textdomain( 'oasis', OASIS_DIR . '/languages' );

	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );          // アイキャッチ画像＝メイン写真
	add_theme_support( 'automatic-feed-links' );
	add_theme_support( 'custom-logo', array(
		'height'      => 160,
		'width'       => 160,
		'flex-height' => true,
		'flex-width'  => true,
	) );
	add_theme_support( 'html5', array( 'search-form', 'gallery', 'caption', 'style', 'script' ) );
	add_theme_support( 'responsive-embeds' );

	register_nav_menus( array(
		'primary' => __( 'ヘッダーのメニュー', 'oasis' ),
		'drawer'  => __( 'スマホのメニュー', 'oasis' ),
		'footer'  => __( 'フッターのメニュー', 'oasis' ),
	) );

	/*
	 * 写真の切り抜きサイズ。true は「指定の縦横で切り抜く」という意味です。
	 * 中央を基準に切り抜かれるので、被写体が中央にくる写真を選んでください。
	 */
	add_image_size( 'oasis-main',    1100, 880, true );  // どうぶつ個別ページのメイン
	add_image_size( 'oasis-main-sm',  700, 560, true );  // 同・スマホ用
	add_image_size( 'oasis-card',     720, 504, true );  // 一覧のカード
	add_image_size( 'oasis-sq',       420, 420, true );  // 丸いリンク
	add_image_size( 'oasis-thumb',    260, 195, true );  // サムネイル
	add_image_size( 'oasis-wide',     900, 670, true );  // トップの大きい写真
	add_image_size( 'oasis-hero',    1800, 900, true );  // トップ最上部
	add_image_size( 'oasis-hero-sm',  900, 700, true );  // 同・スマホ用
	add_image_size( 'oasis-pop',     1000,   0, false ); // POPカードは切り抜かない
}
add_action( 'after_setup_theme', 'oasis_setup' );

/**
 * データベースに残った、以前のテーマの情報を直す。
 *
 * WordPress は「親テーマ（template）」と「使用中のテーマ（stylesheet）」を
 * 別々に覚えています。前のテーマから乗り換えたときに片方だけ古いままだと、
 * このテーマが「前のテーマの子テーマ」と見なされ、
 * ファイルの読み込み先がずれて画面が真っ白になることがあります。
 *
 * このテーマは子テーマではないので、両方が同じ値になるようにそろえます。
 * （このテーマの子テーマを作っている場合は、何もしません）
 */
function oasis_repair_theme_option() {
	$stylesheet = get_option( 'stylesheet' );

	// 使用中のテーマがこのテーマ自身のときだけ直す
	if ( basename( OASIS_DIR ) !== $stylesheet ) {
		return;
	}
	if ( get_option( 'template' ) !== $stylesheet ) {
		update_option( 'template', $stylesheet );
	}
}
add_action( 'after_setup_theme', 'oasis_repair_theme_option', 1 );

/**
 * 管理画面の「画像サイズ」の選択肢に、分かりやすい名前で出す。
 */
function oasis_image_size_names( $sizes ) {
	return array_merge( $sizes, array(
		'oasis-main'  => 'どうぶつ メイン（1100×880）',
		'oasis-card'  => 'どうぶつ カード（720×504）',
		'oasis-sq'    => 'どうぶつ 丸（420×420）',
		'oasis-thumb' => 'どうぶつ サムネイル（260×195）',
		'oasis-pop'   => 'POPカード（幅1000）',
	) );
}
add_filter( 'image_size_names_choose', 'oasis_image_size_names' );

/**
 * WebP をアップロードできるようにする（WordPress 5.8 以降は既定で可、念のため）。
 */
function oasis_mime_types( $mimes ) {
	$mimes['webp'] = 'image/webp';
	return $mimes;
}
add_filter( 'upload_mimes', 'oasis_mime_types' );

/**
 * 抜粋の文字数と末尾。
 */
add_filter( 'excerpt_length', function () { return 70; } );
add_filter( 'excerpt_more',   function () { return '…'; } );
