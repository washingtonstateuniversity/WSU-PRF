<div id="catpdf-wrap" class="wrap">
  <div class="icon32" id="icon-tools"><br>
  </div>
  <h2>
    <?php _e('Add Templates'); ?>
  </h2>
  <?php if( isset($message) && $message!='' ) echo $message; ?>
  <p class="desc-text"> </p>
  <div id="form-wrap">
    <form method="post">
      <input type="hidden" id="templateid" name="templateid" value="<?php echo ( isset( $on_edit )?$on_edit->template_id:'' );?>">
      <div class="actions-wrap">
        <input type="submit" id="catpdf-save" name="catpdf_save" class="button-primary" value="<?php _e('Save Template');?>">
      </div>
      <div class="clr"></div>
      <div class="field-wrap">
        <div class="field">
          <label><?php echo _e( "Template Name" ); ?> </label>
          <input type="text" id="templatename" name="templatename" value="<?php echo ( isset( $on_edit )?$on_edit->template_name:'' );?>" />
        </div>
        <div class="note"> <span>(
          <?php _e("Provide template title."); ?>
          )</span> </div>
      </div>
      
      
      
      
      <div class="field-wrap">
        <div class="field">
          <label>
            <?php _e('PDF page header');?>
          </label>
          <div class="clr"></div>
          <div class="inner">
            <div class="wd850">
              <?php
				$args = array( "textarea_name" => "pageheadertemplate",
					'tinymce' => array( 
						'content_css' => CONCATENATEDPDF_PLUGIN_URL . 'css/pdf_style.css' 
				));
				wp_editor( ( isset( $on_edit )?$on_edit->template_loop:'' ) , "pageheadertemplate", $args );
				?>
              <div class="clr"></div>
            </div>
            <div class="code-list wd850">
              <ul>
                <?php 
				  $codes = "";
				  $i=0;
                  foreach($body_templateShortCodes as $code=>$discription){
                      echo "<li><a class='code' rel='{$code}'>{$discription}</a></li>";
					  $codes.=($i>0?",":"")."[{$code}]";
					  $i++;
                  }?>
              </ul>
              <div class="clr"></div>
              <p class="desc-text"> <?php echo __('Quick shortcode reference:'),"<br /><br /><span class='code'>{$codes}</span>
"; ?> </p>
            </div>
            <div class="clr"></div>
          </div>
        </div>
        <div class="clr"></div>
        <div class="note"> <span>(
          <?php _e("Custruct this template's loop part. "); ?>
          )</span> </div>
      </div>
      
      
      
      
      
      
      
      
      
      
      
      
      
      
      <div class="field-wrap">
        <div class="field">
          <label>
            <?php _e('Template Loop');?>
          </label>
          <div class="clr"></div>
          <div class="inner">
            <div class="wd500 fl">
              <?php

				$args = array( "textarea_name" => "looptemplate",
					'tinymce' => array( 
						'content_css' => CONCATENATEDPDF_PLUGIN_URL . 'css/pdf_style.css' 
				));

				wp_editor( ( isset( $on_edit )?$on_edit->template_loop:'' ) , "looptemplate", $args );
				?>
              <div class="clr"></div>
            </div>
            <div class="code-list wd300 fl">
              <ul>
                <?php 
				  $codes = "";
				  $i=0;
                  foreach($loop_templateShortCodes as $code=>$discription){
                      echo "<li><a class='code' rel='{$code}'>{$discription}</a></li>";
					  $codes.=($i>0?",":"")."[{$code}]";
					  $i++;
                  }?>
              </ul>
              <div class="clr"></div>
              <p class="desc-text"> <?php echo __('Quick shortcode reference:'),"<br /><br /><span class='code'>{$codes}</span>
"; ?> </p>
            </div>
            <div class="clr"></div>
          </div>
        </div>
        <div class="clr"></div>
        <div class="note"> <span>(
          <?php _e("Custruct this template's loop part. "); ?>
          )</span> </div>
      </div>
      <div class="field-wrap nomar">
        <div class="field">
          <label>
            <?php _e('Template Body');?>
          </label>
          <div class="clr"></div>
          <div class="inner">
            <div class="wd500 fl">
              <?php

				$args = array( 
					"textarea_name" => "bodytemplate",
					'tinymce' => array( 
						'content_css' => CONCATENATEDPDF_PLUGIN_URL . 'css/pdf_style.css' 
				   ));
				wp_editor( ( isset( $on_edit )?$on_edit->template_body:'' ) , "bodytemplate", $args );

                                ?>
              <div class="clr"></div>
            </div>
            <div class="code-list wd300 fl">
              <ul>
                <?php 
				  $codes = "";
				  $i=0;
                  foreach($body_templateShortCodes as $code=>$discription){
                      echo "<li><a class='code' rel='{$code}'>{$discription}</a></li>";
					  $codes.=($i>0?",":"")."[{$code}]";
					  $i++;
                  }?>
              </ul>
              <div class="clr"></div>
              <p class="desc-text"> <?php echo __('Quick shortcode reference:'),"<br /><br /><span class='code'>{$codes}</span>
"; ?> </p>
            </div>
            <div class="clr"></div>
          </div>
        </div>
        <div class="clr"></div>
        <div class="note"> <span>(
          <?php _e("Custruct this template's body part. "); ?>
          )</span> </div>
      </div>
      <div class="clr"></div>
      <div class="field-wrap">
        <div class="field">
          <label>
            <?php _e('Template Description');?>
          </label>
          <textarea class="ta_standard" name="description"><?php echo ( isset( $on_edit )?$on_edit->template_description:'' );?></textarea>
        </div>
        <div class="note"> <span>(
          <?php _e("Provide a short description of this template."); ?>
          )</span> </div>
      </div>
      <div class="actions-wrap">
        <input type="submit" id="catpdf-save" name="catpdf_save" class="button-primary" value="<?php _e('Save Template');?>">
      </div>
    </form>
  </div>
</div>
