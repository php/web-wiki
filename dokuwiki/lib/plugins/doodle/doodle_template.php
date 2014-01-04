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
  $is_closed = $this->params['closed'];
  $c = count($template['choices']);
?>

<!-- Doodle Plugin -->
<form action="<?php echo wl() ?>" method="post" name="doodle__form" id="<?php echo $template['formId'] ?>" accept-charset="utf-8" >

<input type="hidden" name="sectok" value="<?php echo getSecurityToken() ?>" />
<input type="hidden" name="do" value="show" >
<input type="hidden" name="id" value="<?php echo $ID ?>" >
<input type="hidden" name="formId" value="<?php echo $template['formId'] ?>" >
<input type="hidden" name="edit__entry"   value="">
<input type="hidden" name="delete__entry" value="">

<table class="inline">
  <tbody>
    <tr class="row0">
      <th class="centeralign" colspan="<?php echo ($c+1) ?>">
        <?php echo $template['title'] ?>
      </th>
    </tr>
    <tr class="row1">
        <th class="col0"><?php echo $lang['fullname'] ?></th>
<?php foreach ($template['choices'] as $choice) {  ?>
        <td class="centeralign"><?php echo $choice ?></td>
<?php } ?>
    </tr>

<?php foreach ($template['doodleData'] as $fullname => $userData) { ?>
    <tr>
        <td class="rightalign">
          <?php $fullname = '<a href="//people.php.net/user.php?username=' . $fullname.'">' .$fullname. '</a>';?>
          <?php echo $userData['editLinks'].$fullname.$userData['username'] ?>
        </td>
        <?php
        if ($is_closed || $INFO['userinfo']['name'] == $fullname) {
            for ($col = 0; $col < $c; $col++) {
                echo $userData['choice'][$col];
            }
        } else {
            ?><td class="votehidden" colspan="<?php echo $c ?>">&nbsp;</td><?php
        }
        ?>
    </tr>
<?php } ?>
 
    <!-- Results / sum per column -->
    <tr>
        <th class="rightalign"><b><?php echo $template['result'] ?></b></th>
        <?php
        if ($is_closed) {
            for ($col = 0; $col < $c; $col++) {
                ?><th class="centeralign"><b><?php echo $template['count'][$col] ?></b></th><?php
            }
        } else {
            ?><th class="centeralign" colspan="<?php echo $c ?>"><?php echo count($template['doodleData']) ?></th><?php
        }
        ?>
    </tr>

<?php
    /* Input fields, if allowed. */
	echo $template['inputTR'] 
?>

<?php if (!empty($template['msg'])) { ?>    
    <tr>
      <td colspan="<?php echo $c+1 ?>">
        <?php echo $template['msg'] ?>
      </td>
    </tr>
<?php } ?>

  </tbody>
</table>

</form>




