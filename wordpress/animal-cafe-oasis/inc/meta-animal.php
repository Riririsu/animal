<?php
/**
 * どうぶつの入力欄。
 *
 *  ・写真   … メイン写真＝アイキャッチ画像／サブ写真とPOPカードはこの画面から選びます
 *  ・お迎え … 「お迎えのご相談」を出すかどうかのチェックボックス
 *  ・データ … 原産地・大きさ・寿命・食べ物・特徴・豆知識（店内POPカードの内容）
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** 入力欄の定義。1か所で管理して、表示・保存の両方から使います。 */
function oasis_animal_fields() {
	return array(
		'en'      => array( 'label' => '英名・学名',   'type' => 'text',     'ph' => 'Meerkat (Suricata suricatta)' ),
		'en_cat'  => array( 'label' => '英字カテゴリ', 'type' => 'text',     'ph' => 'SMALL ANIMAL' ),
		'lead'    => array( 'label' => 'リード文',     'type' => 'textarea', 'ph' => '一覧と個別ページの先頭に出る紹介文' ),
		'blurb'   => array( 'label' => 'キャッチコピー', 'type' => 'textarea', 'ph' => 'POPカードの丸囲みの文' ),
		'origin'  => array( 'label' => '原産地',       'type' => 'text',     'ph' => 'アフリカ南部（ナミビアなど）' ),
		'size'    => array( 'label' => '大きさ',       'type' => 'text',     'ph' => '体長 約25〜35cm ／体重 約700g〜1kg' ),
		'life'    => array( 'label' => '寿命',         'type' => 'text',     'ph' => '約10〜15年' ),
		'food'    => array( 'label' => '食べ物',       'type' => 'textarea', 'ph' => '昆虫・小動物・植物の根や実など' ),
		'feature' => array( 'label' => '特徴',         'type' => 'textarea', 'ph' => 'POPカードの「特徴」欄' ),
		'trivia'  => array( 'label' => '豆知識',       'type' => 'textarea', 'ph' => 'POPカードの「豆知識」欄' ),
	);
}

function oasis_animal_meta_boxes() {
	add_meta_box( 'oasis-animal-photos', '写真', 'oasis_box_photos', 'animal', 'normal', 'high' );
	add_meta_box( 'oasis-animal-adopt', 'お迎えのご相談', 'oasis_box_adopt', 'animal', 'side', 'high' );
	add_meta_box( 'oasis-animal-data', 'どうぶつのデータ（店内POPカードの内容）', 'oasis_box_data', 'animal', 'normal', 'default' );
}
add_action( 'add_meta_boxes', 'oasis_animal_meta_boxes' );

/* ------------------------------------------------------------------ *
 *  写真
 * ------------------------------------------------------------------ */
function oasis_box_photos( $post ) {
	wp_nonce_field( 'oasis_save_animal', 'oasis_animal_nonce' );

	$subs = oasis_get_id_list( $post->ID, '_oasis_photos' );
	$pop  = (int) get_post_meta( $post->ID, '_oasis_pop', true );
	?>
	<p class="description" style="margin:0 0 14px">
		<strong>メイン写真</strong>は右側の「メイン写真（アイキャッチ画像）」から選びます。
		一覧のカード・個別ページの大きな写真・丸いリンクに使われ、必要な大きさに自動で切り抜かれます。
	</p>

	<div class="oasis-field">
		<label class="oasis-label">サブ写真（何枚でも／並び順は左から）</label>
		<p class="description">個別ページのサムネイルに並びます。押すとメイン写真が入れ替わります。</p>
		<div class="oasis-media" data-multiple="1">
			<input type="hidden" name="oasis_photos" class="oasis-media__value" value="<?php echo esc_attr( implode( ',', $subs ) ); ?>">
			<div class="oasis-media__list"><?php oasis_admin_thumbs( $subs ); ?></div>
			<p>
				<button type="button" class="button oasis-media__pick">写真を選ぶ</button>
				<button type="button" class="button oasis-media__clear">すべて外す</button>
			</p>
		</div>
	</div>

	<div class="oasis-field">
		<label class="oasis-label">店内POPカード</label>
		<p class="description">ページ下部の「店内POPカード」の節に、切り抜かずそのまま載ります。</p>
		<div class="oasis-media" data-multiple="0">
			<input type="hidden" name="oasis_pop" class="oasis-media__value" value="<?php echo esc_attr( $pop ? $pop : '' ); ?>">
			<div class="oasis-media__list"><?php oasis_admin_thumbs( $pop ? array( $pop ) : array() ); ?></div>
			<p>
				<button type="button" class="button oasis-media__pick">画像を選ぶ</button>
				<button type="button" class="button oasis-media__clear">外す</button>
			</p>
		</div>
	</div>
	<?php
}

