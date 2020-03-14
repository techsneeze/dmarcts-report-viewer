function reportData(id, url) {
  $.ajax({
    type: 'GET',
    url: url,
    timeout: 10000,
    success: function(data) {
      $('#reportData').show();
      $("#reportData").html(data);
      if (id != -1) {
        $('#'+id).css("background-color", "lightgrey");
      }
    },
    error: function (XMLHttpRequest, textStatus, errorThrown) {
      $("#reportData").html('Timeout contacting server..');
    }
  });
}

function printData() {
  var divToPrint=document.getElementById("reportData");
  newWin= window.open("");
  newWin.document.write(divToPrint.innerHTML);
  setTimeout(function(){
    newWin.print();
    newWin.close();
  }, 10);
}

function showXML(id) {
  $.ajax({
    type: 'GET',
    url: 'index.php?showxml='+id,
    timeout: 10000,
    success: function(data) {
      $('#showxml').show();
      $("#showxml").html(data);
    },
    error: function (XMLHttpRequest, textStatus, errorThrown) {
      $("#showxml").html('Timeout contacting server..');
    }
  });
}
