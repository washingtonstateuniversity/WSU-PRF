<?php 
//var_dump($options);

?>

<div id="catpdf-wrap" class="wrap">
  <div class="icon32" id="icon-options-general"><br>
  </div>
  <h2><?php echo CATPDF_NAME.' '.__('Options'); ?></h2>
  <?php if( isset($message) && $message!='' ) echo $message; ?>
  <div id="form-wrap">
    <form id="catpdf_form" method="post">

      <div class="field-wrap"><a href="#" class="help" title="View Help"><span class="dashicons dashicons-editor-help"></span></a>
<div class="field">
          <label>
            <?php _e( "Enable single post download"); ?>
          </label>
          <input type="checkbox" name="postdl" id="postdl" value="1" <?=checked($options['postdl'],1)?>/>
        </div>
        <div class="note block">
				<div class="note_block">
					<p><?php _e("Tick this checkbox if you want to enable PDF download on each post.")?></p>
				</div>
			</div>
		</div>  
	  
	  
		<h3>Post concatenation generation defaults</h3>
		<div class="field-wrap"><a href="#" class="help" title="View Help"><span class="dashicons dashicons-editor-help"></span></a>
<div class="field">
		  <label>
			<?=_e( "Export PDF Title" ); ?>
		  </label>
		  <input type="text" name="concat[title]" id="title" value="<?=( ( isset( $options['concat']['title'] ) ) ? $options['concat']['title'] : '' )?>">
		</div>
		<div class="note block">
				<div class="note_block">
					<p><?=_e("Put % plus date format(dd,mm,yyyy) to display export date. Ex: Report %dd-%mm-%yyyy.Put keyword '%template' to display the template name. EX: Repost %template.")?></p>
				</div>
			</div>
		</div>  
		<div class="field-wrap"><a href="#" class="help" title="View Help"><span class="dashicons dashicons-editor-help"></span></a>
<div class="field">
			  <label>
				<?php _e( "Post download template" ); ?>
			  </label>
			  <select name="concat[dltemplate]">
				<option <?php selected('default', $options['concat']['dltemplate']); ?> value="default"> <?php _e('Default');?> </option>
				<?php if( count( $templates ) ) : ?>
					<?php foreach( $templates as $template ) :?>
					<option <?php selected($template->template_id, $options['concat']['dltemplate']); ?> value="<?=$template->template_id?>"><?=$template->template_name?></option>
					<?php endforeach; ?>
				<?php endif; ?>
			  </select>
			</div>
			<div class="note block">
				<div class="note_block">
					<p><?=_e("Select template for single post download. The download will only take the loop part from the selected template.")?></p>
				</div>
			</div>
		</div>  
		
		  <div class="field-wrap"><a href="#" class="help" title="View Help"><span class="dashicons dashicons-editor-help"></span></a>
<div class="field">
			  <label><?= _e( "Use theme's CSS?" ); ?> </label>
			  <input type="checkbox" name="concat[enablecss]" id="enablecss"  value="true" <?=checked($options['concat']['enablecss'],"true")?>/>
			</div>
			<div class="note block">
				<div class="note_block">
					<p><?php _e("Tick this checkbox if you want to enable your theme's main CSS in the PDF.")?></p>
				</div>
			</div>
		</div>  
		
		
		<div class="field-wrap"><a href="#" class="help" title="View Help"><span class="dashicons dashicons-editor-help"></span></a>
<div class="field">
			  <label>
				<?php _e( "Custom style" ); ?>
			  </label>
			  <textarea name="concat[customcss]" id="customcss"><?=( ( isset( $options['concat']['customcss'] ) ) ? $options['concat']['customcss'] : '' )?></textarea>
			</div>
			<div class="note block">
				<div class="note_block">
					<p><?php _e("Apply your custom styles here. Do not include style tag( &lt;style&gt; , &lt;/style&gt; ).")?></p>
				</div>
			</div>
		</div>  
	  
	  
	  <div id="single_post_generation" class="<?=($options['postdl']==1?"active":"")?>">
		<h3>Single post generation defaults</h3>
				<div class="field-wrap"><a href="#" class="help" title="View Help"><span class="dashicons dashicons-editor-help"></span></a>
