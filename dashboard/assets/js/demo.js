//Chart

// Function to format date to "MMM DD"
function formatDate(dateString) {
  const date = new Date(dateString);
  return date.toLocaleDateString("en-US", { month: "short", day: "2-digit" });
}

// Extract formatted labels (dates) and data for workers, tokens, and accounts
const labels = dailyData.map((item) => formatDate(item.date));
const workersData = dailyData.map((item) => item.workers_count);
const tokensData = dailyData.map((item) => item.tokens_count);
const accountsData = dailyData.map((item) => item.account_count); // Account created data

var ctx = document.getElementById("statisticsChart").getContext("2d");

var statisticsChart = new Chart(ctx, {
  type: "line",
  data: {
    labels: labels,
    datasets: [
      {
        label: "Workers Present",
        borderColor: "#fdaf4b",
        pointBackgroundColor: "rgba(253, 175, 75, 0.6)",
        pointRadius: 0,
        backgroundColor: "rgba(253, 175, 75, 0.4)",
        legendColor: "#fdaf4b",
        fill: true,
        borderWidth: 2,
        data: workersData,
      },
      {
        label: "Account Created",
        borderColor: "#f3545d",
        pointBackgroundColor: "rgba(243, 84, 93, 0.6)",
        pointRadius: 0,
        backgroundColor: "rgba(243, 84, 93, 0.4)",
        legendColor: "#f3545d",
        fill: true,
        borderWidth: 2,
        data: accountsData,
      },
      {
        label: "Live Tokens",
        borderColor: "#177dff",
        pointBackgroundColor: "rgba(23, 125, 255, 0.6)",
        pointRadius: 0,
        backgroundColor: "rgba(23, 125, 255, 0.4)",
        legendColor: "#177dff",
        fill: true,
        borderWidth: 2,
        data: tokensData,
      },
    ],
  },
  options: {
    responsive: true,
    maintainAspectRatio: false,
    legend: {
      display: true,
      position: "bottom",
    },
    tooltips: {
      bodySpacing: 4,
      mode: "nearest",
      intersect: 0,
      position: "nearest",
      xPadding: 10,
      yPadding: 10,
      caretPadding: 10,
    },
    elements: {
      point: {
        radius: 5, // Makes points more visible
      },
    },
    layout: {
      padding: { left: 5, right: 5, top: 15, bottom: 15 },
    },
    scales: {
      yAxes: [
        {
          ticks: {
            fontStyle: "500",
            beginAtZero: false,
            maxTicksLimit: 5,
            padding: 10,
          },
          gridLines: {
            drawTicks: false,
            display: false,
          },
        },
      ],
      xAxes: [
        {
          gridLines: {
            zeroLineColor: "transparent",
          },
          ticks: {
            padding: 10,
            fontStyle: "500",
          },
        },
      ],
    },
    legendCallback: function (chart) {
      var text = [];
      text.push('<ul class="' + chart.id + '-legend html-legend">');
      for (var i = 0; i < chart.data.datasets.length; i++) {
        text.push(
          '<li><span style="background-color:' +
            chart.data.datasets[i].legendColor +
            '"></span>'
        );
        if (chart.data.datasets[i].label) {
          text.push(chart.data.datasets[i].label);
        }
        text.push("</li>");
      }
      text.push("</ul>");
      return text.join("");
    },
  },
});

// Multiple Bar Chart

// Safeguard against missing or invalid MonthlyData
if (!Array.isArray(MonthlyData) || MonthlyData.length === 0) {
  console.error("Error: MonthlyData is missing or not an array.");
  MonthlyData = []; // Fallback to empty array
}

// Ensure proper structure and data in MonthlyData
const monthlyLabels = MonthlyData.map((item) => {
  if (!item.month) {
    console.error("Error: Month is missing in data", item);
    return "Unknown Month"; // Fallback to a default label
  }
  return item.month;
});

const monthlyTokensData = MonthlyData.map((item) => item.tokens_count || 0); // Fallback to 0 if missing
const monthlyAccountsData = MonthlyData.map((item) => item.accounts_count || 0); // Fallback to 0 if missing

var multipleBarChart = document
  .getElementById("multipleBarChart")
  .getContext("2d");

var myMultipleBarChart = new Chart(multipleBarChart, {
  type: "bar",
  data: {
    labels: monthlyLabels, // Use the formatted month labels
    datasets: [
      {
        label: "Live Tokens",
        backgroundColor: "#59d05d",
        borderColor: "#59d05d",
        data: monthlyTokensData, // Tokens data for each month
      },
      {
        label: "Accounts",
        backgroundColor: "#177dff",
        borderColor: "#177dff",
        data: monthlyAccountsData, // Accounts data for each month
      },
    ],
  },
  options: {
    responsive: true,
    maintainAspectRatio: false,
    legend: {
      position: "bottom",
    },
    title: {
      display: true,
      text: "Monthly Statistics", // You can update the title as needed
    },
    tooltips: {
      mode: "index",
      intersect: false,
    },
    scales: {
      xAxes: [
        {
          stacked: true,
        },
      ],
      yAxes: [
        {
          stacked: true,
        },
      ],
    },
  },
});
