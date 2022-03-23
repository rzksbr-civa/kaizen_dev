<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>

<div class="container">
	<?php foreach($shortcuts as $shortcut) : ?>
		<div class="col-md-4">
			<a type="button" class="btn btn-info btn-lg btn-group-justified" href="<?php echo $shortcut['url']; ?>">
				<span class="glyphicon glyphicon-<?php echo $shortcut['glyphicon']; ?>" aria-hidden="true"></span>&nbsp;&nbsp;&nbsp;<?php echo $shortcut['label']; ?>
			</a>
			<br>
		</div>
	<?php endforeach ?>
</div>