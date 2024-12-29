import webbrowser

def ui(data):
    data_rows = ",\n".join([f"['{key}', {value}]" for key, value in data.items()])

    html_page = f"""
    <html>
      <head>
        <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
        <script type="text/javascript">
          google.charts.load('current', {{'packages':['corechart']}});
          google.charts.setOnLoadCallback(drawChart);
          function drawChart() {{
            var data = new google.visualization.DataTable();
            data.addColumn('string', 'Type de Consommation');
            data.addColumn('number', 'Total');
            data.addRows([
              {data_rows}
            ]);
            var options = {{
              'title':'Les valeurs des factures',
              'width':800,
              'height':600
            }};
            var chart = new google.visualization.PieChart(document.getElementById('chart_div'));
            chart.draw(data, options);
          }}
        </script>
      </head>
      <body>
        <div id="chart_div"></div>
      </body>
    </html>
    """
    # Enregistrer le HTML dans un fichier
    with open("chart.html", "w", encoding="utf-8") as file:
        file.write(html_page)
    print("Page HTML générée.")

    # Ouvrez automatiquement les fichiers HTML dans votre navigateur par défaut
    webbrowser.open("chart.html")