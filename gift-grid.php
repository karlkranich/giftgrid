<?php
/**
Plugin Name: Gift Grid Display
Description: Plugin for displaying a grid from which gift amounts can be chosen
Version: 1.0
Author: Karl Kranich
Author URI: http://karl.kranich.org
*/

include 'giftgrid-options.php';  // adds the admin dashboard settings page to set acct number

add_action('wp_ajax_giftgrid_save', 'giftgrid_save_callback');
add_action('wp_ajax_nopriv_giftgrid_save', 'giftgrid_save_callback');

function giftgrid_save_callback() {
    $current_gifts = get_post_meta($_POST['post-id'],'pending-gifts');
    $current_gifts = join(',', $current_gifts) . ',' . $_POST['gifts'];
    update_post_meta($_POST['post-id'],'pending-gifts',$current_gifts);
    $rightnow = date("F j, Y, g:i a T");
    $subject = 'Giftgrid submission';
    $message = "You got a submission from your giftgrid page! \nDate: $rightnow \nDonor: {$_POST['donorname']} \nAmount(s): {$_POST['gifts']}";
    wp_mail($_POST['email'], $subject, $message);
    die();
}

function giftgrid_func(){
    // Register the styles
    wp_register_style('grid-styles', plugins_url('/css/grid-styles.css', __FILE__ ), false, null);
    wp_enqueue_style('grid-styles');

    // Read gifts from Wordpress custom fields
    $confirmed_gifts = get_post_meta(get_the_ID(),'confirmed-gifts',true);
    $pending_gifts = get_post_meta(get_the_ID(),'pending-gifts',true);

    // Get staff account number from plugin options
    $options = get_option('giftgrid_plugin_options');
    $acct_num = $options['acctnum'];
    $email = $options['email'];
    $redir_URL = $options['URL'];

	ob_start();
	?>
    <div id="giftgrid">
        <div id="total-div">
            <p>* Please enable scripting in your browser to participate in this campaign *<p>
        </div>
        <div id="grid-div"></div>
        <div id="total-chosen-div"></div>
        <div id="donor-name-div"></div>
        <div id="donate-div">
            <div id="donor-div">
                Name: <input type="text" name="donorname" id="donorname">
            </div>
            <div id="donate-button-div">
                <a href="#" id="donate-button" >Donate</a>
                <p>This will send you to my ministry's online giving page.</p>
            </div>
        </div>
    </div>
    <script language="javascript" type="text/javascript">
    <!--
        // Load gifts from PHP variables
        var pendingGifts = [<?php echo $pending_gifts; ?>];
        var confirmedGifts = [<?php echo $confirmed_gifts; ?>];

        // Other global variables
        var allGifts = pendingGifts.concat(confirmedGifts);
        var totalDiv = document.getElementById("total-div");
        var chosenGifts = [];
        var chosenDiv = document.getElementById("total-chosen-div");
        var donateDiv = document.getElementById("donate-div");
        var totalChosen;
        var lastClicked;
        var grid = clickableGrid(10,10,myClick);

        // Write out the total so far
        var grandTotal = eval(allGifts.join('+'));
        if (grandTotal === undefined) {
            grandTotal = 0;
        }
        totalDiv.innerHTML = "<p>We have raised: $" + grandTotal + "<p>";

        // Attach click function to Donate button
        var donateButton = document.getElementById("donate-button");
        donateButton.addEventListener('click',donateClick,false);

        // Build the grid
        document.getElementById("grid-div").appendChild(grid);

        function clickableGrid( rows, cols, callback ){
            var i=0;
            var grid = document.createElement('table');
            grid.className = 'grid';
            for (var r=0;r<rows;++r){
                var tr = grid.appendChild(document.createElement('tr'));
                for (var c=0;c<cols;++c){
                    var cell = tr.appendChild(document.createElement('td'));
                    cell.innerHTML = ++i;
                    if (pendingGifts.indexOf(i) > -1) {
                        cell.className = 'pending';
                    }
                    if (confirmedGifts.indexOf(i) > -1) {
                        cell.className = 'confirmed';
                    }
                    cell.addEventListener('click',(function(el,r,c,i){
                        return function(){
                            callback(el,r,c,i);
                        }
                    })(cell,r,c,i),false);
                }
            }
            return grid;
        }

        function myClick(el,row,col,i){
            if (el.className == '') {
                el.className = 'chosen';
                chosenGifts.push(i);
                chosenGifts.sort(function(a,b){return a - b});
                // console.log(chosenGifts);
                var newText = '';
                if (chosenGifts.length == 1) {
                    totalChosen = chosenGifts[0];
                    newText = "<p>My gift: $" + totalChosen + "<p>";
                } else {
                    totalChosen = eval(chosenGifts.join('+'));
                    newText = "<p>My gift: $" + chosenGifts.join(' + $') + " = $" + totalChosen + "<p>";
                }
                chosenDiv.innerHTML = newText;
                donateDiv.style.display = 'block';
            }
            else if (el.className == 'chosen') {
                el.className = '';
                chosenGifts.splice(chosenGifts.indexOf(i),1);
                // console.log(chosenGifts);
                var newText = '';
                if (chosenGifts.length == 0) {
                    newText = "<p>My gift: $0<p>";
                    donateDiv.style.display = 'none';
                } else if (chosenGifts.length == 1) {
                    totalChosen = chosenGifts[0];
                    newText = "<p>My gift: $" + totalChosen + "<p>";
                } else {
                    totalChosen = eval(chosenGifts.join('+'));
                    newText = "<p>My gift: $" + chosenGifts.join(' + $') + " = $" + totalChosen + "<p>";
                }
                chosenDiv.innerHTML = newText;
            }
        }

        function donateClick(){
            var ajaxURL = "<?php echo get_admin_url(),'admin-ajax.php';?>";
            var postID = "<?php the_ID();?>";
            var giftString = chosenGifts.sort(function(a,b){return a - b}).join();
            var acctNum = "<?php echo $acct_num?>";
            var email = "<?php echo $email?>";
            var redirURL = "<?php echo $redir_URL?>";
            var checkoutURL = "https://give.cru.org/give/EasyCheckout1/process/singleGift?Amount=" +
                totalChosen + "&Frequency=X&DrawDay=&Desig=" + acctNum + "&URL=" + redirURL;
            jQuery.ajax({
                type: 'post',
                url: ajaxURL,
                data: {
                    'action': 'giftgrid_save',
                    'post-id': postID,
                    'email': email,
                    'donorname': document.getElementById('donorname').value,
                    'gifts': giftString
                },
                success: function(){
                    window.location = checkoutURL;
                }
            });
        }
    // -->
    </script>
	<?php return ob_get_clean();
}

add_shortcode( 'giftgrid', 'giftgrid_func' );
?>