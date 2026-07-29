/*
 * Adressen-Verwaltung: Kopieren der ausgewählten E-Mail-Adressen
 *
 * Kommt ohne jQuery aus, weil Contao 4/5 jQuery nicht mehr standardmäßig
 * einbindet. Bevorzugt wird die Clipboard-API genutzt; steht sie nicht zur
 * Verfügung (z.B. ohne HTTPS), wird auf das Textfeld zurückgegriffen.
 */
document.addEventListener('DOMContentLoaded', function () {
	var button = document.getElementById('kopieren');
	var textarea = document.getElementById('kopiertext');

	if (!button || !textarea) {
		return;
	}

	button.addEventListener('click', function () {
		var checkboxes = document.querySelectorAll('input.email-auswahl:checked');
		var zeilen = [];

		Array.prototype.forEach.call(checkboxes, function (checkbox) {
			zeilen.push(checkbox.value);
		});

		var text = zeilen.join('\n');

		if (!text) {
			return;
		}

		if (navigator.clipboard && navigator.clipboard.writeText) {
			navigator.clipboard.writeText(text).catch(function () {
				kopiereUeberTextfeld(textarea, text);
			});

			return;
		}

		kopiereUeberTextfeld(textarea, text);
	});
});

/**
 * Ausweichlösung für Browser ohne Clipboard-API.
 */
function kopiereUeberTextfeld(textarea, text) {
	textarea.style.display = 'block';
	textarea.value = text;
	textarea.select();

	try {
		document.execCommand('copy');
	} catch (err) {
		window.console && window.console.error('Adressen: Kopieren nicht möglich', err);
	}

	textarea.style.display = 'none';
}
