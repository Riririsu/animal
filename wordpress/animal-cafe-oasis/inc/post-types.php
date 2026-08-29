<?php
/**
 * 「どうぶつ」の投稿タイプと、その絞り込み用カテゴリ。
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function oasis_register_post_types() {

	register_post_type( 'animal', array(
		'labels' => array(
			'name'               => 'どうぶつ',
			'singular_name'      => 'どうぶつ',
			'add_new'            => '新規追加',
			'add_new_item'       => 'どうぶつを追加',
			'edit_item'          => 'どうぶつを編集',
			'new_item'           => '新しいどうぶつ',
			'view_item'          => 'どうぶつを見る',
			'search_items'       => 'どうぶつを検索',
			'not_found'          => 'どうぶつが見つかりません',
			'featured_image'     => 'メイン写真',
			'set_featured_image' => 'メイン写真を選ぶ',
			'remove_featured_image' => 'メイン写真を外す',
			'use_featured_image' => 'メイン写真に使う',
		),
		'public'        => true,
		'has_archive'   => 'animals',                 // 一覧は /animals/
		'rewrite'       => array( 'slug' => 'animal', 'with_front' => false ),
		'menu_icon'     => 'dashicons-pets',
		'menu_position' => 5,
		'supports'      => array( 'title', 'thumbnail', 'page-attributes' ),
		'show_in_rest'  => false,                     // 入力欄を分かりやすくするため従来の編集画面を使う
		'taxonomies'    => array( 'animal_cat' ),
	) );

	register_taxonomy( 'animal_cat', 'animal', array(
		'labels' => array(
			'name'          => 'どうぶつのカテゴリ',
			'singular_name' => 'カテゴリ',
			'add_new_item'  => 'カテゴリを追加',
			'edit_item'     => 'カテゴリを編集',
			'search_items'  => 'カテゴリを検索',
			'all_items'     => 'すべてのカテゴリ',
		),
		'public'            => true,
		'hierarchical'      => true,
		'show_admin_column' => true,
		'show_in_rest'      => true,
		'rewrite'           => array( 'slug' => 'animal-cat', 'with_front' => false ),
	) );
}
add_action( 'init', 'oasis_register_post_types' );

/**
 * お知らせ（標準の「投稿」）の初期カテゴリ。
 * すでにあるものは作りません。
 */
function oasis_default_terms() {
	if ( get_option( 'oasis_terms_done' ) ) {
		return;
	}
	$news = array( '入荷' => 'arrival', '休業' => 'closed', '店内の様子' => 'shop' );
	foreach ( $news as $name => $slug ) {
		if ( ! term_exists( $slug, 'category' ) ) {
			wp_insert_term( $name, 'category', array( 'slug' => $slug ) );
		}
	}

	foreach ( oasis_animal_cats() as $slug => $c ) {
		if ( ! term_exists( $slug, 'animal_cat' ) ) {
			wp_insert_term( $c['name'], 'animal_cat', array( 'slug' => $slug, 'description' => $c['desc'] ) );
		}
	}

	update_option( 'oasis_terms_done', 1 );
}
add_action( 'admin_init', 'oasis_default_terms' );

/**
 * どうぶつのカテゴリの初期値。
 *
 * 「説明」は、トップページの丸いリンクの下に出る一言です。
 * 以前はここに「11種」のように件数を出していましたが、
 * どうぶつが増えると古くなるため、代表的な種類の名前にしています。
 * 管理画面（どうぶつ → カテゴリ）から自由に書き換えられます。
 */
function oasis_animal_cats() {
	return array(
		'reptile' => array( 'name' => '爬虫類',        'desc' => 'ヘビ・トカゲ・リクガメ' ),
		'bird'    => array( 'name' => '鳥類・インコ',  'desc' => 'アオメキバタン' ),
		'small'   => array( 'name' => '小動物・うさぎ', 'desc' => 'うさぎ・チンチラ・ミーアキャット' ),
		'dogcat'  => array( 'name' => '犬・猫・サル',  'desc' => 'ラグドール・ブリー・マーモセット' ),
	);
}

/**
 * すでにあるサイト向け：カテゴリの「説明」が空のときだけ、初期値を入れる。
 * お店が書き換えた説明は上書きしません。一度だけ実行されます。
 */
function oasis_fill_cat_descriptions() {
	if ( get_option( 'oasis_cat_desc_done' ) ) {
		return;
	}
	foreach ( oasis_animal_cats() as $slug => $c ) {
		$term = get_term_by( 'slug', $slug, 'animal_cat' );
		if ( $term && ! is_wp_error( $term ) && '' === trim( (string) $term->description ) ) {
			wp_update_term( $term->term_id, 'animal_cat', array( 'description' => $c['desc'] ) );
		}
	}
	update_option( 'oasis_cat_desc_done', 1 );
}
add_action( 'admin_init', 'oasis_fill_cat_descriptions' );

/**
 * 一覧の並び順。「順序」（page-attributes）が小さいものを先に、
 * 同じなら新しいものを先に出します。
 */
function oasis_animal_archive_order( $query ) {
	if ( is_admin() || ! $query->is_main_query() ) {
		return;
	}
	if ( $query->is_post_type_archive( 'animal' ) || $query->is_tax( 'animal_cat' ) ) {
		$query->set( 'orderby', array( 'menu_order' => 'ASC', 'date' => 'DESC' ) );
		$query->set( 'posts_per_page', -1 );   // 絞り込みを画面内で行うため全件出す
	}
}
add_action( 'pre_get_posts', 'oasis_animal_archive_order' );

/**
 * テーマを有効にしたときに、URL の設定を作り直す。
 */
function oasis_flush_rewrite() {
	oasis_register_post_types();
	flush_rewrite_rules();
}
add_action( 'after_switch_theme', 'oasis_flush_rewrite' );
