<?php
// add the admin settings and such
add_action('admin_init', 'giftgrid_admin_init');
function giftgrid_admin_init(){
    register_setting( 'giftgrid_plugin_options', 'giftgrid_plugin_options', 'giftgrid_options_validate' );
    add_settings_section('giftgrid_main', 'Main Settings', 'giftgrid_section_text', 'giftgrid');
    add_settings_field('giftgrid_acctnum', 'Account Number', 'giftgrid_setting_string', 'giftgrid', 'giftgrid_main');
}

// add the admin options page
add_action('admin_menu', 'giftgrid_admin_add_page');
function giftgrid_admin_add_page() {
add_options_page('Gift Grid Plugin Page', 'Gift Grid Menu', 'manage_options', 'plugin', 'giftgrid_options_page');
}

// display the Gift Grid Plugin page
function giftgrid_options_page() {
?>
<div>
<h2>Gift Grid options</h2>
<form action="options.php" method="POST">
<?php settings_fields('giftgrid_plugin_options');
do_settings_sections('giftgrid');
$options = get_option('giftgrid_plugin_options'); ?>
<p>Current value: <?php echo $options['acctnum']; ?></p>
<input name="Submit" type="submit" value="<?php esc_attr_e('Save Changes'); ?>" />
</form></div>
 
<?php
}

function giftgrid_section_text() {
echo '<p>Please enter the Cru account number to receive the donations (7-digit account number)</p>';
}

function giftgrid_setting_string() {
$options = get_option('plugin_options');
echo "<input id='giftgrid_acctnum' name='giftgrid_plugin_options[acctnum]' size='9' type='text' value='{$options['acctnum']}' />";
}

// validate our options
function giftgrid_options_validate($input) {
    $newinput['acctnum'] = trim($input['acctnum']);
    if(!preg_match('/^[0-9]{7,9}$/i', $newinput['acctnum'])) {
        $newinput['acctnum'] = '';
    }
    return $newinput;
}
?>
