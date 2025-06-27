$(document).ready(function() {
    $('.cs-pretty-checkbox').on('change', function() {
        const $checkbox = $(this);
        const $label = $checkbox.next('.cs-checkbox-label');

        if ($checkbox.is(':checked')) {
            // État "en cours"
            $label.addClass('cs-processing');

            // Simulation appel API (remplacer par un vrai appel)
            setTimeout(function() {
                // Action après succès
                $('.cs-message-unread').removeClass('cs-message-unread');

                // État "terminé"
                $label.addClass('cs-completed').removeClass('cs-processing');

                // Réinitialisation après 3s
                setTimeout(function() {
                    $checkbox.prop('checked', false);
                    $label.removeClass('cs-completed');
                }, 3000);
            }, 1500);
        }
    });
});