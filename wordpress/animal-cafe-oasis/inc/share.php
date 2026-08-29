<?php
/**
 * SNSに貼られたときの表示（OGP）と、Googleの検索結果に出る文章。
 *
 * LINE・Instagram・X などにURLを貼ると、その下に
 * 「見出し・説明文・画像」が出ます。その中身をここで作ります。
 *
 *   説明文 … ページごとに、いちばん適した文章を選びます
 *            （どうぶつならリード文、お知らせなら本文の冒頭など）
 *            見つからないときは「Oasis サイト設定 → サイトの紹介文」を使います
 *   画像   … その記事の写真 → 「Oasis 写真の差し替え → URLを貼ったときに出る画像」
 *            → サイトアイコン の順に探します
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** 空白を詰めて、指定の長さで切る。 */
function oasis_trim_text( $text, $limit = 120 ) {
	$text = wp_strip_all_tags( strip_shortcodes( (string) $text ) );
	$text = trim( preg_replace( '/\s+/u', ' ', $text ) );
	if ( '' === $text ) {
		return '';
	}
	if ( function_exists( 'mb_strlen' ) && mb_strlen( $text ) > $limit ) {
		return mb_substr( $text, 0, $limit ) . '…';
	}
	return $text;
}

/** このページの説明文。 */
function oasis_share_description() {
	$fallback = (string) oasis_option( 'site_desc', '' );

	if ( is_front_page() ) {
		return oasis_trim_text( $fallback );
	}

	if ( is_singular( 'animal' ) ) {
		$lead = (string) oasis_animal_meta( get_the_ID(), 'lead' );
		if ( '' !== trim( $lead ) ) {
			return oasis_trim_text( $lead );
		}
	}

	if ( is_singular() ) {
		$excerpt = has_excerpt() ? get_the_excerpt() : '';
		if ( '' !== trim( (string) $excerpt ) ) {
			return oasis_trim_text( $excerpt );
		}
		$body = oasis_trim_text( get_post_field( 'post_content', get_the_ID() ) );
		if ( '' !== $body ) {
			return $body;
		}
	}

	if ( is_post_type_archive( 'animal' ) ) {
		return '店内で暮らすどうぶつたちをご紹介します。ふれあいの可否は当日スタッフにお尋ねください。';
	}

	if ( is_tax( 'animal_cat' ) ) {
		$term = get_queried_object();
		if ( $term && ! is_wp_error( $term ) ) {
			$note = trim( (string) $term->description );
			return oasis_trim_text(
				$term->name . ( '' !== $note ? '（' . $note . '）' : '' ) . 'のどうぶつをご紹介します。'
			);
		}
	}

	if ( is_home() ) {
		return '新しい仲間の入荷、臨時休業、店内の様子などをお届けします。';
	}

	return oasis_trim_text( $fallback );
}

/** このページの見出し。 */
function oasis_share_title() {
	if ( is_front_page() ) {
		$name = get_bloginfo( 'name' );
		$sub  = get_bloginfo( 'description' );
		return $sub ? $name . '｜' . $sub : $name;
	}
	return wp_get_document_title();
}

/** このページの画像URL。無ければ空文字。 */
function oasis_share_image() {
	$id = 0;

	if ( is_singular() ) {
		$id = (int) get_post_thumbnail_id();
		if ( ! $id && is_singular( 'animal' ) ) {
			$id = (int) oasis_main_image_id( get_the_ID() );
		}
	}
	if ( ! $id ) {
		$id = (int) oasis_photo_id( 'og-image' );
	}

	if ( $id ) {
		$src = wp_get_attachment_image_src( $id, 'full' );
		if ( $src && ! empty( $src[0] ) ) {
			return $src[0];
		}
	}

	$icon = get_site_icon_url( 512 );
	return $icon ? $icon : '';
}

/** このページのURL。 */
function oasis_share_url() {
	if ( is_front_page() ) {
		return home_url( '/' );
	}
	if ( is_singular() ) {
		return get_permalink();
	}
	if ( is_post_type_archive( 'animal' ) ) {
		return (string) get_post_type_archive_link( 'animal' );
	}
	if ( is_tax() || is_category() || is_tag() ) {
		$link = get_term_link( get_queried_object() );
		return is_wp_error( $link ) ? home_url( '/' ) : $link;
	}
	if ( is_home() ) {
		$page = (int) get_option( 'page_for_posts' );
		return $page ? get_permalink( $page ) : home_url( '/' );
	}
	return home_url( '/' );
}

/**
 * <head> に書き出す。
 * 他のSEOプラグインが同じタグを出しているときは、二重にならないよう何もしません。
 */
function oasis_share_meta() {
	if ( is_404() || is_search() ) {
		return;
	}
	if ( defined( 'WPSEO_VERSION' ) || defined( 'RANK_MATH_VERSION' ) || defined( 'SEOPRESS_VERSION' ) ) {
		return;
	}

	$title = oasis_share_title();
	$desc  = oasis_share_description();
	$image = oasis_share_image();
	$url   = oasis_share_url();

	echo "\n\t<!-- SNSに貼られたときの表示（Oasis テーマ） -->\n";

	if ( '' !== $desc ) {
		printf( "\t<meta name=\"description\" content=\"%s\">\n", esc_attr( $desc ) );
	}
	printf( "\t<link rel=\"canonical\" href=\"%s\">\n", esc_url( $url ) );

	printf( "\t<meta property=\"og:type\" content=\"%s\">\n", is_singular( 'post' ) ? 'article' : 'website' );
	printf( "\t<meta property=\"og:site_name\" content=\"%s\">\n", esc_attr( get_bloginfo( 'name' ) ) );
	printf( "\t<meta property=\"og:title\" content=\"%s\">\n", esc_attr( $title ) );
	if ( '' !== $desc ) {
		printf( "\t<meta property=\"og:description\" content=\"%s\">\n", esc_attr( $desc ) );
	}
	printf( "\t<meta property=\"og:url\" content=\"%s\">\n", esc_url( $url ) );
	echo "\t<meta property=\"og:locale\" content=\"ja_JP\">\n";

	if ( '' !== $image ) {
		printf( "\t<meta property=\"og:image\" content=\"%s\">\n", esc_url( $image ) );
		echo "\t<meta name=\"twitter:card\" content=\"summary_large_image\">\n";
	} else {
		echo "\t<meta name=\"twitter:card\" content=\"summary\">\n";
	}
}
add_action( 'wp_head', 'oasis_share_meta', 1 );
