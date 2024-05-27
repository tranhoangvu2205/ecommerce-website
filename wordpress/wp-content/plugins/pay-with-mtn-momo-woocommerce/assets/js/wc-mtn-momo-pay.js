(function ($, window, document) {
	"use strict";

	function getCurrentStep() {
		var step = parseInt($("[data-momopay-step]").attr("data-momopay-step"));

		return !step ? 1 : step;
	}

	function setSaveBtnText() {
		$("button[name=save]:last").each(function () {
			$(this).show();
		});

		if (location.search.indexOf("section=momopay") == -1) return;

		var step = getCurrentStep();
		var text = "";

		switch (step) {
			case 1:
				text = "Continue";
				break;
			case 2:
				text = "Save";
				break;
			case 3:
				text = "Reset settings";
				break;
		}

		$("button[name=save]:last").each(function () {
			$(this).text(text);
		});
	}

	function updValidateBox(mode) {
		if (mode == 1) {
			$("[data-momoadv-box=live]").addClass("hide");
			$("[data-momoadv-box=sandbox]").removeClass("hide");

			return;
		}

		$("[data-momoadv-box=live]").removeClass("hide");
		$("[data-momoadv-box=sandbox]").addClass("hide");
	}

	$(document).ready(function () {
		$("body").on("change", "#woocommerce_momopay_mode", function () {
			updValidateBox(this.value);
		});
		setSaveBtnText();

		$("body").on("submit", "#mainform", function () {
			if (getCurrentStep() == 3) {
				var result = confirm("Are you sure you want to reset your settings?");
				if (!result) return false;
			}
		});
	});
})(jQuery, window, document);
