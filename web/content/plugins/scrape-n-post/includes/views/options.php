<?php

//var_dump($scrape_options);

?>


<div id="scrape-wrap" class="wrap">
  <div class="icon32" id="icon-options-general"><br>
  </div>
  <h2><?php echo SCRAPE_NAME.' '.__('Options'); ?></h2>
  <?php if( isset($message) && $message!='' ) echo $message; ?>
  <div id="form-wrap">
    <form id="scrape_form" method="post">
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

        <table class="form-table">

            <tr valign="top">
                <th scope="row"><label>Error handling options</label></th>
                <td>
                <select name="scrape_options[on_error]" id="scrape_on_error" style="width:325px" class="regular-text code" >
                        <option value="error_hide"<?php selected('error_hide', $scrape_options['on_error']); ?>>Fail silently (display blank string on failure)</option>
                        <option value="error_show"<?php selected('error_show', $scrape_options['on_error']); ?>>Display error (can be used while debugging)</option>
                </select>
                <span class="setting-description">Default error handling. Fail silently or display error.</span>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row"><label>Useragent string</label></th>
                <td>
                <input name="scrape_options[useragent]" type="text" id="scrape_useragent" value="<?php echo $scrape_options['useragent']; ?>" class="regular-text code" />
                <span class="setting-description">Default useragent header to identify yourself when crawling sites.</span>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row"><label> Timeout (in seconds)</label></th>
                <td>
                <input name="scrape_options[timeout]" type="text" id="scrape_timeout" value="<?php echo $scrape_options['timeout']; ?>" class="small-text code" />
                <span class="setting-description">Default timeout interval in seconds for cURL or Fopen. Larger interval might slow down your page.</span>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row"><label>Cache expiry (in minutes)</label></th>
                <td>
                <input name="scrape_options[cache]" type="text" id="scrape_cache" value="<?php echo $scrape_options['cache']; ?>" class="small-text code"/>
                <span class="setting-description">Default cache expiry in minutes for cached webpages.</span>
                </td>
            </tr>
        </table>
        <input type="hidden" name="action" value="update" />
      <p class="submit">
        <input type="submit" name="scrape_save_option" class="button-primary" value="Save Changes">
      </p>
      
      
      
      
      
    </form>
  </div>
</div>