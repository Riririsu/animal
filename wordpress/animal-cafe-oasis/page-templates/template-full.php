<?php
/**
 * Template Name: デザインそのまま（全幅）
 *
 * 静的サイトから取り込んだページ用のテンプレートです。
 * 見出しの帯はページのタイトルと「抜粋」から作り、
 * 本文の HTML はそのまま出すので、飾りや帯のデザインが崩れません。
 *
 * ふつうの文章だけのページには、標準のテンプレートをお使いください。
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
get_header();

while ( have_posts() ) :
	the_post();

	$eyebrow = get_post_meta( get_the_ID(), '_oasis_eyebrow', true );
	oasis_page_hero(
		get_the_title(),
		$eyebrow ? $eyebrow : strtoupper( get_post_field( 'post_name', get_the_ID() ) ),
		get_the_title(),
		get_the_excerpt()
	);

	the_content();
endwhile;

get_footer();
