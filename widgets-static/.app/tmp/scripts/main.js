'use strict';

$(document).ready(function () {
    $('.load').each(function (key, val) {
        console.log(val);
        $this = $(val);
        path = 'http://localhost:9000/' + $this.data('path').trim();
        $amount = $this.data('amount');

        console.log(path);
        for (i = 0; i < $amount; i++) {
            $.get(path, function (data) {
                $($(data).find('#loadcontent')).appendTo($this);
            }, 'html');
        }
    });
});