<?php
/**
 * 固定ページ（店舗紹介・メニュー・ルール・アクセスなど）
 *
 * 本文は WordPress の編集画面で作ります。
 * 見出しの帯は、ページのタイトルと「抜粋」から自動で作られます。
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
get_header();

while ( have_posts() ) :
	the_post();
	$slug    = get_post_field( 'post_name', get_the_ID() );
	$eyebrow = get_post_meta( get_the_ID(), '_oasis_eyebrow', true );
	if ( ! $eyebrow ) {
		// スラッグが英字ならそれを、日本語なら OASIS を使う
		$eyebrow = preg_match( '/^[a-z0-9\-]+$/', (string) $slug ) ? strtoupper( $slug ) : 'OASIS';
	}
	oasis_page_hero( get_the_title(), $eyebrow, get_the_title(), get_the_excerpt() );
	?>
	<section class="section" style="padding-top:40px">
		<div class="wrap">
			<div class="entry-body" data-reveal="up">
				<?php the_content(); ?>
			</div>
		</div>
	</section>
	<?php
endwhile;
get_footer();
