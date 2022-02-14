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

		<?php
		echo $this->form->open(
			$this->form->id,
			$this->form->name,
			'',
			'POST',
			'class="cdls-form"'
		);

		?>

		<section class="cdls-form">
			<section class="section">
				<h1>Testing</h1>
				<section class="fields">
				<?php $this->output_form_fields(); ?>
				<?php echo $this->form->input_hidden( 'cdls_form_id', $this->form->id ); ?>
				<?php echo $this->form->input_submit(); ?>
				</section>
			</section>
		</section>
		
		<?php

		echo $this->form->close();

	}

	public function output_form_fields() {

		echo $this->form->input_text( 'some_text', 'Some Text' );

	}

	public static function get_validation_rules( $data ) {

		return array();

	}

	public static function get_validation_labels() {

		return array();

	}

	public function submit() {

		$wow = null;
		try {

			$wow = DB()->query2( 'SELECT some_fake_field FROM table_that_doesnt_exists' );
		} catch ( \PDOException  $e ) {
			$this->error_message( 'error' );
			return;
		}

		ob_start();
		var_dump( $wow );
		$this->success_message( ob_get_clean() );

	}

	public function current_user_can_submit() {
		return true;
	}

}
