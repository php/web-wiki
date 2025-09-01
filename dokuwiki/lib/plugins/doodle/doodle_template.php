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

<div class="doodle__results">
    <div class="title_row" style="grid-template-columns: 2fr repeat(<?php echo $c; ?>, 1fr)">
      <div class="title_caption" style="grid-column: 1 / <?php echo ($c+2) ?>">
        <?php echo $template['title'] ?>
      </div>
    </div>
    <div class="fields_row" style="grid-template-columns: 2fr repeat(<?php echo $c; ?>, 1fr)">
        <div class="fields_caption"><?php echo $lang['fullname'] ?></div>
<?php foreach ($template['choices'] as $choice) {  ?>
        <div class="fields_data"><?php echo $choice ?></div>
<?php } ?>
    </div>

<?php foreach ($template['doodleData'] as $fullname => $userData) { ?>
    <div class="data_row" style="grid-template-columns: 2fr repeat(<?php echo $c; ?>, 1fr)">
        <div class="data_caption">
          <?php $link = '<a href="https://people.php.net/' . htmlspecialchars($userData['username']) . '">' . htmlspecialchars($userData['username']) . '</a>';?>
          <?php echo (array_key_exists('editLinks', $userData) ? $userData['editLinks'] : '') . $link; ?>
        </div>
        <?php for ($col = 0; $col < $c; $col++) {
            echo $userData['choice'][$col];
        } ?>
    </div>
<?php } ?>

    <!-- Results / sum per column -->
    <div class="results_row" style="grid-template-columns: 2fr repeat(<?php echo $c; ?>, 1fr)">
        <div class="results_caption"><?php echo $template['result'] ?></div>
<?php for ($col = 0; $col < $c; $col++) { ?>
        <div class="results_data"><?php echo $template['count'][$col] ?></div>
<?php } ?>
    </div>

<?php
    /* Input fields, if allowed. */
    echo $template['inputTR']
?>

<?php if (!empty($template['msg'])) { ?>
    <div class="title_row" style="grid-template-columns: 2fr repeat(<?php echo $c; ?>, 1fr)">
      <div class="title_caption" style="grid-column: 1 / <?php echo ($c+2) ?>">
        <?php echo $template['msg'] ?>
      </div>
    </div>
<?php } ?>

</div>

</form>




