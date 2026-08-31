<?php
/**
 * あにまるカフェ Oasis テーマ
 *
 * 中身は inc/ に分けています。
 *   setup.php        … テーマの基本設定・画像サイズ
 *   assets.php       … CSS / JavaScript の読み込み
 *   options.php      … サイト共通設定（電話番号・営業時間など）
 *   post-types.php   … 「どうぶつ」の投稿タイプとカテゴリ
 *   sale.php         … 「お迎えできる子」（販売中の個体・性別・金額）
 *   meta-animal.php  … どうぶつの入力欄（写真・データ・お迎えの切り替え）
 *   menu.php         … メニューの「英字」欄
 *   menu-items.php   … メニュー・料金の中身（管理画面から編集）
 *   share.php        … SNSに貼られたときの表示（OGP）と説明文
 *   template-tags.php… テンプレートから呼ぶ共通の関数
 *   importer.php     … 初期データ（21種）の取り込み
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'OASIS_VERSION', '1.8.0' );

/*
 * 固定ページの本文に置いてある「差し替え用の目印」。
 *
 * 初期データの本文にはこの文字がそのまま入っていて、表示するときに
 * 「Oasis サイト設定」の値へ置き換えています（inc/template-tags.php の
 * oasis_fix_legacy_links）。目印を変えるときは、
 * assets/seed/pages/*.html の中身も同じ文字に揃えてください。
 */
define( 'OASIS_PH_ADDRESS', '鹿児島県霧島市国分—————' );
define( 'OASIS_PH_TEL',     '000-000-0000' );
define( 'OASIS_PH_HOURS',   '11:00 – 19:00' );
define( 'OASIS_PH_CLOSED',  '火曜' );
define( 'OASIS_PH_LICENSE', '鹿児島県R8姶保第35号の5（販売）／鹿児島県R8姶保第35号の6（展示）<br>令和8年7月24日〜令和13年7月23日迄' );

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
 * 以前のテーマの情報がデータベースに残っていたら、その場で直す。
 *
 * WordPress は「使用中のテーマ（stylesheet）」と「親テーマ（template）」を
 * 別々に覚えています。前のテーマから乗り換えたときに片方だけ古いままだと、
 * このテーマが「前のテーマの子テーマ」と見なされ、
 * 前のテーマのプログラムまで一緒に読み込まれて動かなくなります。
 *
 * このテーマは子テーマではないので、両方が同じ値になるようにそろえます。
 * （このテーマの子テーマを作っている場合は、何もしません）
 *
 * ※ after_setup_theme などのタイミングでは、前のテーマの読み込みに
 *   間に合わないため、このファイルが読まれた時点で直しています。
 */
if ( function_exists( 'get_option' ) ) {
	$oasis_stylesheet = get_option( 'stylesheet' );
	if ( basename( OASIS_DIR ) === $oasis_stylesheet && get_option( 'template' ) !== $oasis_stylesheet ) {
		update_option( 'template', $oasis_stylesheet );
	}
	unset( $oasis_stylesheet );
}

/*
 * 読み込むファイル（すべて inc フォルダの中にあります）。
 *
 * 1つでも欠けていると「未定義の関数」という分かりにくいエラーになるため、
 * 足りないファイルを控えておいて、あとではっきり知らせるようにしています。
 */
$oasis_missing = array();
foreach ( array( 'setup', 'assets', 'options', 'post-types', 'meta-animal', 'sale', 'photos', 'menu', 'menu-items', 'share', 'template-tags', 'importer' ) as $oasis_part ) {
	$oasis_file = OASIS_DIR . '/inc/' . $oasis_part . '.php';
	if ( file_exists( $oasis_file ) ) {
		require_once $oasis_file;
	} else {
		$oasis_missing[] = 'inc/' . $oasis_part . '.php';
	}
}

if ( $oasis_missing ) {
	$GLOBALS['oasis_missing_files'] = $oasis_missing;

	// 管理画面：お知らせを出す（テーマの切り替えなどはできるようにしておく）
	add_action( 'admin_notices', 'oasis_missing_notice' );

	// サイト表示側：原因がすぐ分かるように、はっきり止める
	add_action( 'template_redirect', 'oasis_missing_die' );
}

/** 管理画面のお知らせ。 */
function oasis_missing_notice() {
	printf(
		'<div class="notice notice-error"><p><strong>あにまるカフェ Oasis テーマ：</strong>'
		. 'テーマのファイルが足りません。<br>探した場所：<code>%s</code><br>'
		. '見つからないファイル：<code>%s</code></p></div>',
		esc_html( OASIS_DIR ),
		esc_html( implode( '</code>, <code>', (array) $GLOBALS['oasis_missing_files'] ) )
	);
}

/** サイト表示側で止める。 */
function oasis_missing_die() {
	$list = '';
	foreach ( (array) $GLOBALS['oasis_missing_files'] as $f ) {
		$list .= '<li><code>' . esc_html( $f ) . '</code></li>';
	}

	wp_die(
		'<h1>テーマのファイルが足りません</h1>'
		. '<p>「あにまるカフェ Oasis」テーマの一部が読み込めませんでした。</p>'
		. '<p><strong>テーマを探した場所</strong><br><code>' . esc_html( OASIS_DIR ) . '</code></p>'
		. '<p><strong>見つからなかったファイル</strong></p><ul>' . $list . '</ul>'
		. '<p><strong>直しかた</strong><br>'
		. 'この場所にある <code>oasis</code> フォルダを削除し、zip を展開し直して置き直してください。<br>'
		. '<code>oasis</code> の中に <code>inc</code> フォルダがあり、その中に7つの php ファイルが'
		. '入っていることをご確認ください。</p>',
		'テーマのファイルが足りません',
		array( 'response' => 500 )
	);
}