<div class="field">
		  <label>
			<?php _e( "Export PDF Title" ); ?>
		  </label>
		  <input type="text" name="single[title]" id="title" value="<?=( ( isset( $options['single']['title'] ) ) ? $options['single']['title'] : '' );?>">
		</div>
		<div class="note block">
				<div class="note_block">
					<p><?php _e("Put % plus date format(dd,mm,yyyy) to display export date. Ex: Report %dd-%mm-%yyyy.Put keyword '%template' to display the template name. EX: Repost %template.")?></p>
				</div>
			</div>
		</div>  
		<div class="field-wrap"><a href="#" class="help" title="View Help"><span class="dashicons dashicons-editor-help"></span></a>
<div class="field">
			  <label>
				<?php _e( "Post download template" ); ?>
			  </label>
			  <select name="single[dltemplate]">
				<option <?php selected('default', $options['single']['dltemplate']); ?> value="default"> <?php _e('Default');?> </option>
				<?php if( count( $templates ) ) : ?>
				<?php foreach( $templates as $template ) :?>
				<option <?php selected($template->template_id, $options['single']['dltemplate']); ?> value="<?php echo $template->template_id;?>"><?php echo $template->template_name;?></option>
				<?php endforeach; ?>
				<?php endif; ?>
			  </select>
			</div>
			<div class="note block">
				<div class="note_block">
					<p><?php _e("Select template for single post download. The download will only take the loop part from the selected template.")?></p>
				</div>
			</div>
		</div>  
		
		  <div class="field-wrap"><a href="#" class="help" title="View Help"><span class="dashicons dashicons-editor-help"></span></a>
<div class="field">
			  <label><?php echo _e( "Use theme's CSS?" ); ?> </label>
			  <input type="checkbox" name="single[enablecss]" id="enablecss" <?=checked($options['single']['enablecss'],"true")?> value="true">
			</div>
			<div class="note block">
				<div class="note_block">
					<p><?php _e("Tick this checkbox if you want to enable your theme's main CSS in the PDF.")?></p>
				</div>
			</div>
		</div>  
		
		
		<div class="field-wrap"><a href="#" class="help" title="View Help"><span class="dashicons dashicons-editor-help"></span></a>
