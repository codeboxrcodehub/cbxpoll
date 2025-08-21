<?php
/**
 * Email Footer
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}
?>
</div>
</td>
</tr>
</table>
<!-- End Content -->
</td>
</tr>
</table>
<!-- End Body -->
</td>
</tr>
</table>
</td>
</tr>
<tr>
    <td align="center" valign="top">
        <!-- Footer -->
        <table border="0" cellpadding="10" cellspacing="0" width="100%" id="template_footer">
            <tr>
                <td valign="top">
                    <table border="0" cellpadding="10" cellspacing="0" width="100%">
                        <tr>
                            <td colspan="2" valign="middle" id="credit">
								<?php

								$footer_text = isset( $template_settings['footertext'] ) ? $template_settings['footertext'] : '';

								echo wp_kses_post(
									wpautop(
										wptexturize(
										/**
										 * Provides control over the email footer text used for most order emails.
										 *
										 * @param  string  $email_footer_text
										 *
										 * @since 4.0.0
										 *
										 */
											apply_filters( 'cbxpoll_email_footer_text', $footer_text )
										)
									)
								);
								?>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
        <!-- End Footer -->
    </td>
</tr>
</table>
</div>
</td>
<td><!-- Deliberately empty to support consistent sizing and layout across multiple email clients. --></td>
</tr>
</table>
</body>
</html>
