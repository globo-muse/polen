<?php

class Mo_API_Authentication_Postman {

    public static function emit_css(){
        ?>
        <style>

            .mo-postman-body {
            display: flex;
            width: 70%;
            min-height: 70vh;
            background: #FFFFFF;

            }

            .container {
            position: relative;
            margin: 0 10px;
            }

            .container .mo-postman-card {
            float: left;
            position: relative;
            width: 250px;
            height: 500px;
            background: #232323;
            border-radius: 20px;
            overflow: hidden;
            margin-top: 50px;
            box-shadow: 0 10px 20px rgba(0,0,0,0.19), 0 6px 6px rgba(0,0,0,0.23);
            }

            .container .mo-postman-card:before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: #ff6c37;
            clip-path: circle(150px at 80% 20%);
            transition: 0.5s ease-in-out;
            }

            .container .mo-postman-card:hover:before {
            clip-path: circle(300px at 80% -20%);
            }

            .container .mo-postman-card:after {
            content: 'Postman';
            position: absolute;
            top: 50%;
            left: 5%;
            font-size: 4em;
            font-weight: 800;
            font-style: italic;
            color: #ff6c374f;
            }

            .container .mo-postman-card .imgBx {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            
            width: 100%;
            height: 220px;
            transition: 0.5s;
            }

            .container .mo-postman-card:hover .imgBx{
            top: 0;
            transform: translateY(0%);
            }

            .container .mo-postman-card .imgBx img{
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 110px;
            }

            .container .mo-postman-card .contentBx {
            position: absolute;
            bottom: 30px;
            width: 100%;
            height: 100px;
            text-align: center;
            transition: 1s;

            }

            .container .mo-postman-card:hover .contentBx {
            height: 210px;
            }

            .container .mo-postman-card .contentBx h2 {
            position: relative;
            font-weight: 600;
            font-size: 2em;
            letter-spacing: 1px;
            color: #fff;
            }

            .container .mo-postman-card:hover .contentBx h2 {
                margin-top: 60px;
            }

            .container .mo-postman-card .contentBx .size, 
            .container .mo-postman-card .contentBx .color {
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 8px 20px;
            transition: 0.5s;
            opacity: 0;
            visibility: hidden;
            }

            .container .mo-postman-card:hover .contentBx .size {
            opacity: 1;
            visibility: visible;
            transition-delay: 0.5s;
            }

            .container .mo-postman-card:hover .contentBx .color {
            opacity: 1;
            visibility: visible;
            transition-delay: 0.6s;
            }

            .container .mo-postman-card .contentBx .size h3, 
            .container .mo-postman-card .contentBx .color h3 {
            color: #fff;
            font-weight: 500;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            margin-right: 5px;
            }

            .container .mo-postman-card .contentBx .size span {
            width: 100%;
            height: 26px;
            text-align: center;
            line-height: 26px;
            font-size: 12px;
            display: inline-block;
            color: #111111;
            background: #FFFFFF;
            margin: 0 5px;
            transition: 0.5s;
            color: #111111;
            border-radius: 4px;
            cursor: pointer;
            }

            .container .mo-postman-card .contentBx .size span:hover,.container .mo-postman-card .contentBx .size span.focus  {
            background: #ff6c37;
            color: #fff;
            }


            .container .mo-postman-card .contentBx a {
            display: inline-block;
            padding: 10px 20px;
            background: #ff6c37;
            border-radius: 4px;
            margin-top: 20px;
            text-decoration: none;
            font-weight: 600;
            color: #fff;
            opacity: 0;
            transform: translateY(50px);
            transition: 0.5s;
            }

            .container .mo-postman-card:hover .contentBx a {
            opacity: 1;
            transform: translateY(0px);
            transition-delay: 0.35s;
            }

            .image {
            width: 320px;
            }

            .mo_api_authentication_postman_layout{
                   margin-top: -32px;
                   background-color:#FFFFFF; 
                   border:1px solid #CCCCCC; 
                   padding-left:20px;
                   border-radius: 0.3em;

            }

            </style>
        <?php
    }

    public static function mo_api_authentication_postman_page(){
        self::emit_css();
        ?>
    <div class="mo_api_authentication_postman_layout" >
        <h2 style="font-size: 20px;font-weight: 700">Postman Samples</h2>
        <p style="font-size: 14px;font-weight: 400;margin-right: 10px;">Download the postman samples to directy test the API configuration from Postman application.</p>
        <p style="font-size: 14px;font-weight: 400;margin-right: 10px;"><b>Tip : </b>Postman app can be downloaded using this <a href="https://www.postman.com/downloads/" target="_blank">LINK</a></p>
        <div style="display: flex;padding-left: 2em; margin-left: -45px;">
            <div class="container" >
                <div class="mo-postman-card">
                    <div class="imgBx">
                        <img class="image" src="<?php echo plugin_dir_url(dirname(dirname(__FILE__)));?>/images/apikey-postman.png">
                    </div>
                    <div class="contentBx">
                        <h2>API Key Auth</h2>
                        <a onclick="mo_postman_download_file('api-key')" href="#">Download</a>
                    </div>
                </div>
            </div>
            
            <div class="container">
                <div class="mo-postman-card">
                <div class="imgBx">
                    <img class="image" src="<?php echo plugin_dir_url(dirname(dirname(__FILE__)));?>/images/basic-auth-postman.png">
                </div>
                <div class="contentBx">
                    <h2>Basic Auth</h2>
                    <div class="size">
                        <span class="select-method" id="basic-username-password" onclick="mo_postman_select_method('basic-username-password')">User : Password</span>
                        <span class="select-method" id="basic-client-credentials" onclick="mo_postman_select_method('basic-client-credentials')">Client ID : Secret</span>
                    </div>
                    <a onclick="mo_postman_download_file('')" href="#">Download</a>
                </div>
                </div>
            </div>

            <div class="container">
                <div class="mo-postman-card">
                <div class="imgBx">
                    <img class="image" src="<?php echo plugin_dir_url(dirname(dirname(__FILE__)));?>/images/jwt-postman.png">
                </div>
                <div class="contentBx">
                    <h2>JWT Auth</h2>
                    <div class="size">
                        <span id="jwt-token" class="select-method" onclick="mo_postman_select_method('jwt-token')" >Token</span>
                        <span id="jwt-resource" class="select-method" onclick="mo_postman_select_method('jwt-resource')">Resource</span>
                    </div>
                    <a onclick="mo_postman_download_file('')" href="#">Download</a>
                </div>
                </div>
            </div>
       </div>
       <br><br>
    </div>
        <form id="mo-postman-form" action="" method="POST">
            <?php wp_nonce_field('mo_api_authentication_postman_config','mo_api_authentication_postman_fields'); ?>
            <input type="hidden" name="option" value="mo_api_authentication_postman_file">
            <input type="hidden" name="file_name" id="mo-postman-file-name-input" value="">
        </form>

        <script>
            function mo_postman_download_file( method ) {
                if( method != '' )
                    document.getElementById("mo-postman-file-name-input").value = method;
                document.getElementById("mo-postman-form").submit();
            }

            function mo_postman_select_method( id ){
                jQuery("span").removeClass("focus");
                jQuery( "#" + id ).addClass("focus");
                document.getElementById("mo-postman-file-name-input").value = id;
            }
        </script>
        <?php
    }

}