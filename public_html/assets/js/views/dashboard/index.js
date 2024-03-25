$(function(){
    $('#tabDashboard a[data-bs-toggle="tab"]').on('shown.bs.tab', function (e) {
        initCharts();
    });

    setTimeout(() => {

        /*introJs().setOptions({
            nextLabel: 'Próximo',
            prevLabel: 'Anterior',
            doneLabel: 'Finalizar',
            showProgress: true,
            exitOnOverlayClick: false,
            keyboardNavigation: true,
            steps: [
                {
                    title: 'Welcome',
                    intro: 'Hello World! 👋'
                },
                {
                    element: document.querySelector('.navbar .navbar-menu-wrapper ul.navbar-nav li:nth-child(1)'),
                    intro: 'Visualize e realize atendientos ao time de suporte',
                    position: 'left'
                },
                {
                    element: document.querySelector('[aria-labelledby="UserDropdown"] a.dropdown-item:nth-child(2)'),
                    intro: 'Visualize seu perfil para realizar alterações em seu usuário e tema.',
                    position: 'left'
                }
            ]
        }).onbeforechange(function () {
            if (this._currentStep === 2) {
                $('#UserDropdown').dropdown('toggle')
            }
        }).start();*/

    }, 500);
});
