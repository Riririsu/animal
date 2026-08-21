<?php
/**
 * ページ上部（<head>・ヘッダー・スマホメニュー）
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$oasis_tel = oasis_tel_link();
$oasis_ig  = oasis_instagram();
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?> class="no-js">
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="theme-color" content="#2E5E3A">
<link rel="profile" href="https://gmpg.org/xfn/11">
<?php wp_head(); ?>
</head>

<body <?php body_class(); ?><?php echo is_front_page() ? ' id="top"' : ''; ?>>
<?php wp_body_open(); ?>

<a class="skip-link" href="#main">本文へスキップ</a>

<?php get_template_part( 'template-parts/svg-defs' ); ?>

<!-- ========== ヘッダー ========== -->
<header class="site-header">
	<div class="site-header__inner">
		<a class="logo" href="<?php echo esc_url( home_url( '/' ) ); ?>">
			<?php if ( has_custom_logo() ) : ?>
				<?php the_custom_logo(); ?>
			<?php else : ?>
				<img src="<?php echo esc_url( OASIS_URI . '/assets/images/logo-160.jpg' ); ?>"
					alt="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>" width="160" height="160" fetchpriority="high">
			<?php endif; ?>
		</a>

		<nav class="gnav" aria-label="メインメニュー">
			<?php
			if ( has_nav_menu( 'primary' ) ) {
				wp_nav_menu( array(
					'theme_location' => 'primary',
					'container'      => false,
					'items_wrap'     => '%3$s',
					'walker'         => new Oasis_Nav_Walker(),
					'depth'          => 1,
				) );
			} else {
				oasis_fallback_nav();
			}
			?>
			<a class="btn btn--primary btn--sm" href="<?php echo esc_attr( $oasis_tel ); ?>">
				<svg width="17" height="17" aria-hidden="true"><use href="#ic-phone"/></svg>
				<span>電話<span class="hide-sp">でお問い合わせ</span></span>
			</a>
		</nav>

		<button class="nav-toggle" type="button" aria-expanded="false" aria-controls="drawer">
			<span class="sr-only">メニューを開く</span>
			<span class="nav-toggle__bar"></span>
			<span class="nav-toggle__bar"></span>
			<span class="nav-toggle__bar"></span>
		</button>
	</div>
</header>

<!-- ========== スマホのメニュー ========== -->
<div class="drawer" id="drawer">
	<div class="drawer__scrim"></div>
	<div class="drawer__panel" role="dialog" aria-modal="true" aria-label="メニュー">
		<button class="drawer__close" type="button">
			<span class="sr-only">メニューを閉じる</span>
			<span aria-hidden="true">&times;</span>
		</button>

		<nav class="drawer__nav" aria-label="メニュー">
			<?php
			if ( has_nav_menu( 'drawer' ) ) {
				wp_nav_menu( array(
					'theme_location' => 'drawer',
					'container'      => false,
					'items_wrap'     => '%3$s',
					'walker'         => new Oasis_Drawer_Walker(),
					'depth'          => 1,
				) );
			} else {
				oasis_fallback_drawer();
			}
			?>
		</nav>

		<div class="drawer__actions">
			<a class="btn btn--primary" href="<?php echo esc_attr( $oasis_tel ); ?>">
				<svg width="17" height="17" aria-hidden="true"><use href="#ic-phone"/></svg>
				<span>電話でお問い合わせ</span>
			</a>
			<?php if ( $oasis_ig ) : ?>
				<a class="btn btn--outline" href="<?php echo esc_url( $oasis_ig ); ?>" target="_blank" rel="noopener">
					<svg width="17" height="17" aria-hidden="true"><use href="#ic-instagram"/></svg>
					<span>Instagram</span>
				</a>
			<?php endif; ?>
		</div>
	</div>
</div>

<main id="main"<?php
	// どうぶつ個別ページでは「お迎えのご相談」の表示状態を持たせる
	if ( is_singular( 'animal' ) ) {
		echo ' data-adopt="' . ( oasis_show_adopt( get_the_ID() ) ? 'on' : 'off' ) . '"';
	}
?>>
