function initFlatpickrById(selector, options = {}) {
	const element = document.querySelector(selector);
	if (!element) return;

	if (element._flatpickr) {
		element._flatpickr.destroy();
	}

	flatpickr(element, options);
}

function initFlatpickrFields() {
	initFlatpickrById("#flatpickr_basic", {
		dateFormat: "d-m-Y",
		disableMobile: true
	});

	initFlatpickrById("#flatpickr_datetime", {
		dateFormat: "Y-m-d H:i",
		enableTime: true,
		disableMobile: true
	});

	initFlatpickrById("#flatpickr_time", {
		noCalendar: true,
		enableTime: true,
		disableMobile: true
	});

	initFlatpickrById("#flatpickr_range", {
		mode: "range"
	});

	initFlatpickrById("#flatpickr_multiple_dates", {
		mode: "multiple"
	});

	initFlatpickrById("#flatpickr_localization", {
		enableTime: true,
		dateFormat: "Y-m-d H:i",
		locale: "fr",
		disableMobile: true
	});

	initFlatpickrById("#flatpickr_inline", {
		enableTime: true,
		dateFormat: "Y-m-d H:i",
		inline: true
	});

	initFlatpickrById("#flatpickr_weeknumbers", {
		weekNumbers: true,
		enableTime: true,
		dateFormat: "Y-m-d H:i"
	});
}

document.addEventListener("DOMContentLoaded", initFlatpickrFields);
document.addEventListener("livewire:navigated", () => {
	initFlatpickrFields();
	setTimeout(initFlatpickrFields, 100);
});