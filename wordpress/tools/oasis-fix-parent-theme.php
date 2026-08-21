<?php
/**
 * Plugin Name: Oasis｜親テーマの設定を直す
 * Description: 「親テーマが見つかりません。colibri-wp 親テーマをインストールしてください」と出る状態を直します。1回ページを開けば直るので、そのあとこのファイルは削除して構いません。
 * Version: 1.1.0
 *
 * ── 使い方 ────────────────────────────────────────────────
 *   1. このファイルを  wp-content/mu-plugins/  に置く
 *      （mu-plugins フォルダが無ければ、その名前で新しく作ってください）
 *   2. サイトか管理画面を1回開く
 *   3. 直ったら、このファイルは削除して構いません
 *
 * ── 何をしているか ────────────────────────────────────────
 *   WordPress は「使用中のテーマ（stylesheet）」と「親テーマ（template）」を
 *   別々に記録しています。前のテーマから乗り換えたときに片方だけ古いまま
 *   残ると、使用中のテーマが「前のテーマの子テーマ」と見なされ、
 *   前のテーマのフォルダが無いと"壊れたテーマ"の扱いになります。
 *
 *   このファイルは、その2つの記録をそろえ直すだけです。
 *   本物の子テーマ（style.css に Template: と書いてあるもの）を使っている
 *   場合は、何もしません。
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'oasis_fix_parent_theme_option' ) ) {

	function oasis_fix_parent_theme_option() {

		// テーマ一覧の古い記録を毎回消す。
		// 前のテーマの情報が残っていて、一覧の表示だけがおかしい場合もあるため。
		delete_site_transient( 'theme_roots' );
		if ( function_exists( 'wp_clean_themes_cache' ) ) {
			wp_clean_themes_cache();
		}

		$stylesheet = get_option( 'stylesheet' );
		$template   = get_option( 'template' );

		// もともとそろっていれば、あとは何もしない
		if ( ! $stylesheet || $stylesheet === $template ) {
			return;
		}

		$dir = WP_CONTENT_DIR . '/themes/' . $stylesheet;

		// 使用中のテーマのフォルダが無い、または中身が足りないときは触らない
		if ( ! is_dir( $dir ) || ! file_exists( $dir . '/index.php' ) ) {
			return;
		}

		// 本物の子テーマなら触らない（style.css に Template: がある）
		$css = $dir . '/style.css';
		if ( file_exists( $css ) ) {
			$head = (string) file_get_contents( $css, false, null, 0, 8192 );
			if ( preg_match( '/^[ \t\/*#@]*Template:\s*\S/mi', $head ) ) {
				return;
			}
		}

		// ここまで来たら、親テーマの記録が古いだけ。そろえる。
		update_option( 'template', $stylesheet );

		if ( function_exists( 'wp_clean_themes_cache' ) ) {
			wp_clean_themes_cache();
		}
		delete_option( 'theme_switched' );
	}
}

oasis_fix_parent_theme_option();
