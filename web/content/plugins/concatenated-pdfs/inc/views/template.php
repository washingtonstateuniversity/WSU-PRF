<div id="catpdf-wrap" class="wrap">
  <div class="icon32" id="icon-tools"><br>
  </div>
  <h2>
    <?php _e('Add Templates'); ?>
  </h2>
  <?php if( isset($message) && $message!='' ) echo $message; ?>
  <p class="desc-text">
  </p>
  <div id="form-wrap">
    <form method="post">
      <div class="actions-wrap">
        <input type="submit" id="catpdf-save" name="catpdf_save" class="button-primary" value="<?php _e('Save Template');?>">
      </div>
      <div class="clr"></div>
      <input type="hidden" id="templateid" name="templateid" value="<?php echo ( isset( $on_edit )?$on_edit->template_id:'' );?>">
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
            <?php _e('Template Description');?>
          </label>
          <textarea class="ta_standard" name="description"><?php echo ( isset( $on_edit )?$on_edit->template_description:'' );?></textarea>
        </div>
        <div class="note"> <span>(
          <?php _e("Provide a short description of this template."); ?>
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
                <li><a class="code" rel="title">Title</a></li>
                <li><a class="code" rel="excerpt">Excerpt</a></li>
                <li><a class="code" rel="content">Content</a></li>
                <li><a class="code" rel="permalink">Permalink</a></li>
                <li><a class="code" rel="date">Date</a></li>
                <li><a class="code" rel="author">Author</a></li>
                <li><a class="code" rel="author_photo">Author Photo</a></li>
                <li><a class="code" rel="author_description">Author Description</a></li>
                <li><a class="code" rel="status">Status</a></li>
                <li><a class="code" rel="featured_image">Featured Image</a></li>
                <li><a class="code" rel="category">Category</a></li>
                <li><a class="code" rel="tags">Tags</a></li>
                <li><a class="code" rel="comments_count">Comments Count</a></li>
              </ul>
              <div class="clr"></div>
              <p class="desc-text">
                <?php _e( 'In case you are user the html editor, here is the shortcode reference: 

                                <br /><br /><span class="code">[title],[excerpt],[content],[permalink],[date],[author],[author_photo],[author_description],[status],[featured_image],[category],[tags],[comments_count]</span>
' ); ?>
              </p>
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
				   ) 
				 );
				wp_editor( ( isset( $on_edit )?$on_edit->template_body:'' ) , "bodytemplate", $args );

                                ?>
              <div class="clr"></div>
            </div>
            <div class="code-list wd300 fl">
              <ul>
                <li><a class="code" rel="loop">Loop</a></li>
                <li><a class="code" rel="site_title">Site Title</a></li>
                <li><a class="code" rel="site_tagline">Site Tagline</a></li>
                <li><a class="code" rel="site_url">Site URL</a></li>
                <li><a class="code" rel="date_today">Date Today</a></li>
                <li><a class="code" rel="from_date">Date(From)</a></li>
                <li><a class="code" rel="to_date">Date(To)</a></li>
                <li><a class="code" rel="categories">Categories</a></li>
                <li><a class="code" rel="post_count">Post Count</a></li>
              </ul>
              <div class="clr"></div>
              <p class="desc-text">
                <?php _e( 'In case you are user the html editor, here is the shortcode reference: 

                                <br /><br /><span class="code">[loop],[site_title],[site_tagline],[site_url],[date_today],[from_date],[to_date],[categories],[post_count]</span>
' ); ?>
              </p>
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
      <div class="actions-wrap">
        <input type="submit" id="catpdf-save" name="catpdf_save" class="button-primary" value="<?php _e('Save Template');?>">
      </div>
    </form>
  </div>
</div>