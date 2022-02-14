<?php
/**
 * This just exists to test different form situations.
 *
 * @phpcs:disable Squiz.Commenting, Generic.Commenting
 * @phpcs:disable PSR2.Classes.PropertyDeclaration.Underscore
 */
namespace CdlS;

defined( 'ABSPATH' ) || die;

class Testing_Form extends Form {

	/**
	 * phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped
	 */
	public function build() {

		?>

		<section class="cdls-form">
			<section class="section">
				<h1>Testing</h1>
				<section class="fields">
				<?php $this->output_form_fields(); ?>
				</section>
			</section>
		</section>
		
		<?php

	}

	public function output_form_fields() {

		$this->form->input_text();

	}

	public static function get_validation_rules() {

		return array(
			'required' => array( 'email', 'password' ),
			'email'    => array( 'email' ),
		);

	}

	public static function get_validation_labels() {

		return array(
			'email'    => 'Correo electrónico',
			'password' => 'Contraseña',
		);

	}

	public function submit() {

	}

	public function current_user_can_submit() {
		return true;
	}

}
