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

function showDiv(id) {
	$('.paneldivMain').fadeOut();
	$('.paneldiv').fadeOut();
	$('.columndiv').fadeOut();
	$('#' + id).fadeIn();
}

function mixUrl(url, param, value) {
	newUrl = "";
	
	arUrl = (url + '').split("?");
	site = arUrl[0];
	params = arUrl[1];
	arParams = params.split("&");
	
	newUrl = site + "?";
	
	found = 0;
	
	for (idx = 0; idx < arParams.length; idx++) {
		arPSharp = arParams[idx].split("#")
		
		if (arPSharp.length == 2) {
			arP1 = arPSharp[0].split("=")
			key1 = arP1[0]
			val1 = arP1[1]
			
			arP2 = arPSharp[1].split("=")
			key2 = arP2[0]
			val2 = arP2[1]
			
			if (key1 == param) {
				found = 1;
				val1 = value
			}
				
			if (key2 == param) {
				found = 1; 
				val2 = value
			}
				
			newUrl = newUrl + key1 + "=" + val1 + "#" + key2 + "=" + val2 + "&";
				
		}
		else {
			
			arP = arParams[idx].split("=");
		
			key = arP[0];
			val = arP[1]

			if (key == param) {
				found = 1;
				val = value;
			}
		
			newUrl = newUrl + key + "=" + val + "&";
		}		
	}

	if (!found) 
		newUrl = newUrl + param + "=" + value;
	else
		newUrl = newUrl.substr(0,newUrl.length-1);
	
	return newUrl;
}

