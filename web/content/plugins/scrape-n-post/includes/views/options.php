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
          <label> <?php _e( "Crawl Depth"); ?> </label>
          <input type="text" name="crawl_depth" id="crawl_depth"  value="<?php echo $scrape_options['crawl_depth']; ?>" class="small-text code" />
        </div>
        <div class="note"> <span>(
          <?php _e("Set the number to the deth you whish to crawl a site.  If the site is big 100-200 is a good choice.  Make sure you have the php max-* limits set to account for a deep crawl before running."); ?>
          )</span> </div>
      </div>


      <div class="field-wrap">
        <div class="field">
          <label> <?php _e( "Useragent string"); ?> </label>
          <input type="text" name="useragent" id="scrape_useragent"  value="<?php echo $scrape_options['useragent']; ?>"  class="large-text code"/>
        </div>
        <div class="note"> <span>(
          <?php _e("Default useragent header to identify yourself when crawling sites."); ?>
          )</span> </div>
      </div>
      <div class="field-wrap">
        <div class="field">
          <label> <?php _e( "Timeout (in seconds)"); ?> </label>
          <input type="text" name="timeout" id="scrape_timeout"  value="<?php echo $scrape_options['timeout']; ?>" class="small-text code" />
        </div>
        <div class="note"> <span>(
          <?php _e("Default timeout interval in seconds for cURL or Fopen. Larger interval might slow down your page."); ?>
          )</span> </div>
      </div>
      
      
      <input type="hidden" name="action" value="update" />
      <p class="submit">
        <input type="submit" name="scrape_save_option" class="button-primary" value="Save Changes">
      </p>
    </form>
  </div>
</div>