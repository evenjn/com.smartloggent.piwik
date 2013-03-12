<script src="plugins/SmartLoggent/templates/sl.js" />

<div style="min-width: 930px">
<select id="lang" onChange="document.location=mixUrl(window.location, 'language', this.options[this.selectedIndex].value);" name="language">>
{foreach from=$availableLanguages item=language}	
	<option {if $languageValue == $language.value}selected{/if} value="{$language.value}">{$language.label}</option>
 {/foreach}
</select>

	<div>
		<h2 class="sl_hasdescription">{'LOC_SL_ClassOverviewPageTitle'|translate}</h2>
		<div class="sl_description_wrapper">
			<div class="sl_description">
				<p class="sl_main_description">
					{'LOC_SL_OverviewDescription'|translate}
				</p>
			</div>
		</div>
	</div>
	
{$evolution}
<br/><br/>
{$geo}

</div>
