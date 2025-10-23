// jQuery(function ($) {
//     $(document).ready(function () {
//         buildChartWidget(fr_chart_data.reviewed, fr_chart_data.unreviewed);
//     });

//     function buildChartWidget(reviewed, unreviewed) {
//         var ctx = document.getElementById("fr_piechart_canvas");
//         if (ctx) {
//             var chartData = {
//                 labels: ['Reviewed', 'Unreviewed'],
//                 datasets: [{
//                     data: [reviewed, unreviewed],
//                     backgroundColor: ['#8238EF', '#ECE9FF'],
//                     hoverOffset: 3,
//                 }]
//             };

//             var fr_PieChart = new Chart(ctx.getContext('2d'), {
//                 type: "doughnut",
//                 data: chartData,
//                 options: {
//                     cutout: "50%",
//                     responsive: true,
//                     plugins: {
//                         legend: { display: false },
//                     },
//                     animation: {
//                         animateRotate: true,
//                         duration: 1000,
//                     },
//                 },
//             });
//         }

//         var currentReviewed = parseInt(reviewed);
//         var currentUnreviewed = parseInt(unreviewed);

//         var legendReviewed = $('.legend-percentage.reviewed');
//         var legendUnreviewed = $('.legend-percentage.unreviewed');

//         var totalPosts = currentReviewed + currentUnreviewed;
//         var newReviewedPercentage = Math.round((currentReviewed / totalPosts) * 100);
//         var newUnreviewedPercentage = Math.round((currentUnreviewed / totalPosts) * 100);

//         legendReviewed.text(newReviewedPercentage + '%');
//         legendUnreviewed.text(newUnreviewedPercentage + '%');
//     }

// });

jQuery(function ($) {
    $(document).ready(function () {
        const chart = buildChartWidget(fr_chart_data.reviewed, fr_chart_data.unreviewed);

        //Expose chart globally for other scripts (like main.js)
        window.fr_PieChart = chart.chartInstance;
    });

    function buildChartWidget(reviewed, unreviewed) {
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
    }
});