<div class="field">
			  <label>
				<?php _e( "Custom style" ); ?>
			  </label>
			  <textarea name="single[customcss]" id="customcss"><?=( ( isset( $options['single']['customcss'] ) ) ? $options['single']['customcss'] : '' );?></textarea>
			</div>
			<div class="note block">
				<div class="note_block">
					<p><?php _e("Apply your custom styles here. Do not include style tag( &lt;style&gt; , &lt;/style&gt; ).")?></p>
				</div>
			</div>
		</div>  
	</div>
	  
	  
	  
	  
	  
	  
	  <h3>PDF generation defaults</h3>
	  
		<div class="field-wrap"><a href="#" class="help" title="View Help"><span class="dashicons dashicons-editor-help"></span></a>
			<div class="field">
				<label><?=_e( "Use Unicode fonts" )?></label>
				<input type="checkbox" name="DOMPDF_UNICODE_ENABLED" id="DOMPDF_UNICODE_ENABLED" value="true" <?=checked($options['DOMPDF_UNICODE_ENABLED'],"true")?>>
			</div>
			<div class="note block">
				<div class="note_block">
					<p><?=_e("When enabled, dompdf can support all Unicode glyphs. Any glyphs used in a document must be present in your fonts, however.")?></p>
				</div>
			</div>
		</div>  
		<div class="field-wrap"><a href="#" class="help" title="View Help"><span class="dashicons dashicons-editor-help"></span></a>
			<div class="field">
				<label><?=_e( "Enable font subsetting" )?></label>
				<input type="checkbox" name="DOMPDF_ENABLE_FONTSUBSETTING" id="DOMPDF_ENABLE_FONTSUBSETTING" value="true" <?=checked($options['DOMPDF_ENABLE_FONTSUBSETTING'],"true")?>>
			</div>
			<div class="note block">
				<div class="note_block">
					<p><?=_e("Whether to enable font subsetting or not.  This can lead to heavier files and longer processing time.")?></p>
				</div>
			</div>
		</div>  
		
		
		<div class="field-wrap"><a href="#" class="help" title="View Help"><span class="dashicons dashicons-editor-help"></span></a>
			<div class="field">
			<?php if(!empty($styles)): ?>
				<label><?=_e( "Default Style to use" )?> </label>
				<select name="style" id="style" >
					<?php foreach($styles as $name): ?>
					<option <?=selected($options['style'],$name)?>><?=$name?></option>
					<?php endforeach;?>
				</select>
			<?php else: ?>
				<h4>the theme you have chossen has no styles to choose from at this time.</h4>
			<?php endif; ?>
			</div>
			<div class="note block"><div class="note_block"><?=_e("You may override the default templates to build parts of the PDFs by adding a folder in your theme folder named ")?><code>concatenated-pdfs</code><?=_e(".  If you put a folder with in that theme override, that folder name is your style that you can choose to use.  It will use the first one found.  The fallback progrssion is as follows:")?> <i>(<?=_e("NOTE: this is an example theme of 'spine' in use with a style of 'bbmp' for cover.php")?>)</i>
			<ol>
				<li><b>wp-content/themes/spine/concatenated-pdfs/bbmp/</b>cover.php <i>-(look here first)</i></li>
				<li><b>wp-content/themes/spine/concatenated-pdfs/</b>cover.php <i>-(if not in `bbmp/` look here)</i></li>
				<li><b>wp-content/plugins/concatenated-pdfs/templates/</b>cover.php <i>-(default used if nothign loaded)</i></li>
			</ol>
			</div></div>
		</div>

		
		<div class="field-wrap"><a href="#" class="help" title="View Help"><span class="dashicons dashicons-editor-help"></span></a>
			<div class="field">
				<label><?=_e( "Media view rendered into pdf" )?> </label>
				<select name="DOMPDF_DEFAULT_MEDIA_TYPE" id="DOMPDF_DEFAULT_MEDIA_TYPE" >
					<?php foreach($media_types as $name): ?>
					<option <?=selected($options['DOMPDF_DEFAULT_MEDIA_TYPE'],$name)?>><?=$name?></option>
					<?php endforeach;?>
				</select>
			</div>
			<div class="note block"><div class="note_block"><?=_e("Note, even though the generated pdf file is intended for print output, the desired content might be different (e.g. screen or projection view of html file).  Therefore allow specification of content here.")?></div></div>
		</div>
		<div class="field-wrap"><a href="#" class="help" title="View Help"><span class="dashicons dashicons-editor-help"></span></a>
			<div class="field">
				<label><?=_e( "Default paper size." )?> </label>
				<select name="DOMPDF_DEFAULT_PAPER_SIZE" id="DOMPDF_DEFAULT_PAPER_SIZE" >
					<?php foreach($sizes as $name=>$spec): ?>
					<option <?=selected($options['DOMPDF_DEFAULT_PAPER_SIZE'],$name)?> value="<?=$name?>"><?=( $name."   - <span>( ".implode(", ",$spec)." )</span>" )?></option>
					<?php endforeach;?>
				</select>

			</div>
			<div class="note block"><div class="note_block"><?=_e("Dimensions of paper sizes in points.  The format is top left point coordinate to bottom right point coordinate (TLy,TLx,BRy,BRx). North America standard is 'letter'; other countries generally 'a4'")?></div></div>
		</div>
      <div class="field-wrap"><a href="#" class="help" title="View Help"><span class="dashicons dashicons-editor-help"></span></a>
        <div class="field">
          <label><?php _e( "Orientation" ); ?></label>
          <select id="orientation" name="DOMPDF_DEFAULT_ORIENTATION">
            <?php foreach( $select_ors as $select_or ) : ?>
            <option <?=selected($options['DOMPDF_DEFAULT_ORIENTATION'],$select_or)?>  value="<?php echo $select_or; ?>"><?php echo $select_or; ?></option>
            <?php endforeach; ?>
          </select>
        </div>
		  <div class="note block"><div class="note_block"><?=_e("Select paper orientation.")?></div></div>
      </div>
		<div class="field-wrap"><a href="#" class="help" title="View Help"><span class="dashicons dashicons-editor-help"></span></a>
			<div class="field">
				<label><?=_e( "Default font family" )?> </label>
				<input type="text" name="DOMPDF_DEFAULT_FONT" id="DOMPDF_DEFAULT_FONT" value="<?=$options['DOMPDF_DEFAULT_FONT']?>">
			</div>
			<div class="note block"><div class="note_block"><?=_e("The default font family")?></div></div>
		</div>	  	  
		<div class="field-wrap"><a href="#" class="help" title="View Help"><span class="dashicons dashicons-editor-help"></span></a>
			<div class="field">
				<label><?=_e( "Image DPI settingv" )?> </label>
				<input type="text" name="DOMPDF_DPI" id="DOMPDF_DPI" value="<?=$options['DOMPDF_DPI']?>">
			</div>
			<div class="note block">
				<div class="note_block">
<p><?=_e("This setting determines the default DPI setting for images and fonts.  The DPI may be overridden for inline images by explictly setting the image's width &amp; height style attributes (i.e. if the image's native width is 600 pixels and you specify the image's width as 72 points, the image will have a DPI of 600 in the rendered PDF.  The DPI of background images can not be overridden and is controlled entirely via this parameter")?>.</p>

