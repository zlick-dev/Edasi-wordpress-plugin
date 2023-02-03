<div class="wrap" >
    <form method="POST" class="zlick-payments">
	    <h2><?php echo __( 'Zlick Payments Configuration', 'zlick-payments' ); ?></h2>
		<table name="zp_configuration">
			<tr valign="top">
				<th scope="row">
					<label for="zp_active" ><?php echo __( 'Status', 'zlick-payments' ); ?></label>
				</th>
				<td>
					<select name="zp_active" id="zp_active" value="<?php echo $data['zp_active']; ?>" >
						<option value="0" <?php echo ( 0 == $data['zp_active'] ) ? "selected=''" : ''; ?> ><?php echo __( 'Disable', 'zlick-payments' ); ?></option>
						<option value="1" <?php echo ( 1 == $data['zp_active'] ) ? "selected=''" : ''; ?> ><?php echo __( 'Enable', 'zlick-payments' ); ?></option>
					</select>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row">
					<label for="zp_environment" ><?php echo __( 'Environment', 'zlick-payments' ); ?></label>
				</th>
				<td>
					<select name="zp_environment" id="zp_environment" value="<?php echo $data['zp_environment']; ?>" >
						<option value="sandbox" <?php echo ( "sandbox" == $data['zp_environment'] ) ? "selected=''" : ''; ?> ><?php echo __( 'Sandbox', 'zlick-payments' ); ?></option>
						<option value="live" <?php echo ( "live" == $data['zp_environment'] ) ? "selected=''" : ''; ?> ><?php echo __( 'Live', 'zlick-payments' ); ?></option>
					</select>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row">
					<label for="zp_client_token"><?php echo __( 'Client Token', 'zlick-payments' ); ?></label>
				</th>
				<td>
					<input type="text" name="zp_client_token" id="zp_client_token" value="<?php echo $data['zp_client_token']; ?>">
				</td>
			</tr>
			<tr valign="top">
				<th scope="row">
					<label for="zp_client_secret"><?php echo __( 'Client Secret', 'zlick-payments' ); ?></label>
				</th>
				<td>
					<input type="text" name="zp_client_secret" id="zp_client_secret" value="<?php echo $data['zp_client_secret']; ?>">
				</td>
			</tr>
			<tr valign="top">
				<th scope="row">
					<label for="zp_post_type" ><?php echo __( 'Post Type', 'zlick-payments' ); ?></label>
				</th>
				<td>
					<select name="zp_post_type" id="zp_post_type" value="<?php echo $data['zp_post_type']; ?>" >
						<?php foreach ( $data['post_types'] as $post_type ) { ?>
							<option value="<?= $post_type; ?>" <?php echo ( $post_type == $data['zp_post_type'] ) ? "selected=''" : ''; ?> ><?php echo $post_type; ?></option>
						<?php } ?>
					</select>
				</td>
			</tr>
            <tr valign="top">
                <th scope="row">
                    <label for="zp_previewable_para_length"><?php echo __( 'Previewable Paragraphs Length', 'zlick-payments' ); ?></label>
                </th>
                <td>
                    <input type="number" name="zp_previewable_para_length" id="zp_previewable_para_length" value="<?php echo $data['zp_previewable_para_length']; ?>">
                </td>
            </tr>
            <tr valign="top">
                <th scope="row">
                    <label for="zp_set_default_price" ><?php echo __( 'Set Default Price', 'zlick-payments' ); ?></label>
                </th>
                <td>
                    <select name="zp_set_default_price" id="zp_set_default_price" value="<?php echo $data['zp_set_default_price']; ?>" >
                        <option value="0" <?php echo ( 0 == $data['zp_set_default_price'] ) ? "selected=''" : ''; ?> ><?php echo __( 'Disable', 'zlick-payments' ); ?></option>
                        <option value="1" <?php echo ( 1 == $data['zp_set_default_price'] ) ? "selected=''" : ''; ?> ><?php echo __( 'Enable', 'zlick-payments' ); ?></option>
                    </select>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row">
                    <label for="zp_default_price"><?php echo __( 'Default Price', 'zlick-payments' ); ?></label>
                </th>
                <td>
                    <input type="text" name="zp_default_price" id="zp_default_price" value="<?php echo $data['zp_default_price']; ?>">
                </td>
            </tr>
            <tr valign="top">
                <th scope="row">
                    <label for="zp_subscription_id"><?php echo __( 'Subscription ID', 'zlick-payments' ); ?></label>
                </th>
                <td>
                    <input type="text" name="zp_subscription_id" id="zp_subscription_id" value="<?php echo $data['zp_subscription_id']; ?>">
                </td>
            </tr>
			<tr valign="top">
				<th scope="row">
					<label for="zp_widget_text"><?php echo __( 'Widget Text', 'zlick-payments' ); ?></label>
				</th>
				<td>
					<textarea name="zp_widget_text" id="zp_widget_text" rows="5" cols="43"><?php echo $data['zp_widget_text']; ?></textarea>
				</td>
			</tr>
			<tr valign="top">
				<th></th>
				<td class="zlick-submit">
				<?php wp_nonce_field( 'zlick-payments-nonce', 'zp_nonce_field' ); ?>
					<input type="submit" value="<?php echo __( 'Save', 'zlick-payments' ); ?>" class="button button-primary button-large">
				</td>
			</tr>
		</table>
	</form>
</div>
