class Validator {
	static validate(event) {
		var input = event.currentTarget;

		if (input.required && !input.value) {
			alert("You must provide a value for this!");
			return;
		}

		if (input.type == "file") {
			var allowedExtensions = /(\.png|\.webp)$/i; 

			if (!allowedExtensions.exec(input.value)) {
				input.value = null;
				alert("This file format is not supported.");
				return; 
			}

			if (input.files[0].size > 50000000) {
				input.value = null;
				alert("The selected file is too big.");
				return;
			}
		}
	}
}