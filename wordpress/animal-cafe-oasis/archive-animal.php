<?php
/**
 * どうぶつ一覧
 *
 * 絞り込みチップは「どうぶつのカテゴリ」から自動でつくられます。
 * カテゴリを増やせば、チップも自動で増えます。
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
get_header();

$oasis_terms = get_terms( array(
	'taxonomy'   => 'animal_cat',
	'hide_empty' => true,
	'orderby'    => 'term_id',   // 作った順（爬虫類 → 鳥類 → 小動物 → 犬・猫・サル）
) );
if ( is_wp_error( $oasis_terms ) ) {
	$oasis_terms = array();
}
$oasis_total = wp_count_posts( 'animal' );
$oasis_total = isset( $oasis_total->publish ) ? (int) $oasis_total->publish : 0;

oasis_page_hero(
	'どうぶつ紹介',
	'ANIMALS',
	'どうぶつ紹介',
	sprintf(
		'店内で暮らす%sどうぶつをご紹介します。体調や日によって、ふれあいをお休みしている場合があります。ふれあいの可否は当日スタッフにお尋ねください。',
		$oasis_total ? $oasis_total . '種の' : ''
	)
);
?>

	<section class="section" style="padding-top:44px">
		<div class="wrap">

			<?php if ( $oasis_terms ) : ?>
				<div class="chips" role="group" aria-label="どうぶつの絞り込み"
					data-filter-group data-filter-target="#animal-list" data-reveal="up">
					<button class="chip" type="button" data-filter="all" aria-pressed="true">すべて</button>
					<?php foreach ( $oasis_terms as $t ) : ?>
						<button class="chip" type="button" data-filter="<?php echo esc_attr( $t->slug ); ?>" aria-pressed="false">
							<?php echo esc_html( $t->name ); ?>
						</button>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>

			<?php if ( have_posts() ) : ?>
				<div class="grid grid--4" id="animal-list" aria-live="polite" style="margin-top:34px" data-reveal-stagger="up">
					<?php
					$oasis_i = 0;
					while ( have_posts() ) :
						the_post();
						set_query_var( 'oasis_card_index', $oasis_i );
						get_template_part( 'template-parts/card', 'animal' );
						$oasis_i++;
					endwhile;
					?>
				</div>
			<?php else : ?>
				<div class="panel" style="margin-top:34px">
					<p class="lead" style="margin:0">まだどうぶつが登録されていません。</p>
					<?php if ( current_user_can( 'manage_options' ) ) : ?>
						<p class="note" style="color:var(--c-text)">
							管理画面の
							<a href="<?php echo esc_url( admin_url( 'options-general.php?page=oasis-settings' ) ); ?>">Oasis サイト設定</a>
							から「初期データを取り込む」を押すと、22種をまとめて登録できます。
						</p>
					<?php endif; ?>
				</div>
			<?php endif; ?>

		</div>
	</section>

<?php
get_footer();
