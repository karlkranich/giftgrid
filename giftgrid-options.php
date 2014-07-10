<?php
// add the admin settings and such
add_action('admin_init', 'giftgrid_admin_init');
function giftgrid_admin_init(){
    register_setting( 'gg_plugin_options', 'giftgrid_plugin_options', 'giftgrid_options_validate' );
    add_settings_section('giftgrid_main', '', 'giftgrid_section_text', 'giftgrid');
    add_settings_field('giftgrid_acctnum', 'Account Number', 'giftgrid_setting_acctnum', 'giftgrid', 'giftgrid_main');
    add_settings_field('giftgrid_email', 'Email Address', 'giftgrid_setting_email', 'giftgrid', 'giftgrid_main');
    add_settings_field('giftgrid_URL', 'URL', 'giftgrid_setting_URL', 'giftgrid', 'giftgrid_main');
}

// add the admin options page
add_action('admin_menu', 'giftgrid_admin_add_page');
function giftgrid_admin_add_page() {
add_options_page('Gift Grid Plugin Page', 'Gift Grid', 'manage_options', 'plugin', 'giftgrid_options_page');
}

// display the Gift Grid Plugin page
function giftgrid_options_page() {
?>
<div>
<h2>Gift Grid options</h2>
<form action="options.php" method="POST">
<?php settings_fields('gg_plugin_options');
do_settings_sections('giftgrid');?>
<input name="Submit" type="submit" value="<?php esc_attr_e('Save Changes'); ?>" />
</form></div>
 
<?php
}

function giftgrid_section_text() {
	echo '<p>Please enter:</p><ol><li>The Cru account number to receive the donations (7-digit account number)</li>';
    echo '<li>The email address that should receive giving notifications</li>';
    echo '<li>The URL that givers should be directed to after the Cru checkout process<br />This may be left blank ';
    echo 'or could be set to your blog URL.  Example: http://www.kranich.org</li></ol>';
}

function giftgrid_setting_acctnum() {
	$options = get_option('giftgrid_plugin_options');
	echo "<input id='giftgrid_acctnum' name='giftgrid_plugin_options[acctnum]' size='9' type='text' value='{$options['acctnum']}'/>";
}

function giftgrid_setting_email() {
    $options = get_option('giftgrid_plugin_options');
    echo "<input id='giftgrid_email' name='giftgrid_plugin_options[email]' size='30' type='text' value='{$options['email']}'/>";
}

function giftgrid_setting_URL() {
	$options = get_option('giftgrid_plugin_options');
	echo "<input id='giftgrid_URL' name='giftgrid_plugin_options[URL]' size='30' type='text' value='{$options['URL']}'/>";
}

// validate our options
function giftgrid_options_validate($input) {
    $newinput['acctnum'] = trim($input['acctnum']);
    if (!preg_match('/^[0-9]{7,9}$/i', $newinput['acctnum'])) {
        $newinput['acctnum'] = '';
    }
    $newinput['email'] = trim($input['email']);
    $newinput['URL'] = trim($input['URL']);
    return $newinput;
}
?>
