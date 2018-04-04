$(document).ready(function () {
    if (window.location.hash == "") window.location.hash = "/home"

    // Init side nav
    $('.sidenav').sidenav({
        draggable: true
    });

    // Init  collapsible
    $('.collapsible').collapsible();

    // Init tooltip
    $('.tooltipped').tooltip()

    // Switch page at load
    switchPage();

    // Switch page on hash change
    window.onhashchange = function () {
        switchPage();
    }

    function switchPage() {
        var hash = window.location.hash.replace(/^#\//, '' ),
            hashSplitted = hash.split('/')

        switch (hashSplitted[0]) {

            case 'reference':
                $('.page:not(#reference)').fadeOut(200, function () {
                    $('#reference').fadeIn(200)
                })

                switchReference(hashSplitted)
                break

            default:
                $('.page:not(#'+ hashSplitted[0] +')').fadeOut(200, function () {
                    $('#'+ hashSplitted[0]).fadeIn(200)
                })

        }
    }

    function switchReference(hashSplitted) {

        $('.reference:not(.'+ hashSplitted[1] +')').fadeOut(200, function () {
            $('.reference.'+hashSplitted[1]).fadeIn(200)
        })

        if (hashSplitted.length > 2) {
            $('.route:not(.'+ hashSplitted[2] +')').fadeOut(200).promise().done(function() {
                $('.route.'+hashSplitted[2]).fadeIn(200)
            })
        }

    }

    // Show all json responses
    getExamplesResponses();

    function getExamplesResponses() {
        var ref = $('.btn-ref')

        // Loop on all references
        for (var i = 0; i < ref.length; i++) {
            $.ajax({
                url: '/doc/'+ ref.eq(i).data('ref') +'.json',
                method: 'GET',
                success: data => {

                    // Loop on all routes
                    data.forEach(route => {
                        var success = route.response["body-success"]
                        var error = route.response["body-error"]

                        // Set in success div the json of success
                        var successDiv = $('.json.success.'+ route.title.replace(/ /g, '-').toLowerCase() +' code')
                        successDiv.html(JSON.stringify(success, undefined, 2));

                        // Set in success divs the json of errors
                        var errorDiv = $('.json.error.'+ route.title.replace(/ /g, '-').toLowerCase() +' code')
                        for (var n = 0; n < errorDiv.length; n++) {
                            errorDiv.eq(n).html(JSON.stringify(error[n], undefined, 2));
                        }
                    })

                    // Init prism highlight
                    Prism.highlightAll();
                }
            })
        }
    }
})