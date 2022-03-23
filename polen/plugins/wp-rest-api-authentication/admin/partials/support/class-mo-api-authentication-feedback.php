<?php
class Mo_API_Authentication_Admin_Feedback {

    static function mo_api_authentication_display_feedback_form() {
        if ( 'plugins.php' != basename( sanitize_text_field($_SERVER['PHP_SELF']) ) ) {
            return;
        }

        $deactivate_reasons = array("Does not have the features I'm looking for", "Do not want to upgrade to Premium version", "Confusing Interface", "Bugs in the plugin", "Unable to register", "Other Reasons");
        wp_enqueue_style( 'wp-pointer' );
        wp_enqueue_script( 'wp-pointer' );
        wp_enqueue_script( 'utils' );
        wp_enqueue_style('mo_api_admin_settings_style', plugin_dir_url(dirname(dirname(dirname(__FILE__)))). 'css/style_settings.css');
        ?>
     <div id="mo_api_feedback_modal" class="mo_api_modal" style="width: 75%; margin: auto; text-align: center;">
        <div class="mo_api_modal-content" style="">
            
            <h3 style="text-align: center; margin-top: 2%;"><b style="font-size: 1.2em;">Your Feedback 
            </b><span class="mo_api_close" id="mo_api_close">&times;</span></h3>
            <hr style="width: 75%">
            <form name="f" method="post" action="" id="mo_api_authentication_feedback">
                <?php wp_nonce_field('mo_api_authentication_feedback_form','mo_api_authentication_feedback_fields'); ?>
                <input type="hidden" name="mo_api_authentication_feedback" value="true"/>
                <div>
                    <p style="margin-left:2%">
                    <h4 style="margin: 2%; text-align:center; font-weight: 600; font-size: 1.2em;">We would like your opinion to improve our plugin.<br></h4>
                    <div align="center">
                    <div id="smi_rate" style="text-align:center">
                    <input type="radio" name="rate" id="angry" value="1"/>
                        <label for="angry"><img class="sm" src="<?php echo plugin_dir_url( __FILE__ ) . 'images/angry.png'; ?>" />
                        </label>
                        
                    <input type="radio" name="rate" id="sad" value="2"/>
                        <label for="sad"><img class="sm" src="<?php echo plugin_dir_url( __FILE__ ) . 'images/sad.png'; ?>" />
                        </label>
                    
                    
                    <input type="radio" name="rate" id="neutral" value="3"/>
                        <label for="neutral"><img class="sm" src="<?php echo plugin_dir_url( __FILE__ ) . 'images/normal.png'; ?>" />
                        </label>
                        
                    <input type="radio" name="rate" id="smile" value="4"/>
                        <label for="smile">
                        <img class="sm" src="<?php echo plugin_dir_url( __FILE__ ) . 'images/smile.png'; ?>" />
                        </label>
                        
                    <input type="radio" name="rate" id="happy" value="5" checked/>
                        <label for="happy"><img class="sm" src="<?php echo plugin_dir_url( __FILE__ ) . 'images/happy.png'; ?>" />
                        </label>
                    </div>

                    <div style="margin: auto;">
                    <h4 style="margin: 2%; font-weight: 600; font-size: 1.1em;">Tell us what happened?<br></h4>

                    <select style="margin: auto; margin-bottom: 10px; text-align: center; width: 60%;" name="deactivate_reason_select" id="deactivate_reason_select" required>
                        <option value="" style="text-align:center; text-align-last: center;">Please select your reason</option>
                    
                <?php
                    foreach ( $deactivate_reasons as $deactivate_reason ) 

                        echo '<option id = "'. esc_attr ( $deactivate_reason ).'" value="'. esc_attr( $deactivate_reason ) .'" style="text-align:center; text-align-last: center;">'.esc_html ( $deactivate_reason ) .'</option>';
                ?>
                    </select>
                    
                    <textarea id="query_feedback" name="query_feedback" rows="4" style="margin: auto; width: 60%;" placeholder="Write your query here.."></textarea>
                    
                    <?php $email = get_option("mo_api_authentication_admin_email");
                        if(empty($email)){
                            $user = wp_get_current_user();
                            $email = $user->user_email;
                        }
                    ?>
                        
                    <div>
                        <input type="email" id="query_mail" name="query_mail" style="margin-bottom: 10px; text-align:center; border:0px solid black; background:#f0f3f7; width:60%;" placeholder="your email address" required value="<?php echo esc_attr( $email ); ?>" readonly="readonly"/>
                        
                        <i class="fa fa-pencil" onclick="editName()" style="margin-left: -3%; cursor:pointer;"></i>
                        
                        </div>
                        <div style="margin-left: 2%; width: 100%; margin-bottom: 5%;">
                        </div>
                       
                    <div class="mo_modal-footer">
                        <div style="width: 60%; margin: 0 auto;">
                        <input type="submit" name="miniorange_feedback_submit"
                               class="button button-primary button-large" style="float: left; width: 20%" value="Submit"/>
                        <input id="mo_skip" type="submit" name="miniorange_feedback_skip"
                               class="button button-primary button-large" style="float: right; width: 20%" value="Skip"/></div>
                    </div>
                    
                </div>

            </form>
            <form name="f" method="post" action="" id="mo_api_feedback_form_close">
                <?php wp_nonce_field('mo_api_authentication_skip_feedback_form','mo_api_authentication_skip_feedback_form_fields'); ?>
                <input type="hidden" name="option" value="mo_api_authentication_skip_feedback"/>
            </form>
        </div>
    </div>
    <script>

        function editName(){
            document.querySelector('#query_mail').removeAttribute('readonly');
            document.querySelector('#query_mail').focus();
            return false;
        }

        jQuery('a[aria-label="Deactivate WordPress REST API Authentication"]').click(function () {
            var mo_api_modal = document.getElementById('mo_api_feedback_modal');
            var mo_skip_api = document.getElementById('mo_skip');
            var mo_api_close = document.getElementById("mo_api_close");
            mo_api_modal.style.display = "block";

            mo_api_close.onclick = function () {
                mo_oauth_client_modal.style.display = "none";
                jQuery('#mo_api_feedback_form_close').submit();
            }
            mo_skip_api.onclick = function() {
                mo_api_modal.style.display = "none";
                jQuery('#mo_api_feedback_form_close').submit();
            }

            window.onclick = function (event) {
                if (event.target == mo_api_modal) {
                    mo_api_modal.style.display = "none";
                }
            }
            return false;

        });
    </script>
<?php

    }

}