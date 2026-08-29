<?php
/**
 * ページ下部（フッター・スマホの固定ボタン）
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$oasis_tel   = oasis_tel_link();
$oasis_ig    = oasis_instagram();
// フッター上部の波の色。直前のセクションの背景色に合わせる。
// 色を変えたいテンプレートは get_footer() の前に oasis_set_footer_wave() を呼ぶ。
$oasis_wave  = oasis_footer_wave();
$oasis_hours = sprintf( '%s – %s', oasis_option( 'open', '11:00' ), oasis_option( 'close', '19:00' ) );
$oasis_off   = oasis_closed_label();
?>
</main>

<footer class="site-footer">
	<svg class="wave" viewBox="0 0 1280 66" preserveAspectRatio="none" aria-hidden="true">
		<path d="M0 0H1280V24C1130 62 950 12 760 38 570 64 380 68 220 46 130 34 58 26 0 32Z" fill="<?php echo esc_attr( $oasis_wave ); ?>"/>
	</svg>
	<svg class="deco deco--sprig" style="bottom:-56px;right:-56px;width:320px;height:320px;--deco-rot:-20deg" aria-hidden="true"><use href="#v2-sprig"/></svg>

	<div class="site-footer__inner">
		<div>
			<img class="site-footer__logo" src="<?php echo esc_url( OASIS_URI . '/assets/images/logo-160.jpg' ); ?>"
				alt="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>" width="160" height="160" loading="lazy" decoding="async">
			<p class="site-footer__addr">
				<?php echo esc_html( oasis_option( 'address', '' ) ); ?><br>
				TEL <?php echo esc_html( oasis_option( 'tel_display', '' ) ); ?><br>
				<?php echo esc_html( $oasis_hours ); ?><?php echo $oasis_off ? '／' . esc_html( $oasis_off ) . '定休' : ''; ?>
			</p>
		</div>

		<?php if ( has_nav_menu( 'footer' ) ) : ?>
			<nav class="footer-links" aria-label="フッターメニュー">
				<?php wp_nav_menu( array( 'theme_location' => 'footer', 'container' => false, 'items_wrap' => '%3$s', 'depth' => 1,
					'walker' => new Oasis_Footer_Walker() ) ); ?>
			</nav>
		<?php else : ?>
			<nav class="footer-links" aria-label="フッターメニュー1">
				<?php oasis_footer_links( array( 'about' => '店舗紹介・コンセプト', '__animals' => 'どうぶつ紹介', 'menu' => 'メニュー・料金', 'rules' => 'ご利用ルール' ) ); ?>
			</nav>
			<nav class="footer-links" aria-label="フッターメニュー2">
				<?php oasis_footer_links( array( 'access' => 'アクセス・駐車場', 'news' => 'お知らせ・ブログ' ) ); ?>
			</nav>
		<?php endif; ?>
	</div>

	<?php if ( oasis_option( 'license', '' ) ) : ?>
		<p class="site-footer__legal">第一種動物取扱業　<?php echo esc_html( oasis_option( 'license', '' ) ); ?></p>
	<?php endif; ?>
</footer>

<?php if ( '1' === oasis_option( 'mobile_cta', '1' ) ) : ?>
	<!-- スマホ下部の固定ボタン -->
	<div class="mobile-cta">
		<a class="btn btn--primary" href="<?php echo esc_attr( $oasis_tel ); ?>">電話</a>
		<?php if ( $oasis_ig ) : ?>
			<a class="btn btn--gold" href="<?php echo esc_url( $oasis_ig ); ?>" target="_blank" rel="noopener">Instagram</a>
		<?php endif; ?>
	</div>
<?php endif; ?>

<?php wp_footer(); ?>
</body>
</html>
