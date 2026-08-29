<?php
/**
 * 「お迎えできる子」（販売中の個体）
 *
 * どうぶつ紹介が「種類」の説明なのに対して、こちらは1匹ずつの登録です。
 * 管理画面から追加・並べ替え・削除ができ、性別と金額もここで入力します。
 *
 * 個体ごとのページは作りません（生体販売ページのカードとして並びます）。
 * 種類を選ぶと、カードがその子の「どうぶつ紹介」ページへのリンクになります。
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** 性別の選択肢。 */
function oasis_sale_sexes() {
	return array(
		''     => '選ばない',
		'male' => 'オス',
		'female' => 'メス',
		'unknown' => '不明',
	);
}

/* ------------------------------------------------------------------ *
 *  投稿タイプ
 * ------------------------------------------------------------------ */
function oasis_register_sale_post_type() {
	register_post_type( 'oasis_sale', array(
		'labels' => array(
			'name'                  => 'お迎えできる子',
			'singular_name'         => 'お迎えできる子',
			'add_new'               => '新規追加',
			'add_new_item'          => 'お迎えできる子を追加',
			'edit_item'             => 'お迎えできる子を編集',
			'new_item'              => '新しい子',
			'view_item'             => '生体販売ページで見る',
			'search_items'          => 'お迎えできる子を検索',
			'not_found'             => 'まだ登録がありません',
			'not_found_in_trash'    => 'ゴミ箱にはありません',
			'featured_image'        => '写真',
			'set_featured_image'    => '写真を選ぶ',
			'remove_featured_image' => '写真を外す',
			'use_featured_image'    => 'この子の写真に使う',
		),
		// 個体ごとのページは作らないので公開URLは持たせない
		'public'              => false,
		'show_ui'             => true,
		'show_in_menu'        => true,
		'publicly_queryable'  => false,
		'exclude_from_search' => true,
		'has_archive'         => false,
		'rewrite'             => false,
		'menu_icon'           => 'dashicons-heart',
		'menu_position'       => 6,
		'supports'            => array( 'title', 'thumbnail', 'page-attributes' ),
		'show_in_rest'        => false,
	) );
}
add_action( 'init', 'oasis_register_sale_post_type' );

/* ------------------------------------------------------------------ *
 *  入力欄
 * ------------------------------------------------------------------ */
function oasis_sale_meta_boxes() {
	add_meta_box( 'oasis-sale-data', 'この子のデータ', 'oasis_box_sale', 'oasis_sale', 'normal', 'high' );
}
add_action( 'add_meta_boxes', 'oasis_sale_meta_boxes' );

