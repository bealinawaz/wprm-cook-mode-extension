(function($) {
    "use strict";
    
    let wakeLock = null;
    
    $(document).ready(function() {
        initCookMode();
    });
    
    function initCookMode() {
        const checkbox = $("#wprm-cook-mode-checkbox");
        const statusElement = $("#wprm-cook-mode-status");
        
        if (checkbox.length === 0) {
            return;
        }
        
        // Check if Wake Lock API is supported
        if (!("wakeLock" in navigator)) {
            statusElement.text(wprm_cook_mode.error_text).addClass("error").show();
            checkbox.prop("disabled", true);
            return;
        }
        
        checkbox.on("change", function() {
            if (this.checked) {
                enableCookMode();
            } else {
                disableCookMode();
            }
        });
        
        // Handle page visibility changes
        document.addEventListener("visibilitychange", function() {
            if (wakeLock !== null && document.visibilityState === "visible") {
                enableCookMode();
            }
        });
    }
    
    async function enableCookMode() {
        const statusElement = $("#wprm-cook-mode-status");
        
        try {
            wakeLock = await navigator.wakeLock.request("screen");
            statusElement.text(wprm_cook_mode.active_text)
                         .removeClass("error")
                         .addClass("active")
                         .show();
            
            wakeLock.addEventListener("release", () => {
                wakeLock = null;
                if ($("#wprm-cook-mode-checkbox").is(":checked")) {
                    statusElement.text(wprm_cook_mode.inactive_text)
                               .removeClass("active")
                               .show();
                }
            });
            
        } catch (err) {
            console.error("Failed to enable cook mode:", err);
            statusElement.text(wprm_cook_mode.error_text).addClass("error").show();
            $("#wprm-cook-mode-checkbox").prop("checked", false);
        }
    }
    
    function disableCookMode() {
        const statusElement = $("#wprm-cook-mode-status");
        
        if (wakeLock !== null) {
            wakeLock.release();
            wakeLock = null;
        }
        
        statusElement.hide().removeClass("active error");
    }
    
    // Auto-disable on page unload
    $(window).on("beforeunload", function() {
        disableCookMode();
    });
    
})(jQuery);