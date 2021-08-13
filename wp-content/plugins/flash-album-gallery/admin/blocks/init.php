<?php

/**
 * Gutenberg block init.
 *
 */
class flagGutenberg
{

	// constructor.
	public function __construct()
	{
		add_action('enqueue_block_editor_assets', [$this, 'gutenberg_assets']);
	}

	/**
	 * Enqueue the block's assets for the gutenberg editor.
	 */
	public function gutenberg_assets()
	{
		global $flagdb, $flag;
		$flag_version = $flag->version;
		wp_register_style(
			'flagallery-block-editor',
			plugins_url('blocks/dist/blocks.build.style.css', dirname(__FILE__)),
			[],
			$flag_version
		);

		require_once(dirname(dirname(__FILE__)) . '/get_skin.php');

		$flag_options = get_option('flag_options');
		$all_skins    = get_skins();
		$skins        = [];
		$presets      = [];
		foreach ($all_skins as $skin_file => $skin_data) {
			$id           = dirname($skin_file);
			$is_default   = ($id == $flag_options['flashSkin']);
			$skins[$id] = [
				'id'         => $id,
				'name'       => $skin_data['Name'],
				'is_default' => $is_default,
				'screenshot' => WP_PLUGIN_URL . '/flagallery-skins/' . $id . '/screenshot.png',
			];
			if (empty($flag_options["{$id}_options"]['presets'])) {
				continue;
			}
			foreach ($flag_options["{$id}_options"]['presets'] as $preset_name => $settings) {
				$key             = $id . ' ' . $preset_name;
				$presets[$key] = [
					'id'   => $id,
					'name' => $preset_name,
				];
			}
		}
		$data = [
			'default_skin' => $flag_options['flashSkin'],
			'skins'        => (object) $skins,
			'presets'      => (object) $presets,
			'galleries'    => $flagdb->find_all_galleries($flag->options['albSort'], $flag->options['albSortDir']),
			'albums'       => $flagdb->find_all_albums('id', 'ASC'),
			'assets'       => FLAG_URLPATH . 'admin/blocks/assets',
			'ajaxurl'      => admin_url('admin-ajax.php'),
			'nonce'        => wp_create_nonce('FlaGallery'),
			'license'      => strtolower($flag_options['license_key']),
			'pack'         => $flag_options['license_name'],
		];
		wp_register_script(
			'flagallery-blocks-script',
			plugins_url('blocks/dist/blocks.build.js', dirname(__FILE__)),
			['wp-blocks', 'wp-element', 'wp-editor'],
			$flag_version,
			false
		);

		wp_localize_script('flagallery-blocks-script', 'FlaGallery', $data);
		add_thickbox();

		register_block_type(
			'flagallery/gallery',
			[
				'editor_script' => 'flagallery-blocks-script',
				'editor_style'  => 'flagallery-block-editor',
			]
		);
	}
}
