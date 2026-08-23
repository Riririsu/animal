<?php
/**
 * サイト共通設定（設定 → Oasis サイト設定）
 *
 * 電話番号や営業時間など、全ページに何度も出てくる情報を1か所で直せるようにしています。
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** 設定の一覧。既定値もここに書いています。 */
function oasis_option_fields() {
	return array(
		'shop' => array(
			'title'  => 'お店の情報',
			'fields' => array(
				'tel_display' => array( 'label' => '電話番号（表示用）', 'type' => 'text', 'default' => '000-000-0000' ),
				'tel_link'    => array( 'label' => '電話番号（発信用・数字のみ）', 'type' => 'text', 'default' => '0000000000',
				                        'desc' => 'ハイフンなしで入力してください。ボタンを押したときにかかる番号です。' ),
				'address'     => array( 'label' => '住所', 'type' => 'text', 'default' => '鹿児島県霧島市国分—————' ),
				'instagram'   => array( 'label' => 'Instagram の URL', 'type' => 'url', 'default' => '',
				                        'desc' => '空のままだと Instagram のボタンは表示されません。' ),
				'license'     => array( 'label' => '第一種動物取扱業の表記', 'type' => 'textarea',
				                        'default' => '鹿児島県R8姶保第35号の5（販売）／鹿児島県R8姶保第35号の6（展示）　令和8年7月24日〜令和13年7月23日迄' ),
				'map_embed'   => array( 'label' => 'Googleマップの埋め込みコード', 'type' => 'textarea', 'default' => '',
				                        'desc' => 'Googleマップ →「共有」→「地図を埋め込む」で出てくる &lt;iframe&gt; をそのまま貼ってください。' ),
			),
		),
		'hours' => array(
			'title'  => '営業時間',
			'fields' => array(
				'open'        => array( 'label' => '開店時刻', 'type' => 'time', 'default' => '11:00' ),
				'close'       => array( 'label' => '閉店時刻', 'type' => 'time', 'default' => '19:00' ),
				'closed_days' => array( 'label' => '定休日', 'type' => 'days', 'default' => array( '2' ) ),
				'holidays'    => array( 'label' => '臨時休業日', 'type' => 'textarea', 'default' => '',
				                        'desc' => '1行に1つ、2026-08-20 の形で書いてください。' ),
				'price'       => array( 'label' => '入場料の表記', 'type' => 'text', 'default' => '1,000円（2ドリンク付）' ),
				'price_rows'  => array( 'label' => 'トップに出す料金の一覧', 'type' => 'textarea',
				                        'default' => "入場料（2ドリンク付・1時間）|1,000円\nドリンク|400円〜\nご飯物・パスタ|700円〜\nケーキ|450円〜",
				                        'desc' => '1行に1つ。<code>項目名|価格</code> のように、たて棒（|）で区切ってください。' ),
				'price_note'  => array( 'label' => 'トップの料金の下に出す注意書き', 'type' => 'textarea',
				                        'default' => 'お食事のみ・生体のお買い上げのみの方は入場料は頂きません。ゾウガメの餌やり体験は1回100円。' ),
			),
		),
		'display' => array(
			'title'  => '表示の切り替え',
			'fields' => array(
				'adopt_enabled' => array( 'label' => 'お迎えのご相談を使う', 'type' => 'check', 'default' => '1',
				                          'desc' => 'オフにすると、どうぶつ個別ページの「お迎えのご相談」の枠を<strong>全ページまとめて</strong>隠します。<br>どの子に出すかは、どうぶつごとの編集画面で1件ずつ決められます。' ),
				'adopt_text'    => array( 'label' => 'お迎えのご相談の本文', 'type' => 'textarea',
				                          'default' => 'お迎えできる子は時期によって変わります。飼育に必要な環境や費用を、スタッフが一つずつご説明しますので、まずはお気軽にお問い合わせください。' ),
				'mobile_cta'    => array( 'label' => 'スマホ下部の固定ボタンを出す', 'type' => 'check', 'default' => '1' ),
			),
		),
	);
}

/** 設定を1つ取り出す。 */
function oasis_option( $key, $fallback = '' ) {
	$all = get_option( 'oasis_settings', array() );
	if ( isset( $all[ $key ] ) && '' !== $all[ $key ] ) {
		return $all[ $key ];
	}
	foreach ( oasis_option_fields() as $group ) {
		if ( isset( $group['fields'][ $key ]['default'] ) ) {
			$d = $group['fields'][ $key ]['default'];
			// 保存済みで空文字（チェックを外した等）の場合は、その値を尊重する
			if ( isset( $all[ $key ] ) ) {
				return $all[ $key ];
			}
			return $d;
		}
	}
	return $fallback;
}

function oasis_settings_menu() {
	add_options_page( 'Oasis サイト設定', 'Oasis サイト設定', 'manage_options', 'oasis-settings', 'oasis_settings_page' );
}
add_action( 'admin_menu', 'oasis_settings_menu' );