/** 管理画面用の小さなサムネイル。 */
function oasis_admin_thumbs( $ids ) {
	foreach ( (array) $ids as $id ) {
		$src = wp_get_attachment_image_url( (int) $id, 'thumbnail' );
		if ( $src ) {
			printf(
				'<span class="oasis-media__item" data-id="%d"><img src="%s" alt=""><button type="button" class="oasis-media__remove" aria-label="この写真を外す">&times;</button></span>',
				(int) $id,
				esc_url( $src )
			);
		}
	}
}

/* ------------------------------------------------------------------ *
 *  お迎えのご相談
 * ------------------------------------------------------------------ */
function oasis_box_adopt( $post ) {
	// 新規追加のときは既定でオン
	$raw  = get_post_meta( $post->ID, '_oasis_adopt', true );
	$show = ( '' === $raw ) ? '1' : $raw;
	$all  = oasis_option( 'adopt_enabled', '1' );
	?>
	<p>
		<label>
			<input type="checkbox" name="oasis_adopt" value="1" <?php checked( $show, '1' ); ?>>
			<strong>このどうぶつのページに出す</strong>
		</label>
	</p>
	<p class="description">
		個別ページの下のほうに出る「お迎えのご相談」（電話・Instagram のご案内）の枠です。<br>
		お迎えの取り扱いがない子は、チェックを外してください。
	</p>
	<?php if ( '1' !== $all ) : ?>
		<p class="description" style="color:#b32d2e">
			いまは<strong>サイト全体で非表示</strong>の設定になっているため、
			ここにチェックを入れても表示されません。<br>
			<a href="<?php echo esc_url( admin_url( 'options-general.php?page=oasis-settings' ) ); ?>">Oasis サイト設定</a>
			で「お迎えのご相談を使う」をオンにしてください。
		</p>
	<?php endif; ?>
	<?php
}

/* ------------------------------------------------------------------ *
 *  データ
 * ------------------------------------------------------------------ */
function oasis_box_data( $post ) {
	echo '<table class="form-table oasis-table"><tbody>';
	foreach ( oasis_animal_fields() as $key => $f ) {
		$val = get_post_meta( $post->ID, '_oasis_' . $key, true );
		echo '<tr><th scope="row"><label for="oasis_' . esc_attr( $key ) . '">' . esc_html( $f['label'] ) . '</label></th><td>';
		if ( 'textarea' === $f['type'] ) {
			printf(
				'<textarea id="oasis_%1$s" name="oasis_%1$s" rows="3" class="large-text" placeholder="%2$s">%3$s</textarea>',
				esc_attr( $key ), esc_attr( $f['ph'] ), esc_textarea( $val )
			);
		} else {
			printf(
				'<input type="text" id="oasis_%1$s" name="oasis_%1$s" class="large-text" placeholder="%2$s" value="%3$s">',
				esc_attr( $key ), esc_attr( $f['ph'] ), esc_attr( $val )
			);
		}
		echo '</td></tr>';
	}
	echo '</tbody></table>';
}

/* ------------------------------------------------------------------ *
 *  保存
 * ------------------------------------------------------------------ */
