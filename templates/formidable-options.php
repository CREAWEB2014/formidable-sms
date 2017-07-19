<div class="wrap">
  
  <form method="post" action="options.php">
    
    <div class="icon32"><img src="<?php echo plugins_url( 'images/logo_32px_32px.png', dirname( __FILE__ ) ); ?>" /></div>
    <h2>Formidable SMS Options</h2>
    
    <?php settings_fields( 'clockwork_formidable' ); ?>
    <?php do_settings_sections( 'clockwork_formidable' ); ?>

<h3>Forms</h3>
    
<p><?php __( 'Numbers should be entered in international format, e.g. 447909123456. The following tags can be used in your SMS messages, though please bear in mind that they may take you over your character limits: <kbd>%form%</kbd> for the form name; numeric identifiers e.g. <kbd>%1%</kbd>, <kbd>%2%</kbd> for fields 1 and 2 respectively.' ); ?></p>
    
<table class="widefat fixed" cellspacing="0">
  <thead>
  <tr>
  <th scope="col" class="manage-column" width="15%">Form Name</th>
  <th scope="col" class="manage-column" width="20%">Send To Number</th>
  <th scope="col" class="manage-column">Message</th>
  <th scope="col" class="manage-column" width="15%">Active</th>
  </tr>
  </thead>

  <tfoot>
  <tr>
  <th scope="col" class="manage-column" width="15%">Form Name</th>
  <th scope="col" class="manage-column" width="20%">Send To Number</th>
  <th scope="col" class="manage-column">Message</th>
  <th scope="col" class="manage-column" width="15%">Active</th>
  </tr>
  </tfoot>
    
  <?php if( empty( $this->forms ) ) { ?> 
  <tbody class="list:user user-list">
    <tr>
    <td colspan="4" style="padding: 20px;">
    To get started, please add some forms.
    </td>
    </tr>
    </tbody>
  <?php } else { ?>
  <tbody class="list:user user-list">
    <?php 
    foreach( $this->forms as $form ) {
      $options = get_option( 'clockwork_formidable_form_' . $form->id );
      $active = $options['active'];
      $to = $options['to'];
      if( empty( $to ) ) {
        $opt = get_option( 'clockwork_formidable' );
        $to = $opt['default_to'];
      }
      $message = $options['message'];
    ?>    
    <tr>
        <td style="padding: 10px 8px;"><p style="padding-top: 3px"><?php echo $form->name; ?></p></td>
        
        <td style="padding: 10px 8px;"><input type="text" size="15" maxlength="13" value="<?php echo $to; ?>" name="to[<?php echo $form->id; ?>]" style="padding: 3px;" /></td>
        
        <td style="padding: 10px 8px;"><input type="text" size="50" maxlength="500" value="<?php echo $message; ?>" name="message[<?php echo $form->id; ?>]" style="padding: 3px;" /></td>
        
      <td style="padding: 10px 8px;"><input type="checkbox" value="1" name="active[<?php echo $form->id; ?>]" <?php if( $active == '1' ) { ?> checked="checked"<?php } ?> /></td>           
    </tr>
    <?php } ?>
  </tbody>      
  <?php } ?>
  
</table>
            
<br /><input name="submit" type="submit" class="button-primary" value="Save Changes" />
  
</form>

</div>
