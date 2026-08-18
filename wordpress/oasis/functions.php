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

define( 'OASIS_VERSION', '1.0.0' );
define( 'OASIS_DIR', get_template_directory() );
define( 'OASIS_URI', get_template_directory_uri() );

require_once OASIS_DIR . '/inc/setup.php';
require_once OASIS_DIR . '/inc/assets.php';
require_once OASIS_DIR . '/inc/options.php';
require_once OASIS_DIR . '/inc/post-types.php';
require_once OASIS_DIR . '/inc/meta-animal.php';
require_once OASIS_DIR . '/inc/template-tags.php';
require_once OASIS_DIR . '/inc/importer.php';
