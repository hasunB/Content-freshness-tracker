jQuery(function ($) {
    $(document).ready(function () {
       applyTimeSelectRulesAndValidate();
    });

    function applyTimeSelectRulesAndValidate() {
        const $valueInput = $('#stale_after_value');
        const $unitInputSelect = $('#stale_after_unit');

        function applyRulesAndValidate() {
            const unit = $unitInputSelect.val();
            let min = 1;
            let max = null;

            switch (unit) {
                case 'minutes':
                    min = 5;
                    max = 59;
                    break;
                case 'hours':
                    min = 1;
                    max = 23;
                    break;
                case 'days':
                    min = 1;
                    max = 29;
                    break;
                case 'months':
                    min = 1;
                    max = 12;
                    break;
            }
            // Set the new min/max properties on the number input
            $valueInput.prop('min', min);
            if (max !== null) {
            $valueInput.attr('max', max);
            } else {
            $valueInput.removeAttr('max');
            }

            // Clamp the current value to be within the new valid rar
            let currentValue = parseInt($valueInput.val(), 10);
            if (currentValue < min) {
                $valueInput.val(min);
            }

            if (max !== null && currentValue > max) {
                $valueInput.val(max);
            }
        }
        
        // Add event listener for when the user changes the unit
        $unitInputSelect.on('change', function() {
            applyRulesAndValidate();
            $valueInput.val($valueInput.prop('min'));
        });
        
        // On initial page load, apply the rules based on the saved
        applyRulesAndValidate();
    }
});
