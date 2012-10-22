function SL_InteractiveTable(selector) {
	
  $(selector + ' table tbody tr').css({'background-color':'red'});
	$(selector + ' table tbody tr')
	.css('cursor', 'pointer')
	.click(function() {
		var $this = $(this);
		var annotation_val = $this.attr('annotation');
		var annotation_type = $this.attr('type');
		var hash = broadcast.getHash();
		var segment = broadcast.getValueFromUrl('segment', hash);
		var request = function(method, additionalsegment, divId) {
			$('#'+divId).html('<div id="loadingPiwik">'
					+ '<img alt="" src="themes/default/images/loading-blue.gif"> '
					+ 'Loading data... </div>');
			
			$.post('index.php', {
				module: 'SmartLoggent',
				action: method,
				annotation: annotation_val,
				smartloggent_reset_filter_evolution: 'yes',
				idSite: piwik.idSite,
				period: piwik.period,
				segment: additionalsegment,
				date: broadcast.getValueFromUrl('date'),
				type: annotation_type
			}, function(response){
				$('#'+divId).html(response);
			});
		}

		if (annotation_type === 'Class'){
			request('getSearchPhrase', 'SLClass=='+annotation_val, 'relatedSearchPhrases');
		}

		
	});
}

$(document).ready(function() {
	$('h2.sl_hasdescription').live('mouseover', function() {
		var $this = $(this);
		var $link = $(document.createElement('span'))
				.addClass('sl_description_button')
				.html('(?)');
		$this.append($link);
	}).live('mouseout', function() {
		$(this).find('span').remove();
	}).live('click', function() {
		$(this).next().toggle();
	});
});
