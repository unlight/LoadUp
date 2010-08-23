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
	
});