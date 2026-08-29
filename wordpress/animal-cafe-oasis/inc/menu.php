<?php
/**
 * メニューの「英字」欄。
 *
 * ヘッダー（PC）は英字＋日本語の2段、スマホメニューは日本語＋英字で表示します。
 * その英字を、管理画面（外観 → メニュー）の各項目から直接入力できるようにします。
 *
 *   ナビゲーションラベル … 日本語（表示名）
 *   英字                 … HOME / SALES など。空にすると自動で入ります
 *
 * 以前は「タイトル属性」の欄を日本語（または英字）として使っていました。
 * その形のメニューもそのまま動きますが、下の変換で新しい形に直します。
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* ------------------------------------------------------------------ *
 *  管理画面の入力欄
 * ------------------------------------------------------------------ */
function oasis_menu_en_field( $item_id, $item ) {
	$en = (string) get_post_meta( $item_id, '_oasis_menu_en', true );
	?>
	<p class="field-oasis-en description description-wide">
		<label for="oasis-menu-en-<?php echo (int) $item_id; ?>">
			英字（PCヘッダーの上段／スマホメニューの右に出ます）<br>
			<input type="text" id="oasis-menu-en-<?php echo (int) $item_id; ?>"
			       class="widefat"
			       name="oasis_menu_en[<?php echo (int) $item_id; ?>]"
			       value="<?php echo esc_attr( $en ); ?>"
			       placeholder="HOME">
		</label>
		<span class="description">
			空のままにすると、リンク先から自動で入ります（生体販売なら SALES）。<br>
			上の「ナビゲーションラベル」が日本語の行になります。
		</span>
	</p>
	<?php
}
add_action( 'wp_nav_menu_item_custom_fields', 'oasis_menu_en_field', 10, 2 );

/* ------------------------------------------------------------------ *
 *  保存
 * ------------------------------------------------------------------ */
function oasis_menu_en_save( $menu_id, $menu_item_db_id ) {
	if ( ! current_user_can( 'edit_theme_options' ) ) {
		return;
	}
	// phpcs:ignore WordPress.Security.NonceVerification.Missing -- メニュー画面側で検証済み
	if ( ! isset( $_POST['oasis_menu_en'][ $menu_item_db_id ] ) ) {
		return;
	}
	// phpcs:ignore WordPress.Security.NonceVerification.Missing
	$en = sanitize_text_field( wp_unslash( $_POST['oasis_menu_en'][ $menu_item_db_id ] ) );

	if ( '' === trim( $en ) ) {
		delete_post_meta( $menu_item_db_id, '_oasis_menu_en' );
	} else {
		update_post_meta( $menu_item_db_id, '_oasis_menu_en', $en );
	}
}
add_action( 'wp_update_nav_menu_item', 'oasis_menu_en_save', 10, 2 );

/* ------------------------------------------------------------------ *
 *  表示に使う「英字」と「日本語」を決める
 * ------------------------------------------------------------------ */
/**
 * メニュー項目から、英字と日本語を取り出す。
 *
 * @param object $item    メニュー項目
 * @param bool   $ja_main スマホメニューのように「ラベル＝日本語」で作られている場合は true
 * @return array array( 英字, 日本語 )
 */
function oasis_menu_labels( $item, $ja_main = false ) {
	$field = (string) get_post_meta( $item->ID, '_oasis_menu_en', true );
	$attr  = trim( (string) $item->attr_title );
	$title = (string) $item->title;

	// 1. 「英字」欄に入っていれば、それを使う（ラベルは日本語）
	if ( '' !== trim( $field ) ) {
		return array( $field, $title );
	}

	// 2. 以前のやり方（タイトル属性を使う形）のメニューにも対応する
	if ( '' !== $attr ) {
		return $ja_main ? array( $attr, $title ) : array( $title, $attr );
	}

	// 3. どちらも無ければ、リンク先から英字を補う
	$map = oasis_nav_en_map();
	$key = oasis_nav_url_key( $item->url );
	return array( isset( $map[ $key ] ) ? $map[ $key ] : '', $title );
}

/* ------------------------------------------------------------------ *
 *  以前のメニューを新しい形に直す（1回だけ）
 *
 *  初期データで作ったメニューは、英字を「メニュー名」や「タイトル属性」に
 *  入れていました。英字欄が使えるようになったので、確実に分かるものだけを
 *  新しい形に移します。手で作った項目には触りません。
 * ------------------------------------------------------------------ */
function oasis_menu_en_migrate() {
	if ( get_option( 'oasis_menu_en_done' ) ) {
		return;
	}

	$known = array_flip( array( 'HOME', 'OASIS', 'ANIMALS', 'PRICE', 'RULES', 'SALES', 'ACCESS', 'NEWS' ) );
	$locations = (array) get_theme_mod( 'nav_menu_locations', array() );

	foreach ( array( 'primary' => false, 'drawer' => true ) as $loc => $ja_main ) {
		if ( empty( $locations[ $loc ] ) ) {
			continue;
		}
		$items = wp_get_nav_menu_items( $locations[ $loc ] );
		if ( ! $items ) {
			continue;
		}
		foreach ( $items as $item ) {
			if ( get_post_meta( $item->ID, '_oasis_menu_en', true ) ) {
				continue;                       // すでに新しい形
			}
			$attr  = trim( (string) $item->attr_title );
			$title = (string) $item->title;

			// スマホ：タイトル属性が英字 → 英字欄へ移す（ラベルはそのまま）
			if ( $ja_main ) {
				if ( '' !== $attr && isset( $known[ $attr ] ) ) {
					update_post_meta( $item->ID, '_oasis_menu_en', $attr );
					oasis_menu_clear_attr( $item->ID );
				}
				continue;
			}

			// ヘッダー：ラベルが英字・タイトル属性が日本語 → 入れ替える
			if ( '' !== $attr && isset( $known[ $title ] ) ) {
				update_post_meta( $item->ID, '_oasis_menu_en', $title );
				wp_update_post( array( 'ID' => $item->ID, 'post_title' => $attr ) );
				oasis_menu_clear_attr( $item->ID );
			}
		}
	}

	update_option( 'oasis_menu_en_done', 1 );
}
add_action( 'admin_init', 'oasis_menu_en_migrate' );

/** メニュー項目のタイトル属性を空にする。 */
function oasis_menu_clear_attr( $item_id ) {
	update_post_meta( $item_id, '_menu_item_attr_title', '' );
}
