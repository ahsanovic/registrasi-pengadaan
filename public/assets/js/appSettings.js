// App settings default
let appSettings = {
	appTheme: 'light',
	appSidebar: 'full',
	appColor: 'blue',
};

// Update settings
function setAppSettings(newSettings = {}) {
	appSettings = {
		...appSettings,
		...newSettings
	};
	applySettings();
}

// Apply settings to DOM
function applySettings() {
	document.documentElement.setAttribute("data-bs-theme", appSettings.appTheme);

	if (window.innerWidth >= 1191) {
		document.documentElement.setAttribute("data-app-sidebar", appSettings.appSidebar);
	}

	document.documentElement.setAttribute("data-color-theme", appSettings.appColor);
}

// Keep root theme/color attributes consistent on first load
// and after Livewire SPA-style navigation.
const initializeAppSettings = () => {
	const currentSidebarAttr = document.documentElement.getAttribute("data-app-sidebar");
	if (currentSidebarAttr) {
		appSettings.appSidebar = currentSidebarAttr.startsWith("mini") ? "mini" : "full";
	}

	applySettings();
};

// Initialize
document.addEventListener("DOMContentLoaded", initializeAppSettings);
document.addEventListener("livewire:initialized", initializeAppSettings);
document.addEventListener("livewire:navigated", initializeAppSettings);
window.setAppSettings = setAppSettings;