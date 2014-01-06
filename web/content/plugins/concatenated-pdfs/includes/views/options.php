<div id="catpdf-wrap" class="wrap">
  <div class="icon32" id="icon-options-general"><br>
  </div>
  <h2><?php echo CATPDF_NAME.' '.__('Options'); ?></h2>
  <?php if( isset($message) && $message!='' ) echo $message; ?>
  <div id="form-wrap">
    <form id="catpdf_form" method="post">
      <div class="field-wrap">
        <div class="field">
          <label><?php echo _e( "User theme's CSS?" ); ?> </label>
          <input type="checkbox" name="enablecss" id="enablecss" <?php echo ( ( isset( $options['enablecss'] ) && $options['enablecss'] == 'on' ) ? 'checked="checked"' : '' );?> >
        </div>
        <div class="note"> <span>(
          <?php _e("Tick this checkbox if you want to enable your theme's main CSS in the PDF."); ?>
          )</span> </div>
      </div>
      <div class="field-wrap">
        <div class="field">
          <label>
            <?php _e( "Export PDF Title" ); ?>
          </label>
          <input type="text" name="title" id="title" value="<?php echo ( ( isset( $options['title'] ) ) ? $options['title'] : '' );?>">
        </div>
        <div class="note"> <span>(
          <?php _e("Put % plus date format(dd,mm,yyyy) to display export date. Ex: Report %dd-%mm-%yyyy.Put keyword '%template' to display the template name. EX: Repost %template."); ?>
          )</span> </div>
      </div>
      <div class="field-wrap">
        <div class="field">
          <label>
            <?php _e( "Enable single post download"); ?>
          </label>
          <input type="checkbox" name="postdl" id="postdl" <?php echo ( ( isset( $options['postdl'] ) && $options['postdl'] == 'on' ) ? 'checked="checked"' : '' );?> >
        </div>
        <div class="note"> <span>(
          <?php _e("Tick this checkbox if you want to enable PDF download on each post."); ?>
          )</span> </div>
      </div>
      <div class="field-wrap">
        <div class="field">
          <label>
            <?php _e( "Post download template" ); ?>
          </label>
          <select name="dltemplate">
            <option <?php selected('def', $options['dltemplate']); ?> value="def"> <?php _e('Default');?> </option>
            <?php if( count( $templates ) ) : ?>
            <?php foreach( $templates as $template ) :?>
            <option <?php selected($template->template_id, $options['dltemplate']); ?> value="<?php echo $template->template_id;?>"><?php echo $template->template_name;?></option>
            <?php endforeach; ?>
            <?php endif; ?>
          </select>
        </div>
        <div class="note"> <span>(
          <?php _e("Select template for single post download. The download will only take the loop part from the selected template."); ?>
          )</span> </div>
      </div>
      <div class="field-wrap">
        <div class="field">
          <label>
            <?php _e( "Custom style" ); ?>
          </label>
          <textarea name="customcss" id="customcss"><?php echo ( ( isset( $options['customcss'] ) ) ? $options['customcss'] : '' );?></textarea>
        </div>
        <div class="note"> <span>(
          <?php _e("Apply your custom styles here. Do not include style tag( &lt;style&gt; , &lt;/style&gt; )."); ?>
          )</span> </div>
      </div>
      <p class="submit">
        <input type="submit" name="catpdf_save_option" class="button-primary" value="Save Changes">
      </p>
    </form>
  </div>
</div>