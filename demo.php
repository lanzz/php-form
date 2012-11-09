<?php
ini_set('display_errors', true);
error_reporting(E_ALL | E_STRICT);

require('./lib/form.php');

$form = Form::from_request('registration');
$form->set_defaults(array(
));

function inspect(Form_Container $form) {
	if (!count($form)) {
		return;
	}
	print('<ul>');
	foreach ($form as $name => $input) {
		print('<li><b>'.htmlspecialchars($name).'</b> <small>('.htmlspecialchars($input->get_name()).')</small>: ');
		if (count($input)) {
			// complex input
			inspect($input);
		} else {
			// single value
			print($input->as_string());
		}
	}
	print('</ul>');
}

?>
<!DOCTYPE html>
<html>
	<head>
		<title>PHP Form :: Demonstration</title>
		<style>

		div.input > label:first-child {
			width: 200px;
			display: inline-block;
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
				</div>
				<div class="input <?php echo $form->password->if_errors('error') ?>">
					<?php echo $form->password->label('Password:') ?>
					<?php echo $form->password->password() ?>
				</div>
				<div class="input <?php echo $form->confirm_password->if_errors('error') ?>">
					<?php echo $form->confirm_password->label('Confirm Password:') ?>
					<?php echo $form->confirm_password->password() ?>
				</div>
			</fieldset>
			<fieldset>
				<legend>Address</legend>
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
				</div>
				<div class="input <?php echo $form->address->city->if_errors('error') ?>">
					<?php echo $form->address->city->label('City:') ?>
					<?php echo $form->address->city->input() ?>
				</div>
				<div class="input <?php echo $form->address->street->if_errors('error') ?>">
					<?php echo $form->address->street->label('Street Address:') ?>
					<?php echo $form->address->street->input() ?>
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
				</div>
				<div class="input <?php echo $form->demo->age->if_errors('error') ?>">
					<label>Age Group:</label>
					<div class="radio-group">
						<?php echo $form->demo->age->radio('0-17') ?><?php echo $form->demo->age->label('Under 18', '0-17') ?><br>
						<?php echo $form->demo->age->radio('18-21') ?><?php echo $form->demo->age->label('18 to 21', '18-21') ?><br>
						<?php echo $form->demo->age->radio('22-40') ?><?php echo $form->demo->age->label('22 to 40', '22-40') ?><br>
						<?php echo $form->demo->age->radio('40+') ?><?php echo $form->demo->age->label('Over 40', '40+') ?><br>
						<?php echo $form->demo->age->radio('-')?><?php echo $form->demo->age->label('Won\'t say', '-') ?>
					</div>
				</div>
			</fieldset>
			<hr>
			<?php echo $form->submit->submit('Submit') ?>
		</form>
<?php if ($form->is_submitted()): ?>
		<hr>
		<h3>Form data</h3>
		<?php inspect($form) ?>
		<hr>
		<h3>Build query string from form data</h3>
		<pre><a href="?<?php echo htmlspecialchars($form->query()) ?>"><?php echo htmlspecialchars(urldecode($form->query())) ?></a></pre>
		<hr>
		<h3>Build hidden fields from form data</h3>
		<pre><?php echo htmlspecialchars($form->hidden()) ?></pre>
<?php endif ?>
	</body>
</html>
