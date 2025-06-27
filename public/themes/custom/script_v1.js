$(document).ready(function() {
    $('.cs-pretty-checkbox').on('change', function() {
        const $checkbox = $(this);
        const $label = $checkbox.next('.cs-checkbox-label');

        if ($checkbox.is(':checked')) {
            // État "en cours"
            $label.addClass('cs-processing');

            const url = $(this).data('url');

            $.ajax({
               url: url,
               method: 'POST',
               success: function (data) {
                   if (data.success) {
                       $('.cs-message-unread').removeClass('cs-message-unread');
                       $label.addClass('cs-completed').removeClass('cs-processing');

                       $checkbox.prop('checked', false);
                       $label.removeClass('cs-completed');

                       location.reload();
                   }
               }
            });
        }
    });
});