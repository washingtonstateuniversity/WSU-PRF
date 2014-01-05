<?php 
if(isset($urls)){
	var_dump($urls);
}

 ?>
<div id="scrape-wrap" class="wrap">
  <div class="icon32" id="icon-tools"><br>
  </div>
  <h2><?php echo SCRAPE_NAME; ?></h2>
  <?php if( isset($message) && $message!='' ) echo $message; ?>
  <p class="desc-text">
    <?php _e( "" );?>
  </p>
  <div id="form-wrap">
    <form id="scrape_form" method="post" action="<?php echo $option_url;?>">
		<label>Url <input type="url" name="scrape_url" /> </label>
     
        <input type="submit" id="scrape-findlinks" name="scrape_findlinks" class="button-primary" value="<?php echo _e('Crawl for links'); ?>">
       <p class="submit"></p>
    </form>
  </div><div class="clr"></div>
</div>

<div id="catpdf-wrap" class="wrap">
  <div class="icon32" id="icon-tools"><br>
  </div>
  <h2>
    <?php _e('Crawler found urls'); ?>
    <a class="add-new-h2" href="<?php menu_page_url( 'catpdf-add-template' , true );?>">
    <?php _e( 'Add New' );?>
    </a></h2>
  <?php if( isset($message) && $message!='' ) echo $message; ?>
  <form method="post">
    <?php echo $table;?>
  </form>
</div>