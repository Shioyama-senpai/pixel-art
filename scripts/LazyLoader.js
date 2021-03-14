class LazyLoader {
	static loadProperImage(event) {
		var img = event.currentTarget;

		var fullImagePath = img.getAttribute("full-image-source");

		img.onload = LazyLoader.removeBlur;
		img.src = fullImagePath;
	}

	static removeBlur() {
		var img = event.currentTarget;
		img.onload = null;
		img.style.filter = "";
	}
}