<?php
/**
 * ページが見つからないとき
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
get_header();
oasis_page_hero( 'ページが見つかりません', '404', 'ページが見つかりません', 'お探しのページは移動または削除された可能性があります。' );
?>
	<section class="section" style="padding-top:40px">
		<div class="wrap" style="display:flex;justify-content:center;gap:14px;flex-wrap:wrap">
			<a class="btn btn--primary" href="<?php echo esc_url( home_url( '/' ) ); ?>">
				トップページへ <?php echo oasis_arrow( '#fff' ); // phpcs:ignore ?>
			</a>
			<a class="btn btn--outline-green" href="<?php echo esc_url( get_post_type_archive_link( 'animal' ) ); ?>">
				どうぶつ紹介へ <?php echo oasis_arrow( '#fff' ); // phpcs:ignore ?>
			</a>
		</div>
	</section>
<?php
get_footer();
