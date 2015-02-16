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
      <div id="tabs">
      <ul>
        <li><a href="#options"><?php echo _e( "Template Options" ); ?></a></li>
        <li><a href="#header_footer"><?php echo _e( "Document Header/Footer" ); ?></a></li>
        <li><a href="#content"><?php echo _e( "Document Content" ); ?></a></li>
      </ul>
      <div id="options">
        <div class="field-wrap">
          <div class="field">
            <label><?php echo _e( "Template Name" ); ?></label>
            <input type="text" id="templatename" name="templatename" value="<?php echo ( isset( $on_edit )?$on_edit->template_name:'' );?>" />
          </div>
          <div class="note"> <span>(
            <?php _e("Provide template title."); ?>
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
      </div>
      <!--#options tab-->
      
      <div id="header_footer">
        <div class="field-wrap">
          <div class="field">
            <div class="useful">
              <?php _e('Use');?> <input type="checkbox" value="1" checked name="options[use_pageheadertemplate]" />
            </div>
            <label>
              <?php _e('PDF page headers');?>
            </label>
            <div class="clr"></div>
            <div class="inner">
              <div class="wd800">
                <?php
                    $args = array( "textarea_name" => "pageheadertemplate",
                        'tinymce' => array( 
                            'content_css' => CATPDF_URL . 'css/pdf_style.css' 
                    ));
                    wp_editor( ( isset( $on_edit )?$on_edit->template_pageheader:'' ) , "pageheadertemplate", $args );
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
            <?php _e("Set the header"); ?>
            )</span> </div>
        </div><!--header -->
        
        <div class="field-wrap">
          <div class="field">
            <div class="useful">
              <?php _e('Use');?> <input type="checkbox" value="1" checked name="options[use_pagefootertemplate]" />
            </div>
            <label>
              <?php _e('PDF page footers');?>
            </label>
            <div class="clr"></div>
            <div class="inner">
              <div class="wd800">
                <?php
                    $args = array( "textarea_name" => "pagefootertemplate",
                        'tinymce' => array( 
                            'content_css' => CATPDF_URL . 'css/pdf_style.css' 
                    ));
                    wp_editor( ( isset( $on_edit )?$on_edit->template_pagefooter:'' ) , "pagefootertemplate", $args );
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
            <?php _e("Set up the footer"); ?>
            )</span> </div>
        </div><!--footer -->
      </div><!--#header_footer tab-->
      
      <div id="content">
        <div class="tabs">
          <ul>
            <li><a href="#body">Body</a></li>
            <li><a href="#loop">Post Loop</a></li>
          </ul>
          <div id="body">
            <div class="field-wrap nomar">
              <div class="field">             
                <label>
                  <?php _e('Template Body');?>
                </label>
                <div class="clr"></div>
                <div class="inner">
                  <div class="wd650">
                    <?php
            
                    $args = array( 
                        "textarea_name" => "bodytemplate",
                        'tinymce' => array( 
                            'content_css' => CATPDF_URL . 'css/pdf_style.css' 
                       ));
                    wp_editor( ( isset( $on_edit )?$on_edit->template_body:'' ) , "bodytemplate", $args );
            
                                    ?>
                    <div class="clr"></div>
                  </div>
                  <div class="code-list wd650">
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
                <?php _e("construct this template's body part. "); ?>
                )</span> </div>
            </div>
          </div>
          <div id="loop">
            <div class="field-wrap">
              <div class="field">
                <label>
                  <?php _e('Template Loop');?>
                </label>
                <div class="clr"></div>
                <div class="inner">
                  <div class="wd650">
                    <?php
                    $args = array( "textarea_name" => "looptemplate",
                        'tinymce' => array( 
                            'content_css' => CATPDF_URL . 'css/pdf_style.css' 
                    ));
                    wp_editor( ( isset( $on_edit )?$on_edit->template_loop:'' ) , "looptemplate", $args );
                    ?>
                    <div class="clr"></div>
                  </div>
                  <div class="code-list wd650">
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
                <?php _e("construct this template's loop part. "); ?>
                )</span> </div>
            </div>
          </div>
        </div>
      </div>
      <div>
      <!--#content tab-->
      <div class="actions-wrap">
        <input type="submit" id="catpdf-save" name="catpdf_save" class="button-primary" value="<?php _e('Save Template');?>">
      </div>
    </form>
  </div>
</div>
