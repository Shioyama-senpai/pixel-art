class Utilities {
	static setCookie(key, value, lifetime) {
		var date = new Date();
		date.setTime(date.getTime() + lifetime);
		var expires = "expires="+ date.toUTCString();
		document.cookie = key + "=" + value + ";" + expires + ";path=/";
	}

	static getCookie(key) {
		var name = key + "=";
		var decodedCookie = decodeURIComponent(document.cookie);
		var ca = decodedCookie.split(';');
		for (var i = 0; i < ca.length; i++) {
			var c = ca[i];
			while (c.charAt(0) == ' ') {
				c = c.substring(1);
			}
			if (c.indexOf(name) == 0) {
				return c.substring(name.length, c.length);
			}
		}
		return null;
	}
}