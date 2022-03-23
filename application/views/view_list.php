<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>
<div class="page-header">
  <h1>
	<?php echo $page_header_title; ?>
	
	<?php if($user_can_add) : ?>
	<span class='pull-right'>
		<a href="<?php echo base_url('db/add/'.$entity_name); ?>" class="btn btn-success" role="button">
			<span class="glyphicon glyphicon-plus" aria-hidden="true"></span> <?php echo ucwords($entity_data['label_singular']); ?>
		</a>
	</span>
	<?php endif; ?>
	
  </h1>
</div>

<?php echo $datatable; ?>