(function($) {
	$(document).ready(function() {

		$('#kopieren').click(function() {
			// Die markierten Elemente selektieren
			var elements = $("input.email-auswahl:checked");
			var text = '';
			
			// Schleife über die einzelnen Elemente
			$.each(elements, function(index, item) 
			{
				text = text + $(this).val() + "\n";
				//alert("Value:" + $(this).val());
			});

    		$('#kopiertext').css('display', 'block');
    		$('#kopiertext').html(text);
    		$('#kopiertext').select();
			try {
			    var successful = document.execCommand('copy');
			    var msg = successful ? 'successful' : 'unsuccessful';
			    console.log('Copying text command was ' + msg);
			} catch (err) {
			    console.error('Oops, unable to copy');
			}
    		$('#kopiertext').css('display', 'none');
			//alert(window.clipboardData.getData('Text'));
		});

	});
})(jQuery);
