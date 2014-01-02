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
      
  <script>

  jQuery(function() {

    jQuery( "#tabs" ).tabs();

  });

  </script>


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
            
            
        </div><!--#options tab-->
        
		<div id="header_footer">
            <div class="field-wrap">
            <div class="field">
              <label>
                <?php _e('PDF page headers and footers');?>
              </label>
              <div class="clr"></div>
              <div class="inner">
                <div class="wd800">
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
        </div><!--#header tab-->
        
        <div id="content">
        <script>
			jQuery(function() {
				jQuery( ".tabs" ).tabs().addClass( "ui-tabs-vertical ui-helper-clearfix" );
				jQuery( ".tabs li" ).removeClass( "ui-corner-top" ).addClass( "ui-corner-left" );
			});
		</script>

		<style>
            .ui-tabs-vertical { width: 59em; }
            .ui-tabs-vertical .ui-tabs-nav { padding: .2em .1em .2em .2em; float: left; width: 12em; }
            .ui-tabs-vertical .ui-tabs-nav li { clear: left; width: 100%; border-bottom-width: 1px !important; border-right-width: 0 !important; margin: 0 -1px .2em 0; }
            .ui-tabs-vertical .ui-tabs-nav li a { display:block; }
            .ui-tabs-vertical .ui-tabs-nav li.ui-tabs-active { padding-bottom: 0; padding-right: .1em; border-right-width: 1px; border-right-width: 1px; }
            .ui-tabs-vertical .ui-tabs-panel { padding: 1em; float: right; width: 44em;}
        </style>

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
                            'content_css' => CONCATENATEDPDF_PLUGIN_URL . 'css/pdf_style.css' 
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
              <?php _e("Custruct this template's body part. "); ?>
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
                            'content_css' => CONCATENATEDPDF_PLUGIN_URL . 'css/pdf_style.css' 
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
              <?php _e("Custruct this template's loop part. "); ?>
              )</span> </div>
            </div>
            </div>
  </div>      
        </div>
        
        
	<div><!--#content tab-->
      
      
      
      
      
      
      
      
      
      
      
      
      
      
      
      
      

      <div class="actions-wrap">
        <input type="submit" id="catpdf-save" name="catpdf_save" class="button-primary" value="<?php _e('Save Template');?>">
      </div>
    </form>
  </div>
</div>
