<?php
ini_set('display_errors', true);
error_reporting(E_ALL | E_STRICT);

require('./lib/form.php');

$form = Form::from_request('foo[bar][baz]');
$form->set_defaults(array(
	'subs' => array(),
	'multi' => array(),
	'remember' => 0,
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
		<title>Form test</title>
	</head>
	<body>
		<form method="post" action="demo.php">
			Username: <?php echo $form->username->input() ?><br>
			Password: <?php echo $form->password->password() ?><br>
			<hr>
			Sub-Username: <?php echo $form->sub->username->input() ?><br>
			Sub-Password: <?php echo $form->sub->password->password() ?><br>
			<hr>
			Choose one: <?php echo $form->choice->select(array('' => 'Nothing', 1 => 'Choose foo (1)', 2 => 'Choose bar (2)', 3 => 'Choose baz (3)')) ?>
			<hr>
			Choose several: <?php echo $form->multi->select(array('' => 'Nothing', 1 => 'Choose foo (1)', 2 => 'Choose bar (2)', 3 => 'Choose baz (3)'), 'multiple') ?>
			<hr>
			<?php echo $form->remember->checkbox('1') ?> Remember me
			<hr>
			Proceed to:<br>
			<?php echo $form->redirect->radio('profile') ?> Profile
			<?php echo $form->redirect->radio('checkout') ?> Checkout
			<hr>
			Subscribe to:<br>
			<?php echo $form->subs->checkbox('list1') ?> Newsletter 1<br>
			<?php echo $form->subs->checkbox('list2') ?> Newsletter 2<br>
			<?php echo $form->subs->checkbox('list3') ?> Newsletter 3<br>
			<?php echo $form->subs->checkbox('list4') ?> Newsletter 4<br>
			<?php echo $form->subs->checkbox('list5') ?> Newsletter 5<br>
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
