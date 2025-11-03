jQuery(function ($) {
    $(document).ready(function () {
        const chart = buildChartWidget(fr_chart_data.reviewed, fr_chart_data.unreviewed);

        //Expose chart globally for other scripts (like main.js)
        window.fr_PieChart = chart.chartInstance;
    });

    function buildChartWidget(reviewed, unreviewed) {

        var chartContentBox = $('.chart-content-box');
        var NoChartContentBox = $('.no-chart-content-box');
        
        if (reviewed > 0 | unreviewed > 0){
            chartContentBox.css('display', 'flex');
            chartContentBox.css('flex-direction', 'column');
            
            var ctx = document.getElementById("fr_piechart_canvas");
            let fr_PieChart = null;
            
            if (ctx) {
                var chartData = {
                    labels: ['Reviewed', 'Unreviewed'],
                    datasets: [{
                        data: [reviewed, unreviewed],
                        backgroundColor: ['#8238EF', '#ECE9FF'],
                        hoverOffset: 3,
                    }]
                };
    
                fr_PieChart = new Chart(ctx.getContext('2d'), {
                    type: "doughnut",
                    data: chartData,
                    options: {
                        cutout: "50%",
                        responsive: true,
                        plugins: {
                            legend: { display: false },
                        },
                        animation: {
                            animateRotate: true,
                            duration: 1000,
                        },
                    },
                });
            }
    
            const currentReviewed = parseInt(reviewed);
            const currentUnreviewed = parseInt(unreviewed);
            const total = currentReviewed + currentUnreviewed;
            const reviewedPct = Math.round((currentReviewed / total) * 100);
            const unreviewedPct = Math.round((currentUnreviewed / total) * 100);
            
            $('.legend-percentage.reviewed').text(reviewedPct + '%');
            $('.legend-percentage.unreviewed').text(unreviewedPct + '%');
            
            //Return the chart instance and percentages
            return { chartInstance: fr_PieChart };
        } else {
            NoChartContentBox.css('display', 'flex');
            return { chartInstance: null };
        }

    }
});
