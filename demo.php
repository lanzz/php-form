<?php
ini_set('display_errors', true);
error_reporting(E_ALL | E_STRICT);

session_start();

require('./lib/form.php');

$form = Form::from_request('registration');
if ($form->is_submitted()) {
	$_SESSION['saved_form'] = $form->get_values();
} else if (isset($_SESSION['saved_form'])) {
	$form = Form::from_array($_SESSION['saved_form'], 'registration', false);
}

$form->set_defaults(array(
	'address' => array(
		'country' => 'er',
	),
));

if ($form->is_submitted()) {
	if (!strlen($form->username->get_value())) {
		$form->username->set_error('Please enter a username');
	}
	if (!strlen($form->password->get_value())) {
		$form->password->set_error('Please enter a password for your account');
	} else {
		if (mb_strlen($form->password->get_value()) < 6) {
			$form->password->add_error('Password must be at least 6 characters');
		}
		if ($form->password->get_value() == $form->username->get_value()) {
			$form->password->add_error('Password cannot be the same as the username');
		}
	}
	if (!$form->password->has_errors()) {
		if ($form->confirm_password->get_value() != $form->password->get_value()) {
			$form->confirm_password->add_error('Passwords does not match');
			$form->password->set_error();
		}
	}
	if (!strlen($form->address->country->get_value())) {
		$form->address->country->set_error('Country is required');
	} else if ($form->address->country->get_submitted() != $form->address->country->get_default()) {
		$form->address->country->set_error('We accept only Eriador registrations at this time');
	}
}

function render_errors(array $errors) {
	return count($errors)
		? '<ul class="errors"><li>'.join('</li><li>', array_map('htmlspecialchars', $errors)).'</li></ul>'
		: '';
}

?>
<!DOCTYPE html>
<html>
	<head>
		<title>PHP Form :: Demonstration</title>
		<style>

		* {
			font-family: Helvetica, sans-serif;
		}

		div.input > label:first-child {
			width: 200px;
			display: block;
			float: left;
		}
		div.input.error {
			background: #FDD;
			margin: 0 -5px 0.25em -5px;
			padding: 0 5px;
			border-radius: 5px;
		}
		div.input ul.errors {
			margin: 0;
			padding: 0 0 0 0.5em;
			font-size: 80%;
			color: #A00;
		}
		div.input ul.errors li {
			list-style-position: inside;
		}

		div.radio-group {
			float: left;
		}

		</style>
	</head>
	<body>
		<form method="post">
			<fieldset>
				<legend>Account</legend>
				<div class="input <?php echo $form->username->if_errors('error') ?>">
					<?php echo $form->username->label('Username:') ?>
					<?php echo $form->username->input() ?>
					<?php echo render_errors($form->username->get_errors()) ?>
				</div>
				<div class="input <?php echo $form->password->if_errors('error') ?>">
					<?php echo $form->password->label('Password:') ?>
					<?php echo $form->password->password() ?>
					<?php echo render_errors($form->password->get_errors()) ?>
				</div>
				<div class="input <?php echo $form->confirm_password->if_errors('error') ?>">
					<?php echo $form->confirm_password->label('Confirm Password:') ?>
					<input type="password" <?php echo $form->confirm_password->id_name() ?>>
					<?php echo render_errors($form->confirm_password->get_errors()) ?>
				</div>
			</fieldset>
			<fieldset>
				<legend>Address</legend>
				<div class="input <?php echo $form->address->street->if_errors('error') ?>">
					<?php echo $form->address->street->label('Street Address:') ?>
					<input type="text"
						id="<?php echo htmlspecialchars($form->address->street->get_id()) ?>"
						name="<?php echo htmlspecialchars($form->address->street->get_name()) ?>"
						value="<?php echo htmlspecialchars($form->address->street->get_value()) ?>">
					<?php echo render_errors($form->address->street->get_errors()) ?>
				</div>
				<div class="input <?php echo $form->address->city->if_errors('error') ?>">
					<?php echo $form->address->city->label('City:') ?>
					<input type="text"
						<?php echo $form->address->city->id() ?>
						<?php echo $form->address->city->name() ?>
						<?php echo $form->address->city->value() ?>>
					<?php echo render_errors($form->address->city->get_errors()) ?>
				</div>
				<div class="input <?php echo $form->address->country->if_errors('error') ?>">
					<?php echo $form->address->country->label('Country:') ?>
					<?php echo $form->address->country->select(array(
						'' => 'Please choose...',
						'er' => 'Eriador',
						'gd' => 'Gondor',
						'md' => 'Mordor',
						'rh' => 'Rhovanion',
						'ro' => 'Rohan',
					)) ?>
					<?php echo render_errors($form->address->country->get_errors()) ?>
				</div>
			</fieldset>
			<fieldset>
				<legend>Demographic</legend>
				<div class="input <?php echo $form->demo->gender->if_errors('error') ?>">
					<?php echo $form->demo->gender->label('Gender:') ?>
					<select <?php echo $form->demo->gender->id_name() ?>>
						<?php echo $form->demo->gender->option('', 'Please choose...') ?>
						<?php echo $form->demo->gender->option('m', 'Male') ?>
						<?php echo $form->demo->gender->option('f', 'Female') ?>
						<?php echo $form->demo->gender->option('-', 'Won\'t say') ?>
					</select>
					<?php echo render_errors($form->demo->gender->get_errors()) ?>
				</div>
				<div class="input <?php echo $form->demo->age->if_errors('error') ?>">
					<label>Age Group:</label>
					<select <?php echo $form->demo->age->id_name() ?>>
						<option value="">Please choose...</option>
						<option value="0-17" <?php echo $form->demo->age->selected('0-17') ?>>Under 18</option>
						<option value="18-21" <?php echo $form->demo->age->selected('18-21') ?>>18 to 21</option>
						<option value="22-40" <?php echo $form->demo->age->selected('22-40') ?>>21 to 40</option>
						<option value="40+" <?php echo $form->demo->age->selected('40+') ?>>Over 40</option>
						<option value="-" <?php echo $form->demo->age->selected('-') ?>>Won't say</option>
					</select>
					<?php echo render_errors($form->demo->age->get_errors()) ?>
				</div>
			</fieldset>
			<hr>
			<?php echo $form->submit->submit('Submit') ?>
		</form>
	</body>
</html>
