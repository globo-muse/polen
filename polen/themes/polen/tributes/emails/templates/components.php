<?php

class Tributes_Email_Class
{
	public static function get_assets_url()
	{
		return TEMPLATE_URI . "/tributes/assets/img";
	}

	public static function get_margin($margin = "40px")
	{
?>
		<tr>
			<td style="height: <?php echo $margin; ?>"></td>
		</tr>
	<?php
	}

	public static function get_button_link($text, $link)
	{
	?>
		<a href="<?php echo $link; ?>" style="
									padding: 15px 100px;
									font-site: 14px;
									text-decoration: none;
									color: white;
									background-color: #fd6c36;
									border-radius: 8px;
								" target="_blank"><?php echo $text; ?></a>
<?php
	}
}
