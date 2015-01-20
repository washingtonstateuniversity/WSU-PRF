<div id="catpdf-wrap" class="wrap">
  <div class="icon32" id="icon-tools"><br>
  </div>
  <h2><?php echo CATPDF_NAME; ?></h2>
  <?php if( isset($message) && $message!='' ) echo $message; ?>
  <p class="desc-text">
    <?php _e( "The Concatenated PDFs (`<span class='code'>[catpdf]</span>`) plugin allows downloading posts from the site. This form is filling the values that you would find simular to the shortcodes. You can also use the shortcode '<span class='code'>[catpdf]</span>' for frontend implementation. This shortcode display a download link. You can also add parameters on it EX: '<span class='code'>[catpdf text='Download' cat='1' template='1']</span>'." );?>
  </p>
  <div id="form-wrap">
    <form id="catpdf_form" method="post" action="<?php echo $option_url;?>">
      <div class="field-wrap">
        <div class="field">
          <label><?php echo _e( "Span" ); ?></label>
          <span class="sept-mar"><?php echo _e( 'From' ); ?></span>
          <input type="text" class="datepicker" id="from" name="from" value="" >
          <span class="sept-mar"><?php echo _e( 'To' ); ?></span>
          <input type="text" class="datepicker" id="to" name="to" value="" >
        </div>
      </div>
      <div class="field-wrap">
        <div class="field">
        
          <div class="wd200 fl">
            <label><?php _e( "Types" ); ?></label>
            <?php echo $select_types; ?>
            <input class="all-btn sept-mar" type="button" value="Select All">
          </div>
           <div class="wd200 fl">
            <label><?php _e( "Tags" ); ?></label>
            <?php echo $select_tags; ?>
            <input class="all-btn sept-mar" type="button" value="Select All">
          </div>       
        
        
          <div class="wd200 fl">
            <label><?php _e( "Category" ); ?></label>
            <?php echo $select_cats; ?>
            <input class="all-btn sept-mar" type="button" value="Select All">
          </div>
          <div class="wd200 fl">
            <label><?php _e( "Author" ); ?></label>
            <?php echo $select_author; ?>
            <input class="all-btn sept-mar" type="button" value="Select All">
          </div>
          <div class="wd200 fl">
            <label class="marb5">
              <?php _e( "Status" ); ?></label>
            <select id="status" name="status[]" multiple="multiple">
              <option selected="selected" value="any">
              <?php _e( 'Any' );?>
              </option>
              <option value="publish">
              <?php _e( 'Publish' );?>
              </option>
              <option value="pending">
              <?php _e( 'Pending Review' );?>
              </option>
              <option value="draft">
              <?php _e( 'Draft' );?>
              </option>
              <option value="future">
              <?php _e( 'Future' );?>
              </option>
              <option value="private">
              <?php _e( 'Private' );?>
              </option>
            </select>
          </div>
          <div class="clr"></div>
        </div>
        <div class="clr"></div>
        <div class="note"> <span>(
          <?php _e("Select parameters to download. Will download all if each set to blank."); ?>
          )</span> </div>
      </div>
      <div class="field-wrap">
        <div class="field">
          <label><?php _e( "Paper size" ); ?></label>
          <select id="papersize" name="papersize">
            <?php foreach( $select_sizes as $select_size ) : ?>
            <option value="<?php echo $select_size; ?>"><?php echo $select_size; ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="note"> <span>(
          <?php _e("Select paper size."); ?>
          )</span> </div>
      </div>
      <div class="field-wrap">
        <div class="field">
          <label><?php _e( "Orientation" ); ?></label>
          <select id="orientation" name="orientation">
            <?php foreach( $select_ors as $select_or ) : ?>
            <option value="<?php echo $select_or; ?>"><?php echo $select_or; ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="note"> <span>(
          <?php _e("Select paper orientation."); ?>
          )</span> </div>
      </div>
      <div class="field-wrap">
        <div class="field">
          <label><?php _e( "Template" ); ?></label>
          <select name="template">
            <option value="def">Default</option>
            <?php if( count( $templates ) ) : ?>
            <?php foreach( $templates as $template ) :?>
            <option value="<?php echo $template->template_id;?>"><?php echo $template->template_name;?></option>
            <?php endforeach; ?>
            <?php endif; ?>
          </select>
        </div>
        <div class="note"> <span>(
          <?php _e("Select paper orientation."); ?>
          )</span> </div>
      </div>
      <p class="submit">
        <input type="submit" id="catpdf-export" name="catpdf_export" class="button-primary" value="<?php echo _e('Download'); ?>">
      </p>
    </form>
  </div>
</div>