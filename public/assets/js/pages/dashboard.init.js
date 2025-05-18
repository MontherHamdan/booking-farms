// dashboard.init.js
!(function (e) {
    "use strict";
    function a() {
        this.$realData = [];
    }
    a.prototype.createBarChart = function (element, data, xkey, ykeys, labels, barColors) {
        Morris.Bar({
            element: element,
            data: data,
            xkey: xkey,
            ykeys: ykeys,
            labels: labels,
            hideHover: "auto",
            resize: true,
            gridLineColor: "rgba(173, 181, 189, 0.1)",
            barSizeRatio: 0.2,
            dataLabels: false,
            barColors: barColors,
        });
    };

    a.prototype.createLineChart = function (element, data, xkey, ykeys, labels, fillOpacity, pointFillColors, pointStrokeColors, lineColors) {
        Morris.Line({
            element: element,
            data: data,
            xkey: xkey,
            ykeys: ykeys,
            labels: labels,
            fillOpacity: fillOpacity,
            pointFillColors: pointFillColors,
            pointStrokeColors: pointStrokeColors,
            behaveLikeLine: true,
            gridLineColor: "rgba(173, 181, 189, 0.1)",
            hideHover: "auto",
            resize: true,
            pointSize: 0,
            dataLabels: false,
            lineColors: lineColors,
        });
    };

    a.prototype.createDonutChart = function (element, data, colors) {
        Morris.Donut({
            element: element,
            data: data,
            resize: true,
            colors: colors,
            backgroundColor: "transparent",
        });
    };

    a.prototype.init = function () {
        // Clear existing chart elements
        e("#morris-bar-example").empty();
        e("#morris-line-example").empty();
        e("#morris-donut-example").empty();

        // Create Bar Chart (static example)
        this.createBarChart(
            "morris-bar-example",
            [
                { y: "2010", a: 75 },
                { y: "2011", a: 42 },
                { y: "2012", a: 75 },
                { y: "2013", a: 38 },
                { y: "2014", a: 19 },
                { y: "2015", a: 93 },
            ],
            "y",
            ["a"],
            ["Statistics"],
            ["#188ae2"]
        );

        // Create Line Chart (static example)
        this.createLineChart(
            "morris-line-example",
            [
                { y: "2008", a: 50, b: 0 },
                { y: "2009", a: 75, b: 50 },
                { y: "2010", a: 30, b: 80 },
                { y: "2011", a: 50, b: 50 },
                { y: "2012", a: 75, b: 10 },
                { y: "2013", a: 50, b: 40 },
                { y: "2014", a: 75, b: 50 },
                { y: "2015", a: 100, b: 70 },
            ],
            "y",
            ["a", "b"],
            ["Series A", "Series B"],
            "0.9",
            ["#ffffff"],
            ["#999999"],
            ["#10c469", "#188ae2"]
        );

        // Create Donut Chart using dynamic data if available, otherwise fallback to static data
        if (typeof window.dynamicDonutData !== "undefined" && window.dynamicDonutData.length > 0) {
            this.createDonutChart(
                "morris-donut-example",
                window.dynamicDonutData,
                ["#ff8acc", "#5b69bc", "#35b8e0", "#10c469", "#f9c851"]
            );
        } else {
            this.createDonutChart(
                "morris-donut-example",
                [
                    { label: "Download Sales", value: 12 },
                    { label: "In-Store Sales", value: 30 },
                    { label: "Mail-Order Sales", value: 20 },
                ],
                ["#ff8acc", "#5b69bc", "#35b8e0"]
            );
        }
    };

    e.Dashboard1 = new a();
    e.Dashboard1.Constructor = a;
})(window.jQuery),
(function (a) {
    "use strict";
    a.Dashboard1.init();
    window.addEventListener("adminto.setBoxed", function (e) {
        a.Dashboard1.init();
    });
    window.addEventListener("adminto.setFluid", function (e) {
        a.Dashboard1.init();
    });
})(window.jQuery);
