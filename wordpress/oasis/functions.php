<?php
/**
 * あにまるカフェ Oasis テーマ
 *
 * 中身は inc/ に分けています。
 *   setup.php        … テーマの基本設定・画像サイズ
 *   assets.php       … CSS / JavaScript の読み込み
 *   options.php      … サイト共通設定（電話番号・営業時間など）
 *   post-types.php   … 「どうぶつ」の投稿タイプとカテゴリ
 *   meta-animal.php  … どうぶつの入力欄（写真・データ・お迎えの切り替え）
 *   template-tags.php… テンプレートから呼ぶ共通の関数
 *   importer.php     … 初期データ（22種）の取り込み
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'OASIS_VERSION', '1.0.1' );

/*
 * テーマの場所。
 *
 * get_template_directory() は「親テーマ」の場所を返す関数で、
 * 以前使っていたテーマの情報がデータベースに残っていると、
 * まったく別のフォルダを指してしまうことがあります。
 * ここではこのファイル自身の場所（__DIR__）から求めているので、
 * どんな環境でも必ずこのテーマのフォルダになります。
 */
define( 'OASIS_DIR', __DIR__ );

if ( ! function_exists( 'oasis_theme_uri' ) ) {
	/** テーマフォルダの URL を、フォルダの場所から組み立てる。 */
	function oasis_theme_uri() {
		$dir     = wp_normalize_path( OASIS_DIR );
		$content = wp_normalize_path( WP_CONTENT_DIR );

		if ( 0 === strpos( $dir, $content ) ) {
			return untrailingslashit( content_url( substr( $dir, strlen( $content ) ) ) );
		}
		// 想定外の場所に置かれている場合の保険
		return untrailingslashit( get_stylesheet_directory_uri() );
	}
}
define( 'OASIS_URI', oasis_theme_uri() );

/*
 * 読み込むファイル。1つでも欠けていると画面が真っ白になってしまうため、
 * 「無いものは飛ばして、管理画面にお知らせを出す」ようにしています。
 */
$oasis_missing = array();
foreach ( array( 'setup', 'assets', 'options', 'post-types', 'meta-animal', 'template-tags', 'importer' ) as $oasis_part ) {
	$oasis_file = OASIS_DIR . '/inc/' . $oasis_part . '.php';
	if ( file_exists( $oasis_file ) ) {
		require_once $oasis_file;
	} else {
		$oasis_missing[] = 'inc/' . $oasis_part . '.php';
	}
}

if ( $oasis_missing ) {
	$GLOBALS['oasis_missing_files'] = $oasis_missing;
	add_action( 'admin_notices', function () {
		printf(
			'<div class="notice notice-error"><p><strong>あにまるカフェ Oasis テーマ：</strong>次のファイルが見つかりません。テーマを入れ直してください。<br><code>%s</code></p></div>',
			esc_html( implode( '</code><br><code>', (array) $GLOBALS['oasis_missing_files'] ) )
		);
	} );
}