<p><?=_e("For the purposes of DOMPDF, pixels per inch (PPI) = dots per inch (DPI). If a size in html is given as px (or without unit as image size), this tells the corresponding size in pt at 72 DPI. This adjusts the relative sizes to be similar to the rendering of the html page in a reference browser.")?></p>
 
<p><?=_e("In pdf, always 1 pt = 1/72 inch")?></p>

<p><?=_e("Rendering resolution of various browsers in px per inch:<br>
Windows Firefox and Internet Explorer:")?></p>
<pre><code>    SystemControl->Display properties->FontResolution: Default:96, largefonts:120, custom:?</code></pre><br>
Linux Firefox:
<pre><code>    about:config *resolution: Default:96 </code></pre><br>
<pre><code>    (xorg screen dimension in mm and Desktop font dpi settings are ignored)</code></pre><br>

<p><?=_e("Take care about extra font/image zoom factor of browser.")?></p>
<p><?=_e("In images, &lt;img&gt; size in pixel attribute, img css style, are overriding the real image dimension in px for rendering.</p>")?>
				</div>
			</div>
		</div>
		<div class="field-wrap"><a href="#" class="help" title="View Help"><span class="dashicons dashicons-editor-help"></span></a>
			<div class="field">
				<label><?=_e( "Font Height Ratio" )?> </label>
				<input type="text" name="DOMPDF_FONT_HEIGHT_RATIO" id="DOMPDF_FONT_HEIGHT_RATIO" value="<?=$options['DOMPDF_FONT_HEIGHT_RATIO']?>">
			</div>
			<div class="note block">
				<div class="note_block">
					<p><?=_e("A ratio applied to the fonts height to be more like browsers' line height")?></p>
				</div>
			</div>
		</div>
				
		<div class="field-wrap"><a href="#" class="help" title="View Help"><span class="dashicons dashicons-editor-help"></span></a>
			<div class="field">
				<label><?=_e( "Enable inline PHP" )?></label>
				<input type="checkbox" name="DOMPDF_ENABLE_PHP" id="DOMPDF_ENABLE_PHP" value="true" <?=checked($options['DOMPDF_ENABLE_PHP'],"true")?>>
			</div>
			<div class="note block">
				<div class="note_block">
					<p><?=_e("If this setting is set to true then DOMPDF will automatically evaluate inline PHP contained within &lt;script type='text/php''&gt; ... &lt;/script&gt; tags.")?></p>
				</div>
			</div>
		</div> 
		<div class="field-wrap"><a href="#" class="help" title="View Help"><span class="dashicons dashicons-editor-help"></span></a>
			<div class="field">
				<label><?=_e( "Enable inline Javascript" )?></label>
				<input type="checkbox" name="DOMPDF_ENABLE_JAVASCRIPT" id="DOMPDF_ENABLE_JAVASCRIPT" value="true" <?=checked($options['DOMPDF_ENABLE_JAVASCRIPT'],"true")?>>
			</div>
			<div class="note block">
				<div class="note_block">
					<p><?=_e("If this setting is set to true then DOMPDF will automatically insert JavaScript code contained within &lt;script type='text/javascript'&gt; ... &lt;/script&gt; tags.")?></p>
				</div>
			</div>
		</div> 
		<div class="field-wrap"><a href="#" class="help" title="View Help"><span class="dashicons dashicons-editor-help"></span></a>
			<div class="field">
				<label><?=_e( "Enable remote file access" )?></label>
				<input type="checkbox" name="DOMPDF_ENABLE_REMOTE" id="DOMPDF_ENABLE_REMOTE" value="true" <?=checked($options['DOMPDF_ENABLE_REMOTE'],"true")?>>
			</div>
			<div class="note block">
				<div class="note_block">
					<p><?=_e("If this setting is set to true, DOMPDF will access remote sites for images and CSS files as required.")?></p>
					<p><b><?=_e("NOTE")?>:</b><?=_e("Make sure you know where the content is coming from as you will be pulling from and processing items, so be mindfull of who's content you trust.")?></p>
				</div>
			</div>
		</div> 


		<div class="field-wrap"><a href="#" class="help" title="View Help"><span class="dashicons dashicons-editor-help"></span></a>
			<div class="field">
				<label><?=_e( "Enable CSS float" )?></label>
				<input type="checkbox" name="DOMPDF_ENABLE_CSS_FLOAT" id="DOMPDF_ENABLE_CSS_FLOAT" value="true" <?=checked($options['DOMPDF_ENABLE_CSS_FLOAT'],"true")?>>
			</div>
			<div class="note block">
				<div class="note_block">
					<p><?=_e("Allows people to disabled CSS float support")?></p>
				</div>
			</div>
		</div> 

		<div class="field-wrap"><a href="#" class="help" title="View Help"><span class="dashicons dashicons-editor-help"></span></a>
			<div class="field">
				<label><?=_e( "Use HTML5 Lib parser" )?></label>
				<input type="checkbox" name="DOMPDF_ENABLE_HTML5PARSER" id="DOMPDF_ENABLE_HTML5PARSER" value="true" <?=checked($options['DOMPDF_ENABLE_HTML5PARSER'],"true")?>>
			</div>
			<div class="note block">
				<div class="note_block">
					<p><?=_e("This provides a little more fault tolerant processing of the html.")?></p>
				</div>
			</div>
		</div>
		
		
		
		
		<h3>Debugging</h3>
		<div class="field-wrap select_area" id="debug">
			<div class="field">
				<label><?=_e( "_dompdf_show_warnings" )?></label>
				<input type="checkbox" name="_dompdf_show_warnings" id="_dompdf_show_warnings" value="true" <?=checked($options['_dompdf_show_warnings'],"true")?>>
			</div>
			<div class="field">
				<label><?=_e( "_dompdf_debug" )?></label>
				<input type="checkbox" name="_dompdf_debug" id="_dompdf_debug" value="true" <?=checked($options['_dompdf_debug'],"true")?>>
			</div>
			<div class="field">
				<label><?=_e( "DEBUGPNG" )?></label>
				<input type="checkbox" name="DEBUGPNG" id="DEBUGPNG" value="true" <?=checked($options['DEBUGPNG'],"true")?>>
			</div>
			<div class="field">
				<label><?=_e( "DEBUGKEEPTEMP" )?></label>
				<input type="checkbox" name="DEBUGKEEPTEMP" id="DEBUGKEEPTEMP" value="true" <?=checked($options['DEBUGKEEPTEMP'],"true")?>>
			</div>
			<div class="field">
				<label><?=_e( "DEBUGCSS" )?></label>
				<input type="checkbox" name="DEBUGCSS" id="DEBUGCSS" value="true" <?=checked($options['DEBUGCSS'],"true")?>>
			</div>
			<div class="field">
				<label><?=_e( "DEBUG_LAYOUT" )?></label>
				<input type="checkbox" name="DEBUG_LAYOUT" id="DEBUG_LAYOUT" value="true" <?=checked($options['DEBUG_LAYOUT'],"true")?>>
			</div>
			<div class="field">
				<label><?=_e( "DEBUG_LAYOUT_LINES" )?></label>
				<input type="checkbox" name="DEBUG_LAYOUT_LINES" id="DEBUG_LAYOUT_LINES" value="true" <?=checked($options['DEBUG_LAYOUT_LINES'],"true")?>>
			</div>
			<div class="field">
				<label><?=_e( "DEBUG_LAYOUT_BLOCKS" )?></label>
				<input type="checkbox" name="DEBUG_LAYOUT_BLOCKS" id="DEBUG_LAYOUT_BLOCKS" value="true" <?=checked($options['DEBUG_LAYOUT_BLOCKS'],"true")?>>
			</div>
			<div class="field">
				<label><?=_e( "DEBUG_LAYOUT_INLINE" )?></label>
				<input type="checkbox" name="DEBUG_LAYOUT_INLINE" id="DEBUG_LAYOUT_INLINE" value="true" <?=checked($options['DEBUG_LAYOUT_INLINE'],"true")?>>
			</div>
			<div class="field">
				<label><?=_e( "DEBUG_LAYOUT_PADDINGBOX" )?></label>
				<input type="checkbox" name="DEBUG_LAYOUT_PADDINGBOX" id="DEBUG_LAYOUT_PADDINGBOX" value="true" <?=checked($options['DEBUG_LAYOUT_PADDINGBOX'],"true")?>>
			</div>
			<div class="select block">
				<label>Click to <b><span>Select</span></b>  All the debug options
 All:</label>
				<input type="checkbox" class="alter_all ALL_OFF" value=""/>
			</div>
		</div>

	  
	  
	  
	  
	  
	  
      <p class="submit">
        <input type="submit" name="catpdf_save_option" class="button-primary" value="Save Changes">
      </p>
    </form>
  </div>
</div>