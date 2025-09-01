<?php
  /**
   * This is the HTML template for the doodle table.
   *
   * I am utilizing the PHP parser as a templating engine:
   * doodle_tempalte.php is simply included and evaled from syntax.php
   * The variable  $template   will be inherited from syntax.php and can be used here.
   */
  global $ID;

  $template = $this->template;
  $c = count($template['choices']);
?>

<!-- Doodle Plugin -->
<form action="<?php echo wl($ID) ?>#<?php echo $template['formId'] ?>" method="post" name="doodle__form" id="<?php echo $template['formId'] ?>" accept-charset="utf-8" >

<input type="hidden" name="sectok" value="<?php echo getSecurityToken() ?>" />
<input type="hidden" name="do" value="show" >
<input type="hidden" name="id" value="<?php echo $ID ?>" >
<input type="hidden" name="formId" value="<?php echo $template['formId'] ?>" >
<input type="hidden" name="edit__entry"   value="">
<input type="hidden" name="delete__entry" value="">

<table class="doodle__results">
	<thead>
	<tr class="title_row" style="grid-template-columns: 2fr repeat(<?php echo $c; ?>, 1fr)">
	  <th class="title_caption" style="grid-column: 1 / <?php echo ($c+2) ?>">
		<?php echo $template['title'] ?>
	  </th>
	</tr>
	<tr class="fields_row" style="grid-template-columns: 2fr repeat(<?php echo $c; ?>, 1fr)">
		<th class="fields_caption"><?php echo $lang['fullname'] ?></th>
<?php foreach ($template['choices'] as $choice) {  ?>
		<th class="fields_data"><?php echo $choice ?></th>
<?php } ?>
	</tr>
	</thead>

	<tbody>
<?php foreach ($template['doodleData'] as $fullname => $userData) { ?>
    <tr class="data_row" style="grid-template-columns: 2fr repeat(<?php echo $c; ?>, 1fr)">
        <td class="data_caption">
          <?php $link = '<a href="https://people.php.net/' . htmlspecialchars($userData['username']) . '">' . htmlspecialchars($userData['username']) . '</a>';?>
          <?php echo (array_key_exists('editLinks', $userData) ? $userData['editLinks'] : '') . $link; ?>
        </td>
        <?php for ($col = 0; $col < $c; $col++) {
            echo $userData['choice'][$col];
        } ?>
    </tr>
<?php } ?>

    <!-- Results / sum per column -->
    <tr class="results_row" style="grid-template-columns: 2fr repeat(<?php echo $c; ?>, 1fr)">
        <td class="results_caption"><?php echo $template['result'] ?></td>
<?php for ($col = 0; $col < $c; $col++) { ?>
        <td class="results_data"><?php echo $template['count'][$col] ?></td>
<?php } ?>
    </tr>

<?php
    /* Input fields, if allowed. */
    echo $template['inputTR']
?>

<?php if (!empty($template['msg'])) { ?>
    <tr class="title_row" style="grid-template-columns: 2fr repeat(<?php echo $c; ?>, 1fr)">
      <td class="title_caption" style="grid-column: 1 / <?php echo ($c+2) ?>">
        <?php echo $template['msg'] ?>
      </td>
    </tr>
<?php } ?>
	</tbody>
</table>

</form>




