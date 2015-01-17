<?php

/**
 * File       results.php
 * Created    1/17/15 5:03 AM
 * Author     Matt Thomas | matt@betweenbrain.com | http://betweenbrain.com
 * Support    https://github.com/betweenbrain/
 * Copyright  Copyright (C) 2015 betweenbrain llc. All Rights Reserved.
 * License    GNU GPL v2 or later
 */

if ($this->data) : ?>
	<form action="<?php echo URL ?>import/export/" method="POST">
		<input type="submit" value="Export Data" />
	</form>
	<table class="table table-striped table-hover">
		<tbody>
		<thead>
		<tr>
			<?php foreach ($this->data[0] as $name => $value) : ?>
				<th><?php echo $name ?></th>
			<?php endforeach ?>
		</tr>
		</thead>
		<?php foreach ($this->data as $data) : ?>
			<tr>
				<td><?php echo $data['firstName'] ?></td>
				<td><?php echo $data['lastName'] ?></td>
				<td><?php echo $data['email'] ?></td>
			</tr>
		<?php endforeach ?>
		</tbody>
	</table>
<?php endif;