function oasis_settings_register() {
	register_setting( 'oasis_settings_group', 'oasis_settings', array(
		'sanitize_callback' => 'oasis_sanitize_settings',
		'default'           => array(),
	) );
}
add_action( 'admin_init', 'oasis_settings_register' );

function oasis_sanitize_settings( $input ) {
	$out = array();
	foreach ( oasis_option_fields() as $group ) {
		foreach ( $group['fields'] as $key => $f ) {
			switch ( $f['type'] ) {
				case 'check':
					$out[ $key ] = ! empty( $input[ $key ] ) ? '1' : '0';
					break;
				case 'days':
					$days = isset( $input[ $key ] ) ? (array) $input[ $key ] : array();
					$out[ $key ] = array_values( array_filter( array_map( 'strval', array_map( 'absint', $days ) ), function ( $d ) {
						return $d >= 0 && $d <= 6;
					} ) );
					break;
				case 'textarea':
					// マップの埋め込みだけ iframe を許可する
					$raw = isset( $input[ $key ] ) ? wp_unslash( $input[ $key ] ) : '';
					if ( 'map_embed' === $key ) {
						$out[ $key ] = wp_kses( $raw, array( 'iframe' => array(
							'src' => true, 'width' => true, 'height' => true, 'style' => true,
							'allowfullscreen' => true, 'loading' => true, 'referrerpolicy' => true,
							'title' => true, 'frameborder' => true, 'aria-hidden' => true, 'tabindex' => true,
						) ) );
					} else {
						$out[ $key ] = sanitize_textarea_field( $raw );
					}
					break;
				case 'url':
					$out[ $key ] = isset( $input[ $key ] ) ? esc_url_raw( wp_unslash( $input[ $key ] ) ) : '';
					break;
				default:
					$out[ $key ] = isset( $input[ $key ] ) ? sanitize_text_field( wp_unslash( $input[ $key ] ) ) : '';
			}
		}
	}
	return $out;
}

function oasis_settings_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	$days = array( '日', '月', '火', '水', '木', '金', '土' );
	?>
	<div class="wrap oasis-settings">
		<h1>Oasis サイト設定</h1>
		<p>ここで直した内容は、サイト全体に反映されます。</p>

		<form method="post" action="options.php">
			<?php settings_fields( 'oasis_settings_group' ); ?>

			<?php foreach ( oasis_option_fields() as $gid => $group ) : ?>
				<h2><?php echo esc_html( $group['title'] ); ?></h2>
				<table class="form-table"><tbody>
				<?php foreach ( $group['fields'] as $key => $f ) :
					$val = oasis_option( $key ); ?>
					<tr>
						<th scope="row"><label for="oasis_<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $f['label'] ); ?></label></th>
						<td>
							<?php if ( 'check' === $f['type'] ) : ?>
								<label><input type="checkbox" id="oasis_<?php echo esc_attr( $key ); ?>"
									name="oasis_settings[<?php echo esc_attr( $key ); ?>]" value="1"
									<?php checked( $val, '1' ); ?>> 使う</label>

							<?php elseif ( 'days' === $f['type'] ) : ?>
								<?php $sel = (array) $val; ?>
								<?php foreach ( $days as $i => $d ) : ?>
									<label style="margin-right:14px"><input type="checkbox"
										name="oasis_settings[<?php echo esc_attr( $key ); ?>][]"
										value="<?php echo esc_attr( $i ); ?>"
										<?php checked( in_array( (string) $i, array_map( 'strval', $sel ), true ) ); ?>>
										<?php echo esc_html( $d ); ?></label>
								<?php endforeach; ?>

							<?php elseif ( 'textarea' === $f['type'] ) : ?>
								<textarea id="oasis_<?php echo esc_attr( $key ); ?>"
									name="oasis_settings[<?php echo esc_attr( $key ); ?>]"
									rows="3" class="large-text"><?php echo esc_textarea( $val ); ?></textarea>

							<?php elseif ( 'time' === $f['type'] ) : ?>
								<input type="time" id="oasis_<?php echo esc_attr( $key ); ?>"
									name="oasis_settings[<?php echo esc_attr( $key ); ?>]"
									value="<?php echo esc_attr( $val ); ?>">

							<?php else : ?>
								<input type="<?php echo esc_attr( 'url' === $f['type'] ? 'url' : 'text' ); ?>"
									id="oasis_<?php echo esc_attr( $key ); ?>"
									name="oasis_settings[<?php echo esc_attr( $key ); ?>]"
									class="regular-text" value="<?php echo esc_attr( $val ); ?>">
							<?php endif; ?>

							<?php if ( ! empty( $f['desc'] ) ) : ?>
								<p class="description"><?php echo wp_kses_post( $f['desc'] ); ?></p>
							<?php endif; ?>
						</td>
					</tr>
				<?php endforeach; ?>
				</tbody></table>
			<?php endforeach; ?>

			<?php submit_button( '変更を保存' ); ?>
		</form>

		<hr>
		<?php oasis_importer_panel(); ?>
	</div>
	<?php
}
