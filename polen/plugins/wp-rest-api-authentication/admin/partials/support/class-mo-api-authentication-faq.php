<?php
	
	class Mo_API_Authentication_Admin_FAQ {
	
		public static function mo_api_authentication_faq() {
			self::faq_page();
		}

		public static function faq_page(){
		?>
			<div class="mo_table_layout">
			    <object type="text/html" data="https://faq.miniorange.com/kb/" width="100%" height="600px" > 
			    </object>
			</div>
		<?php
		}
	}