function oasis_box_sale( $post ) {
	wp_nonce_field( 'oasis_save_sale', 'oasis_sale_nonce' );

	$animal = (int) get_post_meta( $post->ID, '_oasis_sale_animal', true );
	$sex    = (string) get_post_meta( $post->ID, '_oasis_sale_sex', true );
	$price  = (string) get_post_meta( $post->ID, '_oasis_sale_price', true );
	$note   = (string) get_post_meta( $post->ID, '_oasis_sale_note', true );

	$animals = get_posts( array(
		'post_type'      => 'animal',
		'posts_per_page' => -1,
		'orderby'        => array( 'menu_order' => 'ASC', 'title' => 'ASC' ),
	) );
	?>
	<p class="description" style="margin:0 0 14px">
		上の「タイトル」に出したい名前を入れてください（例：ネザーランドドワーフ、うさこ）。<br>
		写真は右側の「写真」から選びます。並び順は右側の「順序」の数字が小さい子から並びます。
	</p>

	<table class="form-table oasis-table"><tbody>
		<tr>
			<th scope="row"><label for="oasis_sale_animal">種類</label></th>
			<td>
				<select id="oasis_sale_animal" name="oasis_sale_animal">
					<option value="0">選ばない</option>
					<?php foreach ( $animals as $a ) : ?>
						<option value="<?php echo (int) $a->ID; ?>" <?php selected( $animal, $a->ID ); ?>>
							<?php echo esc_html( $a->post_title ); ?>
						</option>
					<?php endforeach; ?>
				</select>
				<p class="description">
					選ぶと、カードを押したときにその子の「どうぶつ紹介」ページが開きます。<br>
					「選ばない」のときは、カードはリンクにならずそのまま表示されます。
				</p>
			</td>
		</tr>
		<tr>
			<th scope="row"><label for="oasis_sale_sex">性別</label></th>
			<td>
				<select id="oasis_sale_sex" name="oasis_sale_sex">
					<?php foreach ( oasis_sale_sexes() as $val => $label ) : ?>
						<option value="<?php echo esc_attr( $val ); ?>" <?php selected( $sex, $val ); ?>>
							<?php echo esc_html( $label ); ?>
						</option>
					<?php endforeach; ?>
				</select>
				<p class="description">「選ばない」にすると、カードに性別の行が出ません。</p>
			</td>
		</tr>
		<tr>
			<th scope="row"><label for="oasis_sale_price">金額</label></th>
			<td>
				<input type="text" id="oasis_sale_price" name="oasis_sale_price" class="regular-text"
				       placeholder="88,000円" value="<?php echo esc_attr( $price ); ?>">
				<p class="description">
					そのままカードに出ます。「88,000円」「応相談」「お問い合わせください」など自由に書けます。<br>
					空にすると、カードに金額の行が出ません。
				</p>
			</td>
		</tr>
		<tr>
			<th scope="row"><label for="oasis_sale_note">ひとこと</label></th>
			<td>
				<textarea id="oasis_sale_note" name="oasis_sale_note" rows="2" class="large-text"
				          placeholder="人なつっこく、抱っこもできます。"><?php echo esc_textarea( $note ); ?></textarea>
				<p class="description">カードの下に小さく出ます。長いときは2行までで切れます。空でも構いません。</p>
			</td>
		</tr>
	</tbody></table>
	<?php
}

/* ------------------------------------------------------------------ *
 *  保存
 * ------------------------------------------------------------------ */
function oasis_save_sale( $post_id ) {
	if ( ! isset( $_POST['oasis_sale_nonce'] )
		|| ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['oasis_sale_nonce'] ) ), 'oasis_save_sale' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	$animal = isset( $_POST['oasis_sale_animal'] ) ? absint( wp_unslash( $_POST['oasis_sale_animal'] ) ) : 0;
	if ( $animal && 'animal' === get_post_type( $animal ) ) {
		update_post_meta( $post_id, '_oasis_sale_animal', $animal );
	} else {
		delete_post_meta( $post_id, '_oasis_sale_animal' );
	}

	$sexes = oasis_sale_sexes();
	$sex   = isset( $_POST['oasis_sale_sex'] ) ? sanitize_key( wp_unslash( $_POST['oasis_sale_sex'] ) ) : '';
	update_post_meta( $post_id, '_oasis_sale_sex', isset( $sexes[ $sex ] ) ? $sex : '' );

	$price = isset( $_POST['oasis_sale_price'] ) ? sanitize_text_field( wp_unslash( $_POST['oasis_sale_price'] ) ) : '';
	update_post_meta( $post_id, '_oasis_sale_price', $price );

	$note = isset( $_POST['oasis_sale_note'] ) ? sanitize_textarea_field( wp_unslash( $_POST['oasis_sale_note'] ) ) : '';
	update_post_meta( $post_id, '_oasis_sale_note', $note );
}
add_action( 'save_post_oasis_sale', 'oasis_save_sale' );

/* ------------------------------------------------------------------ *
 *  一覧画面の列
 * ------------------------------------------------------------------ */
function oasis_sale_columns( $cols ) {
	$new = array();
	foreach ( $cols as $key => $label ) {
		if ( 'title' === $key ) {
			$new['oasis_sale_photo'] = '写真';
		}
		$new[ $key ] = $label;
		if ( 'title' === $key ) {
			$new['oasis_sale_kind']  = '種類';
			$new['oasis_sale_sex']   = '性別';
			$new['oasis_sale_price'] = '金額';
		}
	}
	return $new;
}
add_filter( 'manage_oasis_sale_posts_columns', 'oasis_sale_columns' );

