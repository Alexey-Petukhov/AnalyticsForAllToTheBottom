
$('.answer1').click(function () {

    $data = {"action": "getMostActiveCountry"};
    $.ajax({
        type: "POST",
        url: "/test/inc/func.php",
        data: $data,
        success: function (json_data) {
            console.log("ajax success");
            var data = JSON.parse(json_data);
            $(".forAnswer").html("") ;
            $("<div>", {class: "toAnswerDiv pre-scrollable"}).appendTo('.forAnswer');
            $('<table>', {class: "toAnswer table table-striped"}).appendTo('.toAnswerDiv');
            $('<thead>', {class: "headOfTable"}).appendTo('.toAnswer');
            $('<tr>', {id: "headRow"}).appendTo(".headOfTable");
            var html = "    <th scope=\"col\">#</th>\n" +
                "            <th scope=\"col\">Код страны</th>\n" +
                "            <th scope=\"col\">Страна</th>\n" +
                "            <th scope=\"col\">Количество действий на сайте</th>";
            $('#headRow').append(html);
            $('<tbody>', {class: "bodyOfTable"}).appendTo('.toAnswer');
            for (var key in data){
                var numOfRow, country_code, country, cnt_of_acts;
                numOfRow = parseInt(key) + 1;
                country_code = data[key].country_code;
                country = data[key].country;
                cnt_of_acts = data[key].cnt_of_acts;
                html = "<tr id=\"row_"+numOfRow+"\">\n" +
                    "       <th scope=\"row\">"+numOfRow+"</th>\n" +
                    "       <td>"+country_code+"</td>\n" +
                    "       <td>"+country+"</td>\n" +
                    "       <td>"+cnt_of_acts+"</td>\n" +
                    "   </tr>";
                $('.bodyOfTable').append(html);

            }
        },

        error: function (request, status, error) {
            console.log(status);
            console.log(error);
        }
    });
});

$('.answer2').click(function () {

    $data = {"action": "requestOFCategory"};
    $.ajax({
        type: "POST",
        url: "/test/inc/func.php",
        data: $data,
        success: function (json_data) {
            console.log("ajax success");
            var data = JSON.parse(json_data);
            $(".forAnswer").html("") ;
            for (var key in data){
                $("<div>", {id: "chartContainer"+key, class: "row", style: "height: 300px; width: 100%;"}).appendTo(".forAnswer");
                grapthFor2(data[key], key);
            }
        },
        error: function (request, status, error) {
            console.log(status);
            console.log(error);
        }
    });
});

function grapthFor2(data, key) {
    var dataPoints = [];
    var options = {
        title: {
            text: data["category"]
        },
        animationEnabled: true,
        data: [{
            type: "pie",
            startAngle: 40,
            toolTipContent: "<b>{label}</b>: {y}%",
            showInLegend: "true",
            legendText: "{label}",
            indexLabelFontSize: 16,
            indexLabel: "{label} - {y}%",
            dataPoints: dataPoints

        }]
    };
    function addData(top_contries, i) {
        var total_acts = 0;
        var used_acts = 0;
        // console.log(top_contries);
        for (var key in top_contries) {
            total_acts = total_acts + parseInt(top_contries[key].cnt_of_acts);
        }
        var total_percent = 100;
        for (var key in top_contries){
            var cnt_of_acts = parseInt(top_contries[key].cnt_of_acts);
            var percent_of_acts = ((parseInt(cnt_of_acts)/parseInt(total_acts))*100).toFixed(2);
            used_acts =  used_acts + cnt_of_acts;
            total_percent = total_percent-percent_of_acts;
            $label = top_contries[key].country + "(" + cnt_of_acts + ")";
            dataPoints.push({
                label: $label,
                y: parseFloat(percent_of_acts)
            });
            if (parseInt(key) > 7){
                percent_of_acts = total_percent;
                cnt_of_acts = total_acts - used_acts;
                $label = "Others countries (" + cnt_of_acts + ")";
                dataPoints.push({
                    label: $label,
                    y: parseFloat(percent_of_acts)
                });
                break;
            }

        }
        $("#chartContainer"+i).CanvasJSChart(options);
    }
    var top_contries = data["top_contries"];
    addData(top_contries, key);
}


$('.answer4').click(function () {
    $time = $("#inputTime").val();

    $data = {"time": $time, "action": "loadPerHour"};
    $.ajax({
        type: "POST",
        url: "/test/inc/func.php",
        data: $data,
        success: function (json_data) {
            console.log("ajax success");
            var data = JSON.parse(json_data);
            $(".forAnswer").html("") ;
            $("<div>", {id: "chartContainer", class: "row", style: "height: 300px; width: 100%;"}).appendTo(".forAnswer");
            graphFor4(data);
        },
        error: function (request, status, error) {
            console.log(status);
            console.log(error);
        }
    });
});
function graphFor4(data) {

    var dataPoints = [];

    var options =  {
        animationEnabled: true,
        zoomEnabled: true,
        theme: "light2",
        title: {
            text: "Запросы за час"
        },
        axisX: {
            valueFormatString: "DD.MM.YYYY HH:mm",
        },
        axisY: {
            title: "Число запросов",
            titleFontSize: 24,
            includeZero: false
        },
        data: [{
            type: "spline",
            yValueFormatString: "###",
            dataPoints: dataPoints
        }]
    };

    function addData(data) {
        for (var key in data){
            $str = data[key].date+"T"+data[key].hour+":00:00";
            $label = data[key].date + " за " + data[key].hour + " ч.";
            dataPoints.push({
                label: $label,
                x: new Date($str),
                y: parseInt(data[key].cnt)
            });
        }
        $("#chartContainer").CanvasJSChart(options);
    }
    addData(data);
}



