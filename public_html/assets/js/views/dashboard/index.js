$(function(){
    $('#tabDashboard a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
        initCharts();
    });

    //introJs().setOptions({ showProgress: true }).start();
});
