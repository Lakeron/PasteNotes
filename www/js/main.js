$(function(){
    // odesílání odkazů
    $('a.ajax').live('click', function (event) {
        event.preventDefault();
        $.get(this.href);
    });

    // odesílání formulářů
    $('form.ajax').live('submit', function (event) {
        event.preventDefault();
        $.post(this.action, $(this).serialize());
    });

    $('#switch a').click(function (e) {
        e.preventDefault();
        $(this).tab('show');
    })
});

function debug(object) {
    console.log(object);
}