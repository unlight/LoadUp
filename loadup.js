$(document).ready(function(){
	
	if ($.fn.autogrow) $('textarea.TextBox').livequery(function() {
		$(this).autogrow();
	});
	
	// clipboard
	var swfpath = gdn.definition('WebRoot') + 'plugins/LoadUp/jquery.clipboard.swf';
	$.clipboardReady(function(){
		$("#Form_MyResult").hover(function(){
			var Text = $(this).val();
			if(Text != ""){
				$.clipboard(Text);
				Text = Text.replace(/</, "&lt;");
				Text = Text.replace(/>/, "&gt;");
				gdn.gdn.inform( Text + " " + gdn.definition("TextClipboarded") );
			}
		});
	}, {swfpath: swfpath, debug: false});
	
	var WebRoot = gdn.definition('WebRoot');
	if ($('#Form_RawData').length > 0) {
		var UploadedData = $('#Form_RawData').val().split("\n");
		
		$('label[for=Form_WithDomain]').click(function(){
			var bWithDomain = $(this).find(':input').is(':checked');
			var Data = [];
			for (var i = 0; i < UploadedData.length; i++) {
				var Value = jQuery.trim(UploadedData[i]);
				if (Value) {
					if (bWithDomain) Value = WebRoot + Value;
					Data[Data.length] = Value;
				}
			}
			$("#Form_RawData").val(Data.join("\n"));
		});		
	}
	
});