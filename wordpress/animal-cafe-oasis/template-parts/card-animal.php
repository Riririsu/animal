<?php
/**
 * どうぶつ一覧のカード1枚。
 * 写真はメイン写真（アイキャッチ画像）を使い、720×504 に自動で切り抜かれます。
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$oasis_id    = get_the_ID();
$oasis_img   = oasis_main_image_id( $oasis_id );
$oasis_index = (int) get_query_var( 'oasis_card_index', 0 );
$oasis_cats  = wp_get_object_terms( $oasis_id, 'animal_cat', array( 'fields' => 'slugs' ) );
$oasis_cat   = ( ! is_wp_error( $oasis_cats ) && $oasis_cats ) ? implode( ' ', $oasis_cats ) : '';
$oasis_en    = oasis_animal_meta( $oasis_id, 'en' );
$oasis_label = $oasis_en ? trim( explode( '(', $oasis_en )[0] ) : '';
?>
<a class="card" href="<?php the_permalink(); ?>" data-cat="<?php echo esc_attr( $oasis_cat ); ?>">
	<?php
	oasis_image( $oasis_img, 'oasis-card', 'card__thumb', array(
		'alt'      => get_the_title(),
		'fallback' => '',
		'lazy'     => $oasis_index >= 4,
	) );
	?>
	<span class="card__body">
		<?php if ( oasis_animal_meta( $oasis_id, 'en_cat' ) ) : ?>
			<span class="card__cat"><?php echo esc_html( oasis_animal_meta( $oasis_id, 'en_cat' ) ); ?></span>
		<?php endif; ?>
		<span class="card__name"><?php the_title(); ?></span>
		<?php if ( $oasis_label ) : ?>
			<span class="tag tag--plain"><?php echo esc_html( $oasis_label ); ?></span>
		<?php endif; ?>
	</span>
</a>