function oasis_sale_column( $col, $post_id ) {
	if ( 'oasis_sale_photo' === $col ) {
		$id = get_post_thumbnail_id( $post_id );
		echo $id ? wp_get_attachment_image( $id, array( 60, 60 ), false, array( 'style' => 'border-radius:8px' ) ) : '—';
		return;
	}
	if ( 'oasis_sale_kind' === $col ) {
		$a = (int) get_post_meta( $post_id, '_oasis_sale_animal', true );
		echo $a ? esc_html( get_the_title( $a ) ) : '—';
		return;
	}
	if ( 'oasis_sale_sex' === $col ) {
		$sexes = oasis_sale_sexes();
		$sex   = (string) get_post_meta( $post_id, '_oasis_sale_sex', true );
		echo ( '' !== $sex && isset( $sexes[ $sex ] ) ) ? esc_html( $sexes[ $sex ] ) : '—';
		return;
	}
	if ( 'oasis_sale_price' === $col ) {
		$price = (string) get_post_meta( $post_id, '_oasis_sale_price', true );
		echo '' !== $price ? esc_html( $price ) : '—';
	}
}
add_action( 'manage_oasis_sale_posts_custom_column', 'oasis_sale_column', 10, 2 );

/* ------------------------------------------------------------------ *
 *  表示（ショートコード [oasis_sale_list]）
 * ------------------------------------------------------------------ */
function oasis_sale_list_shortcode() {
	$posts = get_posts( array(
		'post_type'      => 'oasis_sale',
		'posts_per_page' => -1,
		'orderby'        => array( 'menu_order' => 'ASC', 'date' => 'DESC' ),
	) );

	if ( ! $posts ) {
		return '<p class="sale-empty">いまお迎えいただける子のご案内は準備中です。'
			. 'お気軽にお問い合わせください。</p>';
	}

	$sexes = oasis_sale_sexes();
	$out   = '<div class="grid grid--4" data-reveal-stagger="up">';

	foreach ( $posts as $post ) {
		$animal_id = (int) get_post_meta( $post->ID, '_oasis_sale_animal', true );
		$link      = $animal_id ? get_permalink( $animal_id ) : '';
		$sex       = (string) get_post_meta( $post->ID, '_oasis_sale_sex', true );
		$price     = (string) get_post_meta( $post->ID, '_oasis_sale_price', true );
		$note      = (string) get_post_meta( $post->ID, '_oasis_sale_note', true );
		$cat       = $animal_id ? (string) oasis_animal_meta( $animal_id, 'en_cat' ) : '';

		$thumb_id = get_post_thumbnail_id( $post->ID );
		if ( ! $thumb_id && $animal_id ) {
			$thumb_id = oasis_main_image_id( $animal_id );   // 写真未設定なら種類の写真を借りる
		}
		$thumb = $thumb_id
			? wp_get_attachment_image( $thumb_id, 'oasis-card', false, array(
				'class' => 'card__thumb', 'alt' => $post->post_title,
				'loading' => 'lazy', 'decoding' => 'async',
			) )
			: '<span class="card__thumb card__thumb--sand"></span>';

		$meta = '';
		if ( '' !== $sex && isset( $sexes[ $sex ] ) ) {
			$meta .= '<span class="sale-meta__label">性別</span>'
				. '<span class="sale-meta__value">' . esc_html( $sexes[ $sex ] ) . '</span>';
		}
		if ( '' !== $price ) {
			$meta .= '<span class="sale-meta__label">価格</span>'
				. '<span class="sale-meta__value sale-meta__value--price">' . esc_html( $price ) . '</span>';
		}

		$body = '<span class="card__body">'
			. ( '' !== $cat ? '<span class="card__cat">' . esc_html( $cat ) . '</span>' : '' )
			. '<span class="card__name">' . esc_html( $post->post_title ) . '</span>'
			. ( '' !== $meta ? '<span class="sale-meta">' . $meta . '</span>' : '' )
			. ( '' !== $note ? '<span class="sale-note">' . esc_html( $note ) . '</span>' : '' )
			. '</span>';

		$out .= $link
			? '<a class="card" href="' . esc_url( $link ) . '">' . $thumb . $body . '</a>'
			: '<div class="card">' . $thumb . $body . '</div>';
	}

	return $out . '</div>';
}
add_shortcode( 'oasis_sale_list', 'oasis_sale_list_shortcode' );