function oasis_save_animal( $post_id ) {
	if ( ! isset( $_POST['oasis_animal_nonce'] )
		|| ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['oasis_animal_nonce'] ) ), 'oasis_save_animal' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	foreach ( oasis_animal_fields() as $key => $f ) {
		$name = 'oasis_' . $key;
		if ( ! isset( $_POST[ $name ] ) ) {
			continue;
		}
		$raw = wp_unslash( $_POST[ $name ] );
		$val = ( 'textarea' === $f['type'] ) ? sanitize_textarea_field( $raw ) : sanitize_text_field( $raw );
		update_post_meta( $post_id, '_oasis_' . $key, $val );
	}

	// サブ写真（カンマ区切りのID）
	$photos = isset( $_POST['oasis_photos'] ) ? sanitize_text_field( wp_unslash( $_POST['oasis_photos'] ) ) : '';
	$ids    = array_filter( array_map( 'absint', explode( ',', $photos ) ) );
	update_post_meta( $post_id, '_oasis_photos', implode( ',', $ids ) );

	// POPカード
	$pop = isset( $_POST['oasis_pop'] ) ? absint( wp_unslash( $_POST['oasis_pop'] ) ) : 0;
	if ( $pop ) {
		update_post_meta( $post_id, '_oasis_pop', $pop );
	} else {
		delete_post_meta( $post_id, '_oasis_pop' );
	}

	// お迎えのご相談（チェックが無いときは POST に入ってこない）
	update_post_meta( $post_id, '_oasis_adopt', isset( $_POST['oasis_adopt'] ) ? '1' : '0' );
}
add_action( 'save_post_animal', 'oasis_save_animal' );

/* ------------------------------------------------------------------ *
 *  一覧画面に「写真」「お迎え」の列を出す
 * ------------------------------------------------------------------ */
function oasis_animal_columns( $cols ) {
	$new = array();
	foreach ( $cols as $key => $label ) {
		if ( 'title' === $key ) {
			$new['oasis_photo'] = '写真';
		}
		$new[ $key ] = $label;
		if ( 'title' === $key ) {
			$new['oasis_adopt'] = 'お迎え';
		}
	}
	return $new;
}
add_filter( 'manage_animal_posts_columns', 'oasis_animal_columns' );

function oasis_animal_column( $col, $post_id ) {
	if ( 'oasis_photo' === $col ) {
		if ( has_post_thumbnail( $post_id ) ) {
			echo get_the_post_thumbnail( $post_id, array( 60, 45 ), array( 'style' => 'border-radius:6px;object-fit:cover' ) );
		} else {
			echo '<span style="color:#b32d2e">未設定</span>';
		}
	}
	if ( 'oasis_adopt' === $col ) {
		$raw = get_post_meta( $post_id, '_oasis_adopt', true );
		$on  = ( '' === $raw ) ? true : ( '1' === $raw );
		echo $on
			? '<span style="color:#1a7f37">● 出す</span>'
			: '<span style="color:#8a8a8a">— 出さない</span>';
	}
}
add_action( 'manage_animal_posts_custom_column', 'oasis_animal_column', 10, 2 );

/* ------------------------------------------------------------------ *
 *  一覧画面から、お迎えの表示をまとめて切り替える
 * ------------------------------------------------------------------ */
function oasis_bulk_actions( $actions ) {
	$actions['oasis_adopt_on']  = 'お迎えのご相談を「出す」にする';
	$actions['oasis_adopt_off'] = 'お迎えのご相談を「出さない」にする';
	return $actions;
}
add_filter( 'bulk_actions-edit-animal', 'oasis_bulk_actions' );

function oasis_handle_bulk( $redirect, $action, $ids ) {
	if ( 'oasis_adopt_on' !== $action && 'oasis_adopt_off' !== $action ) {
		return $redirect;
	}
	$value = ( 'oasis_adopt_on' === $action ) ? '1' : '0';
	foreach ( $ids as $id ) {
		if ( current_user_can( 'edit_post', $id ) ) {
			update_post_meta( $id, '_oasis_adopt', $value );
		}
	}
	return add_query_arg( 'oasis_adopt_done', count( $ids ), $redirect );
}
add_filter( 'handle_bulk_actions-edit-animal', 'oasis_handle_bulk', 10, 3 );

function oasis_bulk_notice() {
	if ( empty( $_GET['oasis_adopt_done'] ) ) {
		return;
	}
	printf(
		'<div class="notice notice-success is-dismissible"><p>%d件の「お迎えのご相談」の表示を変更しました。</p></div>',
		absint( $_GET['oasis_adopt_done'] )
	);
}
add_action( 'admin_notices', 'oasis_bulk_notice' );
